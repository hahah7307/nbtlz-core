
    <!-- 侧边菜单 -->
    <div class="layui-side layui-side-menu" id="layui-side-menu">
        <div class="layui-side-scroll">
            <a class="layui-logo" layui-href="/Manage">
                <span><img src="" height="40"></span>
            </a>
          
            <ul class="layui-nav layui-nav-tree" lay-shrink="all" id="LAY-system-side-menu" lay-filter="layadmin-system-side-menu">
                <li data-name="Home" class="layui-nav-item layui-nav-itemed">
                    <a layui-href="/Manage/Index/index.html" lay-tips="控制台" lay-direction="2">
                        <i class="layui-icon layui-icon-home"></i>
                        <cite>控制台</cite>
                    </a>
                </li>
                <li data-name="Member" class="layui-nav-item">
                    <a layui-href="javascript:;" lay-tips="核价" lay-direction="2">
                        <i class="layui-icon iconfont icon-xunpan"></i>
                        <cite>返单测算</cite>
                    </a>
                    <dl class="layui-nav-child">
                        <dd><a layui-href="{:url('Store/index')}">返单测算</a></dd>
                    </dl>
                </li>
                <li data-name="Storage" class="layui-nav-item">
                    <a layui-href="javascript:;" lay-tips="订单" lay-direction="2">
                        <i class="layui-icon iconfont icon-dingdan1"></i>
                        <cite>登记中心</cite>
                    </a>
                    <dl class="layui-nav-child">
                        <dd><a layui-href="{:url('WarehouseClaimant/index')}">海外仓索赔</a></dd>
                        {if condition="in_array('Wildberries Seller', $role) or in_array('Wildberries Purchaser', $role) or $user.super"}
                        <dd><a layui-href="{:url('Wildberries/index')}">Wildberries采购</a></dd>
                        {/if}
                    </dl>
                </li>
                <li data-name="SkuRelation" class="layui-nav-item">
                    <a layui-href="javascript:;" lay-tips="销售" lay-direction="2">
                        <i class="layui-icon iconfont icon-xiaoshoue"></i>
                        <cite>销售</cite>
                    </a>
                    <dl class="layui-nav-child">
                        <dd><a layui-href="{:url('SkuRelation/index')}">销售产品</a></dd>
                        <dd><a layui-href="{:url('SkuRelation/audit')}">销售产品审核</a></dd>
                        <dd><a layui-href="{:url('Echarts/index')}">美国各州销量热力图</a></dd>
                        <dd><a layui-href="{:url('Seller/index')}">平台货号</a></dd>
                        <dd><a layui-href="{:url('Order/save')}">订单留存</a></dd>
                    </dl>
                </li>
                <li data-name="SkuRelation" class="layui-nav-item">
                    <a layui-href="javascript:;" lay-tips="补货" lay-direction="2">
                        <i class="layui-icon iconfont icon-xiaoshoue"></i>
                        <cite>补货</cite>
                    </a>
                    <dl class="layui-nav-child">
                        <dd><a layui-href="{:url('Replenish/index')}">补货列表</a></dd>
                    </dl>
                </li>
                <li data-name="SkuRelation" class="layui-nav-item">
                    <a layui-href="javascript:;" lay-tips="产品" lay-direction="2">
                        <i class="layui-icon iconfont icon-chanpin1"></i>
                        <cite>产品</cite>
                    </a>
                    <dl class="layui-nav-child">
                        <dd><a layui-href="{:url('Product/index')}">产品列表</a></dd>
                        <dd><a layui-href="{:url('Product/edit')}">产品编辑</a></dd>
                        <dd><a layui-href="{:url('Product/warehouse_barcode')}">海外仓编码</a></dd>
                    </dl>
                </li>
                {if condition="$user.super eq 1"}
                <li data-name="Storage" class="layui-nav-item">
                    <a layui-href="javascript:;" lay-tips="仓库" lay-direction="2">
                        <i class="layui-icon iconfont icon-jichugongneng"></i>
                        <cite>基础</cite>
                    </a>
                    <dl class="layui-nav-child">
                        <dd><a layui-href="{:url('Warehouse/index')}">基础仓库</a></dd>
                        <dd><a layui-href="{:url('UserAccount/index')}">基础店铺</a></dd>
                    </dl>
                </li>
                <li data-name="Site" class="layui-nav-item">
                    <a layui-href="javascript:;" lay-tips="设置" lay-direction="2">
                        <i class="layui-icon layui-icon-set"></i>
                        <cite>设置</cite>
                    </a>
                    <dl class="layui-nav-child">
                        <dd><a layui-href="{:url('Param/web')}">参数配置</a></dd>
                        <!-- <dd><a layui-href="{:url('Mail/index')}">邮件设置</a></dd> -->
                        {if condition="$user.super eq 1"}
                        <dd data-name="info">
                            <a layui-href="javascript:;">管理设置</a>
                            <dl class="layui-nav-child">
                                <dd><a layui-href="{:url('Admin/index')}">管理员</a></dd>
                                <dd><a layui-href="{:url('Admin/role')}">角色</a></dd>
                                {if condition="$user.manage eq 1"}
                                <dd><a layui-href="{:url('Admin/node')}">节点</a></dd>
                                {/if}
                            </dl>
                        </dd>
                        {/if}
                    </dl>
                </li>
                {/if}
            </ul>
        </div>
    </div>
    <script type="text/javascript">
    layui.use(['jquery'], function(){
        var $ = layui.jquery;

        {if condition="$user.super"}
        $("#layui-side-menu dl").each(function(){
            let arr = [],
                a_tag = $(this).find('a'),
                unique_arr = [],
                controller_str = '';
            a_tag.each(function(){
                arr.push($(this).attr('layui-href').split('/')[2]);
            });
            unique_arr = $.grep($.unique(arr), function(item) {
                return item !== undefined && item !== null && item !== '';
            });

            controller_str = unique_arr.join(',');
            let url = "/Manage/Auth/index/controller/" + controller_str + ".html";
            $(this).append('<dd class=""><a layui-href="' + url + '">权限列表</a></dd>');
        });
        {/if}

        if ('{$userMenu}') {
            $("#layui-side-menu").html('{$userMenu}');
            $(".layui-nav-bar").remove();
        }

        $("#layui-side-menu a").click(function(){
            $('dd').removeClass('layui-this');
            if ($(this).attr('layui-href') != 'javascript:;') {
                $(this).parent('dd').addClass('layui-this');
            }
            var html = $("#layui-side-menu").html(),
                href = $(this).attr('layui-href');

            $.ajax({
                type:'POST',url:"{:url('Index/initMenu')}",data:{"info": html},dataType:'json',
                success:function(data){
                    if(data.code == 1){
                        location.href = href;
                    }
                }
            });
        });
    });
    </script>
