<?php
namespace app\Manage\controller;

use app\Manage\model\AccountModel;
use app\Manage\model\WarehouseClaimantLogModel;
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

        $state = $this->request->get('state', '', 'htmlspecialchars');
        $this->assign('state', $state);
        if ($state != "") {
            $where['state'] = $state;
        }

        $access_ids = AccountModel::account_access_ids();
        $where['admin_id'] = ['in', $access_ids];

        // 列表
        $warehouse = new WarehouseClaimantModel();
        $list = $warehouse->with(["warehouse","admin"])->where($where)->order('id asc')->paginate(Config::get('PAGE_NUM'), false, ['query' => ['keyword' => $keyword, 'state' => $state]]);
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
            if ($post['claimant_type'] != 1 && (empty($post['shipping_method_no']) || empty($post['reference_no']))) {
                echo json_encode(['code' => 0, 'msg' => '非库内丢失，跟踪号和订单号必填！']);
                exit;
            }
            $dataValidate = new WarehouseClaimantValidate();
            if ($dataValidate->scene('add')->check($post)) {
                $model = new WarehouseClaimantModel();
                if ($model->allowField(true)->save($post)) {
                    $logObj = new WarehouseClaimantLogModel();
                    $log = [
                        'cid'       =>  $model->getLastInsID(),
                        'tag'       =>  'add',
                        'content'   =>  json_encode($post)
                    ];
                    $logObj->save($log);
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
            if ($post['claimant_type'] != 1 && (empty($post['shipping_method_no']) || empty($post['reference_no']))) {
                echo json_encode(['code' => 0, 'msg' => '非库内丢失，跟踪号和订单号必填！']);
                exit;
            }
            $dataValidate = new WarehouseClaimantValidate();
            if ($dataValidate->scene('edit')->check($post)) {
                $model = new WarehouseClaimantModel();
                if ($model->allowField(true)->save($post, ['id' => $id])) {
                    $post['cid'] = $id;
                    $logObj = new WarehouseClaimantLogModel();
                    $log = [
                        'cid'       =>  $id,
                        'tag'       =>  'edit',
                        'content'   =>  json_encode($post)
                    ];
                    $logObj->save($log);
                    echo json_encode(['code' => 1, 'msg' => '修改成功']);
                } else {
                    echo json_encode(['code' => 0, 'msg' => '修改失败，请重试']);
                }
            } else {
                echo json_encode(['code' => 0, 'msg' => $dataValidate->getError()]);
            }
            exit;
        } else {
            $info = WarehouseClaimantModel::get(['id' => $id,]);
            if ($info['admin_id'] != Session::get(Config::get('USER_LOGIN_FLAG'))) {
                $this->error('你没有编辑权限！', url('index'));
            }

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
}
