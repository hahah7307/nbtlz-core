<?php

namespace app\Manage\model;

use think\Config;
use think\Model;
use think\Session;

class WarehouseClaimantModel extends Model
{
    protected $name = 'warehouse_claimant';

    protected $resultSetType = 'collection';

    protected $insert = ['created_at', 'updated_at'];

    protected $update = ['updated_at'];

    protected function setCreatedAtAttr()
    {
        return date('Y-m-d H:i:s');
    }

    protected function setUpdatedAtAttr()
    {
        return date('Y-m-d H:i:s');
    }

    public function warehouse(): \think\model\relation\HasOne
    {
        return $this->hasOne('WarehouseModel', 'id', 'warehouse_id');
    }

    public function admin(): \think\model\relation\HasOne
    {
        return $this->hasOne('AccountModel', 'id', 'admin_id');
    }
}
