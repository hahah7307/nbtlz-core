<?php

namespace app\Manage\model;

use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use think\Model;

class SkuRelationModel extends Model
{
    protected $name = 'sku_relation';

    protected $resultSetType = 'collection';

    protected $insert = ['created_time', 'updated_time'];

    protected $update = ['updated_time'];

    protected function setCreatedTimeAttr()
    {
        return date('Y-m-d H:i:s');
    }

    protected function setUpdatedTimeAttr()
    {
        return date('Y-m-d H:i:s');
    }

    public function adminUser(): \think\model\relation\HasOne
    {
        return $this->hasOne('AccountModel', 'id', 'seller_id');
    }

    public function user(): \think\model\relation\HasOne
    {
        return $this->hasOne('UserAccountModel', 'id', 'user_account');
    }

    /**
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws DataNotFoundException
     */
    static public function getWarehouseSkuLabelBySSCode($ssCode, $wsgCode): string
    {
        $skuRelationItemModel = new SkuRelationItemModel();
        $warehouseSku = $skuRelationItemModel->where(['ss_code' => $ssCode, 'wsg_code' => $wsgCode])->select();
        $list = [];
        foreach ($warehouseSku as $value) {
            $list[] = $value['warehouse_sku'] . ' * ' . intval($value['qty']);
        }

        return implode(', ', $list);
    }
}
