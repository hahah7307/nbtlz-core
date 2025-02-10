<?php

namespace app\Manage\model;

use think\exception\DbException;
use think\Model;

class WildberriesPurchaseModel extends Model
{
    const STATUS_CREATE = 0;

    const STATUS_COMPLETE = 1;

    protected $name = 'wildberries_purchase';

    protected $resultSetType = 'collection';
}
