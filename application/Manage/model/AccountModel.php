<?php

namespace app\Manage\model;

use think\Config;
use think\exception\DbException;
use think\Model;
use think\Session;

class AccountModel extends Model
{
    const STATUS_ACTIVE = 1;

    protected $name = 'admin_user';

    protected $resultSetType = 'collection';

    protected $insert = ['created_at', 'updated_at'];

    protected $update = ['updated_at'];

    protected function setCreatedAtAttr(): int
    {
        return time();
    }

    protected function setUpdatedAtAttr(): int
    {
        return time();
    }

    public function userRole(): \think\model\relation\HasMany
    {
        return $this->hasMany('AdminUserRoleModel', 'user_id', 'id');
    }

    // 获取用户的所有权限
    static public function account_access($id): array
    {
        $access = self::with(['userRole.role.accessLevel.adminNode.parentNode'])->where(['status' => self::STATUS_ACTIVE, 'id' => $id])->find();
        $accessList = array();
        foreach ($access['user_role'] as $ka => $va) {
            foreach ($va['role']['access_level'] as $kb => $vb) {
                $accessList[] = strtolower($vb['admin_node']['parent_node']['code'] . '/' . $vb['admin_node']['code']);
            }
        }
        return array_unique($accessList);
    }

    // 验证用户权限
    static public function action_access($controller, $action, $access, $user): bool
    {
        if ($user['super'] == 1) {
            return true;
        } else {
            if (in_array(strtolower($controller), config('ACCESS_CONTROLLER'))) {
                return true;
            } else {
                if (in_array(strtolower($controller . '/' . $action), config('ACCESS_ACTION'))) {
                    return true;
                } else {
                    if (in_array(strtolower($controller . '/' . $action), $access)) {
                        return true;
                    } else {
                        return false;
                    }
                }
            }
        }
    }

    /**
     * @throws DbException
     */
    static public function account_access_ids()
    {
        $accountOnj = new AccountModel();
        $account = $accountOnj->where(['id' => Session::get(Config::get('USER_LOGIN_FLAG'))])->find();
        if ($account['super'] == 1) {
            return $accountOnj->column('id');
        } elseif ($account['manage'] == 1) {
            $userRoleObj = new AdminUserRoleModel();
            $userRole = $userRoleObj->where(['user_id' => $account['id']])->find();
            return $userRoleObj->where(['role_id' => $userRole['role_id']])->column('user_id');
        } else {
            return [$account['id']];
        }
    }
}
