
{include file="public/header" /}

<!-- 主体内容 -->
<div class="layui-body" id="LAY_app_body">
    <div class="right">
		<a href="{:url('index')}" class="layui-btn layui-btn-danger layui-btn-sm fr"><i class="layui-icon">&#xe603;</i>返回上一页</a>
        <div class="title">编辑</div>
		<div class="layui-form">
            <div class="layui-form-item">
                <label class="layui-form-label w100">WB平台订单号</label>
                <div class="layui-input-inline w300">
                    <input type="text" class="layui-input" name="wb_order_code" value="{$info.wb_order_code}" placeholder="请填写WB平台订单号">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label w100">WB产品编号</label>
                <div class="layui-input-inline w300">
                    <input type="text" class="layui-input" name="wb_product_code" value="{$info.wb_product_code}" placeholder="请填写WB产品编号">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label w100">产品名称</label>
                <div class="layui-input-inline w300">
                    <input type="text" class="layui-input" name="product_name" value="{$info.product_name}" placeholder="请填写产品名称">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label w100">数量</label>
                <div class="layui-input-inline w300">
                    <input type="text" class="layui-input" name="qty" value="{$info.qty}" placeholder="请填写数量">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label w100">颜色</label>
                <div class="layui-input-inline w300">
                    <input type="text" class="layui-input" name="color" value="{$info.color}" placeholder="请填写颜色">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label w100">尺寸</label>
                <div class="layui-input-inline w300">
                    <input type="text" class="layui-input" name="size" value="{$info.size}" placeholder="请填写尺寸">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label w100">采购链接</label>
                <div class="layui-input-inline w300">
                    <input type="text" class="layui-input" name="purchase_url" value="{$info.purchase_url}" placeholder="请填写采购编号">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label w100">包装要求</label>
                <div class="layui-input-inline w300">
                    <textarea name="packaging_requirements" placeholder="请填写包装要求" class="layui-textarea">{$info.packaging_requirements}</textarea>
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
                        location.href = "{:url('index')}";
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
