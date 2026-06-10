
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
        <div class="title">详情</div>

        <div class="layui-form">
            <table class="layui-table" lay-size="sm">
                <colgroup>
                    <col>
                    <col>
                    <col>
                    <col>
                    <col>
                    <col>
                    <col>
                    <col class="w720">
                    <col class="w720">
                    <col>
                    <col>
                </colgroup>
                <thead>
                <tr>
                    <th class="tc">SKU</th>
                    <th class="tc">中文品名</th>
                    <th class="tc">下单未包数</th>
                    <th class="tc">货号未出数</th>
                    <th class="tc">海上在途数</th>
                    <th class="tc">海外仓在库</th>
                    <th class="tc">账面总库存</th>
                    <th class="tc">日销预估</th>
                    <th class="tc">月销预估</th>
                    <th class="tc">月销量合计</th>
                    <th class="tc">差额库存</th>
                </tr>
                </thead>
                <tbody>
                {foreach name="plan.plan_detail" item="v"}
                {if condition="$v.type eq 1"}
                <tr>
                    <td class="tc">{$plan.warehouse_sku}</td>
                    <td class="tc">{$plan.warehouse_sku|getProductTitleByWarehouseSku}</td>
                    <td class="tc">{$plan.unproduced|intval}</td>
                    <td class="tc">{$plan.pendingship|intval}</td>
                    <td class="tc">{$plan.onWay|intval}</td>
                    <td class="tc">{$plan.store|intval}</td>
                    <td class="tc">{:$plan['unproduced'] + $plan['pendingship'] + $plan['onWay'] + $plan['store']}</td>
                    <td class="tc">
                        {if condition="$v.day_sale_sum_json"}
                        <div class="grid-box">
                            {foreach name="v.day_sale_sum_json|json_decode=###,true" key="k" item="item"}
                            <div class="grid-item">
                                <div class="key">{$k}</div>
                                <div class="value">{$item}</div>
                            </div>
                            {/foreach}
                        </div>
                        {/if}
                    </td>
                    <td class="tc">
                        {if condition="$v.month_sale_sum_json"}
                        <div class="grid-box">
                            {foreach name="v.month_sale_sum_json|json_decode=###,true" key="k" item="item"}
                            <div class="grid-item">
                                <div class="key">{$k}</div>
                                <div class="value">{$item}</div>
                            </div>
                            {/foreach}
                        </div>
                        {/if}
                    </td>
                    <td class="tc">{$v.month_sale_sum}</td>
                    <td class="tc">{$v.settled_inventory}</td>
                    {/if}
                </tr>
                {/foreach}
                </tbody>
            </table>
            {foreach name="plan.plan_detail" item="v"}
            {if condition="$v.type eq 0"}
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
                    <col class="w720">
                    <col class="w720">
                    <col>
                </colgroup>
                <thead>
                <tr>
                    <th class="tc">销售人员</th>
                    <th class="tc">平台名称</th>
                    <th class="tc">SKU</th>
                    <th class="tc">中文品名</th>
                    <th class="tc">下单未包数</th>
                    <th class="tc">货号未出数</th>
                    <th class="tc">海上在途数</th>
                    <th class="tc">海外仓在库</th>
                    <th class="tc">账面总库存</th>
                    <th class="tc">日销预估</th>
                    <th class="tc">月销预估</th>
                    <th class="tc">月销量合计</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td class="tc">{$v.user.nickname}</td>
                    <td class="tc">{$v.platform}</td>
                    <td class="tc">{$plan.warehouse_sku}</td>
                    <td class="tc">{$plan.warehouse_sku|getProductTitleByWarehouseSku}</td>
                    <td class="tc">{$plan.unproduced|intval}</td>
                    <td class="tc">{$plan.pendingship|intval}</td>
                    <td class="tc">{$plan.onWay|intval}</td>
                    <td class="tc">{$plan.store|intval}</td>
                    <td class="tc">{:$plan['unproduced'] + $plan['pendingship'] + $plan['onWay'] + $plan['store']}</td>
                    <td class="tc">
                        {if condition="$v.day_sale_sum_json"}
                        <div class="grid-box">
                            {foreach name="v.day_sale_sum_json|json_decode=###,true" key="k" item="item"}
                            <div class="grid-item">
                                <div class="key">{$k}</div>
                                <div class="value">{$item}</div>
                            </div>
                            {/foreach}
                        </div>
                        {/if}
                    </td>
                    <td class="tc">
                        {if condition="$v.month_sale_sum_json"}
                        <div class="grid-box">
                            {foreach name="v.month_sale_sum_json|json_decode=###,true" key="k" item="item"}
                            <div class="grid-item">
                                <div class="key">{$k}</div>
                                <div class="value">{$item}</div>
                            </div>
                            {/foreach}
                        </div>
                        {/if}
                    </td>
                    <td class="tc">{$v.month_sale_sum}</td>
                </tr>
                </tbody>
            </table>
            {/if}
            {/foreach}
        </div>
        <div><img src="{:getImgUrlByWarehouseSku($plan['warehouse_sku'])}" alt=""></div>

    </div>
</div>
<script>
    layui.use(['form', 'jquery'], function(){
        var $ = layui.jquery,
            form = layui.form;

    });
</script>

{include file="public/footer" /}
