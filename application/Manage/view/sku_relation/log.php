
{include file="public/header" /}

<!-- 主体内容 -->
<div class="layui-body" id="LAY_app_body">
    <div class="right">
        <a href="{:session('manage.back_url')}" class="layui-btn layui-btn-danger layui-btn-sm fr"><i class="layui-icon">&#xe603;</i>返回上一页</a>
        <div class="title">操作记录</div>

        <div class="layui-form">
            <table class="layui-table" lay-size="sm">
                <colgroup>
                    <col>
                    <col>
                    <col>
                    <col>
                    <col>
                    <col>
                    <col width="100">
                    <col width="150">
                </colgroup>
                <thead>
                <tr>
                    <th>销售SKU</th>
                    <th>销售SKU系统编号</th>
                    <th>仓库SKU组系统编号</th>
                    <th>操作</th>
                    <th>操作后状态</th>
                    <th>操作时间</th>
                    <th>操作人员</th>
                    <th>操作IP</th>
                </tr>
                </thead>
                <tbody>
                {foreach name="list" item="v"}
                <tr>
                    <td>{$v.seller_sku}</td>
                    <td>{$v.ss_code}</td>
                    <td>{$v.wsg_code}</td>
                    <td>{$v.action}</td>
                    <td>
                        {if condition="$v.status eq 0"}
                        新建待审核
                        {elseif condition="$v.status eq 1"/}
                        使用中
                        {elseif condition="$v.status eq 2"/}
                        编辑待审核
                        {elseif condition="$v.status eq 3"/}
                        停用待审核
                        {elseif condition="$v.status eq 4"/}
                        已停用
                        {elseif condition="$v.status eq 5"/}
                        已驳回
                        {/if}
                    </td>
                    <td class="tr">{$v.created_time}</td>
                    <td>{$v.action_user}</td>
                    <td>{$v.action_ip}</td>
                </tr>
                {/foreach}
                </tbody>
            </table>
        </div>

    </div>
</div>
<script>
    layui.use(['form', 'jquery'], function(){
        let $ = layui.jquery,
            form = layui.form;

    });
</script>

{include file="public/footer" /}
