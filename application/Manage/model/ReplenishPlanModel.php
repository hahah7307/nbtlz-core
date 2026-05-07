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
}
