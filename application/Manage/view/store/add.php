
{include file="public/header" /}

<style>
    .layui-body {left: 220px!important;}
    .layui-form-label {width: 100px!important;}
    .layui-form-item .layui-inline {margin-right: 0!important;}
    .layui-form-label {width: 160px!important;}
    .w84 {width: 84px!important;}
    .deliver_num {width: 100px!important;}
    .layui-table {width: 50%; display: inline}
    .select {margin-left: 0!important;}
    .warm-tips {display: inline-block; font-size: 14px; position: relative; top: 8px; left: 5px; color: #ce0000}
</style>
<div class="layui-body">
<div class="right">
    <a href="{:session('manage.back_url')}" class="layui-btn layui-btn-danger layui-btn-sm fr"><i class="layui-icon">&#xe603;</i>返回上一页</a>
    <div class="title">返单测算</div>
    <div class="layui-form">
        <div class="layui-form-item">
            <div class="layui-inline layui-col-md4">
                <label class="layui-form-label">测算日期</label>
                <div class="layui-input-inline">
                    <input type="text" autocomplete="off" class="layui-input datetime w300" name="query_date" value="{$query_date}">
                </div>
            </div>
        </div>
        <div class="layui-form-item">
            <div class="layui-inline layui-col-md4">
                <label class="layui-form-label">产品名称<span class="red">*</span></label>
                <div class="layui-input-inline">
                    <input type="text" autocomplete="off" class="layui-input w300" name="product_name" value="{$info.product_name}">
                </div>
            </div>
            <div class="layui-inline layui-col-md4">
                <label class="layui-form-label">产品SKU<span class="red">*</span></label>
                <div class="layui-input-inline">
                    <input type="text" autocomplete="off" class="layui-input w300" name="product_sku" value="{$info.product_sku}">
                </div>
            </div>
        </div>
        <div class="layui-form-item">
            <div class="layui-inline layui-col-md6">
                <label class="layui-form-label">美西美东出单比例<span class="red">*</span></label>
                <div class="layui-input-block layui-col-md4 select">
                    <select name="w_sale_proportion" lay-verify="">
                        <option value=""></option>
                        <option value="0.2" {if condition="$info.w_sale_proportion eq '0.2'"}selected{/if}>美西20% 美东80%</option>
                        <option value="0.3" {if condition="$info.w_sale_proportion eq '0.3'"}selected{/if}>美西30% 美东70%</option>
                        <option value="0.4" {if condition="$info.w_sale_proportion eq '0.4'"}selected{/if}>美西40% 美东60%</option>
                        <option value="0.5" {if condition="$info.w_sale_proportion eq '0.5'"}selected{/if}>美西50% 美东50%</option>
                    </select>
                </div>
            </div>
        </div>
        {if condition="$id eq 0"}
        <div class="layui-form-item" id="sale-item">
            <div class="layui-inline layui-col-md3">
                <label class="layui-form-label"><b>日</b>销量预估<span class="red">*</span></label>
                <div class="layui-input-inline">
                    <input type="text" class="layui-input" name="month[]" placeholder="年月(如:202401)">
                </div>
            </div>
            <div class="layui-inline layui-col-md1">
                <div class="layui-input-inline deliver_num">
                    <input type="text" class="layui-input w84" name="sale[]" placeholder="销量">
                </div>
            </div>
            <button class="layui-btn layui-btn-sm btn-lc" lay-submit lay-filter="saleAdd">添加</button>
            <div class="warm-tips"></div>
        </div>
        {else /}
        <?php $i = 0; ?>
        {foreach name="$info.sale_data" item="sale_data"}
        {if condition="$i eq 0"}
        <div class="layui-form-item" id="sale-item">
            <div class="layui-inline layui-col-md3">
                <label class="layui-form-label"><b>日</b>销量预估<span class="red">*</span></label>
                <div class="layui-input-inline">
                    <input type="text" class="layui-input" name="month[]" placeholder="月份" value="{$key}">
                </div>
            </div>
            <div class="layui-inline layui-col-md1">
                <div class="layui-input-inline deliver_num">
                    <input type="text" class="layui-input w84" name="sale[]" placeholder="销量" value="{$sale_data}">
                </div>
            </div>
            <button class="layui-btn layui-btn-sm btn-lc" lay-submit lay-filter="saleAdd">添加</button>
            <div class="warm-tips"></div>
        </div>
        {else /}
        <div class="layui-form-item" id="sale-item">
            <div class="layui-inline layui-col-md3">
                <label class="layui-form-label"></label>
                <div class="layui-input-inline">
                    <input type="text" class="layui-input" name="month[{$i}]" placeholder="月份" value="{$key}">
                </div>
            </div>
            <div class="layui-inline layui-col-md1">
                <div class="layui-input-inline deliver_num">
                    <input type="text" class="layui-input w84" name="sale[{$i}]" placeholder="销量" value="{$sale_data}">
                </div>
            </div>
            <button class="layui-btn layui-btn-sm layui-btn-danger btn-lc" lay-submit lay-filter="attrDel">删除</button>
            <div class="warm-tips"></div>
        </div>
        {/if}
        <?php $i++; ?>
        {/foreach}
        {/if}
        <div class="title" id="america-west">美国西部</div>
        <div class="layui-form-item">
            <div class="layui-inline layui-col-md4">
                <label class="layui-form-label">当前库存<span class="red">*</span></label>
                <div class="layui-input-inline">
                    <input type="text" autocomplete="off" class="layui-input w300" name="w_basic_store" value="{$info.post_data.w_info.basic_store}">
                </div>
            </div>
            <div class="warm-tips">（*实际上架日期 = 预计到港日期 + 10天。预计到港日期 = 开船日期 + 26天）</div>
        </div>
        {if condition="$id eq 0"}
        <div class="layui-form-item" id="w-deliver-item">
            <div class="layui-inline layui-col-md3">
                <label class="layui-form-label">在途详情(预计到港日期)<span class="red">*</span></label>
                <div class="layui-input-inline">
                    <input type="text" class="layui-input datetime" name="w_deliver_date[]" placeholder="日期" belong="a-w">
                </div>
            </div>
            <div class="layui-inline layui-col-md1">
                <div class="layui-input-inline deliver_num">
                    <input type="text" class="layui-input w84" name="w_deliver_num[]" placeholder="发货量">
                </div>
            </div>
            <button class="layui-btn layui-btn-sm btn-lc" lay-submit lay-filter="wDeliverAdd">添加</button>
            <div class="warm-tips"></div>
        </div>
        {else /}
        <?php $j = 0; ?>
        {foreach name="$info.post_data.w_info.deliver" item="deliver" key="jk"}
        {if condition="$j eq 0"}
        <div class="layui-form-item" id="w-deliver-item">
            <div class="layui-inline layui-col-md3">
                <label class="layui-form-label">在途详情(预计到港日期)<span class="red">*</span></label>
                <div class="layui-input-inline">
                    <input type="text" class="layui-input datetime" name="w_deliver_date[]" placeholder="日期" value="{$jk}" belong="a-w">
                </div>
            </div>
            <div class="layui-inline layui-col-md1">
                <div class="layui-input-inline deliver_num">
                    <input type="text" class="layui-input w84" name="w_deliver_num[]" placeholder="发货量" value="{$deliver}">
                </div>
            </div>
            <button class="layui-btn layui-btn-sm btn-lc" lay-submit lay-filter="wDeliverAdd">添加</button>
            <div class="warm-tips">{$info['post_data']['w_info']['tip'][$j]}</div>
        </div>
        {else /}
        <div class="layui-form-item" id="w-deliver-item">
            <div class="layui-inline layui-col-md3">
                <label class="layui-form-label"></label>
                <div class="layui-input-inline">
                    <input type="text" class="layui-input datetime" name="w_deliver_date[{$j}]" placeholder="日期" value="{$jk}" belong="a-w">
                </div>
            </div>
            <div class="layui-inline layui-col-md1">
                <div class="layui-input-inline deliver_num">
                    <input type="text" class="layui-input w84" name="w_deliver_num[{$j}]" placeholder="发货量" value="{$deliver}">
                </div>
            </div>
            <button class="layui-btn layui-btn-sm layui-btn-danger btn-lc" lay-submit lay-filter="attrDel">删除</button>
            <div class="warm-tips">{$info['post_data']['w_info']['tip'][$j]}</div>
        </div>
        {/if}
        <?php $j++; ?>
        {/foreach}
        {/if}
        <div class="title" id="america-east">美国东部</div>
        <div class="layui-form-item">
            <div class="layui-inline layui-col-md4">
                <label class="layui-form-label">当前库存<span class="red">*</span></label>
                <div class="layui-input-inline">
                    <input type="text" autocomplete="off" class="layui-input w300" name="e_basic_store" value="{$info.post_data.e_info.basic_store}">
                </div>
            </div>
            <div class="warm-tips">（*实际上架日期 = 预计到港日期 + 10天。预计到港日期 = 开船日期 + 40天）</div>
        </div>
        {if condition="$id eq 0"}
        <div class="layui-form-item" id="e-deliver-item">
            <div class="layui-inline layui-col-md3">
                <label class="layui-form-label">在途详情(预计到港日期)<span class="red">*</span></label>
                <div class="layui-input-inline">
                    <input type="text" class="layui-input datetime" name="e_deliver_date[]" placeholder="日期" belong="a-e">
                </div>
            </div>
            <div class="layui-inline layui-col-md1">
                <div class="layui-input-inline deliver_num">
                    <input type="text" class="layui-input w84" name="e_deliver_num[]" placeholder="发货量">
                </div>
            </div>
            <button class="layui-btn layui-btn-sm btn-lc" lay-submit lay-filter="eDeliverAdd">添加</button>
            <div class="warm-tips"></div>
        </div>
        {else /}
        <?php $k = 0; ?>
        {foreach name="$info.post_data.e_info.deliver" item="deliver" key="kk"}
        {if condition="$k eq 0"}
        <div class="layui-form-item" id="e-deliver-item">
            <div class="layui-inline layui-col-md3">
                <label class="layui-form-label">在途详情(预计到港日期)<span class="red">*</span></label>
                <div class="layui-input-inline">
                    <input type="text" class="layui-input datetime" name="e_deliver_date[]" placeholder="日期" value="{$kk}" belong="a-e">
                </div>
            </div>
            <div class="layui-inline layui-col-md1">
                <div class="layui-input-inline deliver_num">
                    <input type="text" class="layui-input w84" name="e_deliver_num[]" placeholder="发货量" value="{$deliver}">
                </div>
            </div>
            <button class="layui-btn layui-btn-sm btn-lc" lay-submit lay-filter="eDeliverAdd">添加</button>
            <div class="warm-tips">{$info['post_data']['e_info']['tip'][$k]}</div>
        </div>
        {else /}
        <div class="layui-form-item" id="e-deliver-item">
            <div class="layui-inline layui-col-md3">
                <label class="layui-form-label"></label>
                <div class="layui-input-inline">
                    <input type="text" class="layui-input datetime" name="e_deliver_date[{$k}]" placeholder="日期" value="{$kk}" belong="a-e">
                </div>
            </div>
            <div class="layui-inline layui-col-md1">
                <div class="layui-input-inline deliver_num">
                    <input type="text" class="layui-input w84" name="e_deliver_num[{$k}]" placeholder="发货量" value="{$deliver}">
                </div>
            </div>
            <button class="layui-btn layui-btn-sm layui-btn-danger btn-lc" lay-submit lay-filter="attrDel">删除</button>
            <div class="warm-tips">{$info['post_data']['e_info']['tip'][$k]}</div>
        </div>
        {/if}
        <?php $k++; ?>
        {/foreach}
        {/if}
        <div class="layui-form-item tl" id="btn-submit">
            <div class="layui-input-block">
                <button class="layui-btn w200 button" lay-submit lay-filter="formCoding" style="margin-right: 80px!important;">提交</button>
                <a href="{:url('add')}" class="layui-btn layui-btn-normal w200">重置</a>
            </div>
        </div>
    </div>
    {if condition="$id neq 0"}
    <div class="title">测算结果</div>
    <table class="layui-table">
        <colgroup>
            <col width="25%">
            <col width="25%">
        </colgroup>
        <thead>
        <tr>
            <th colspan="2" class="tc">美国西部</th>
        </tr>
        </thead>
        <tbody>
        {foreach name="$info.store_data.w" item="w_store"}
        <tr>
            <td class="tc">{:date('Y-m-d', strtotime($key))}</td>
            <td class="tc">{$w_store|round=###,0}</td>
        </tr>
        {/foreach}
        </tbody>
    </table>
    <table class="layui-table">
        <colgroup>
            <col width="25%">
            <col width="25%">
        </colgroup>
        <thead>
        <tr>
            <th colspan="2" class="tc">美国东部</th>
        </tr>
        </thead>
        <tbody>
        {foreach name="$info.store_data.e" item="e_store"}
        <tr>
            <td class="tc">{:date('Y-m-d', strtotime($key))}</td>
            <td class="tc">{$e_store|round=###,0}</td>
        </tr>
        {/foreach}
        </tbody>
    </table>
    <table class="layui-table">
        <colgroup>
            <col width="25%">
            <col>
        </colgroup>
        <thead>
        <tr>
            <th colspan="2" class="tc">总库存预警</th>
        </tr>
        </thead>
        <tbody>
        {foreach name="$info.store_data.sum" item="sum"}
        <tr>
            <td class="tc">
                {if condition="$sum['code'] eq 2"}
                <p class="blue">{$sum.value|round=###, 0}</p>
                {elseif condition="$sum['code'] eq 1"/}
                <p class="red">{$sum.value|round=###, 0}</p>
                {else/}
                <p class="green">{$sum.value|round=###, 0}</p>
                {/if}
            </td>
            <td class="tl">
                {if condition="$sum['code'] eq 2"}
                <p class="blue">总库存可销售日期高于90天</p>
                {elseif condition="$sum['code'] eq 1"/}
                <p class="red">总库存可销售日期低于45天</p>
                {else/}
                <p class="green">正常</p>
                {/if}
            </td>
        </tr>
        {/foreach}
        </tbody>
    </table>
    {/if}
</div>
</div>
<script>
    layui.config({
        base: '/static/layuiadmin/' //静态资源所在路径
    }).extend({
        index: 'lib/index' //主入口模块
    }).use('index');

    layui.use(['form', 'jquery', 'laydate'], function() {
        let $ = layui.jquery,
            form = layui.form,
            laydate = layui.laydate;

        function timeAdd(){
            lay('.datetime').each(function() {
                laydate.render({
                    elem : this,
                    trigger : 'click',
                    done: function(value, date, endDate){
                        let dateObj = new Date(value);
                        if (this.elem[0].getAttribute('belong') === "a-w") {
                            const transDate = {$Think.config.AMERICAN_WEST_TRANSFER_DAY} //
                            const orderDate = {$Think.config.AMERICAN_WEST_ORDER_DAY};

                            let timestamp = dateObj.getTime() - transDate * 24 * 60 * 60 * 1000;
                            let newDateObj = new Date(timestamp);
                            let transDateFormat = newDateObj.getFullYear() + "-" + (newDateObj.getMonth() + 1) + "-" + newDateObj.getDate();
                            let oTimestamp = dateObj.getTime() - orderDate * 24 * 60 * 60 * 1000;
                            let oNewDateObj = new Date(oTimestamp);
                            let orderDateFormat = oNewDateObj.getFullYear() + "-" + (oNewDateObj.getMonth() + 1) + "-" + oNewDateObj.getDate();
                            this.elem[0].parentNode.parentNode.nextElementSibling.nextElementSibling.nextElementSibling.innerText = "出运日期" + transDateFormat + "，下单日期" + orderDateFormat;
                        } else if (this.elem[0].getAttribute('belong') === "a-e") {
                            const transDate = {$Think.config.AMERICAN_EAST_TRANSFER_DAY}; //
                            const orderDate = {$Think.config.AMERICAN_EAST_ORDER_DAY};

                            let timestamp = dateObj.getTime() - transDate * 24 * 60 * 60 * 1000;
                            let newDateObj = new Date(timestamp);
                            let transDateFormat = newDateObj.getFullYear() + "-" + (newDateObj.getMonth() + 1) + "-" + newDateObj.getDate();
                            let oTimestamp = dateObj.getTime() - orderDate * 24 * 60 * 60 * 1000;
                            let oNewDateObj = new Date(oTimestamp);
                            let orderDateFormat = oNewDateObj.getFullYear() + "-" + (oNewDateObj.getMonth() + 1) + "-" + oNewDateObj.getDate();
                            this.elem[0].parentNode.parentNode.nextElementSibling.nextElementSibling.nextElementSibling.innerText = "出运日期" + transDateFormat + "，下单日期" + orderDateFormat;
                        } else {
                            return false;
                        }
                    }
                });
            });
        }

        timeAdd();

        $('.datetime').on('click', function(){
            // 显示日期选择器
            laydate.render({
                elem: '.datetime'
            });
        });

        // 添加属性
        let saleIndex = {$info.sale_data|count} ? {$info.sale_data|count} - 1 : 0;
        form.on('submit(saleAdd)', function(data) {
            saleIndex ++;
            var new_attr =
                '<div class="layui-form-item" id="sale-item"><div class="layui-inline layui-col-md3"><label class="layui-form-label"></label><div class="layui-input-inline"><input type="text" class="layui-input" name="month[' + saleIndex + ']" placeholder="月份"></div></div><div class="layui-inline layui-col-md1"><div class="layui-input-inline deliver_num"><input type="text" class="layui-input w84" name="sale[' + saleIndex + ']" placeholder="销量"></div></div><button class="layui-btn layui-btn-sm layui-btn-danger btn-lc" lay-submit lay-filter="attrDel">删除</button><div class="warm-tips"></div></div>';
            $("#america-west").before(new_attr);
            form.render();
            timeAdd();
            return false;
        });

        let wDeliverIndex = {$info.post_data.w_info.deliver|count} ? {$info.post_data.w_info.deliver|count} - 1 : 0;
        form.on('submit(wDeliverAdd)', function(data) {
            wDeliverIndex ++;
            var new_attr = '<div class="layui-form-item"><div class="layui-inline layui-col-md3"><label class="layui-form-label"></label><div class="layui-input-inline"><input type="text" class="layui-input datetime" name="w_deliver_date[' + wDeliverIndex + ']" placeholder="日期" belong="a-w"></div></div><div class="layui-inline layui-col-md1"><div class="layui-input-inline deliver_num"><input type="text" class="layui-input w84" name="w_deliver_num[' + wDeliverIndex + ']" placeholder="发货量"></div></div><button class="layui-btn layui-btn-sm layui-btn-danger btn-lc" lay-submit lay-filter="attrDel">删除</button><div class="warm-tips"></div></div></div>';
            $("#america-east").before(new_attr);
            form.render();
            timeAdd();
            return false;
        });

        let eDeliverIndex = {$info.post_data.e_info.deliver|count} ? {$info.post_data.e_info.deliver|count} - 1 : 0;
        form.on('submit(eDeliverAdd)', function(data) {
            eDeliverIndex ++;
            var new_attr = '<div class="layui-form-item"><div class="layui-inline layui-col-md3"><label class="layui-form-label"></label><div class="layui-input-inline"><input type="text" class="layui-input datetime" name="e_deliver_date[' + eDeliverIndex + ']" placeholder="日期" belong="a-e"></div></div><div class="layui-inline layui-col-md1"><div class="layui-input-inline deliver_num"><input type="text" class="layui-input w84" name="e_deliver_num[' + eDeliverIndex + ']" placeholder="发货量"></div></div><button class="layui-btn layui-btn-sm layui-btn-danger btn-lc" lay-submit lay-filter="attrDel">删除</button><div class="warm-tips"></div></div></div>';
            $("#btn-submit").before(new_attr);
            form.render();
            timeAdd();
            return false;
        });

        // 删除属性
        form.on('submit(attrDel)', function(data) {
            $(this).parent().remove();
        });

        // 提交
        form.on('submit(formCoding)', function(data) {
            var text = $(this).text(),
                button = $(this);
            $('button').attr('disabled',true);
            button.text('请稍候...');
            $.ajax({
                type:'POST',url:"{:url('add')}",data:data.field,dataType:'json',
                success:function(data){
                    if(data.code === 1){
                        layer.alert(data.msg,{icon:1,closeBtn:0,title:false,btnAlign:'c'},function(){
                            // location.href = "{:url('add', ['id' => " + data.id + "])}";
                            location.href = "/Manage/Store/add/id/" + data.id + ".html";
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
