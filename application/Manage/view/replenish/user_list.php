
{include file="public/header" /}

<style>
    .grid-box{
        display: grid;
        grid-template-columns: repeat(3,1fr);
        gap: 10px;
    }

    .grid-item{
        border: 1px solid #ddd;
        padding: 10px;
        text-align: center;

        display: flex;
        flex-direction: column;
    }

    .key{
        font-weight: bold;
        color: #666;
        margin-bottom: 6px;
    }

    .value{
        color: #333;
        font-size: 16px;
    }
</style>
<!-- 主体内容 -->
<div class="layui-body" id="LAY_app_body">
    <div class="right">
        <a href="{:url('Replenish/index')}" class="layui-btn layui-btn-danger layui-btn-sm fr"><i class="layui-icon">&#xe603;</i>返回上一页</a>
        <div class="title">列表(<span class="red">*只能启用一个，启用时其他项会默认禁用</span>)</div>

        <div class="layui-form">
            {if condition="!in_array('Replenish', $role)"}
            <a href="{:url('user_add', ['id' => $id])}" class="layui-btn">添加</a>
            {/if}
            {if condition="in_array('Replenish', $role)"}
            <a href="{:url('user_add_addition', ['id' => $id])}" class="layui-btn">补充</a>
            <span class="total">补货合计：{$sum}</span>
            {/if}
            <table class="layui-table" lay-size="sm">
                <colgroup>
                    <col>
                    <col>
                    <col>
                    <col>
                    <col>
                    <col>
                    <col width="180">
                    <col width="100">
                </colgroup>
                <thead>
                <tr>
                    <th class="tc">所属平台</th>
                    <th class="tc">仓库SKU</th>
                    <th class="tc">补货合计</th>
                    <th class="tc">日销预估</th>
                    <th class="tc">实际日销</th>
                    <th class="tc">是否启用</th>
                    <th class="tc">添加时间</th>
                    <th class="tc">运营</th>
                </tr>
                </thead>
                <tbody>
                {foreach name="list" item="v"}
                <tr>
                    <td class="tc">{$v.platform}</td>
                    <td class="tc">{$v.plan.warehouse_sku}</td>
                    <td class="tc">{$v.amount}</td>
                    <td class="tc">
                        <div class="grid-box">
                        {foreach name="v.json_detail|json_decode=###,true" key="k" item="item"}
                            <div class="grid-item">
                                <div class="key">{$k}</div>
                                <div class="value">{$item}</div>
                            </div>
                        {/foreach}
                        </div>
                    </td>
                    <td class="tc">
                        {foreach name="actual_list[$v['id']]" key="k_month" item="v_item"}
                        <div>{$k_month} : {$v_item|round=###, 2}</div>
                        {/foreach}
                    </td>
                    <td class="tc">
                        <input type="checkbox" class="h30" name="status" value="{$v.id}" lay-skin="switch" lay-text="是|否" lay-filter="formLock" {if condition="$v.status eq 1"}checked{/if}>
                    </td>
                    <td class="tc">{$v.created_time}</td>
                    <td class="tc">{$v.user.nickname}</td>
                </tr>
                {/foreach}
                </tbody>
            </table>
            {$list->render()}
        </div>

    </div>
</div>
<script>
    layui.use(['form', 'jquery'], function(){
        var $ = layui.jquery,
            form = layui.form;

        // 状态
        form.on('switch(formLock)', function(data){
            // 选中所有复选框
            let boolean = $(this).prop('checked');
            $('input[type="checkbox"]').prop('checked', false);
            $(this).prop('checked', boolean);
            form.render('checkbox'); // 只重新渲染checkbox类型
            $.ajax({
                type:'POST',url:"{:url('user_status')}",data:{id:data.value,type:'look'},dataType:'json',
                success:function(data){
                    if(data.code == 0){
                        layer.alert(data.msg,{icon:2,closeBtn:0,title:false,btnAlign:'c'},function(){
                            location.reload();
                        });
                    }
                }
            });
        });
    });
</script>

{include file="public/footer" /}
