
{include file="public/header" /}

<!-- 主体内容 -->
<style>
    .layui-body {left: 220px!important;}
    .layui-form-label {width: 100px!important;}
    .layui-form-item .layui-inline {margin-right: 0!important;}
    .layui-form-label {width: 160px!important;}
</style>
<div class="layui-body" id="LAY_app_body">
    <div class="right">
        <a href="{:session('manage.back_url')}" class="layui-btn layui-btn-danger layui-btn-sm fr"><i class="layui-icon">&#xe603;</i>返回上一页</a>
        <div class="title">核价详情</div>
        <div class="layui-row">
            <div class="layui-col-md3">
                <div class="layui-form-item">
                    <label class="layui-form-label">包装长(cm)</label>
                    <div class="layui-text-inline">{$info.length}</div>
                </div>
            </div>
            <div class="layui-col-md3">
                <div class="layui-form-item">
                    <label class="layui-form-label">包装宽(cm)</label>
                    <div class="layui-text-inline">{$info.width}</div>
                </div>
            </div>
            <div class="layui-col-md3">
                <div class="layui-form-item">
                    <label class="layui-form-label">包装高(cm)</label>
                    <div class="layui-text-inline">{$info.height}</div>
                </div>
            </div>
        </div>
        <div class="layui-row">
            <div class="layui-col-md3">
                <div class="layui-form-item">
                    <label class="layui-form-label">毛重(kg)</label>
                    <div class="layui-text-inline">{$info.gross_weight}</div>
                </div>
            </div>
            <div class="layui-col-md3">
                <div class="layui-form-item">
                    <label class="layui-form-label">采购成本(¥)</label>
                    <div class="layui-text-inline">{$info.cost}</div>
                </div>
            </div>
            <div class="layui-col-md3">
                <div class="layui-form-item">
                    <label class="layui-form-label">最低市场售价($)</label>
                    <div class="layui-text-inline">{$info.min_price}</div>
                </div>
            </div>
            <div class="layui-col-md3">
                <div class="layui-form-item">
                    <label class="layui-form-label">目标定价($)</label>
                    <div class="layui-text-inline">{$info.target_pricing}</div>
                </div>
            </div>
        </div>
        <div class="layui-row">
            <div class="layui-col-md3">
                <div class="layui-form-item">
                    <label class="layui-form-label">头程价格标准(元/CBM)</label>
                    <div class="layui-text-inline">{$info.flp_standard}</div>
                </div>
            </div>
            <div class="layui-col-md3">
                <div class="layui-form-item">
                    <label class="layui-form-label">关税率</label>
                    <div class="layui-text-inline">{$info.tariff_rate}</div>
                </div>
            </div>
            <div class="layui-col-md3">
                <div class="layui-form-item">
                    <label class="layui-form-label">汇率</label>
                    <div class="layui-text-inline">{$info.exchange_rate}</div>
                </div>
            </div>
            <div class="layui-col-md3">
                <div class="layui-form-item">
                    <label class="layui-form-label">派送方式</label>
                    <div class="layui-text-inline">{$info.delivery}</div>
                </div>
            </div>
        </div>
        <div class="layui-row">
            <div class="layui-col-md3">
                <div class="layui-form-item">
                    <label class="layui-form-label">广告费占比</label>
                    <div class="layui-text-inline">{$info.ad_rate}</div>
                </div>
            </div>
            <div class="layui-col-md3">
                <div class="layui-form-item">
                    <label class="layui-form-label">退货率</label>
                    <div class="layui-text-inline">{$info.return_rate}</div>
                </div>
            </div>
            <div class="layui-col-md3">
                <div class="layui-form-item">
                    <label class="layui-form-label">平台费占比</label>
                    <div class="layui-text-inline">{$info.platform_rate}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="right">
        {foreach $info.storage_info.storage as $storage}
        <div class="title"><b class="black">{$storage.storage_name}</b></div>
        <div class="layui-row">
            <div class="layui-col-md3">
                <div class="layui-form-item">
                    <label class="layui-form-label">体积(m³)</label>
                    <div class="layui-text-inline">{$storage.data.volume}</div>
                </div>
            </div>
            <div class="layui-col-md3">
                <div class="layui-form-item">
                    <label class="layui-form-label">毛重(lbs)</label>
                    <div class="layui-text-inline">{$storage.data.gross_weight_lbs}</div>
                </div>
            </div>
            <div class="layui-col-md3">
                <div class="layui-form-item">
                    <label class="layui-form-label">体积重(lbs)</label>
                    <div class="layui-text-inline">{$storage.data.volume_lbs}</div>
                </div>
            </div>
        </div>
        <div class="layui-row">
            <div class="layui-col-md3">
                <div class="layui-form-item">
                    <label class="layui-form-label">装箱数</label>
                    <div class="layui-text-inline">1</div>
                </div>
            </div>
            <div class="layui-col-md3">
                <div class="layui-form-item">
                    <label class="layui-form-label">装柜数</label>
                    <div class="layui-text-inline">{$storage.data.loading_qty}</div>
                </div>
            </div>
            <div class="layui-col-md3">
                <div class="layui-form-item">
                    <label class="layui-form-label">FOB成本($)</label>
                    <div class="layui-text-inline">{$storage.data.fob}</div>
                </div>
            </div>
        </div>
        <div class="layui-row">
            <div class="layui-col-md3">
                <div class="layui-form-item">
                    <label class="layui-form-label">头程成本($)</label>
                    <div class="layui-text-inline">{$storage.data.initial_cost}</div>
                </div>
            </div>
            <div class="layui-col-md3">
                <div class="layui-form-item">
                    <label class="layui-form-label">关税($)</label>
                    <div class="layui-text-inline">{$storage.data.tariff}</div>
                </div>
            </div>
            <div class="layui-col-md3">
                <div class="layui-form-item">
                    <label class="layui-form-label">4个月仓储费($)</label>
                    <div class="layui-text-inline">{$storage.data.storage_charge}</div>
                </div>
            </div>
            <div class="layui-col-md3">
                <div class="layui-form-item">
                    <label class="layui-form-label"><b class="black">尾程($)</b></label>
                    <div class="layui-text-inline" id="liang_tail_end"><b class="black">{$storage.data.tail_end}</b></div>
                </div>
            </div>
        </div>
        <div class="layui-row">
            <div class="layui-col-md3">
                <div class="layui-form-item">
                    <label class="layui-form-label">头程成本占比</label>
                    <div class="layui-text-inline">{$storage.data.initial_cost_rate}</div>
                </div>
            </div>
            <div class="layui-col-md3">
                <div class="layui-form-item">
                    <label class="layui-form-label">关税占比</label>
                    <div class="layui-text-inline">{$storage.data.tariff_proportion}</div>
                </div>
            </div>
            <div class="layui-col-md3">
                <div class="layui-form-item">
                    <label class="layui-form-label">仓储费占比</label>
                    <div class="layui-text-inline">{$storage.data.storage_charge_proportion}</div>
                </div>
            </div>
            <div class="layui-col-md3">
                <div class="layui-form-item">
                    <label class="layui-form-label">尾程占比</label>
                    <div class="layui-text-inline">{$storage.data.tail_end_proportion}</div>
                </div>
            </div>
        </div>
        <div class="layui-row">
            <div class="layui-col-md3">
                <div class="layui-form-item">
                    <label class="layui-form-label">广告费($)</label>
                    <div class="layui-text-inline">{$storage.data.advertising_expenses}</div>
                </div>
            </div>
            <div class="layui-col-md3">
                <div class="layui-form-item">
                    <label class="layui-form-label">退货费($)</label>
                    <div class="layui-text-inline">{$storage.data.return_fee}</div>
                </div>
            </div>
            <div class="layui-col-md3">
                <div class="layui-form-item">
                    <label class="layui-form-label">平台费($)</label>
                    <div class="layui-text-inline">{$storage.data.platform_fees}</div>
                </div>
            </div>
            <div class="layui-col-md3">
                <div class="layui-form-item">
                    <label class="layui-form-label"><b class="black">零利润售价($)</b></label>
                    <div class="layui-text-inline"><b class="black">{$storage.data.no_profit_price}</b></div>
                </div>
            </div>
        </div>
        <div class="layui-row">
            <div class="layui-col-md3">
                <div class="layui-form-item">
                    <label class="layui-form-label"><b class="black">最低售价利润($)</b></label>
                    <div class="layui-text-inline"><b class="black">{$storage.data.min_selling_profit}</b></div>
                </div>
            </div>
            <div class="layui-col-md3">
                <div class="layui-form-item">
                    <label class="layui-form-label"><b class="black">最低售价利润率</b></label>
                    <div class="layui-text-inline"><b class="black">{$storage.data.min_selling_profit_rate}</b></div>
                </div>
            </div>
            <div class="layui-col-md3">
                <div class="layui-form-item">
                    <label class="layui-form-label"><b class="black">目标定价利润($)</b></label>
                    <div class="layui-text-inline"><b class="black">{$storage.data.target_pricing_profit}</b></div>
                </div>
            </div>
            <div class="layui-col-md3">
                <div class="layui-form-item">
                    <label class="layui-form-label"><b class="black">目标定价利润率</b></label>
                    <div class="layui-text-inline"><b class="black">{$storage.data.target_pricing_profit_rate}</b></div>
                </div>
            </div>
        </div>
        {/foreach}
    </div>
</div>
<script>
    layui.use(['form', 'jquery'], function(){
        var $ = layui.jquery,
            form = layui.form;

        // 更新
        form.on('submit(formCoding)', function(data) {
            var text = $(this).text(),
                button = $(this);
            $('button').attr('disabled',true);
            button.text('请稍候...');
            $.ajax({
                type:'POST',url:"{:url('sitemap')}",data:data.field,dataType:'json',
                success:function(data){
                    if(data.code == 1){
                        layer.alert(data.msg,{icon:1,closeBtn:0,title:false,btnAlign:'c'},function(){
                            location.href = "{:url('sitemap')}";
                        });
                    }else{
                        layer.alert(data.msg,{icon:2,closeBtn:0,title:false,btnAlign:'c'},function(){
                            layer.closeAll();
                            $('button').attr('disabled',false);
                            button.text(text);
                        });
                    }
                }
            });
            return false;
        });
    });
</script>

{include file="public/footer" /}
