<?php

namespace app\Manage\model;

use think\Model;

class SellerSkuModel extends Model
{
    protected $name = 'seller_sku';

    protected $resultSetType = 'collection';

    public function user(): \think\model\relation\HasOne
    {
        return $this->hasOne('UserAccountModel', 'id', 'user_account');
    }

    public function adminUser(): \think\model\relation\HasOne
    {
        return $this->hasOne('AccountModel', 'id', 'seller_id');
    }
}
