<?php

namespace app\Manage\model;

use think\Model;

class UserAccountModel extends Model
{
    protected $name = 'user_account';

    protected $resultSetType = 'collection';

    protected $insert = ['created_at'];

    protected function setCreatedAtAttr()
    {
        return date('Y-m-d H:i:s');
    }
}
