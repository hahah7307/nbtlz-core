<?php

namespace app\Manage\model;

use think\Model;

class UserAccountAccessModel extends Model
{
    protected $name = 'user_account_access';

    protected $resultSetType = 'collection';

    public function adminUser(): \think\model\relation\HasOne
    {
        return $this->hasOne('AccountModel', 'id', 'admin_user_id');
    }

    public function userAccount(): \think\model\relation\HasMany
    {
        return $this->hasMany('UserAccountModel', 'id', 'user_account_id');
    }
}
