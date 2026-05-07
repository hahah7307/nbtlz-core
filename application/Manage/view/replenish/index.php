
{include file="public/header" /}

<!-- 主体内容 -->
<div class="layui-body" id="LAY_app_body">
    <div class="right">
        <div class="title">补货列表</div>
        <form class="layui-form search-form" method="get">
            <div class="layui-inline w100">
                <select name="status">
                    <option value="-1" {if condition="$status eq '-1'"}selected{/if}>全部</option>
                    <option value="0" {if condition="$status eq 0"}selected{/if}>草稿</option>
                    <option value="1" {if condition="$status eq 1"}selected{/if}>已提交</option>
                </select>
            </div>
            <div class="layui-inline w200">
                <input type="text" class="layui-input" name="keyword" value="{$keyword}" placeholder="搜索标题">
            </div>
            <div class="layui-inline">
                <button class="layui-btn" lay-submit lay-filter="Search"><i class="layui-icon">&#xe615;</i> 查询</button>
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
                    <col class="w180">
                    <col class="w100">
                    <col class="w180">
                </colgroup>
                <thead>
                <tr>
                    <th class="tl">标题</th>
                    <th class="tc">仓库SKU</th>
                    <th class="tc">开始日期</th>
                    <th class="tc">结束日期</th>
                    <th class="tc">预估补货合计</th>
                    <th class="tc">创建人</th>
                    <th class="tc">创建时间</th>
                    <th class="tc">状态</th>
                    <th class="tc">操作</th>
                </tr>
                </thead>
                <tbody>
                {foreach name="list" item="v"}
                <tr>
                    <td class="tl">{$v.plan_title}</td>
                    <td class="tc">{$v.warehouse_sku}</td>
                    <td class="tc">{$v.start_date|strtotime|date="Y-m-d",###}</td>
                    <td class="tc">{$v.end_date|strtotime|date="Y-m-d",###}</td>
                    <td class="tc">{$v.amount}</td>
                    <td class="tc">{$v.admin_user.nickname}</td>
                    <td class="tc">{$v.create_time}</td>
                    <td class="tc">{if condition="$v.status eq 1"}<span class="green">已结存</span>{else/}<span class="blue">进行中</span>{/if}</td>
                    <td class="tc">
                        <a href="{:url('user_list', ['id' => $v.id])}" class="layui-btn layui-btn-sm">列表</a>
                        {if condition="in_array('Replenish', $role)"}
                        <button data-id="{$v.id}" class="layui-btn layui-btn-sm layui-btn-normal ml0" lay-submit lay-filter="Balance">合并</button>
                        {/if}
                        <button data-id="{$v.id}" class="layui-btn layui-btn-sm layui-btn-danger ml0" lay-submit lay-filter="Delete">删除</button>
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
        const $ = layui.jquery,
            form = layui.form;

        // 排序
        form.on('submit(Sort)', function(data){
            var text = $(this).text(), button = $(this);
            $('button').attr('disabled',true);
            button.text('请稍候...');
            $.ajax({
                type:'POST',url:"{:url('sort')}",data:data.field,dataType:'json',
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
            return false;
        });

        // 结存
        form.on('submit(Balance)', function(data){
            const text = $(this).text(),
                button = $(this),
                id = $(this).data('id');
            layer.confirm('确定结存吗？',{icon:3,closeBtn:0,title:false,btnAlign:'c'},function(){
                $('button').attr('disabled',true);
                button.text('请稍候...');
                $.ajax({
                    type:'POST',url:"{:url('balance')}",data:{id:id},dataType:'json',
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

        // 删除
        form.on('submit(Delete)', function(data){
            var text = $(this).text(),
                button = $(this),
                id = $(this).data('id');
            layer.confirm('确定删除吗？',{icon:3,closeBtn:0,title:false,btnAlign:'c'},function(){
                $('button').attr('disabled',true);
                button.text('请稍候...');
                $.ajax({
                    type:'POST',url:"{:url('delete')}",data:{id:id},dataType:'json',
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
