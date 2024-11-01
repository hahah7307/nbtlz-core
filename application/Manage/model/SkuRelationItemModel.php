<?php

namespace app\Manage\model;

use think\Model;

class SkuRelationItemModel extends Model
{
    protected $name = 'sku_relation_item';

    protected $resultSetType = 'collection';

    public function adminUser(): \think\model\relation\HasOne
    {
        return $this->hasOne('AccountModel', 'id', 'seller_id');
    }

    public function user(): \think\model\relation\HasOne
    {
        return $this->hasOne('UserAccountModel', 'id', 'user_account');
    }
}
