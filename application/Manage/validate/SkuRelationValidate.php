<?php

namespace app\Manage\validate;

use think\Validate;

class SkuRelationValidate extends Validate
{
    protected $rule = [
        'platform'          =>  'require',
        'user_account'      =>  'require',
        'warehouse_name'    =>  'require',
        'ss_code'           =>  'require',
        'wsg_code'          =>  'require',
        'seller_sku'        =>  'require',
        'seller_id'         =>  'require',
        'created_time'      =>  'require',
        'updated_time'      =>  'require',
        'status'            =>  'require',
        'delivery_type'     =>  'require',
    ];

    protected $message = [

    ];

    protected $field = [
        'platform'          =>  '所属平台',
        'user_account'      =>  '所属店铺',
        'warehouse_name'    =>  '仓库名称',
        'ss_code'           =>  '销售SKU系统标识符',
        'wsg_code'          =>  '仓库SKU系统标识符',
        'seller_sku'        =>  '销售SKU',
        'seller_id'         =>  '销售人员ID',
        'created_time'      =>  '创建时间',
        'updated_time'      =>  '更新时间',
        'status'            =>  '状态',
        'delivery_type'     =>  '发货类型',
    ];

    protected $scene = [
        'add'           =>  ['platform', 'user_account', 'warehouse_name', 'ss_code', 'wsg_code', 'seller_sku', 'seller_id', 'status', 'delivery_type'],
        'edit'          =>  ['updated_time'],
    ];
}
