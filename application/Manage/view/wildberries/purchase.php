
{include file="public/header" /}

<!-- 主体内容 -->
<div class="layui-body" id="LAY_app_body">
    <div class="right">
		<a href="{:url('index')}" class="layui-btn layui-btn-danger layui-btn-sm fr"><i class="layui-icon">&#xe603;</i>返回上一页</a>
        <div class="title">采购</div>
		<div class="layui-form">
            <div class="layui-form-item">
                <label class="layui-form-label w100">WB平台订单号</label>
                <div class="layui-input-inline w300">
                    <div class="layui-text-inline">{$info.wb_order_code}</div>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label w100">WB产品编号</label>
                <div class="layui-input-inline w300">
                    <div class="layui-text-inline">{$info.wb_product_code}</div>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label w100">产品名称</label>
                <div class="layui-input-inline w300">
                    <div class="layui-text-inline">{$info.product_name}</div>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label w100">数量</label>
                <div class="layui-input-inline w300">
                    <div class="layui-text-inline">{$info.qty}</div>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label w100">颜色</label>
                <div class="layui-input-inline w300">
                    <div class="layui-text-inline">{$info.color}</div>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label w100">尺寸</label>
                <div class="layui-input-inline w300">
                    <div class="layui-text-inline">{$info.size}</div>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label w100">采购链接</label>
                <div class="layui-input-inline w300">
                    <a href="{$info.purchase_url}" target="_blank"><div class="layui-text-inline">{$info.purchase_url}</div></a>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label w100">包装要求</label>
                <div class="layui-input-inline w300">
                    <div class="layui-text-inline">{$info.packaging_requirements}</div>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label w100">采购单号</label>
                <div class="layui-input-inline w300">
                    <input type="text" class="layui-input" name="po_no" placeholder="请填写采购单号">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label w100">1688运单号</label>
                <div class="layui-input-inline w300">
                    <input type="text" class="layui-input" name="tracking_no_1" placeholder="请填写1688运单号">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label w100">采购单价</label>
                <div class="layui-input-inline w300">
                    <input type="text" class="layui-input" name="unit_price" placeholder="请填写采购单价">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label w100">采购总金额</label>
                <div class="layui-input-inline w300">
                    <input type="text" class="layui-input" name="amount" placeholder="请填写采购总金额">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label w100">采购日期</label>
                <div class="layui-input-inline w300">
                    <input type="text" class="layui-input" id="date" name="purchase_date"">
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
layui.use(['form', 'jquery', 'laydate'], function(){
	let $ = layui.jquery,
		form = layui.form,
        laydate = layui.laydate;

    // 显示日期选择器
    laydate.render({
        elem: '#date',
        type: 'date'
    });

	//监听提交
	form.on('submit(formCoding)', function(data){
		let text = $(this).text(),
			button = $(this);
		$('button').attr('disabled',true);
		button.text('请稍候...');
        axios.post("{:url('purchase', ['id' => $info['id']])}", data.field)
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
