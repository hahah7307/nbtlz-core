<?php

namespace app\Manage\model;

use think\Model;

class ReplenishPlanUserModel extends Model
{
    protected $name = 'replenishment_plan_user';

    protected $resultSetType = 'collection';

    public function plan(): \think\model\relation\HasOne
    {
        return $this->hasOne('ReplenishPlanModel', 'id', 'plan_id');
    }

    public function user(): \think\model\relation\HasOne
    {
        return $this->hasOne('AccountModel', 'id', 'user_id');
    }
}
