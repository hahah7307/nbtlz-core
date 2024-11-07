<?php

namespace app\Manage\validate;

use think\Validate;

class SkuRelationItemValidate extends Validate
{
    protected $rule = [
        'ss_code'           =>  'require',
        'wsg_code'          =>  'require',
        'seller_sku'        =>  'require',
        'warehouse_sku'     =>  'require',
        'qty'               =>  'require',
        'percent'           =>  'require',
        'created_time'      =>  'require',
        'updated_time'      =>  'require',
        'status'            =>  'require',
    ];

    protected $message = [
        
    ];

    protected $field = [
        'ss_code'           =>  '销售SKU系统标识符',
        'wsg_code'          =>  '仓库SKU系统标识符',
        'seller_sku'        =>  '销售SKU',
        'warehouse_sku'     =>  '仓库SKU',
        'qty'               =>  '仓库SKU数量',
        'percent'           =>  '仓库SKU占比',
        'created_time'      =>  '创建时间',
        'updated_time'      =>  '更新时间',
        'status'            =>  '状态',
    ];

    protected $scene = [
        'add'           =>  ['ss_code', 'wsg_code', 'seller_sku', 'warehouse_sku', 'qty', 'percent', 'status'],
        'edit'          =>  ['updated_time'],
    ];
}
