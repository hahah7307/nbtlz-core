
{include file="public/header" /}

<?php use app\Manage\model\SkuRelationModel; ?>
<!-- 主体内容 -->
<div class="layui-body" id="LAY_app_body">
    <div class="right">
        <div class="title">销售产品列表</div>
        <form class="layui-form search-form" method="get">
            <div class="layui-inline w200">
                <input type="text" class="layui-input" name="keyword" value="{$keyword}">
            </div>
            <div class="layui-inline w120">
                <select name="status" lay-verify="">
                    <option value="">状态</option>
                    <option value="1" {if condition="$status eq 1"}selected{/if}>使用中</option>
                    <option value="4" {if condition="$status eq 4"}selected{/if}>已停用</option>
                    <option value="5" {if condition="$status eq 5"}selected{/if}>已驳回</option>
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
                    <col width="100">
                    <col width="180">
                </colgroup>
                <thead>
                <tr>
                    <th>系统编号</th>
                    <th>系统编号</th>
                    <th>所属平台</th>
                    <th>所属店铺</th>
                    <th>所属仓库</th>
                    <th>销售SKU</th>
                    <th>仓库SKU</th>
                    <th>创建时间</th>
                    <th>最后修改时间</th>
                    <th>运营人员</th>
                    <th class="tc">状态</th>
                    <th class="tc">操作</th>
                </tr>
                </thead>
                <tbody>
                {foreach name="list" item="v"}
                <tr>
                    <td>{$v.ss_code}</td>
                    <td>{$v.wsg_code}</td>
                    <td>{$v.platform}</td>
                    <td>{$v.user.user_account}</td>
                    <td>{$v.warehouse_name}</td>
                    <td>{$v.seller_sku}</td>
                    <td>{:SkuRelationModel::getWarehouseSkuLabelBySSCode($v['ss_code'], $v['wsg_code'])}</td>
                    <td class="tr">{$v.created_time}</td>
                    <td class="tr">{$v.updated_time}</td>
                    <td>{$v.admin_user.nickname}</td>
                    <td class="tc">
                        {if condition="$v.status eq 0"}
                            <p class="blue">新建待审核</p>
                        {elseif condition="$v.status eq 1"/}
                            <p class="green">使用中</p>
                        {elseif condition="$v.status eq 2"/}
                            <p class="blue">编辑待审核</p>
                        {elseif condition="$v.status eq 3"/}
                            <p class="blue">停用待审核</p>
                        {elseif condition="$v.status eq 4"/}
                            <p class="red">已停用</p>
                        {elseif condition="$v.status eq 5"/}
                            <p class="orange">已驳回</p>
                        {/if}
                    </td>
                    <td class="tc">
                        <a href="{:url('log', ['ssCode' => $v.ss_code])}" class="layui-btn layui-btn-sm">日志</a>
                        <a href="{:url('edit', ['id' => $v.id])}" class="layui-btn layui-btn-normal layui-btn-sm">编辑</a>
                        <button data-id="{$v.id}" class="layui-btn layui-btn-sm layui-btn-danger ml0" lay-submit lay-filter="Detele">停用</button>
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

        // 状态
        form.on('switch(formLock)', function(data){
            $('button').attr('disabled',true);
            axios.post("{:url('status')}", {id:data.value,type:'look'})
                .then(function (response) {
                    let res = response.data;
                    if (res.code === 0) {
                        layer.alert(data.msg,{icon:2,closeBtn:0,title:false,btnAlign:'c'},function(){
                            location.reload();
                        });
                    }
                })
                .catch(function (error) {
                    console.log(error);
                });
            return false;
        });

        // 删除
        form.on('submit(Detele)', function(data){
            let text = $(this).text(),
                button = $(this),
                id = $(this).data('id');
            layer.confirm('确定停用吗？',{icon:3,closeBtn:0,title:false,btnAlign:'c'},function(){
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
