
{include file="public/header" /}

<style>
    .grid-box {
        display: flex;
        flex-wrap: nowrap;
        gap: 0; /* 去掉间隔，靠边框分割 */
    }

    .grid-item {
        flex: 1;
        min-width: 0;
        border-right: 1px solid #ddd;
        border-bottom: 1px solid #ddd;
        padding: 5px;
        box-sizing: border-box;
    }

    /* 去掉最后一个右边框 */
    .grid-item:last-child {
        border-right: none;
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

    .bg-pink {
        background-color: #FFE4EC;
    }

    .layui-table{
        display: inline-table;
        vertical-align: top;
    }

    .layui-table + div{
        display: inline-block;
        vertical-align: top;
        margin-left: 20px;
    }

    .layui-table + div img{
        width: 120px;
    }
</style>
<!-- 主体内容 -->
<div class="layui-body" id="LAY_app_body">
    <div class="right">
        <a href="{:url('Replenish/index')}" class="layui-btn layui-btn-danger layui-btn-sm fr"><i class="layui-icon">&#xe603;</i>返回上一页</a>
        <div class="title">列表(<span class="red">*只能启用一个，启用时其他项会默认禁用</span>)</div>

        <div class="layui-form">
            <div>
                {if condition="!in_array('Replenish', $role)"}
                <a href="{:url('user_add', ['id' => $id])}" class="layui-btn">添加</a>
                {/if}
                {if condition="in_array('Replenish', $role)"}
                <a href="{:url('user_add_addition', ['id' => $id])}" class="layui-btn">补充</a>
                <button data-id="{$id}" class="layui-btn layui-btn-normal ml0" lay-submit lay-filter="Balance">合并</button>
                {/if}
            </div>
            {if condition="in_array('Replenish', $role)"}
            <table class="layui-table w720" lay-size="sm">
                <colgroup>
                    <col>
                    <col>
                    <col>
                    <col>
                    <col>
                </colgroup>
                <thead>
                <tr>
                    <th class="tc">工厂下单未包数</th>
                    <th class="tc">工厂待出</th>
                    <th class="tc">海上在途</th>
                    <th class="tc">海外仓库存</th>
                    <th class="tc">账面总库存</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td class="tc">
                        <input type="text" class="layui-input unproduced" name="unproduced">
                    </td>
                    <td class="tc">
                        <input type="text" class="layui-input pendingship" name="pendingship">
                    </td>
                    <td class="tc on-way">
                        {:$leOnWay + $wydOnWay + $lcOnWay}
                    </td>
                    <td class="tc store">
                        {$leStore + $wydStore + $lcStore}
                    </td>
                    <td class="tc all-store"></td>
                </tr>
                </tbody>
            </table>
            <div><img src="{:getImgUrlByWarehouseSku($plan['warehouse_sku'])}" alt=""></div>
            {/if}
            <table class="layui-table" lay-size="sm">
                <colgroup>
                    <col>
                    <col>
                    <col>
                    <col>
                    <col width="180">
                    <col width="100">
                    {if condition="in_array('Replenish', $role)"}
                    <col width="80">
                    {/if}
                </colgroup>
                <thead>
                <tr>
                    <th class="tc">所属平台</th>
                    <th class="tc">仓库SKU</th>
                    <th class="tc">日销预估</th>
                    <th class="tc">是否启用</th>
                    <th class="tc">添加时间</th>
                    <th class="tc">运营</th>
                    {if condition="in_array('Replenish', $role)"}
                    <th class="tc">操作</th>
                    {/if}
                </tr>
                </thead>
                <tbody>
                {foreach name="list" item="v"}
                <tr class="{if condition='$v.is_reject eq 1'}bg-pink{/if}">
                    <td class="tc">{$v.platform}</td>
                    <td class="tc">{$v.plan.warehouse_sku}</td>
                    <td class="tc">
                        {if condition="$v.json_detail"}
                        <div class="grid-box">
                        {foreach name="v.json_detail|json_decode=###,true" key="k" item="item"}
                            <div class="grid-item">
                                <div class="key">{$k}</div>
                                <div class="value">{$item}</div>
                            </div>
                        {/foreach}
                        </div>
                        {else/}
                        合计数量：{$v.amount}
                        {/if}
                    </td>
                    <td class="tc">
                        <input type="checkbox" class="h30" name="status" value="{$v.id}" lay-skin="switch" lay-text="是|否" lay-filter="formLock" {if condition="$v.status eq 1"}checked{/if}>
                    </td>
                    <td class="tc">{$v.created_time}</td>
                    <td class="tc">{$v.user.nickname}</td>
                    {if condition="in_array('Replenish', $role)"}
                    <td class="tc">
                        <button data-id="{$v.id}" class="layui-btn layui-btn-sm layui-btn-normal ml0" lay-submit lay-filter="Reject">驳回</button>
                    </td>
                    {/if}
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

        let unproduced = 0,
            pendingship = 0,
            onWay = 0,
            store = 0;
        $('.unproduced').on('input', function () {
            unproduced = Number($(this).val());
            resetAllStore();
        });

        $('.pendingship').on('input', function () {
            pendingship = Number($(this).val());
            resetAllStore();
        });

        function resetAllStore(){
            onWay = Number($('.on-way').html());
            store = Number($('.store').html());
            $('.all-store').html(unproduced + pendingship + onWay + store);
        }

        resetAllStore();

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

        // 合并
        form.on('submit(Balance)', function(data){
            const text = $(this).text(),
                button = $(this),
                id = $(this).data('id');
            const field = data.field;
                field.id = id;
                field.onWay = onWay;
                field.store = store;
            layer.confirm('确定合并吗？',{icon:3,closeBtn:0,title:false,btnAlign:'c'},function(){
                $('button').attr('disabled',true);
                button.text('请稍候...');
                $.ajax({
                    type:'POST',url:"{:url('balance')}",data:field,dataType:'json',
                    success:function(data){
                        if(data.code === 1){
                            layer.alert(data.msg,{icon:1,closeBtn:0,title:false,btnAlign:'c'},function(){
                                location.href = "{:url('detail', ['id' => $id])}";
                            });
                        }else{
                            layer.alert(data.msg,{icon:2,closeBtn:0,title:false,btnAlign:'c'},function(){
                                layer.closeAll();
                                $('button').attr('disabled',false);
                                button.text(text);
                            });
                        }
                    }
                });
            });
        });

        // 驳回
        form.on('submit(Reject)', function(data){
            const text = $(this).text(),
                button = $(this),
                id = $(this).data('id');
            layer.confirm('确定驳回吗？',{icon:3,closeBtn:0,title:false,btnAlign:'c'},function(){
                $('button').attr('disabled',true);
                button.text('请稍候...');
                $.ajax({
                    type:'POST',url:"{:url('reject')}",data:{id:id},dataType:'json',
                    success:function(data){
                        if(data.code === 1){
                            layer.alert(data.msg,{icon:1,closeBtn:0,title:false,btnAlign:'c'},function(){
                                location.reload();
                            });
                        }else{
                            layer.alert(data.msg,{icon:2,closeBtn:0,title:false,btnAlign:'c'},function(){
                                layer.closeAll();
                                $('button').attr('disabled',false);
                                button.text(text);
                            });
                        }
                    }
                });
            });
        });
    });
</script>

{include file="public/footer" /}
