
{include file="public/header" /}

<!-- 主体内容 -->
<div class="layui-body" id="LAY_app_body">
    <div class="right">
        <a href="{:url('index')}" class="layui-btn layui-btn-danger layui-btn-sm fr"><i class="layui-icon">&#xe603;</i>返回上一页</a>
        <div class="title">编辑销售产品</div>
        <div class="layui-form">
            <div class="layui-form-item">
                <label class="layui-form-label">所属平台</label>
                <div class="layui-input-block w300"">
                <select name="platform" lay-filter="platform" id="platform">
                    <option value=""></option>
                    {foreach name="platform" item="v"}
                    <option value="{$v.platform}" {if condition="$info.platform eq $v.platform"}selected{/if}>{$v.platform}</option>
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
    <div class="layui-form-item">
        <label class="layui-form-label">销售SKU</label>
        <div class="layui-input-inline w300">
            <input type="text" class="layui-input" name="seller_sku" value="{$info.seller_sku}" placeholder="请填写销售SKU">
        </div>
    </div>
    {foreach name="list" key="k" item="v"}
    <div class="layui-form-item">
        <label class="layui-form-label">仓库SKU</label>
        <div class="layui-input-inline w300">
            <input type="text" class="layui-input" name="warehouse_sku[]" value="{$v.warehouse_sku}" placeholder="请填写仓库SKU">
        </div>
        <label class="layui-form-label">产品数量</label>
        <div class="layui-input-inline w300">
            <input type="text" class="layui-input" name="qty[]" value="{$v.qty}" placeholder="请填写产品数量">
        </div>
        {if condition="$k eq 0"}
            <button class="layui-btn layui-btn-sm btn-lc" lay-submit lay-filter="AttrAdd">添加</button>
        {else/}
            <button class="layui-btn layui-btn-sm layui-btn-danger btn-lc" lay-submit lay-filter="attrDel">删除</button>
        {/if}
    </div>
    {/foreach}
    <div class="layui-form-item" id="sub-dom">
        <label class="layui-form-label">仓库名称</label>
        <div class="layui-input-block w300"">
        <select name="warehouse_name" lay-filter="platform">
            <option value="全部仓库" {if condition="$info.warehouse_name eq '全部仓库'"}selected{/if}>全部仓库</option>
        </select>
    </div>
    <input type="hidden" name="ss_code" value="{$info.ss_code}">
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

        $(document).ready(function() {
            $('#platform').val("{$info.platform}").trigger('change');
            axios.post("{:url('getUserAccountByPlatform')}", {platform: "{$info.platform}", userAccountId: {$info.user_account}})
                .then(function (response) {
                    let res = response.data;
                    if (res.code === 1) {
                        $('#user_account').html('');
                        let option = '';
                        let selected = '';
                        $.each(res.data, function(key, value){
                            selected = value.id === res.user_account_id ? 'selected' : '';
                            option += '<option value="' + value.id + '" ' + selected + '>' + value.user_account + '</option>';
                        })
                        $('#user_account').append(option);
                        form.render('select');
                    }
                });
        });

        let domIndex = {:count($list)} - 1;
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
            axios.post("{:url('getUserAccountByPlatform')}", {platform: data.value, userAccountId: {$info.user_account}})
                .then(function (response) {
                    let res = response.data;
                    if (res.code === 1) {
                        $('#user_account').html('');
                        let option = '';
                        let selected = '';
                        $.each(res.data, function(key, value){
                            selected = value.id === response.user_account_id ? 'selected' : '';
                            option += '<option value="' + value.id + '" ' + selected + '>' + value.user_account + '</option>';
                        })
                        $('#user_account').append(option);
                        form.render('select');
                    }
                });
            return false;
        });

        //监听提交
        form.on('submit(formCoding)', function(data){
            let text = $(this).text(),
                button = $(this);
            $('button').attr('disabled',true);
            button.text('请稍候...');
            axios.post("{:url('edit', ['id' => $info['id']])}", data.field, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            })
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
