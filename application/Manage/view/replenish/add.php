
{include file="public/header" /}

<div class="layui-body">
<div class="right">
    <a href="{:session('manage.back_url')}" class="layui-btn layui-btn-danger layui-btn-sm fr"><i class="layui-icon">&#xe603;</i>返回上一页</a>
    <div class="title">添加补货计划</div>
    <div class="layui-form">
        <div class="layui-form-item">
            <label class="layui-form-label">名称</label>
            <div class="layui-input-inline w300">
                <input type="text" class="layui-input" name="plan_title">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">仓库SKU</label>
            <div class="layui-input-inline w300">
                <input type="text" class="layui-input" name="warehouse_sku">
            </div>
        </div>
        <div class="layui-form-item">
            <div class="layui-inline layui-col-md4">
                <label class="layui-form-label">开始日期</label>
                <div class="layui-input-inline">
                    <input type="text" autocomplete="off" class="layui-input w300" id="start_date" name="start_date">
                </div>
            </div>
        </div>
        <div class="layui-form-item">
            <div class="layui-inline layui-col-md4">
                <label class="layui-form-label">结束日期</label>
                <div class="layui-input-inline">
                    <input type="text" autocomplete="off" class="layui-input w300" id="end_date" name="end_date">
                </div>
            </div>
        </div>
        <div class="layui-form-item">
            <div class="layui-input-block">
                <button class="layui-btn w200" lay-submit lay-filter="formCoding">提交保存</button>
            </div>
        </div>
    </div>
</div>
</div>
<script>
layui.use(['form', 'jquery', 'laydate'], function() {
    let $ = layui.jquery,
        form = layui.form,
        laydate = layui.laydate;

    // 显示日期选择器
    laydate.render({
        elem: '#start_date',
        type: 'date'
    });
    laydate.render({
        elem: '#end_date',
        type: 'date'
    });

    // 监听提交
    form.on('submit(formCoding)', function(data){
        let text = $(this).text(),
            button = $(this);
        $('button').attr('disabled',true);
        button.text('请稍候...');
        axios.post("{:url('add')}", data.field)
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
</script>

{include file="public/footer" /}
