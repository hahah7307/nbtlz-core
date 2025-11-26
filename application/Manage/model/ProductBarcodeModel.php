<?php

namespace app\Manage\model;

use think\exception\DbException;
use think\Model;

class ProductBarcodeModel extends Model
{
    protected $name = 'ecang_product_barcode';

    protected $resultSetType = 'collection';
}
