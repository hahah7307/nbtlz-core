<?php

namespace app\Manage\model;

use think\Model;

class ProductInstallVideoModel extends Model
{
    protected $name = 'product_install_video';

    protected $resultSetType = 'collection';

    public function seller(): \think\model\relation\HasOne
    {
        return $this->hasOne('AccountModel', 'id', 'created_id');
    }
}
