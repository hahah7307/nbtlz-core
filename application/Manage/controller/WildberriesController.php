<?php
namespace app\Manage\controller;

use app\Manage\model\WildberriesPurchaseModel;
use app\Manage\validate\WildberriesPurchaseValidate;
use Exception;
use think\Db;
use think\exception\DbException;
use think\Session;
use think\Config;

class WildberriesController extends BaseController
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
            $where['product_name|wb_product_code|wb_order_code|po_no|tracking_no_1|tracking_no_2|color|size'] = ['like', '%' . $keyword . '%'];
        }

        $status = $this->request->get('status', 0, 'intval');
        $this->assign('status', $status);

        $is_shipping = $this->request->get('is_shipping', '');
        $this->assign('is_shipping', $is_shipping);
        if ($is_shipping == '') {
            $where['status'] = $status;
        } else {
            $where['is_shipping'] = $is_shipping;
        }

        $storage = new WildberriesPurchaseModel();
        $list = $storage->where($where)->order('id desc')->paginate(Config::get('PAGE_NUM'), false, ['query' => ['keyword' => $keyword, 'status' => $status, 'is_shipping' => $is_shipping]]);
        $this->assign('list', $list);
        $this->assign('qty', $storage->where($where)->sum('qty'));
        $this->assign('amount', $storage->where($where)->sum('amount'));

        Session::set(Config::get('BACK_URL'), $this->request->url(), 'manage');
        return view();
    }

    // 添加
    public function add()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            $post['status'] = WildberriesPurchaseModel::STATUS_CREATE;
            $post['seller_id'] = Session::get(Config::get('USER_LOGIN_FLAG'));
            $post['created_time'] = date('Y-m-d H:i:s');
            $dataValidate = new WildberriesPurchaseValidate();
            if ($dataValidate->scene('add')->check($post)) {
                $model = new WildberriesPurchaseModel();
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
            $info = WildberriesPurchaseModel::get(['id' => $id]);
            if ($info['status'] != 0) {
                echo json_encode(['code' => 0, 'msg' => '异常操作']);
                exit();
            }

            $post = $this->request->post();
            $dataValidate = new WildberriesPurchaseValidate();
            if ($dataValidate->scene('edit')->check($post)) {
                $model = new WildberriesPurchaseModel();
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
            $info = WildberriesPurchaseModel::get(['id' => $id]);
            $this->assign('info', $info);

            return view();
        }
    }

    // 编辑
    /**
     * @throws DbException
     */
    public function purchase($id)
    {
        if ($this->request->isPost()) {
            $info = WildberriesPurchaseModel::get(['id' => $id]);
            if ($info['status'] != 0) {
                echo json_encode(['code' => 0, 'msg' => '异常操作']);
                exit();
            }

            $post = $this->request->post();
            $post['status'] = 1;
            $post['purchase_date'] = empty($post['purchase_date']) ? '' : date('Ymd', strtotime($post['purchase_date']));
            $post['purchaser_id'] = Session::get(Config::get('USER_LOGIN_FLAG'));
            $dataValidate = new WildberriesPurchaseValidate();
            if ($dataValidate->scene('purchase')->check($post)) {
                $model = new WildberriesPurchaseModel();
                if ($model->allowField(true)->save($post, ['id' => $id])) {
                    echo json_encode(['code' => 1, 'msg' => '提交成功']);
                } else {
                    echo json_encode(['code' => 0, 'msg' => '提交失败，请重试']);
                }
            } else {
                echo json_encode(['code' => 0, 'msg' => $dataValidate->getError()]);
            }
            exit;
        } else {
            $info = WildberriesPurchaseModel::get(['id' => $id]);
            $this->assign('info', $info);

            return view();
        }
    }

    // 编辑
    /**
     * @throws DbException
     */
    public function ship($id)
    {
        if ($this->request->isPost()) {
            $info = WildberriesPurchaseModel::get(['id' => $id]);
            if ($info['status'] != 1) {
                echo json_encode(['code' => 0, 'msg' => '异常操作']);
                exit();
            }

            $post = $this->request->post();
            $post['is_shipping'] = 1;
            $dataValidate = new WildberriesPurchaseValidate();
            if ($dataValidate->scene('ship')->check($post)) {
                $model = new WildberriesPurchaseModel();
                if ($model->allowField(true)->save($post, ['id' => $id])) {
                    echo json_encode(['code' => 1, 'msg' => '提交成功']);
                } else {
                    echo json_encode(['code' => 0, 'msg' => '提交失败，请重试']);
                }
            } else {
                echo json_encode(['code' => 0, 'msg' => $dataValidate->getError()]);
            }
            exit;
        } else {
            $info = WildberriesPurchaseModel::get(['id' => $id]);
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
            $block = WildberriesPurchaseModel::get($post['id']);

            if ($block['status'] != 0) {
                echo json_encode(['code' => 0, 'msg' => '异常操作']);
                exit();
            }

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

    // 发货
    /**
     */
    public function shipping()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();

            $model = new WildberriesPurchaseModel();
            Db::startTrans();
            try {
                foreach ($post as $item) {
                    if (!$model->where(['id' => $item])->setField('is_shipping', 1)) {
                        throw new Exception("操作失败，请重试");
                    }
                }

                Db::commit();
                echo json_encode(['code' => 1, 'msg' => '操作成功']);
            } catch (Exception $e) {
                Db::rollback();
                echo json_encode(['code' => 0, 'msg' => $e->getMessage()]);
                exit;
            }
            exit;
        } else {
            echo json_encode(['code' => 0, 'msg' => '异常操作']);
            exit;
        }
    }
}
