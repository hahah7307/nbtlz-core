<?php

namespace app\Manage\model;

use think\exception\DbException;
use think\Model;

class ProductEditLogModel extends Model
{
    protected $name = 'ecang_product_edit_log';

    protected $resultSetType = 'collection';
}
