
{include file="public/header" /}

<style>
    .layui-form-switch {margin: 8px 4px 0 !important;}
</style>
<!-- 主体内容 -->
<div class="layui-body" id="LAY_app_body">
    <div class="right">
        <div class="title">海外仓编码</div>
		<div class="layui-form">
            <div class="layui-form-item">
                <label class="layui-form-label">SKU</label>
                <div class="layui-input-inline w300">
                    <textarea name="sku" placeholder="请输入SKU" class="layui-textarea"></textarea>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">仓库</label>
                <div class="layui-input-block">
                    {foreach name="warehouseBarcode" item="warehouse"}
                    <input type="checkbox" name="warehouse_code[]" title="{$warehouse.warehouse_code}" value="{$warehouse.warehouse_id}" checked><br><br>
                    {/foreach}
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">产品校验</label>
                <div class="layui-input-block">
                    <input type="checkbox" lay-filter="switchDemo" name="is_verify" lay-skin="switch" value="1" checked>
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
        axios.post("{:url('warehouse_barcode')}", data.field, {
            headers: {
                'Content-Type': 'multipart/form-data' // 设置请求头，确保服务器正确解析 FormData
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

    form.on('switch(switchDemo)', function(data) {
        console.log(data.elem.checked);
        if (data.elem.checked === false) {
            layer.alert("警告：关闭产品校验后，请做好SKU的人工校验，并及时同步到易仓系统！",{icon:0,closeBtn:0,title:false,btnAlign:'c'},function(){
                layer.closeAll();
            });
        }
    });
});
</script>

{include file="public/footer" /}
