
{include file="public/header" /}

<?php use app\Manage\model\SkuRelationModel; ?>
<!-- 主体内容 -->
<div class="layui-body" id="LAY_app_body">
    <div class="right">
        <div class="title">销售产品s审核列表</div>
        <form class="layui-form search-form" method="get">
            <div class="layui-inline w200">
                <input type="text" class="layui-input" name="keyword" value="{$keyword}">
            </div>
            <div class="layui-inline">
                <button class="layui-btn" lay-submit lay-filter="Search"><i class="layui-icon">&#xe615;</i> 查询</button>
            </div>
            <div class="layui-inline">
                <a class="layui-btn layui-btn-normal" href="{:url('index')}"><i class="layui-icon">&#xe621;</i> 重置</a>
            </div>
        </form>

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
                    <col>
                    <col width="80">
                    <col width="100">
                    {if condition="$user.super or $user.manage"}
                    <col width="120">
                    {/if}
                </colgroup>
                <thead>
                <tr>
                    <th>销售SKU系统编号</th>
                    <th>仓库SKU组系统编号</th>
                    <th>所属平台</th>
                    <th>所属店铺</th>
                    <th>所属仓库</th>
                    <th>销售SKU</th>
                    <th>仓库SKU</th>
                    <th>创建时间</th>
                    <th>运营人员</th>
                    <th class="tc">状态</th>
                    {if condition="$user.super or $user.manage"}
                    <th class="tc">操作</th>
                    {/if}
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
                    {if condition="$user.super or $user.manage"}
                    <td class="tc">
                        <button class="layui-btn layui-btn-normal layui-btn-sm" data-ss_code="{$v.ss_code}" data-wsg_code="{$v.wsg_code}" lay-submit lay-filter="APPROVED">通过</button>
                        <button class="layui-btn layui-btn-danger layui-btn-sm" data-ss_code="{$v.ss_code}" data-wsg_code="{$v.wsg_code}" lay-submit lay-filter="REJECT">驳回</button>
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

        //
        form.on('submit(APPROVED)', function(data){
            let text = $(this).text(),
                button = $(this),
                ss_code = $(this).data('ss_code'),
                wsg_code = $(this).data('wsg_code');
            layer.confirm('确定审核通过吗？',{icon:3,closeBtn:0,title:false,btnAlign:'c'},function(){
                $('button').attr('disabled',true);
                button.text('请稍候...');
                axios.post("{:url('approved')}", {ss_code: ss_code, wsg_code: wsg_code})
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

        //
        form.on('submit(REJECT)', function(data){
            let text = $(this).text(),
                button = $(this),
                ss_code = $(this).data('ss_code'),
                wsg_code = $(this).data('wsg_code');
            layer.confirm('确定驳回吗？',{icon:3,closeBtn:0,title:false,btnAlign:'c'},function(){
                $('button').attr('disabled',true);
                button.text('请稍候...');
                axios.post("{:url('reject')}", {ss_code: ss_code, wsg_code: wsg_code})
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
