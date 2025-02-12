
{include file="public/header" /}

<style>
    .total {padding: 0 10px 0 10px}
</style>
<!-- 主体内容 -->
<div class="layui-body" id="LAY_app_body">
    <div class="right">
        <div class="title">Wildberries采购列表</div>
        <form class="layui-form search-form" method="get">
            <div class="layui-inline w200">
                <input type="text" class="layui-input" name="keyword" value="{$keyword}" placeholder="">
            </div>
            <div class="layui-inline w120">
                <select name="status" lay-verify="">
                    <option value="0" {if condition="$status eq 0"}selected{/if}>未采购</option>
                    <option value="1" {if condition="$status eq 1"}selected{/if}>已采购</option>
                    <option value="2" {if condition="$status eq 2"}selected{/if}>已取消</option>
                </select>
            </div>
            <div class="layui-inline">
                <button class="layui-btn" lay-submit lay-filter="Search"><i class="layui-icon">&#xe615;</i> 查询</button>
            </div>
        </form>

        <div class="layui-form">
            <a class="layui-btn" href="{:url('add')}">添加</a>
            <span class="total">采购数量合计：{$qty|number_format=###}个</span>
            <span class="total">采购金额合计：{$amount|number_format=###, 2}元</span>
            <table class="layui-table" lay-size="sm">
                <colgroup>
                    <col>
                    <col>
                    <col>
                    <col>
                    <col>
                    <col>
                    <col>
                    <col>
                    <col>
                    <col>
                    <col>
                    <col>
                    <col>
                    <col>
                    <col>
                    <col width="80">
                    <col width="220">
                </colgroup>
                <thead>
                <tr>
                    <th>WB订单号</th>
                    <th>WB产品编号</th>
                    <th>产品名称</th>
                    <th>数量</th>
                    <th>颜色</th>
                    <th>尺寸</th>
                    <th>包装要求</th>
                    <th>采购链接</th>
                    <th>采购编号</th>
                    <th>1688运单号</th>
                    <th>申通运单号</th>
                    <th>揽件码</th>
                    <th>单价/元</th>
                    <th>总金额/元</th>
                    <th>采购日期</th>
                    <th class="tc">状态</th>
                    <th class="tc">操作</th>
                </tr>
                </thead>
                <tbody>
                {foreach name="list" item="v"}
                <tr>
                    <td>{$v.wb_order_code}</td>
                    <td>{$v.wb_product_code}</td>
                    <td>{$v.product_name}</td>
                    <td class="tr">{$v.qty}</td>
                    <td>{$v.color}</td>
                    <td>{$v.size}</td>
                    <td>{$v.packaging_requirements}</td>
                    <td><a href="{$v.purchase_url}" target="_blank">{$v.purchase_url}</a></td>
                    <td>{$v.po_no}</td>
                    <td>{$v.tracking_no_1}</td>
                    <td>{$v.tracking_no_2}</td>
                    <td>{$v.pickup_code}</td>
                    <td class="tr">{$v.unit_price}</td>
                    <td class="tr">{$v.amount}</td>
                    <td class="tr">{:empty($v['purchase_date']) ? '' : date('Y-m-d', strtotime($v['purchase_date']))}</td>
                    <td class="tc">
                        {if condition="$v.status eq 0"}
                        <p class="blue">未采购</p>
                        {elseif condition="$v.status eq 1"/}
                        <p class="green">已采购</p>
                        {elseif condition="$v.status eq 2"/}
                        <p class="red">已取消</p>
                        {/if}
                    </td>
                    <td class="tc">
                        {if condition="in_array('Wildberries Seller', $role) or $user.super"}
                        <a href="{:url('edit', ['id' => $v.id])}" class="layui-btn layui-btn-normal layui-btn-sm">编辑</a>
                        {/if}
                        {if condition="in_array('Wildberries Purchaser', $role) or $user.super"}
                        <a href="{:url('purchase', ['id' => $v.id])}" class="layui-btn layui-btn-normal layui-btn-sm">采购</a>
                        {/if}
                        {if condition="in_array('Wildberries Seller', $role) or $user.super"}
                        <a href="{:url('ship', ['id' => $v.id])}" class="layui-btn layui-btn-normal layui-btn-sm">发货</a>
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
