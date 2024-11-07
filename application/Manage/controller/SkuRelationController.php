<?php
namespace app\Manage\controller;

use app\Manage\model\AccountModel;
use app\Manage\model\ApiClient;
use app\Manage\model\ProductModel;
use app\Manage\model\SkuRelationItemModel;
use app\Manage\model\SkuRelationLogModel;
use app\Manage\model\SkuRelationModel;
use app\Manage\model\UserAccountAccessModel;
use app\Manage\model\UserAccountModel;
use app\Manage\validate\SkuRelationItemValidate;
use app\Manage\validate\SkuRelationLogValidate;
use app\Manage\validate\SkuRelationValidate;
use Exception;
use think\Db;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use think\Session;
use think\Config;

class SkuRelationController extends BaseController
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
            $userAccountModel = new UserAccountModel();
            $userAccount = $userAccountModel->where(['user_account' => $keyword])->find();
            if ($userAccount) {
                $where['user_account'] = $userAccount['id'];
            } else {
                $where['seller_sku|ss_code|wsg_code|warehouse_name|platform'] = ['like', '%' . $keyword . '%'];
            }
        }
        $status = $this->request->get('status', 1, 'intval');
        $this->assign('status', $status);
        if ($status) {
            $where['status'] = $status;
        } else {
            $where['status'] = ['in', [1, 4, 5]];
        }

        // 查看权限
        $access_ids = AccountModel::account_access_ids();
        $where['seller_id'] = ['in', $access_ids];

        // 映射关系列表
        $skuRelationModel = new SkuRelationModel();
        $list = $skuRelationModel->where($where)->order('id asc')->paginate(Config::get('PAGE_NUM'), false, ['keyword' => $keyword, 'status' => $status]);
        $this->assign('list', $list);

        Session::set(Config::get('BACK_URL'), $this->request->url(), 'manage');
        return view();
    }

    // 添加

    /**
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws DataNotFoundException
     * @throws \Exception
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            // 核验新增时传入字段
            if (empty(array_filter($post['warehouse_sku']))) {
                echo json_encode(['code' => 0, 'msg' => '请填写仓库SKU']);
                exit;
            }
            if (empty(array_filter($post['qty']))) {
                echo json_encode(['code' => 0, 'msg' => '请填写仓库SKU数量']);
                exit;
            }

            // 获取操作用户信息
            $userModel = new AccountModel();
            $user = $userModel->where(['id'=>Session::get(Config::get('USER_LOGIN_FLAG')), 'status' => AccountModel::STATUS_ACTIVE])->find();

            Db::startTrans();
            try {
                $ssCode = 'SS' . time() . mt_rand(100, 999);
                $wsgCode = 'WSG' . time() . mt_rand(100, 999);
                $skuRelationData = [
                    'platform'          =>  $post['platform'],
                    'user_account'      =>  $post['user_account'],
                    'warehouse_name'    =>  $post['warehouse_name'],
                    'ss_code'           =>  $ssCode,
                    'wsg_code'          =>  $wsgCode,
                    'seller_sku'        =>  $post['seller_sku'],
                    'seller_id'         =>  $user['id'],
                    'status'            =>  0
                ];

                // 新增映射关系销售SKU
                $skuRelationValidate = new SkuRelationValidate();
                if ($skuRelationValidate->scene('add')->check($skuRelationData)) {
                    $skuRelationModel = new SkuRelationModel();
                    if ($skuRelationModel->allowField(true)->save($skuRelationData)) {
                        $total = 0;
                        $itemData = [];
                        $productModel = new ProductModel();
                        foreach ($post['warehouse_sku'] as $key => $value) {
                            if (empty($post['qty'][$key])) {
                                throw new Exception("请填写" . $value . "的数量");
                            }

                            $product = $productModel->where(['productSku' => $value])->find();
                            if (empty($product)) {
                                throw new Exception("不存在的仓库SKU(" . $value . ")");
                            }

                            $data['ss_code'] = $ssCode;
                            $data['wsg_code'] = $wsgCode;
                            $data['seller_sku'] = $post['seller_sku'];
                            $data['warehouse_sku'] = $value;
                            $data['qty'] = $post['qty'][$key];
                            $data['status'] = 0;
                            $amount = $product['sp_unit_price'] * $post['qty'][$key];
                            $data['amount'] = $amount;
                            $itemData[] = $data;
                            $total += $amount;
                        }

                        $percentSum = 0;
                        foreach ($itemData as $k => $v) {
                            if ($k + 1 == count($itemData)) {
                                $v['percent'] = 1 - $percentSum;;
                            } else {
                                $percent = round($v['amount'] / $total, 5);
                                $v['percent'] = $percent;
                                $percentSum += $percent;
                            }
                            unset($v['amount']);

                            // 新增映射关系仓库SKU
                            $skuRelationItemValidate = new SkuRelationItemValidate();
                            if ($skuRelationItemValidate->scene('add')->check($v)) {
                                $skuRelationItemModel = new SkuRelationItemModel();
                                if (!$skuRelationItemModel->allowField(true)->save($v)) {
                                    throw new Exception("新增失败，请重试！");
                                }
                            } else {
                                throw new Exception($skuRelationItemValidate->getError());
                            }
                        }

                        // 新建记录
                        $logData = [
                            'seller_sku'    =>  $post['seller_sku'],
                            'ss_code'       =>  $ssCode,
                            'wsg_code'      =>  $wsgCode,
                            'action'        =>  '新建',
                            'status'        =>  0,
                            'action_user'   =>  $user['nickname'],
                            'action_ip'     =>  get_real_ip()
                        ];
                        $skuRelationItemLogValidate = new SkuRelationLogValidate();
                        if ($skuRelationItemLogValidate->scene('add')->check($logData)) {
                            $skuRelationLogModel = new SkuRelationLogModel();
                            if ($skuRelationLogModel->allowField(true)->save($logData)) {

                                Db::commit();
                                echo json_encode(['code' => 1, 'msg' => '操作成功']);
                                exit;
                            } else {
                                throw new Exception("新增失败，请重试！");
                            }
                        } else {
                            throw new Exception($skuRelationItemLogValidate->getError());
                        }
                    } else {
                        throw new Exception("新增失败，请重试！");
                    }
                } else {
                    throw new Exception($skuRelationValidate->getError());
                }
            } catch (Exception $e) {
                Db::rollback();
                echo json_encode(['code' => 0, 'msg' => $e->getMessage()]);
                exit;
            }
        } else {
            $userAccountModel = new UserAccountModel();
            $platform = $userAccountModel->distinct(true)->field('platform')->select();
            $this->assign('platform', $platform);

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
            if (empty(array_filter($post['warehouse_sku']))) {
                echo json_encode(['code' => 0, 'msg' => '请填写仓库SKU']);
                exit;
            }
            if (empty(array_filter($post['qty']))) {
                echo json_encode(['code' => 0, 'msg' => '请填写仓库SKU数量']);
                exit;
            }

            $userModel = new AccountModel();
            $user = $userModel->where(['id'=>Session::get(Config::get('USER_LOGIN_FLAG')), 'status' => AccountModel::STATUS_ACTIVE])->find();

            Db::startTrans();
            try {
                $ssCode = $post['ss_code'];
                $wsgCode = 'WSG' . time() . mt_rand(100, 999);

                $total = 0;
                $itemData = [];
                $productModel = new ProductModel();
                foreach ($post['warehouse_sku'] as $key => $value) {
                    if (empty($post['qty'][$key])) {
                        throw new Exception("请填写" . $value . "的数量");
                    }

                    $product = $productModel->where(['productSku' => $value])->find();
                    if (empty($product)) {
                        throw new Exception("不存在的仓库SKU(" . $value . ")");
                    }

                    $data['ss_code'] = $ssCode;
                    $data['wsg_code'] = $wsgCode;
                    $data['seller_sku'] = $post['seller_sku'];
                    $data['warehouse_sku'] = $value;
                    $data['qty'] = $post['qty'][$key];
                    $data['status'] = 2;
                    $amount = $product['sp_unit_price'] * $post['qty'][$key];
                    $data['amount'] = $amount;
                    $itemData[] = $data;
                    $total += $amount;
                }

                $percentSum = 0;
                foreach ($itemData as $k => $v) {
                    if ($k + 1 == count($itemData)) {
                        $v['percent'] = 1 - $percentSum;;
                    } else {
                        $percent = round($v['amount'] / $total, 5);
                        $v['percent'] = $percent;
                        $percentSum += $percent;
                    }
                    unset($v['amount']);

                    // 新增映射关系仓库SKU
                    $skuRelationItemValidate = new SkuRelationItemValidate();
                    if ($skuRelationItemValidate->scene('add')->check($v)) {
                        $skuRelationItemModel = new SkuRelationItemModel();
                        if (!$skuRelationItemModel->allowField(true)->save($v)) {
                            throw new Exception("编辑失败，请重试！");
                        }
                    } else {
                        throw new Exception($skuRelationItemValidate->getError());
                    }
                }

                // 编辑记录
                $logData = [
                    'seller_sku'    =>  $post['seller_sku'],
                    'ss_code'       =>  $ssCode,
                    'wsg_code'      =>  $wsgCode,
                    'action'        =>  '编辑',
                    'status'        =>  2,
                    'action_user'   =>  $user['nickname'],
                    'action_ip'     =>  get_real_ip()
                ];
                $skuRelationItemLogValidate = new SkuRelationLogValidate();
                if ($skuRelationItemLogValidate->scene('add')->check($logData)) {
                    $skuRelationLogModel = new SkuRelationLogModel();
                    if ($skuRelationLogModel->allowField(true)->save($logData)) {

                        Db::commit();
                        echo json_encode(['code' => 1, 'msg' => '操作成功']);
                        exit;
                    } else {
                        throw new Exception("编辑失败，请重试！");
                    }
                } else {
                    throw new Exception($skuRelationItemLogValidate->getError());
                }
            } catch (Exception $e) {
                Db::rollback();
                echo json_encode(['code' => 0, 'msg' => $e->getMessage()]);
                exit;
            }
        } else {
            $userAccountModel = new UserAccountModel();
            $platform = $userAccountModel->distinct(true)->field('platform')->select();
            $this->assign('platform', $platform);

            $skuRelationModel = new SkuRelationModel();
            $skuRelation = $skuRelationModel->find($id);
            $this->assign('info', $skuRelation);
            $skuRelationItemModel = new SkuRelationItemModel();
            $items = $skuRelationItemModel->where(['wsg_code' => $skuRelation['wsg_code'], 'ss_code' => $skuRelation['ss_code']])->select();
            $this->assign('list', $items);

            return view();
        }
    }

    /**
     * @throws DbException
     */
    public function audit(): \think\response\View
    {
        $where = [];
        $keyword = $this->request->get('keyword', '', 'htmlspecialchars');
        $this->assign('keyword', $keyword);
        if ($keyword) {
            $userAccountModel = new UserAccountModel();
            $userAccount = $userAccountModel->where(['user_account' => $keyword])->find();
            if ($userAccount) {
                $where['user_account'] = $userAccount['id'];
            } else {
                $where['seller_sku|ss_code|wsg_code|warehouse_name|platform'] = ['like', '%' . $keyword . '%'];
            }
        }

        // 查看权限
        $access_ids = AccountModel::account_access_ids();
        $where['seller_id'] = ['in', $access_ids];
        $where['b.status'] = ['in', [0, 2, 3]];

        // 映射关系列表
        $skuRelationModel = new SkuRelationModel();
        $list = $skuRelationModel->alias('a')
            ->distinct(true)
            ->join('nbtlz_sku_relation_item b', 'a.ss_code = b.ss_code', 'LEFT')
            ->field('b.ss_code, b.wsg_code, a.platform, a.user_account, a.warehouse_name, a.seller_sku, b.created_time, a.seller_id, b.status')
            ->where($where)
            ->paginate(Config::get('PAGE_NUM'), false, ['keyword' => $keyword]);
        $this->assign('list', $list);

        Session::set(Config::get('BACK_URL'), $this->request->url(), 'manage');
        return view();
    }

    /**
     * @throws ModelNotFoundException
     * @throws DbException
     * @throws DataNotFoundException
     */
    public function approved()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            $userModel = new AccountModel();
            $user = $userModel->where(['id'=>Session::get(Config::get('USER_LOGIN_FLAG')), 'status' => AccountModel::STATUS_ACTIVE])->find();

            Db::startTrans();
            try {
                $ssCode = $post['ss_code'];

                //
                $skuRelationValidate = new SkuRelationValidate();
                $skuRelationItemValidate = new SkuRelationItemValidate();
                $skuRelationLogValidate = new SkuRelationLogValidate();
                $skuRelationModel = new SkuRelationModel();
                $skuRelationItemModel = new SkuRelationItemModel();
                $skuRelationLogModel = new SkuRelationLogModel();
                $skuRelation = $skuRelationModel->where(['ss_code' => $ssCode])->find();
                $skuRelationItem = $skuRelationItemModel->where(['ss_code' => $ssCode, 'wsg_code' => $post['wsg_code']])->find();
                if ($skuRelationItem['status'] == 0) {
                    // 新建待审核
                    if ($skuRelationModel->allowField(true)->update(['id' => $skuRelation['id'], 'status' => 1])) {
                        if ($skuRelationItemModel->allowField(true)->where(['ss_code' => $ssCode, 'wsg_code' => $post['wsg_code']])->update(['status' => 1, 'updated_time' => date('Y-m-d H:i:s')])) {
                            $res = self::sendSkuRelationRequest($skuRelation, $post['wsg_code']);
                            if (empty($res['code'])) {
                                throw new Exception($res['data']);
                            }

                            // 审核记录
                            $logData = [
                                'seller_sku'    =>  $skuRelation['seller_sku'],
                                'ss_code'       =>  $ssCode,
                                'wsg_code'      =>  $post['wsg_code'],
                                'action'        =>  '审核通过',
                                'status'        =>  1,
                                'action_user'   =>  $user['nickname'],
                                'action_ip'     =>  get_real_ip()
                            ];
                            if ($skuRelationLogValidate->scene('add')->check($logData)) {
                                if ($skuRelationLogModel->allowField(true)->save($logData)) {
                                    Db::commit();
                                    echo json_encode(['code' => 1, 'msg' => '操作成功']);
                                    exit;
                                } else {
                                    throw new Exception("审核失败，请重试");
                                }
                            } else {
                                throw new Exception($skuRelationLogValidate->getError());
                            }
                        } else {
                            throw new Exception("审核失败，请重试");
                        }
                    } else {
                        throw new Exception("审核失败，请重试");
                    }
                } elseif ($skuRelationItem['status'] == 2) {
                    // 编辑待审核
                    $skuRelationItemUpdate_1 = [
                        'seller_sku'    =>  $skuRelation['seller_sku'] . '-' . mt_rand(100, 999),
                        'updated_time'  =>  date('Y-m-d H:i:s'),
                        'status'        =>  4
                    ];
                    if ($skuRelationItemModel->allowField(true)->where(['ss_code' => $ssCode, 'status' => 1])->update($skuRelationItemUpdate_1)) {
                        if ($skuRelationModel->allowField(true)->update(['id' => $skuRelation['id'], 'wsg_code' => $post['wsg_code']])) {
                            $skuRelationItemUpdate_2 = [
                                'updated_time'  =>  date('Y-m-d H:i:s'),
                                'status'        =>  1
                            ];
                            if ($skuRelationItemModel->allowField(true)->where(['ss_code' => $ssCode, 'wsg_code' => $post['wsg_code']])->update($skuRelationItemUpdate_2)) {
                                $res = self::sendSkuRelationRequest($skuRelation, $post['wsg_code']);
                                if (empty($res['code'])) {
                                    throw new Exception($res['data']);
                                }

                                // 审核记录
                                $logData = [
                                    'seller_sku'    =>  $skuRelation['seller_sku'],
                                    'ss_code'       =>  $ssCode,
                                    'wsg_code'      =>  $post['wsg_code'],
                                    'action'        =>  '审核通过',
                                    'status'        =>  1,
                                    'action_user'   =>  $user['nickname'],
                                    'action_ip'     =>  get_real_ip()
                                ];
                                if ($skuRelationLogValidate->scene('add')->check($logData)) {
                                    if ($skuRelationLogModel->allowField(true)->save($logData)) {
                                        Db::commit();
                                        echo json_encode(['code' => 1, 'msg' => '操作成功']);
                                        exit;
                                    } else {
                                        throw new Exception("审核失败，请重试");
                                    }
                                } else {
                                    throw new Exception($skuRelationLogValidate->getError());
                                }
                            } else {
                                throw new Exception("审核失败，请重试");
                            }
                        } else {
                            throw new Exception("审核失败，请重试");
                        }
                    } else {
                        throw new Exception("审核失败，请重试");
                    }
                } elseif ($skuRelationItem['status'] == 3) {
                    // 停用待审核
                    $skuRelationItemUpdate_1 = [
                        'seller_sku'    =>  $skuRelation['seller_sku'] . '-' . mt_rand(100, 999),
                        'updated_time'  =>  date('Y-m-d H:i:s'),
                        'status'        =>  4
                    ];
                    if ($skuRelationItemModel->allowField(true)->where(['ss_code' => $ssCode, 'status' => 3])->update($skuRelationItemUpdate_1)) {
                        if ($skuRelationModel->allowField(true)->update(['id' => $skuRelation['id'], 'status' => 4])) {

                            // 审核记录
                            $logData = [
                                'seller_sku'    =>  $skuRelation['seller_sku'],
                                'ss_code'       =>  $ssCode,
                                'wsg_code'      =>  $post['wsg_code'],
                                'action'        =>  '审核通过',
                                'status'        =>  4,
                                'action_user'   =>  $user['nickname'],
                                'action_ip'     =>  get_real_ip()
                            ];
                            if ($skuRelationLogValidate->scene('add')->check($logData)) {
                                if ($skuRelationLogModel->allowField(true)->save($logData)) {
                                    Db::commit();
                                    echo json_encode(['code' => 1, 'msg' => '操作成功']);
                                    exit;
                                } else {
                                    throw new Exception("审核失败，请重试");
                                }
                            } else {
                                throw new Exception($skuRelationLogValidate->getError());
                            }
                        } else {
                            throw new Exception("审核失败，请重试");
                        }
                    } else {
                        throw new Exception("审核失败，请重试");
                    }
                } else {
                    throw new Exception("异常操作");
                }
            } catch (Exception $e) {
                Db::rollback();
                echo json_encode(['code' => 0, 'msg' => $e->getMessage()]);
                exit;
            }
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
    public function reject()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            $userModel = new AccountModel();
            $user = $userModel->where(['id'=>Session::get(Config::get('USER_LOGIN_FLAG')), 'status' => AccountModel::STATUS_ACTIVE])->find();

            Db::startTrans();
            try {
                $ssCode = $post['ss_code'];

                //
                $skuRelationModel = new SkuRelationModel();
                $skuRelationItemModel = new SkuRelationItemModel();
                $skuRelation = $skuRelationModel->where(['ss_code' => $ssCode])->find();
                $skuRelationItem = $skuRelationItemModel->where(['ss_code' => $ssCode, 'wsg_code' => $post['wsg_code']])->find();
                if ($skuRelationItem['status'] == 0) {
                    // 新建待审核
                    if ($skuRelationModel->where(['ss_code' => $ssCode])->setField('status', 5)) {
                        if ($skuRelationItemModel->where(['ss_code' => $ssCode, 'wsg_code' => $post['wsg_code']])->setField('status', 5)) {
                            //

                            // 审核记录
                            $logData = [
                                'seller_sku'    =>  $skuRelation['seller_sku'],
                                'ss_code'       =>  $ssCode,
                                'wsg_code'      =>  $skuRelation['wsg_code'],
                                'action'        =>  '审核驳回',
                                'status'        =>  5,
                                'created_time'  =>  date('Y-m-d H:i:s'),
                                'action_user'   =>  $user['nickname'],
                                'action_ip'     =>  get_real_ip()
                            ];
                            $skuRelationLogModel = new SkuRelationLogModel();
                            if (!$skuRelationLogModel->insert($logData)) {
                                throw new Exception("审核失败，请重试！");
                            }

                            Db::commit();
                            echo json_encode(['code' => 1, 'msg' => '操作成功']);
                            exit;
                        } else {

                            throw new Exception("审核失败，请重试");
                        }
                    } else {

                        throw new Exception("审核失败，请重试");
                    }
                } elseif ($skuRelationItem['status'] == 2) {
                    // 编辑待审核
                    if ($skuRelationItemModel->where(['ss_code' => $ssCode, 'wsg_code' => $post['wsg_code']])->setField('status', 5)) {
                        //

                        // 审核记录
                        $logData = [
                            'seller_sku'    =>  $skuRelation['seller_sku'],
                            'ss_code'       =>  $ssCode,
                            'wsg_code'      =>  $skuRelation['wsg_code'],
                            'action'        =>  '审核驳回',
                            'status'        =>  5,
                            'created_time'  =>  date('Y-m-d H:i:s'),
                            'action_user'   =>  $user['nickname'],
                            'action_ip'     =>  get_real_ip()
                        ];
                        $skuRelationLogModel = new SkuRelationLogModel();
                        if (!$skuRelationLogModel->insert($logData)) {
                            throw new Exception("审核失败，请重试！");
                        }

                        Db::commit();
                        echo json_encode(['code' => 1, 'msg' => '操作成功']);
                        exit;
                    } else {

                        throw new Exception("审核失败，请重试");
                    }
                } elseif ($skuRelationItem['status'] == 3) {
                    // 停用待审核
                    if ($skuRelationItemModel->where(['ss_code' => $ssCode, 'status' => 3])->setField('status', 1)) {
                        if ($skuRelationModel->where(['ss_code' => $ssCode])->setField('status', 1)) {
                            //

                            // 审核记录
                            $logData = [
                                'seller_sku'    =>  $skuRelation['seller_sku'],
                                'ss_code'       =>  $ssCode,
                                'wsg_code'      =>  $skuRelation['wsg_code'],
                                'action'        =>  '审核驳回',
                                'status'        =>  1,
                                'created_time'  =>  date('Y-m-d H:i:s'),
                                'action_user'   =>  $user['nickname'],
                                'action_ip'     =>  get_real_ip()
                            ];
                            $skuRelationLogModel = new SkuRelationLogModel();
                            if (!$skuRelationLogModel->insert($logData)) {
                                throw new Exception("审核失败，请重试！");
                            }

                            Db::commit();
                            echo json_encode(['code' => 1, 'msg' => '操作成功']);
                            exit;
                        } else {

                            throw new Exception("审核失败，请重试");
                        }
                    } else {

                        throw new Exception("审核失败，请重试");
                    }
                } else {

                    throw new Exception("异常操作");
                }
            } catch (Exception $e) {
                Db::rollback();
                echo json_encode(['code' => 0, 'msg' => $e->getMessage()]);
                exit;
            }
        } else {
            echo json_encode(['code' => 0, 'msg' => '异常操作']);
            exit;
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
            $userModel = new AccountModel();
            $user = $userModel->where(['id'=>Session::get(Config::get('USER_LOGIN_FLAG')), 'status' => AccountModel::STATUS_ACTIVE])->find();

            Db::startTrans();
            try {
                $relationId = $post['id'];

                //
                $skuRelationModel = new SkuRelationModel();
                $skuRelationItemModel = new SkuRelationItemModel();

                $skuRelation = $skuRelationModel->find($relationId);
                if ($skuRelation['status'] == 1) {
                    // 停用待审核
                    $skuRelationDeleteData = [
                        'id'        =>  $relationId,
                        'status'    =>  3
                    ];
                    if ($skuRelationModel->allowField(true)->update($skuRelationDeleteData)) {
                        if ($skuRelationItemModel->allowField(true)->where(['ss_code' => $skuRelation['ss_code'], 'wsg_code' => $skuRelation['wsg_code']])->update(['status' => 3, 'updated_time' => date('Y-m-d H:i:s')])) {

                            // 审核记录
                            $logData = [
                                'seller_sku'    =>  $skuRelation['seller_sku'],
                                'ss_code'       =>  $skuRelation['ss_code'],
                                'wsg_code'      =>  $skuRelation['wsg_code'],
                                'action'        =>  '停用',
                                'status'        =>  3,
                                'action_user'   =>  $user['nickname'],
                                'action_ip'     =>  get_real_ip()
                            ];
                            $skuRelationItemLogValidate = new SkuRelationLogValidate();
                            if ($skuRelationItemLogValidate->scene('add')->check($logData)) {
                                $skuRelationLogModel = new SkuRelationLogModel();
                                if ($skuRelationLogModel->allowField(true)->save($logData)) {

                                    Db::commit();
                                    echo json_encode(['code' => 1, 'msg' => '操作成功']);
                                    exit;
                                } else {
                                    throw new Exception("停用失败，请重试！");
                                }
                            } else {
                                throw new Exception($skuRelationItemLogValidate->getError());
                            }
                        } else {
                            throw new Exception("停用失败，请重试！");
                        }
                    } else {
                        throw new Exception("停用失败，请重试！");
                    }
                } else {
                    throw new Exception("异常操作");
                }
            } catch (Exception $e) {
                Db::rollback();
                echo json_encode(['code' => 0, 'msg' => $e->getMessage()]);
                exit;
            }
        } else {
            echo json_encode(['code' => 0, 'msg' => '异常操作']);
            exit;
        }
    }

    /**
     * @throws DbException
     */
    public function log($ssCode): \think\response\View
    {
        $list = new SkuRelationLogModel();
        $list = $list->where(['ss_code' => $ssCode])->order('id desc')->paginate(Config::get('PAGE_NUM'));
        $this->assign('list', $list);

        return view();
    }

    /**
     * @throws ModelNotFoundException
     * @throws DbException
     * @throws DataNotFoundException
     */
    public function getUserAccountByPlatform()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            $platform = $post['platform'];

            $userModel = new AccountModel();
            $user = $userModel->where(['id'=>Session::get(Config::get('USER_LOGIN_FLAG')), 'status' => AccountModel::STATUS_ACTIVE])->find();

            $userAccountModel = new UserAccountModel();
            $userAccount = $userAccountModel->where(['platform' => $platform])->select();
            if ($user['super'] == 0 && $user['manage'] == 0) {
                $userAccountAccessModel = new UserAccountAccessModel();
                $userAccess = $userAccountAccessModel->where(['admin_user_id' => $user['id']])->column('user_account_id');
                foreach ($userAccount as $key => $value) {
                    if (!in_array($value['id'], $userAccess)) {
                        unset($userAccount[$key]);
                    }
                }
            }

            echo json_encode(['code' => 1, 'data' => $userAccount, 'user_account_id' => $post['userAccountId'] ?? 0]);
        } else {
            echo json_encode(['code' => 0, 'msg' => '异常操作']);
        }
        exit;
    }

    /**
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws \SoapFault
     */
    static public function sendSkuRelationRequest($skuRelation, $wsgCode): array
    {
        $userAccountModel = new UserAccountModel();
        $skuRelationItemModel = new SkuRelationItemModel();
        $userAccount = $userAccountModel->find($skuRelation['user_account']);
        $skuRelationItems = $skuRelationItemModel->where(['ss_code' => $skuRelation['ss_code'], 'wsg_code' => $wsgCode])->select();
        $pcr = [];
        foreach ($skuRelationItems as $item) {
            $pcr[] = [
                'pcr_product_sku'   =>  $item['warehouse_sku'],
                'pcr_quantity'      =>  intval($item['qty']),
                'pcr_pu_price'      =>  $item['percent'] * intval($item['qty']) * 100000
            ];
        }

        $dataArr['data'][] = [
            'product_sku'   =>  $skuRelation['seller_sku'],
            'user_account'  =>  [$userAccount['user_account']],
            'pcr'           =>  $pcr
        ];

        return ApiClient::EcWarehouseApi(Config::get("ec_eb_uri"), "modifySkuRelation", json_encode($dataArr));
    }

    /**
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws DataNotFoundException
     */
    public function getWarehouseSkuByWsgCode()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            $wsg_code = $post['wsg_code'];

            $skuRelationItemModel = new SkuRelationItemModel();
            $list = $skuRelationItemModel->where(['wsg_code' => $wsg_code])->select();
            $labelArr = [];
            foreach ($list as $value) {
                $labelArr[] = $value['warehouse_sku'] . ' * ' . intval($value['qty']);
            }

            echo json_encode(['code' => 1, 'msg' => implode(', ', $labelArr)]);
            exit;
        }
    }
}
