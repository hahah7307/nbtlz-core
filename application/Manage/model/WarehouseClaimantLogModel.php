<?php

namespace app\Manage\model;

use think\Model;

class WarehouseClaimantLogModel extends Model
{
    protected $name = 'warehouse_claimant_log';

    protected $resultSetType = 'collection';

    protected $insert = ['created_at'];

    protected function setCreatedAtAttr()
    {
        return date('Y-m-d H:i:s');
    }
}
