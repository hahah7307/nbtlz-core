
{include file="public/header" /}

<!-- 主体内容 -->
<div class="layui-body" id="LAY_app_body">
    <div class="right">
        <div class="title">订单信息留存</div>
        <form class="layui-form search-form" method="get">
            <div class="layui-inline w200">
                <input type="text" class="layui-input" name="keyword" value="{$keyword}">
            </div>
            <div class="layui-inline">
                <button class="layui-btn" lay-submit lay-filter="Search"><i class="layui-icon">&#xe615;</i> 查询</button>
            </div>
        </form>

        <div class="layui-form">
            <div class="layui-input-inline w180">
                <select name="user_account" id="user_account">
                    <option value="">请选择店铺</option>
                    <option value="1">CA_JG_Direct_US_US</option>
                    <option value="3">US_Carajali_US</option>
                    <option value="4">TOLEAD_CN_US</option>
                    <option value="5">USA_FW_Direct_US</option>
                    <option value="6">TOPWIN_Direct_US</option>
                    <option value="7">Ori_Power_Medtech_US_US</option>
                    <option value="8">MARKETUNION_US_US</option>
                    <option value="9">Glory_Universal_Direct_US</option>
                    <option value="10">USA_MC_Direct_US</option>
                    <option value="12">Dawaki_USA_US</option>
                    <option value="13">TOLEAD_LLC_Direct_US</option>
                    <option value="14">Mini_Stream_Direct_US</option>
                    <option value="28">US_YAATEE_US</option>
                </select>
            </div>
            <button type="button" class="layui-btn  layui-btn-normal" id="excel">导入</button>
            <table class="layui-table" lay-size="sm">
                <colgroup>
                    <col>
                    <col>
                    <col>
                    <col>
                    <col>
                    <col>
                    <col>
                    <col>
                    <col>
                    <col>
                    <col>
                    <col>
                    <col>
                    <col>
                    <col>
                    <col>
                </colgroup>
                <thead>
                <tr>
                    <th>user_account</th>
                    <th>order_id</th>
                    <th>purchase_date</th>
                    <th>payment_date</th>
                    <th>buyer_email</th>
                    <th>buyer_name</th>
                    <th>buyer_phone_number</th>
                    <th>sku</th>
                    <th>quantity</th>
                    <th>recipient_name</th>
                    <th>ship_address_1</th>
                    <th>ship_city</th>
                    <th>ship_state</th>
                    <th>ship_postal_code</th>
                    <th>ship_phone_number</th>
                </tr>
                </thead>
                <tbody>
                {foreach name="list" item="v"}
                <tr>
                    <td>{$v.user.user_account}</td>
                    <td>{$v.order_id}</td>
                    <td>{$v.purchase_date}</td>
                    <td>{$v.payment_date}</td>
                    <td>{$v.buyer_email}</td>
                    <td>{$v.buyer_name}</td>
                    <td>{$v.buyer_phone_number}</td>
                    <td>{$v.sku}</td>
                    <td>{$v.quantity_purchase}</td>
                    <td>{$v.recipient_name}</td>
                    <td>{$v.ship_address_1}</td>
                    <td>{$v.ship_city}</td>
                    <td>{$v.ship_state}</td>
                    <td>{$v.ship_postal_code}</td>
                    <td>{$v.ship_phone_number}</td>
                </tr>
                {/foreach}
                </tbody>
            </table>
            {$list->render()}
        </div>

    </div>
</div>
<script>
    layui.use(['form', 'jquery', 'upload'], function(){
        let $ = layui.jquery,
            form = layui.form,
            upload = layui.upload;

        // 导入
        let uploadInst = upload.render({
            elem: '#excel' //绑定元素
            ,url: '/Manage/upload/order_save_upload' //上传接口
            ,exts: 'xls|xlsx|csv'
            ,data: {
                user_account: function(){
                    return $("#user_account").val();
                }
            }
            ,multiple: true
            ,before: function (obj){
                layer.load(1);
            }
            ,done: function(res){
                //上传完毕回调
                if (res.code === 1) {
                    location.href = "/Manage/Order/save_import/filename/" + encodeURIComponent(res.data) + "/origin/" + res.origin + "/user_account/" + res.user_account;
                } else {
                    layer.alert(res.msg,{icon:2,closeBtn:0,title:false,btnAlign:'c'},function(){
                        layer.closeAll();
                    });
                }
            }
            ,error: function(){
                //请求异常回调
            }
        });

        // 删除
        form.on('submit(Detele)', function(data){
            let text = $(this).text(),
                button = $(this),
                id = $(this).data('id');
            layer.confirm('确定停用吗？',{icon:3,closeBtn:0,title:false,btnAlign:'c'},function(){
                $('button').attr('disabled',true);
                button.text('请稍候...');
                axios.post("{:url('delete')}", {id:id})
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
    });
</script>

{include file="public/footer" /}
