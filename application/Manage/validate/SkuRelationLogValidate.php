<?php

namespace app\Manage\validate;

use think\Validate;

class SkuRelationLogValidate extends Validate
{
    protected $rule = [
        'seller_sku'        =>  'require',
        'ss_code'           =>  'require',
        'wsg_code'          =>  'require',
        'action'            =>  'require',
        'status'            =>  'require',
        'created_time'      =>  'require',
        'action_user'       =>  'require',
        'action_ip'         =>  'require',
    ];

    protected $message = [
        
    ];

    protected $field = [
        'seller_sku'        =>  '销售SKU',
        'ss_code'           =>  '销售SKU系统标识符',
        'wsg_code'          =>  '仓库SKU系统标识符',
        'action'            =>  '操作',
        'status'            =>  '状态',
        'created_time'      =>  '创建时间',
        'action_user'       =>  '操作人',
        'action_ip'         =>  '操作IP',
    ];

    protected $scene = [
        'add'           =>  ['seller_sku', 'ss_code', 'wsg_code', 'action', 'action', 'action_user', 'action_ip'],
    ];
}
