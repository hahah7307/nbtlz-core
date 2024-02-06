<?php

namespace app\Manage\validate;

use think\Validate;

class WarehouseClaimantValidate extends Validate
{
    protected $rule = [
        'warehouse_id'          =>  'require',
        'claimant_type'         =>  'require',
//        'shipping_method_no'    =>  'require',
//        'reference_no'          =>  'require',
        'product_sku'           =>  'require',
        'claimant_amount'       =>  'require',
    ];

    protected $message = [
        
    ];

    protected $field = [
        'warehouse_id'          =>  '仓库',
        'claimant_type'         =>  '索赔类型',
        'shipping_method_no'    =>  '跟踪号',
        'reference_no'          =>  '订单号',
        'product_sku'           =>  '产品SKU',
        'claimant_amount'       =>  '索赔金额',
    ];

    protected $scene = [
        'add'           =>  ['warehouse_id', 'claimant_type', 'product_sku', 'claimant_amount'],
        'edit'          =>  ['warehouse_id', 'claimant_type', 'product_sku', 'claimant_amount'],
    ];
}
