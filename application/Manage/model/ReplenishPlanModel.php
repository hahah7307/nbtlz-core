<?php

namespace app\Manage\model;

use think\Model;

class ReplenishPlanModel extends Model
{
    protected $name = 'replenishment_plan';

    protected $resultSetType = 'collection';

    protected $type = [
        'create_time' => 'datetime'
    ];

    public function adminUser(): \think\model\relation\HasOne
    {
        return $this->hasOne('AccountModel', 'id', 'create_id');
    }

    public function planDetail(): \think\model\relation\HasMany
    {
        return $this->hasMany('ReplenishPlanDetailModel', 'plan_id', 'id')->order('platform asc');
    }
}
