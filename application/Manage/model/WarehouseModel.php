<?php

namespace app\Manage\model;

use think\Config;
use think\exception\DbException;
use think\Model;

class WarehouseModel extends Model
{
    const STATE_ACTIVE = 1;
    const LC_WAREHOUSE_ID = 1;
    const LE_WAREHOUSE_ID = 2;

    protected $name = 'warehouse';

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
}
