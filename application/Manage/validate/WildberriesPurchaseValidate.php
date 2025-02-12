<?php

namespace app\Manage\validate;

use think\Validate;

class WildberriesPurchaseValidate extends Validate
{
    protected $rule = [
        "product_name"	            =>	"require",
        "color"	                    =>	"require",
        "size"	                    =>	"require",
        "qty"	                    =>	"require",
        "wb_product_code"	        =>	"require",
        "wb_order_code"	            =>	"require",
        "po_no"	                    =>	"require",
        "tracking_no_1"	            =>	"require",
        "tracking_no_2"	            =>	"require",
        "pickup_code"	            =>	"require",
        "seller_id"	                =>	"require",
        "purchaser_id"	            =>	"require",
        "purchase_date"	            =>	"require",
        "unit_price"	            =>	"require",
        "amount"	                =>	"require",
        "status"	                =>	"require",
        "created_time"	            =>	"require"
    ];

    protected $message = [
        "product_name"	            =>	"请填写产品名称",
        "color"	                    =>	"请填写颜色",
        "size"	                    =>	"请填写尺寸",
        "qty"	                    =>	"请填写数量",
        "wb_product_code"	        =>	"请填写WB产品编号",
        "wb_order_code"	            =>	"请填写WB平台订单号",
        "po_no"	                    =>	"请填写采购单号",
        "tracking_no_1"	            =>	"请填写1688运单号",
        "tracking_no_2"	            =>	"请填写申通运单号",
        "pickup_code"	            =>	"请填写揽件码",
        "seller_id"	                =>	"缺少运营人员",
        "purchaser_id"	            =>	"缺少采购人员",
        "purchase_date"	            =>	"请选择采购日期",
        "unit_price"	            =>	"请填写单价",
        "amount"	                =>	"请填写总金额",
        "status"	                =>	"缺少采购状态",
        "purchase_url"	            =>	"请填写采购链接",
        "packaging_requirements"	=>	"请填写包装要求",
        "created_time"	            =>	"缺少创建时间"
    ];

    protected $field = [
        "product_name"	            =>	"产品名称",
        "color"	                    =>	"颜色",
        "size"	                    =>	"尺寸",
        "qty"	                    =>	"数量",
        "wb_product_code"	        =>	"WB产品编号",
        "wb_order_code"	            =>	"WB平台订单号",
        "po_no"	                    =>	"采购单号",
        "tracking_no_1"	            =>	"1688运单号",
        "tracking_no_2"	            =>	"申通运单号",
        "pickup_code"	            =>	"揽件码",
        "seller_id"	                =>	"运营人员",
        "purchaser_id"	            =>	"采购人员",
        "purchase_date"	            =>	"采购日期",
        "unit_price"	            =>	"单价",
        "amount"	                =>	"总金额",
        "status"	                =>	"状态",
        "purchase_url"	            =>	"采购链接",
        "packaging_requirements"	=>	"包装要求",
        "created_time"	            =>	"创建时间"
    ];

    protected $scene = [
        'add'           =>  ['wb_order_code', 'wb_product_code', 'product_name', 'qty', 'status', 'seller_id'],
        'edit'          =>  ['wb_order_code', 'wb_product_code', 'product_name', 'qty'],
        'purchase'      =>  ['po_no', 'purchase_date', 'unit_price', 'amount', 'status', 'purchaser_id'],
        'ship'          =>  ['tracking_no_2', 'pickup_code']
    ];
}
