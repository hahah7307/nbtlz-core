
{include file="public/header" /}

<!-- 主体内容 -->
<div class="layui-body" id="LAY_app_body">
    <div class="right">
        <a href="{:session('manage.back_url')}" class="layui-btn layui-btn-danger layui-btn-sm fr"><i class="layui-icon">&#xe603;</i>返回上一页</a>
        <div class="title">添加销售产品</div>
        <div class="layui-form">
            <div class="layui-form-item">
                <label class="layui-form-label">所属平台</label>
                <div class="layui-input-block w300"">
                    <select name="platform" lay-filter="platform">
                        <option value=""></option>
                        {foreach name="platform" item="v"}
                        <option value="{$v.platform}">{$v.platform}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">所属店铺</label>
                <div class="layui-input-block w300"">
                    <select name="user_account" lay-filter="user_account" id="user_account">
                    </select>
                </div>
            </div>
            <div class="layui-form-item" id="brand">
                <label class="layui-form-label">所属品牌</label>
                <div class="layui-input-block w300"">
                    <select name="brand">
                        <option value=""></option>
                        {foreach name="brand" item="vb"}
                        <option value="{$vb.brand_code}">{$vb.brand_name}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">产品颜色</label>
                <div class="layui-input-block w300"">
                    <select name="color">
                        <option value=""></option>
                        {foreach name="color" item="vc"}
                        <option value="{$vc.color_code}">{$vc.color_code}({$vc.color_name})</option>
                        {/foreach}
                    </select>
                </div>
            </div>
            <div class="layui-form-item" id="season" style="display: none">
                <label class="layui-form-label">季度代码</label>
                <div class="layui-input-block w300"">
                    <select name="season">
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                        <option value="D">D</option>
                    </select>
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
    layui.use(['form', 'jquery'], function(){
        let $ = layui.jquery,
            form = layui.form;

        let domIndex = 0;
        // 添加属性
        form.on('submit(AttrAdd)', function(data) {
            domIndex ++;
            let newDom = '<div class="layui-form-item"><label class="layui-form-label">仓库SKU</label><div class="layui-input-inline w300"><input type="text" class="layui-input" name="warehouse_sku[' + domIndex + ']" placeholder="请填写仓库SKU"></div><label class="layui-form-label">产品数量</label><div class="layui-input-inline w300"><input type="text" class="layui-input" name="qty[' + domIndex + ']" placeholder="请填写产品数量"></div><button class="layui-btn layui-btn-sm layui-btn-danger btn-lc" lay-submit lay-filter="attrDel">删除</button></div>';
            $("#sub-dom").before(newDom);
            form.render();
            return false;
        });

        // 删除属性
        form.on('submit(attrDel)', function(data) {
            $(this).parent().remove();
        });

        //
        form.on('select(platform)', function(data){
            axios.post("{:url('getUserAccountByPlatform')}", {platform: data.value})
                .then(function (response) {
                    let res = response.data;
                    if (res.code === 1) {
                        $('#user_account').html('');
                        let option = '';
                        $.each(res.data, function(key, value){
                            option += '<option value="' + value.id + '">' + value.user_account + '</option>';
                        })
                        $('#user_account').append(option);

                        if (data.value == "wayfair") {
                            $("#season").show();
                            $("#brand").hide();
                        } else if (data.value == "walmart") {
                            $("#season").show();
                            $("#brand").hide();
                        } else if (data.value == "temu") {
                            $("#season").show();
                            $("#brand").hide();
                        } else if (data.value == "shein") {
                            $("#season").show();
                            $("#brand").hide();
                        } else if (data.value == "tiktok") {
                            $("#brand").hide();
                        } else if (data.value == "ebay") {
                            $("#brand").hide();
                        } else {
                            $("#season").hide();
                            $("#brand").show();
                        }
                        form.render('select');
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

        //监听提交
        form.on('submit(formCoding)', function(data){
            let text = $(this).text(),
                button = $(this);
            $('button').attr('disabled',true);
            button.text('请稍候...');
            axios.post("{:url('add')}", data.field, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            })
                .then(function (response) {
                    let res = response.data;
                    if (res.code === 1) {
                        layer.alert(res.msg,{icon:1,closeBtn:0,title:false,btnAlign:'c',},function(){
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
