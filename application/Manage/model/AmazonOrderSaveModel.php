<?php

namespace app\Manage\model;

use think\exception\DbException;
use think\Model;

class AmazonOrderSaveModel extends Model
{
    protected $name = 'amazon_order_save';

    protected $resultSetType = 'collection';

    public function user(): \think\model\relation\HasOne
    {
        return $this->hasOne('UserAccountModel', 'id', 'user_account');
    }
}
