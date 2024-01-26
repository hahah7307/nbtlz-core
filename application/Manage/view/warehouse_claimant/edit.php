
{include file="public/header" /}

<!-- 主体内容 -->
<div class="layui-body" id="LAY_app_body">
    <div class="right">
		<a href="{:url('index')}" class="layui-btn layui-btn-danger layui-btn-sm fr"><i class="layui-icon">&#xe603;</i>返回上一页</a>
        <div class="title">编辑海外仓索赔</div>
		<div class="layui-form">
            <div class="layui-form-item">
                <label class="layui-form-label">所属仓库</label>
                <div class="layui-input-inline w300">
                    <select name="warehouse_id" lay-verify="">
                        {foreach name="warehouse" item="v"}
                        <option value="{$v.id}" {if condition="$v.id eq $info.warehouse_id"}selected{/if}>{$v.name}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">索赔类型</label>
                <div class="layui-input-inline w300">
                    <select name="claimant_type" lay-verify="">
                        <option value="1" {if condition="$info.claimant_type eq 1"}selected{/if}>库内丢失</option>
                        <option value="2" {if condition="$info.claimant_type eq 2"}selected{/if}>妥投未签收</option>
                        <option value="3" {if condition="$info.claimant_type eq 3"}selected{/if}>暴力运输（产品破损）</option>
                        <option value="4" {if condition="$info.claimant_type eq 4"}selected{/if}>途中丢包</option>
                    </select>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">跟踪号</label>
                <div class="layui-input-inline w300">
                    <input type="text" class="layui-input" name="shipping_method_no" value="{$info.shipping_method_no}">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">订单号</label>
                <div class="layui-input-inline w300">
                    <input type="text" class="layui-input" name="reference_no" value="{$info.reference_no}">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">仓库SKU</label>
                <div class="layui-input-inline w300">
                    <input type="text" class="layui-input" name="product_sku" value="{$info.product_sku}">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">索赔金额</label>
                <div class="layui-input-inline w300">
                    <input type="text" class="layui-input" name="claimant_amount" value="{$info.claimant_amount}">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">到账金额</label>
                <div class="layui-input-inline w300">
                    <input type="text" class="layui-input" name="received_amount" value="{$info.received_amount}">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">当前状态</label>
                <div class="layui-input-block">
                    <input type="radio" name="state" value="0" title="未完成" {if condition="$info.state eq 0"}checked{/if}>
                    <input type="radio" name="state" value="1" title="已完成" {if condition="$info.state eq 1"}checked{/if}>
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
<script src="https://unpkg.com/axios/dist/axios.min.js"></script>
<script>
layui.use(['form', 'jquery'], function(){
	let $ = layui.jquery,
		form = layui.form;

	//监听提交
	form.on('submit(formCoding)', function(data){
		let text = $(this).text(),
			button = $(this);
		$('button').attr('disabled',true);
		button.text('请稍候...');
        axios.post("{:url('edit', ['id' => $info['id']])}", data.field)
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
