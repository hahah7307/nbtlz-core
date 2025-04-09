
{include file="public/header" /}

<!-- 主体内容 -->
<div class="layui-body" id="LAY_app_body">
    <div class="right">
        <div class="title">易仓产品列表</div>
        <form class="layui-form search-form" method="get">
            <div class="layui-inline w200">
                <input type="text" class="layui-input" name="keyword" value="{$keyword}" placeholder="SKU">
            </div>
            <div class="layui-inline">
                <button class="layui-btn" lay-submit lay-filter="Search"><i class="layui-icon">&#xe615;</i> 查询</button>
            </div>
            <div class="layui-inline">
                <button type="button" class="layui-btn layui-btn-normal" lay-submit lay-filter="Copy"><i class="layui-icon">&#xe621;</i> 复制</button>
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
                </colgroup>
                <thead>
                <tr>
                    <th>ID</th>
                    <th>SKU</th>
                    <th>名称</th>
                    <th>名称EN</th>
                    <th>重量(KG)</th>
                    <th>长(CM)</th>
                    <th>宽(CM)</th>
                    <th>高(CM)</th>
                </tr>
                </thead>
                <tbody>
                {foreach name="list" item="v"}
                <tr>
                    <td>{$v.id}</td>
                    <td>{$v.productSku}</td>
                    <td>{$v.productTitle}</td>
                    <td>{$v.productTitleEn}</td>
                    <td>{$v.productWeight}</td>
                    <td>{$v.productLength}</td>
                    <td>{$v.productWidth}</td>
                    <td>{$v.productHeight}</td>
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

        // 复制
        form.on('submit(Copy)', function(data){
            const text = `{$skuAll}`;
            const textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';  // 防止页面跳动
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);

            textarea.focus();
            textarea.select();

            try {
                const success = document.execCommand('copy');
                if (success) {
                    layer.msg('复制成功', {icon: 6});
                } else {
                    layer.msg('复制失败，请手动复制', {icon: 5});
                }
            } catch (err) {
                layer.msg('复制失败，请手动复制', {icon: 5});
            }

            document.body.removeChild(textarea);

            return false;
        });

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
        form.on('submit(Delete)', function(data){
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
