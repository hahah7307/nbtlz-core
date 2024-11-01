<?php

namespace app\Manage\validate;

use think\Validate;

class SkuRelationValidate extends Validate
{
    protected $rule = [
        'platform'          =>  'require',
        'user_account'      =>  'require',
        'seller_sku'        =>  'require',
        'warehouse_name'    =>  'require',
    ];

    protected $message = [
        
    ];

    protected $field = [
        'platform'          =>  '所属平台',
        'user_account'      =>  '所属店铺',
        'seller_sku'        =>  '销售SKU',
        'warehouse_name'    =>  '仓库名称',
    ];

    protected $scene = [
        'add'           =>  ['platform', 'user_account', 'seller_sku', 'warehouse_name'],
        'edit'          =>  ['platform', 'user_account', 'seller_sku', 'warehouse_name'],
    ];
}
