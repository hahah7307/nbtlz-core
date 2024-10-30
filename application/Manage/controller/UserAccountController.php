<?php
namespace app\Manage\controller;

use app\Manage\model\AccountModel;
use app\Manage\model\AdminUserRoleModel;
use app\Manage\model\UserAccountAccessModel;
use app\Manage\model\UserAccountModel;
use app\Manage\validate\UserAccountValidate;
use Exception;
use think\Db;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use think\Session;
use think\Config;

class UserAccountController extends BaseController
{
    /**
     * @throws DbException
     */
    public function index(): \think\response\View
    {
        $where = [];
        $keyword = $this->request->get('keyword', '', 'htmlspecialchars');
        $this->assign('keyword', $keyword);
        if ($keyword) {
            $where['user_account|platform'] = ['like', '%' . $keyword . '%'];
        }

        // 店铺列表
        $storage = new UserAccountModel();
        $list = $storage->where($where)->order('id asc')->paginate(Config::get('PAGE_NUM'));
        $this->assign('list', $list);

        Session::set(Config::get('BACK_URL'), $this->request->url(), 'manage');
        return view();
    }

    // 添加
    public function add()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            $dataValidate = new UserAccountValidate();
            if ($dataValidate->scene('add')->check($post)) {
                $model = new UserAccountModel();
                if ($model->allowField(true)->save($post)) {
                    echo json_encode(['code' => 1, 'msg' => '添加成功']);
                } else {
                    echo json_encode(['code' => 0, 'msg' => '添加失败，请重试']);
                }
            } else {
                echo json_encode(['code' => 0, 'msg' => $dataValidate->getError()]);
            }
            exit;
        } else {

            return view();
        }
    }

    // 编辑
    /**
     * @throws DbException
     */
    public function edit($id)
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            $dataValidate = new UserAccountValidate();
            if ($dataValidate->scene('edit')->check($post)) {
                $model = new UserAccountModel();
                if ($model->allowField(true)->save($post, ['id' => $id])) {
                    echo json_encode(['code' => 1, 'msg' => '修改成功']);
                } else {
                    echo json_encode(['code' => 0, 'msg' => '修改失败，请重试']);
                }
            } else {
                echo json_encode(['code' => 0, 'msg' => $dataValidate->getError()]);
            }
            exit;
        } else {
            $info = UserAccountModel::get(['id' => $id,]);
            $this->assign('info', $info);

            return view();
        }
    }

    // 删除
    /**
     * @throws DbException
     */
    public function delete()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            $block = UserAccountModel::get($post['id']);
            if ($block->delete()) {
                echo json_encode(['code' => 1, 'msg' => '操作成功']);
            } else {
                echo json_encode(['code' => 0, 'msg' => '操作失败，请重试']);
            }
            exit;
        } else {
            echo json_encode(['code' => 0, 'msg' => '异常操作']);
            exit;
        }
    }

    /**
     * @throws ModelNotFoundException
     * @throws DbException
     * @throws DataNotFoundException
     */
    public function seller_access($id)
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            Db::startTrans();
            try {
                $userAccountAccessModel = new UserAccountAccessModel();
                $count = $userAccountAccessModel->where(['user_account_id' => $id])->count();
                if ($count) {
                    if (!$userAccountAccessModel->where(['user_account_id' => $id])->delete()) {
                        throw new Exception("操作失败，请重试");
                    }
                }
                $data = [];
                foreach ($post['user_id'] as $v) {
                    $data[] = [
                        'user_account_id'   => $id,
                        'admin_user_id'     => $v,
                    ];
                }
                if ($userAccountAccessModel->insertAll($data)) {
                    Db::commit();
                    echo json_encode(['code' => 1, 'msg' => '操作成功']);
                    exit;
                } else {
                    throw new Exception("操作失败，请重试");
                }
            } catch (Exception $e) {
                Db::rollback();
                echo json_encode(['code' => 0, 'msg' => $e->getMessage()]);
                exit;
            }
        } else {
            $this->assign('user_account', AccountModel::get($id));
            $userAccountAccessModel = new UserAccountAccessModel();
            $userAccountAccess = $userAccountAccessModel->where(['user_account_id' => $id])->select();
            $this->assign('userAccountAccess', $userAccountAccess);

            $userRoleModel = new AdminUserRoleModel();
            $sellerList = $userRoleModel->with('user')->where(['role_id' => 5])->select();
            foreach ($sellerList as $key => $value) {
                if ($value['user']) {
                    if (in_array($value['user']['id'], array_column($userAccountAccess->toArray(), 'admin_user_id'))) {
                        $sellerList[$key]['user']['access'] = 1;
                    } else {
                        $sellerList[$key]['user']['access'] = 0;
                    }
                } else {
                    unset($sellerList[$key]);
                }
            }
            $this->assign('sellerList', $sellerList);

            return view();
        }
    }
}
