
{include file="public/header" /}

<div class="layui-body">
<div class="right">
    <a href="{:session('manage.back_url')}" class="layui-btn layui-btn-danger layui-btn-sm fr"><i class="layui-icon">&#xe603;</i>返回上一页</a>
    <div class="title">添加日销预估</div>
    <div class="layui-form">
        <div class="layui-form-item">
            <div class="layui-form-item">
                <label class="layui-form-label">所属平台</label>
                <div class="layui-input-block w300"">
                <select name=platform lay-filter="user_account">
                    <option value="amazon">amazon</option>
                    <option value="wayfair">wayfair</option>
                    <option value="walmart">walmart</option>
                    <option value="temu">temu</option>
                    <option value="tiktok">tiktok</option>
                </select>
            </div>
        </div>
        <div class="layui-form-item">
            <table class="layui-table" style="width:300px;">
                <thead>
                <tr>
                    <th>月份</th>
                    <th>日销预估</th>
                </tr>
                </thead>
                <tbody>
                {foreach name="month" item="item"}
                <tr>
                    <td>{$item}</td>
                    <td>
                        <input type="text" name="month[{$item}]" class="layui-input">
                    </td>
                </tr>
                {/foreach}
                </tbody>
            </table>
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
        axios.post("{:url('user_add', ['id' => $id])}", data.field, {
            headers: {
                'Content-Type': 'multipart/form-data' // 设置请求头，确保服务器正确解析 FormData
            }
        })
            .then(function (response) {
                let res = response.data;
                if (res.code === 1) {
                    layer.alert(res.msg,{icon:1,closeBtn:0,title:false,btnAlign:'c',},function(){
                        location.reload();
                        location.href = "{:session('manage.back_url')}";
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
