
{include file="public/header" /}

<!-- 主体内容 -->
<div class="layui-body" id="LAY_app_body">
    <div class="right">
        <div class="title">海外仓索赔登记列表</div>
        <form class="layui-form search-form" method="get">
            <div class="layui-inline w200">
                <input type="text" class="layui-input" name="keyword" value="{$keyword}" placeholder="跟踪号/订单号/SKU">
            </div>
            <div class="layui-inline w120">
                <select name="state" lay-verify="">
                    <option value="">索赔状态</option>
                    <option value="0" {if condition="$state eq '0'"}selected{/if}>未完成</option>
                    <option value="1" {if condition="$state eq '1'"}selected{/if}>已完成</option>
                </select>
            </div>
            <div class="layui-inline">
                <button class="layui-btn" lay-submit lay-filter="Search"><i class="layui-icon">&#xe615;</i> 查询</button>
            </div>
            <div class="layui-inline">
                <a class="layui-btn layui-btn-normal" href="{:url('index')}"><i class="layui-icon">&#xe621;</i> 重置</a>
            </div>
        </form>

        <div class="layui-form">
            <a class="layui-btn" href="{:url('add')}">添加</a>
            <table class="layui-table" lay-size="sm">
                <colgroup>
                    <col width="50">
                    <col>
                    <col>
                    <col>
                    <col>
                    <col>
                    <col>
                    <col>
                    <col>
                    <col>
                    {if condition="$user.super eq 1 or $user.manage eq 1"}
                    <col>
                    {/if}
                    <col>
                    <col width="150">
                </colgroup>
                <thead>
                <tr>
                    <th class="tc">ID</th>
                    <th>跟踪号</th>
                    <th>订单号</th>
                    <th>仓库SKU</th>
                    <th class="tc">索赔金额</th>
                    <th class="tc">到账金额</th>
                    <th class="tc">索赔类型</th>
                    <th>所属仓库</th>
                    <th class="tc">添加时间</th>
                    <th class="tc">修改时间</th>
                    {if condition="$user.super eq 1 or $user.manage eq 1"}
                    <th>索赔人</th>
                    {/if}
                    <th class="tc">状态</th>
                    <th class="tc">操作</th>
                </tr>
                </thead>
                <tbody>
                {foreach name="list" item="v"}
                <tr>
                    <td class="tc">{$v.id}</td>
                    <td>{$v.shipping_method_no}</td>
                    <td>{$v.reference_no}</td>
                    <td>{$v.product_sku}</td>
                    <td class="tr">{$v.claimant_amount}</td>
                    <td class="tr">{$v.received_amount}</td>
                    <td class="tc">
                        {if condition="$v.claimant_type eq 1"}
                            库内丢失
                        {elseif condition="$v.claimant_type eq 2"/}
                            妥投未签收
                        {elseif condition="$v.claimant_type eq 3"/}
                            暴力运输（产品破损）
                        {elseif condition="$v.claimant_type eq 4"/}
                            途中丢包
                        {/if}
                    </td>
                    <td>{$v.warehouse.name}</td>
                    <td class="tc">{$v.created_at}</td>
                    <td class="tc">{$v.updated_at}</td>
                    {if condition="$user.super eq 1 or $user.manage eq 1"}
                    <td>{$v.admin.nickname}</td>
                    {/if}
                    <td class="tc">
                        {if condition="$v.state eq 1"}
                        <span class="green">已完成</span>
                        {else/}
                        <span class="red">未完成</span>
                        {/if}
                    </td>
                    <td class="tc">
                        <a href="{:url('edit', ['id' => $v.id])}" class="layui-btn layui-btn-normal layui-btn-sm">编辑</a>
                        {if condition="$user.super eq 1"}
                            <button data-id="{$v.id}" class="layui-btn layui-btn-sm layui-btn-danger ml0" lay-submit lay-filter="Detele">删除</button>
                        {/if}
                    </td>
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
        let $ = layui.jquery,
            form = layui.form;

        // 删除
        form.on('submit(Detele)', function(data){
            let text = $(this).text(),
                button = $(this),
                id = $(this).data('id');
            layer.confirm('确定删除吗？',{icon:3,closeBtn:0,title:false,btnAlign:'c'},function(){
                $('button').attr('disabled',true);
                button.text('请稍候...');
                axios.post("{:url('delete')}", {id:id})
                    .then(function (response) {
                        let res = response.data;
                        if (res.code === 1) {
                            layer.alert(res.msg,{icon:1,closeBtn:0,title:false,btnAlign:'c',},function(){
                                location.reload();
                            });
                        } else {
                            layer.alert(res.msg,{icon:2,closeBtn:0,title:false,btnAlign:'c'},function(){
                                layer.closeAll();
                                $('button').attr('disabled',false);
                                button.text(text);
                            });
                        }
                    })
                    .catch(function (error) {
                        console.log(error);
                    });
                return false;
            });
        });
    });
</script>

{include file="public/footer" /}
