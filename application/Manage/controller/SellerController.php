<?php
namespace app\Manage\controller;

use app\Manage\model\AccountModel;
use app\Manage\model\ApiClient;
use app\Manage\model\ProductModel;
use app\Manage\model\SellerSkuBrandModel;
use app\Manage\model\SellerSkuColorModel;
use app\Manage\model\SellerSkuModel;
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

class SellerController extends BaseController
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
                $where['seller_sku|platform|brand'] = ['like', '%' . $keyword . '%'];
            }
        }

        // 查看权限
        $access_ids = AccountModel::account_access_ids();
        $where['seller_id'] = ['in', $access_ids];

        // 列表
        $sellerSkuModel = new SellerSkuModel();
        $list = $sellerSkuModel->with(['user', 'adminUser'])->where($where)->order('id desc')->paginate(Config::get('PAGE_NUM'), false, ['query' => []]);
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

            if (empty($post['user_account'])) {
                echo json_encode(['code' => 0, 'msg' => '请选择店铺']);
                exit;
            }
            $userAccountModel = new UserAccountModel();
            $userAccountCode = $userAccountModel->where(['id' => $post['user_account']])->value('user_account_code');

            if (empty($post['color'])) {
                echo json_encode(['code' => 0, 'msg' => '请选择颜色']);
                exit;
            }

            // 获取操作用户信息
            $userModel = new AccountModel();
            $user = $userModel->where(['id'=>Session::get(Config::get('USER_LOGIN_FLAG')), 'status' => AccountModel::STATUS_ACTIVE])->find();

            // 获取当前销售sku索引
            $sellerSkuModel = new SellerSkuModel();
            $lastOne = $sellerSkuModel
                ->where(['seller_id' => $user['id'], 'user_account' => $post['user_account']])
                ->order('created_time desc')
                ->find();
            $index = empty($lastOne) ? 1 : intval($lastOne['index']) + 1;

            // 销售sku逻辑
            if ($post['platform'] == "amazon") {
                if (empty($post['brand'])) {
                    echo json_encode(['code' => 0, 'msg' => '请选择品牌']);
                    exit;
                }
                $sellerSku = $userAccountCode . $post['brand'] . $user['user_code'] . sprintf("%03d", $index) . $post['color'];

                $addData = [
                    'seller_sku'        =>  $sellerSku,
                    'platform'          =>  $post['platform'],
                    'user_account'      =>  $post['user_account'],
                    'brand'             =>  $post['brand'],
                    'index'             =>  sprintf("%03d", $index),
                    'color'             =>  $post['color'],
                    'created_time'      =>  date('Y-m-d H:i:s'),
                    'created_date'      =>  date('Ymd'),
                    'seller_id'         =>  $user['id']
                ];
            } elseif ($post['platform'] == "wayfair") {
                if (empty($post['season'])) {
                    echo json_encode(['code' => 0, 'msg' => '请选择季度']);
                    exit;
                }

                if ($post['user_account'] == 2) {
                    $sellerSku = $userAccountCode . 'WY' . $post['season'] . $user['user_code'] . sprintf("%04d", $index) . $post['color'];

                    $addData = [
                        'seller_sku'        =>  $sellerSku,
                        'platform'          =>  $post['platform'],
                        'user_account'      =>  $post['user_account'],
                        'index'             =>  sprintf("%04d", $index),
                        'color'             =>  $post['color'],
                        'season'            =>  $post['season'],
                        'created_time'      =>  date('Y-m-d H:i:s'),
                        'created_date'      =>  date('Ymd'),
                        'seller_id'         =>  $user['id']
                    ];
                } elseif ($post['user_account'] == 16) {
                    $sellerSku = 'WY' . $user['user_code'] . date('Ym') . sprintf("%03d", $index) . $post['color'];

                    $addData = [
                        'seller_sku'        =>  $sellerSku,
                        'platform'          =>  $post['platform'],
                        'user_account'      =>  $post['user_account'],
                        'index'             =>  sprintf("%03d", $index),
                        'color'             =>  $post['color'],
                        'created_time'      =>  date('Y-m-d H:i:s'),
                        'created_date'      =>  date('Ymd'),
                        'seller_id'         =>  $user['id']
                    ];
                } else {
                    echo json_encode(['code' => 0, 'msg' => '异常操作']);
                    exit;
                }
            } elseif ($post['platform'] == "walmart") {
                if (empty($post['season'])) {
                    echo json_encode(['code' => 0, 'msg' => '请选择季度']);
                    exit;
                }

                if ($post['user_account'] == 11) {
                    $sellerSku = $userAccountCode . 'WLM' . $post['season'] . $user['user_code'] . sprintf("%04d", $index) . $post['color'];

                    $addData = [
                        'seller_sku'        =>  $sellerSku,
                        'platform'          =>  $post['platform'],
                        'user_account'      =>  $post['user_account'],
                        'index'             =>  sprintf("%04d", $index),
                        'color'             =>  $post['color'],
                        'season'            =>  $post['season'],
                        'created_time'      =>  date('Y-m-d H:i:s'),
                        'created_date'      =>  date('Ymd'),
                        'seller_id'         =>  $user['id']
                    ];
                } elseif ($post['user_account'] == 15) {
                    $sellerSku = 'WLM' . $user['user_code'] . date('Ym') . sprintf("%03d", $index) . $post['color'];

                    $addData = [
                        'seller_sku'        =>  $sellerSku,
                        'platform'          =>  $post['platform'],
                        'user_account'      =>  $post['user_account'],
                        'index'             =>  sprintf("%03d", $index),
                        'color'             =>  $post['color'],
                        'created_time'      =>  date('Y-m-d H:i:s'),
                        'created_date'      =>  date('Ymd'),
                        'seller_id'         =>  $user['id']
                    ];
                } else {
                    echo json_encode(['code' => 0, 'msg' => '异常操作']);
                    exit;
                }
            } elseif ($post['platform'] == "temu") {
                if (empty($post['season'])) {
                    echo json_encode(['code' => 0, 'msg' => '请选择季度']);
                    exit;
                }

                if ($post['user_account'] == 41) {
                    $sellerSku = $userAccountCode . $user['user_code'] . date('Ym') . sprintf("%03d", $index) . $post['color'];

                    $addData = [
                        'seller_sku'        =>  $sellerSku,
                        'platform'          =>  $post['platform'],
                        'user_account'      =>  $post['user_account'],
                        'index'             =>  sprintf("%04d", $index),
                        'color'             =>  $post['color'],
                        'created_time'      =>  date('Y-m-d H:i:s'),
                        'created_date'      =>  date('Ymd'),
                        'seller_id'         =>  $user['id']
                    ];
                } elseif ($post['user_account'] == 42) {
                    $sellerSku = $userAccountCode . $post['season']  . $user['user_code'] . sprintf("%03d", $index) . $post['color'];

                    $addData = [
                        'seller_sku'        =>  $sellerSku,
                        'platform'          =>  $post['platform'],
                        'user_account'      =>  $post['user_account'],
                        'index'             =>  sprintf("%03d", $index),
                        'color'             =>  $post['color'],
                        'season'            =>  $post['season'],
                        'created_time'      =>  date('Y-m-d H:i:s'),
                        'created_date'      =>  date('Ymd'),
                        'seller_id'         =>  $user['id']
                    ];
                } elseif ($post['user_account'] == 43) {
                    $sellerSku = 'TM' . $userAccountCode . $post['season'] . $user['user_code'] . sprintf("%03d", $index) . $post['color'];

                    $addData = [
                        'seller_sku'        =>  $sellerSku,
                        'platform'          =>  $post['platform'],
                        'user_account'      =>  $post['user_account'],
                        'index'             =>  sprintf("%03d", $index),
                        'color'             =>  $post['color'],
                        'season'            =>  $post['season'],
                        'created_time'      =>  date('Y-m-d H:i:s'),
                        'created_date'      =>  date('Ymd'),
                        'seller_id'         =>  $user['id']
                    ];
                } else {
                    echo json_encode(['code' => 0, 'msg' => '异常操作']);
                    exit;
                }
            } elseif ($post['platform'] == "shein") {
                if (empty($post['season'])) {
                    echo json_encode(['code' => 0, 'msg' => '请选择季度']);
                    exit;
                }

                if ($post['user_account'] == 22) {
                    $sellerSku = $userAccountCode . 'SHE' . $post['season'] . $user['user_code'] . sprintf("%04d", $index) . $post['color'];

                    $addData = [
                        'seller_sku'        =>  $sellerSku,
                        'platform'          =>  $post['platform'],
                        'user_account'      =>  $post['user_account'],
                        'index'             =>  sprintf("%04d", $index),
                        'color'             =>  $post['color'],
                        'season'            =>  $post['season'],
                        'created_time'      =>  date('Y-m-d H:i:s'),
                        'created_date'      =>  date('Ymd'),
                        'seller_id'         =>  $user['id']
                    ];
                } elseif ($post['user_account'] == 37) {
                    $sellerSku = 'SIN' . $user['user_code'] . date('Ym') . sprintf("%03d", $index) . $post['color'];

                    $addData = [
                        'seller_sku'        =>  $sellerSku,
                        'platform'          =>  $post['platform'],
                        'user_account'      =>  $post['user_account'],
                        'index'             =>  sprintf("%03d", $index),
                        'color'             =>  $post['color'],
                        'created_time'      =>  date('Y-m-d H:i:s'),
                        'created_date'      =>  date('Ymd'),
                        'seller_id'         =>  $user['id']
                    ];
                } else {
                    echo json_encode(['code' => 0, 'msg' => '异常操作']);
                    exit;
                }
            } elseif ($post['platform'] == "tiktok") {
                if ($post['user_account'] == 33) {
                    $sellerSku = 'TK' . $user['user_code'] . '02' . date('Ym') . sprintf("%04d", $index) . $post['color'];

                    $addData = [
                        'seller_sku'        =>  $sellerSku,
                        'platform'          =>  $post['platform'],
                        'user_account'      =>  $post['user_account'],
                        'index'             =>  sprintf("%04d", $index),
                        'color'             =>  $post['color'],
                        'created_time'      =>  date('Y-m-d H:i:s'),
                        'created_date'      =>  date('Ymd'),
                        'seller_id'         =>  $user['id']
                    ];
                } elseif ($post['user_account'] == 38) {
                    $sellerSku = 'TK' . $user['user_code'] . 'SC' . date('Y') . sprintf("%04d", $index) . $post['color'];

                    $addData = [
                        'seller_sku'        =>  $sellerSku,
                        'platform'          =>  $post['platform'],
                        'user_account'      =>  $post['user_account'],
                        'index'             =>  sprintf("%03d", $index),
                        'color'             =>  $post['color'],
                        'created_time'      =>  date('Y-m-d H:i:s'),
                        'created_date'      =>  date('Ymd'),
                        'seller_id'         =>  $user['id']
                    ];
                } else {
                    echo json_encode(['code' => 0, 'msg' => '异常操作']);
                    exit;
                }
            } elseif ($post['platform'] == "ebay") {
                if ($post['user_account'] == 24) {
                    $sellerSku = 'SLEBJY' . $user['user_code'] . sprintf("%04d", $index) . $post['color'];

                    $addData = [
                        'seller_sku'        =>  $sellerSku,
                        'platform'          =>  $post['platform'],
                        'user_account'      =>  $post['user_account'],
                        'index'             =>  sprintf("%04d", $index),
                        'color'             =>  $post['color'],
                        'created_time'      =>  date('Y-m-d H:i:s'),
                        'created_date'      =>  date('Ymd'),
                        'seller_id'         =>  $user['id']
                    ];
                } else {
                    echo json_encode(['code' => 0, 'msg' => '异常操作']);
                    exit;
                }
            } else {
                echo json_encode(['code' => 0, 'msg' => '请选择正确的平台']);
                exit;
            }

            if ($sellerSkuModel->insert($addData)) {
                echo json_encode(['code' => 1, 'msg' => '新增成功，你的平台货号是' . $sellerSku]);
                exit;
            } else {
                echo json_encode(['code' => 0, 'msg' => '新增失败']);
                exit;
            }
        } else {
            $userAccountModel = new UserAccountModel();
            $platform = $userAccountModel->distinct(true)->field('platform')->select();
            $this->assign('platform', $platform);

            $SellerSkuBrandModel = new SellerSkuBrandModel();
            $this->assign('brand', $SellerSkuBrandModel->order('brand_name asc')->select());

            $SellerSkuColorModel = new SellerSkuColorModel();
            $this->assign('color', $SellerSkuColorModel->order('color_code asc')->select());

            return view();
        }
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
}
