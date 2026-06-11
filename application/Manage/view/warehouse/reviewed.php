
{include file="public/header" /}

<!-- 主体内容 -->
<div class="layui-body" id="LAY_app_body">
    <div class="right">
        <a href="{:session('back_url', '', 'manage')}" class="layui-btn layui-btn-danger layui-btn-sm fr"><i class="layui-icon">&#xe603;</i>返回上一页</a>
        <div class="title">已查看列表</div>

        <form class="layui-form" method="get">
            <div class="layui-input-inline w200">
                <input type="text" class="layui-input" id="sale_day" name="sale_day" value="{$sale_day}" placeholder="请选择日期">
            </div>
            <div class="layui-inline">
                <button class="layui-btn" lay-submit lay-filter="Search"><i class="layui-icon">&#xe615;</i> 查询</button>
            </div>
        </form>

        <div class="layui-form table-flex">
            <table class="layui-table" lay-size="sm">
                <colgroup>
                    <col>
                    <col>
                    <col>
                </colgroup>
                <thead>
                <tr>
                    <th>运营</th>
                    <th>查看日期</th>
                    <th>最后查看时间</th>
                </tr>
                </thead>
                <tbody>
                {foreach name="list" item="v"}
                <tr class="sku-item" data-sku="{$v.sku}">
                    <td>{$v.nickname}</td>
                    <td>{$v.created_date}</td>
                    <td>{$v.created_time}</td>
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
            elem: '#sale_day',
            type: 'date'
        });
    });
</script>

{include file="public/footer" /}
