
{include file="public/header" /}

<style>
    .ellipsis-link {
        display: block;
        width: 200px;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
    }

    /* 悬浮提示框 */
    .layui-table-cell a:hover::after {
        content: attr(href);
        position: absolute;
        left: 0;
        top: 100%;
        z-index: 9999;
        max-width: 500px;
        width: max-content;
        padding: 8px 12px;
        background: #fff;
        color: #333;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .15);
        white-space: normal;
        word-break: break-all;
        line-height: 1.5;
        font-size: 13px;
    }
</style>
<!-- 主体内容 -->
<div class="layui-body" id="LAY_app_body">
    <div class="right">
        <div class="title">安装视频</div>
        <form class="layui-form search-form" method="get">
            <div class="layui-inline w200">
                <input type="text" class="layui-input" name="keyword" value="{$keyword}" placeholder="">
            </div>
            <div class="layui-inline">
                <button class="layui-btn" lay-submit lay-filter="Search"><i class="layui-icon">&#xe615;</i> 查询</button>
            </div>
            <div class="layui-inline">
                <a class="layui-btn layui-btn-normal" href="{:url('index')}"><i class="layui-icon">&#xe621;</i> 重置</a>
            </div>
        </form>

        <div class="layui-form">
            <button type="button" class="layui-btn  layui-btn-normal" id="uplaod" lay-submit lay-filter="upload">上传</button>
            <table class="layui-table" lay-size="sm" id="table">
                <colgroup>
                    <col>
                    <col>
                    <col>
                    <col>
                    <col>
                    <col width="180">
                    <col width="60">
                    <col width="100">
                    <col width="150">
                </colgroup>
                <thead>
                <tr>
                    <th>SKU</th>
                    <th>品名</th>
                    <th>文件名称</th>
                    <th>临时文件下载路径</th>
                    <th>预览图</th>
                    <th>过期时间</th>
                    <th>状态</th>
                    <th>创建人</th>
                    <th class="tc">操作</th>
                </tr>
                </thead>
                <tbody>
                {foreach name="list" item="v"}
                <tr>
                    <td>{$v.sku}</td>
                    <td>{$v.product_name}</td>
                    <td>{$v.file_name}</td>
                    <td><a href="{$v.file_tmp_url}" class="ellipsis-link">{$v.file_tmp_url}</a></td>
                    <td><a href="{$v.file_tmp_url}"><img src="{$v.file_tmp_url}" alt="" style="height: 80px;object-fit: contain"></a></td>
                    <td>{$v.file_tmp_expire}</td>
                    <td class="tc">
                        {if condition="empty($v.file_tmp_expire)"}
                            <span class="orange">未生成</span>
                        {elseif condition="$v.file_tmp_expire gt time()"/}
                            <span class="red">已过期</span>
                        {else/}
                            <span class="green">未过期</span>
                        {/if}
                    </td>
                    <td>{$v.seller.nickname}</td>
                    <td class="tc">
                        <a href="{:url('install_edit', ['id' => $v.id])}" class="layui-btn layui-btn-normal layui-btn-sm">配对</a>
                        <button class="layui-btn layui-btn-normal layui-btn-sm" data-id="{$v.id}" lay-submit lay-filter="formCoding">生成文件</button>
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
    layui.use(['form', 'jquery', 'upload'], function(){
        let $ = layui.jquery,
            form = layui.form,
            upload = layui.upload;

        //监听提交
        form.on('submit(formCoding)', function(data){
            let text = $(this).text(),
                button = $(this),
                id = $(this).data('id');
            console.log(id);
            $('button').attr('disabled',true);
            button.text('请稍候...');
            axios.post("{:url('createTmpUrl')}", {id: id})
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

        //文件上传
        layui.use('upload', function () {
            let err_msg = '';
            let icon = 1;
            //执行实例
            let uploadInst = upload.render({
                elem: '#uplaod',  //绑定元素
                accept: 'file',
                multiple: true,
                url: '/Manage/Upload/video_upload',  //上传接口
                before: function () {
                    //加载层-风格4
                    layer.msg('上传中，请勿关闭或刷新页面', {
                        icon: 16,
                        shade: 0.3,
                        time: 0
                    });
                },
                allDone: function(obj) {
                    setTimeout(function() {
                        // 这里是延迟执行的代码
                        if (err_msg === '') {
                            err_msg = obj.successful + '个文件已上传成功';
                        } else {
                            icon = 2;
                        }
                        // layer.alert(obj.successful + '个文件已上传成功',{icon:1,closeBtn:0,title:false,btnAlign:'c',},function(){
                        layer.alert(err_msg, {icon:icon,closeBtn:0,title:false,btnAlign:'c',},function(){
                            location.reload();
                        });
                    }, 2000);
                },
                done: function(res){
                    //上传完毕回调
                    if (res.code === 1) {
                        layer.msg('上传成功', {
                            time: 1500, //1.5秒关闭（如果不配置，默认是3秒）
                            shade: 0.3,
                        }, function() {
                            //加载层-风格4
                            layer.msg('上传中，请勿关闭或刷新页面', {
                                icon: 16,
                                shade: 0.3,
                                time: 0
                            });
                        });
                    } else {
                        err_msg += res.msg + "<br/>";
                        layer.msg(res.msg, {
                            time: 1500, //1.5秒关闭（如果不配置，默认是3秒）
                            shade: 0.3,
                        });
                    }
                },
                error: function () {
                    //请求异常回调
                    layer.alert('操作异常',{icon:2,closeBtn:0,title:false,btnAlign:'c',},function(){
                        location.reload();
                    });
                }
            });
        });
    });
</script>

{include file="public/footer" /}
