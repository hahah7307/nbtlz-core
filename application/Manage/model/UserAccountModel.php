<?php

namespace app\Manage\model;

use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use think\Model;

class UserAccountModel extends Model
{
    protected $name = 'user_account';

    protected $resultSetType = 'collection';

    protected $insert = ['created_at'];

    protected function setCreatedAtAttr()
    {
        return date('Y-m-d H:i:s');
    }

    /**
     * @throws ModelNotFoundException
     * @throws DbException
     * @throws DataNotFoundException
     */
    static public function getPlatformByUser($userId): array
    {
        $access = new UserAccountAccessModel();
        $user_account = $access->with(['user_account'])->where(['admin_user_id' => $userId])->select();

        $platformList = [];
        foreach ($user_account as $item) {
            $platformList[] = $item['user_account'][0]['platform'];
        }

        return array_unique($platformList);
    }
}
