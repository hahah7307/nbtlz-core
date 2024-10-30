<?php

namespace app\Manage\validate;

use think\Validate;

class UserAccountValidate extends Validate
{
    protected $rule = [
        'user_account'      =>  'require',
        'platform'          =>  'require',
    ];

    protected $message = [
        
    ];

    protected $field = [
        'user_account'      =>  '仓库名称',
        'platform'          =>  '短描述',
    ];

    protected $scene = [
        'add'           =>  ['user_account', 'platform'],
        'edit'          =>  ['user_account', 'platform'],
    ];
}
