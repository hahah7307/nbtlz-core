<?php
namespace app\Manage\controller;

use app\Manage\model\AccountModel;
use app\Manage\model\WarehouseClaimantModel;
use app\Manage\validate\WarehouseClaimantValidate;
use think\exception\DbException;
use think\Session;
use think\Config;

class WarehouseClaimantController extends BaseController
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
            $where['shipping_method_no|reference_no|product_sku'] = ['like', '%' . $keyword . '%'];
        }

        $access_ids = AccountModel::account_access_ids();
        $where['admin_id'] = ['in', $access_ids];

        // 列表
        $warehouse = new WarehouseClaimantModel();
        $list = $warehouse->with(["warehouse","admin"])->where($where)->order('id asc')->paginate(Config::get('PAGE_NUM'));
        $this->assign('list', $list);

        Session::set(Config::get('BACK_URL'), $this->request->url(), 'manage');
        return view();
    }

    // 添加
    /**
     * @throws DbException
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            $post['admin_id'] = Session::get(Config::get('USER_LOGIN_FLAG'));
            $dataValidate = new WarehouseClaimantValidate();
            if ($dataValidate->scene('add')->check($post)) {
                $model = new WarehouseClaimantModel();
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
            $this->assign('warehouse', getWarehouse());

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
            $post['admin_id'] = Session::get(Config::get('USER_LOGIN_FLAG'));
            $dataValidate = new WarehouseClaimantValidate();
            if ($dataValidate->scene('edit')->check($post)) {
                $model = new WarehouseClaimantModel();
                if ($model->allowField(true)->save($post, ['id' => $id])) {
                    echo json_encode(['code' => 1, 'msg' => '修改成功']);
                    exit;
                } else {
                    echo json_encode(['code' => 0, 'msg' => '修改失败，请重试']);
                    exit;
                }
            } else {
                echo json_encode(['code' => 0, 'msg' => $dataValidate->getError()]);
                exit;
            }
        } else {
            $info = WarehouseClaimantModel::get(['id' => $id,]);
            $this->assign('info', $info);
            $this->assign('warehouse', getWarehouse());

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
            $block = WarehouseClaimantModel::get($post['id']);
            if ($block->delete()) {
                echo json_encode(['code' => 1, 'msg' => '操作成功']);
            } else {
                echo json_encode(['code' => 0, 'msg' => '操作失败，请重试']);
            }
        } else {
            echo json_encode(['code' => 0, 'msg' => '异常操作']);
        }
        exit;
    }

    // 状态切换
    /**
     * @throws DbException
     */
    public function status()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            $user = WarehouseClaimantModel::get($post['id']);
            $user['state'] = $user['state'] == WarehouseClaimantModel::STATE_ACTIVE ? 0 : WarehouseClaimantModel::STATE_ACTIVE;
            $user->save();
            echo json_encode(['code' => 1, 'msg' => '操作成功']);
        } else {
            echo json_encode(['code' => 0, 'msg' => '异常操作']);
        }
        exit;
    }
}
