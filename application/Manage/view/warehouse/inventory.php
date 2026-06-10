
{include file="public/header" /}

<style>
    .layui-body {left: 220px!important;}
    .layui-form-label {width: 100px!important;}
    .layui-form-item .layui-inline {margin-right: 0!important;}
    .layui-form-label {width: 160px!important;}
    .w84 {width: 84px!important;}
    .deliver_num {width: 100px!important;}
    /*.layui-table {display: flex}*/
    .select {margin-left: 0!important;}
    .warm-tips {display: inline-block; font-size: 14px; position: relative; top: 8px; left: 5px; color: #ce0000}
</style>
<!-- 主体内容 -->
<div class="layui-body" id="LAY_app_body">
    <div class="right">
        <a href="{:session('back_url', '', 'manage')}" class="layui-btn layui-btn-danger layui-btn-sm fr"><i class="layui-icon">&#xe603;</i>返回上一页</a>
        <div class="title">{$date|strtotime|date="Y-m-d", ###}库龄 {$numStart}-{$num}天海外仓库存<strong>(不包含RETURN、ACCESSORY)</strong></div>

        <div class="layui-form table-flex">
            <table class="layui-table" lay-size="sm">
                <colgroup>
                    <col
                    <col>
                    <col>
                    <col>
                    <col>
                    <col>
                </colgroup>
                <thead>
                <tr>
                    <th>产品图片</th>
                    <th>仓库Sku</th>
                    <th>中文品名</th>
                    <th>所在仓</th>
                    <th>库存数</th>
                    <th>主销售</th>
                </tr>
                </thead>
                <tbody>
                {foreach name="list" item="v"}
                <tr class="sku-item" data-sku="{$v.sku}">
                    <td><img src="{$v.productImages}" height="80" alt=""></td>
                    <td>{$v.sku}</td>
                    <td>{$v.productTitle}</td>
                    <td class="tc">{$v.warehouse}</td>
                    <td class="tr">{$v.num|number_format=###}</td>
                    <td class="tc">{$v.user_name}</td>
                </tr>
                {/foreach}
                </tbody>
            </table>
        </div>

    </div>
</div>
<script>
    layui.use(['form', 'jquery', 'laydate'], function(){
        var $ = layui.jquery,
            form = layui.form,
            laydate = layui.laydate;

        // 显示日期选择器
        laydate.render({
            elem: '#sale_start',
            type: 'datetime'
        });
        laydate.render({
            elem: '#sale_end',
            type: 'datetime'
        });
        laydate.render({
            elem: '#qty_start',
            type: 'datetime'
        });
        laydate.render({
            elem: '#qty_end',
            type: 'datetime'
        });
    });
</script>

{include file="public/footer" /}
