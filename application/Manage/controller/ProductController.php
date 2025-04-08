<?php
namespace app\Manage\controller;

use app\Manage\model\AccountModel;
use app\Manage\model\ApiClient;
use app\Manage\model\ProductEditLogModel;
use app\Manage\model\ProductModel;
use app\Manage\model\UserModel;
use app\Manage\model\WarehouseAreaModel;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use think\Session;
use think\Config;

class ProductController extends BaseController
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
            $where['productSku'] = ['like', '%' . $keyword . '%'];
        }

        $storage = new ProductModel();
        $list = $storage->where($where)->order('id asc')->paginate(Config::get('PAGE_NUM'), false, ['query' => ['keyword' => $keyword]]);
        $this->assign('list', $list);

        Session::set(Config::get('BACK_URL'), $this->request->url(), 'manage');
        return view();
    }

    /**
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws DataNotFoundException
     * @throws \SoapFault
     * @throws \Exception
     */
    public function edit()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();

            $userModel = new UserModel();
            $userAdd = $userModel->where(['user_name' => $post['user_name']])->find();
            if (empty($userAdd)) {
                echo json_encode(['code' => 0, 'msg' => '不存在的运营人员']);
                exit();
            }

            if ($post['type'] == 1) {
                $list = array_filter(explode("\n", $post['content']));
                $string = [];
                $productList = [];
                $productModel = new ProductModel();
                foreach ($list as $item) {
                    $product = $productModel->where(['productSku' => $item])->find();
                    if (!empty($product)) {
                        if ($product['sellerId'] || $product['sellerId'] == '0') {
                            $userList = array_filter(explode(",", $product['sellerId']));
                            $userArr = [];
                            $sellerId = [];
                            foreach ($userList as $userId) {
                                $user = $userModel->where(['user_id' => $userId])->find();
                                if ($userAdd['user_code'] != $user['user_code']
                                    && $user['user_code'] != 'LJT'
                                    && $user['user_code'] != 'RLY'
                                    && $user['user_code'] != 'HJ'
                                    && $user['user_code'] != 'YCX'
                                    && $user['user_code'] != 'NJJ'
                                ) {
                                    $userArr[] = '"' . $user['user_code'] . '"';
                                    $sellerId[] = $user['user_id'];
                                }
                            }

                            $userArr[] = '"' . $userAdd['user_code'] . '"';
                            $sellerId[] = $userAdd['user_id'];
                            $string[] = '{"actionType":"edit","productSku":"' . $item .'","seller":[' . implode(',', $userArr) . ']}';
                            $productList[] = ['id' => $product['id'], 'sellerId' => implode(',', $sellerId)];
                        }
                    }
                }
                $jsonString = implode(',', $string);
                $rest = ApiClient::EcWarehouseApi(Config::get("ec_wms_uri"), "syncBatchProduct", '[' . $jsonString . ']');
                if ($rest['code'] == 1) {
                    $logModel = new ProductEditLogModel();
                    $user = AccountModel::where(['id'=>Session::get(Config::get('USER_LOGIN_FLAG')), 'status' => AccountModel::STATUS_ACTIVE])->find();
                    $data = [
                        'user_name'     =>  $post['user_name'],
                        'type'          =>  $post['type'],
                        'tag'           =>  "syncBatchProduct",
                        'content'       =>  $post['content'],
                        'content_json'  =>  '[' . $jsonString . ']',
                        'created_at'    =>  date('Y-m-d H:i:s'),
                        'created_id'    =>  $user['id']
                    ];
                    $logModel->insert($data);
                    $productModel->saveAll($productList);
                    echo json_encode(['code' => 1, 'msg' => '编辑成功']);
                } else {
                    echo json_encode(['code' => 0, 'msg' => '数据异常，请重试']);
                }
            } elseif ($post['type'] == 2) {
                $list = array_filter(explode("\n", $post['content']));
                $string = [];
                $productList = [];
                $productModel = new ProductModel();
                foreach ($list as $item) {
                    $product = $productModel->where(['productSku' => $item])->find();
                    if (!empty($product)) {
                        if ($product['sellerId'] || $product['sellerId'] == '0') {
                            $userList = array_filter(explode(",", $product['sellerId']));
                            $userArr = [];
                            $sellerId = [];
                            foreach ($userList as $userId) {
                                $user = $userModel->where(['user_id' => $userId])->find();
                                if ($userAdd['user_code'] != $user['user_code']
                                    && $user['user_code'] != 'LJT'
                                    && $user['user_code'] != 'RLY'
                                    && $user['user_code'] != 'HJ'
                                    && $user['user_code'] != 'YCX'
                                    && $user['user_code'] != 'NJJ'
                                ) {
                                    $userArr[] = '"' . $user['user_code'] . '"';
                                    $sellerId[] = $user['user_id'];
                                }
                            }

                            $string[] = '{"actionType":"edit","productSku":"' . $item .'","seller":[' . implode(',', $userArr) . ']}';
                            $productList[] = ['id' => $product['id'], 'sellerId' => implode(',', $sellerId)];
                        }
                    }
                }
                $jsonString = implode(',', $string);
                $rest = ApiClient::EcWarehouseApi(Config::get("ec_wms_uri"), "syncBatchProduct", '[' . $jsonString . ']');
                if ($rest['code'] == 1) {
                    $logModel = new ProductEditLogModel();
                    $user = AccountModel::where(['id'=>Session::get(Config::get('USER_LOGIN_FLAG')), 'status' => AccountModel::STATUS_ACTIVE])->find();
                    $data = [
                        'user_name'     =>  $post['user_name'],
                        'type'          =>  $post['type'],
                        'tag'           =>  "syncBatchProduct",
                        'content'       =>  $post['content'],
                        'content_json'  =>  '[' . $jsonString . ']',
                        'created_at'    =>  date('Y-m-d H:i:s'),
                        'created_id'    =>  $user['id']
                    ];
                    $logModel->insert($data);
                    $productModel->saveAll($productList);
                    echo json_encode(['code' => 1, 'msg' => '编辑成功']);
                } else {
                    echo json_encode(['code' => 0, 'msg' => '数据异常，请重试']);
                }
            } elseif ($post['type'] == 3) {
                $list = array_filter(explode("\n", $post['content']));
                $string = [];
                $productList = [];
                $productModel = new ProductModel();
                foreach ($list as $item) {
                    $product = $productModel->where(['productSku' => $item])->find();
                    if (!empty($product)) {
                        if ($product['personSellerId'] || $product['personSellerId'] == '0') {
                            if ($userAdd['user_code'] != 'LJT'
                                && $userAdd['user_code'] != 'RLY'
                                && $userAdd['user_code'] != 'HJ'
                                && $userAdd['user_code'] != 'YCX'
                                && $userAdd['user_code'] != 'NJJ'
                            ) {

                                $string[] = '{"actionType":"edit","productSku":"' . $item .'","personSellerId":' . $userAdd['user_id'] . '}';
                            }

                            $productList[] = ['id' => $product['id'], 'personSellerId' => $userAdd['user_id']];
                        }
                    }
                }
                $jsonString = implode(',', $string);
                $rest = ApiClient::EcWarehouseApi(Config::get("ec_wms_uri"), "syncBatchProduct", '[' . $jsonString . ']');
                if ($rest['code'] == 1) {
                    $logModel = new ProductEditLogModel();
                    $user = AccountModel::where(['id'=>Session::get(Config::get('USER_LOGIN_FLAG')), 'status' => AccountModel::STATUS_ACTIVE])->find();
                    $data = [
                        'user_name'     =>  $post['user_name'],
                        'type'          =>  $post['type'],
                        'tag'           =>  "syncBatchProduct",
                        'content'       =>  $post['content'],
                        'content_json'  =>  '[' . $jsonString . ']',
                        'created_at'    =>  date('Y-m-d H:i:s'),
                        'created_id'    =>  $user['id']
                    ];
                    $logModel->insert($data);
                    $productModel->saveAll($productList);
                    echo json_encode(['code' => 1, 'msg' => '编辑成功']);
                } else {
                    echo json_encode(['code' => 0, 'msg' => '数据异常，请重试']);
                }
            } else {
                echo json_encode(['code' => 0, 'msg' => '异常操作']);
            }
            exit();
        } else {

            return view();
        }
    }

    /**
     * @throws DbException
     * @throws \SoapFault
     */
    public function warehouse_barcode()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();

            if (empty($post['sku'])) {
                echo json_encode(['code' => 0, 'msg' => '请输入SKU']);
                exit();
            }

            if (empty($post['warehouse_code'])) {
                echo json_encode(['code' => 0, 'msg' => '请选择你需要添加的仓库']);
                exit();
            }

            $skuList = array_filter(explode("\r\n", $post['sku']));
            if ($post['is_verify']) {
                $productModel = new ProductModel();
                $noSku = [];
                foreach ($skuList as $item) {
                    $product = $productModel->where(['productSku' => $item])->find();
                    if (empty($product)) {
                        $noSku[] = $item;
                    }
                }
                if ($noSku) {
                    echo json_encode(['code' => 0, 'msg' => implode(',', $noSku) . '在易仓系统不存在，请及时创建！']);
                    exit();
                }
            }

            $warehouseNewId = [];
            $warehouseBarcode = WarehouseAreaModel::all();
            foreach ($skuList as $sku) {
                $jsonString = '
{
    "warehouse_code":["' . implode('","', array_column($warehouseBarcode->toArray(), 'warehouse_code')) . '"],
    "product_barcode":"' . $sku . '",
    "pageSize":1000,
    "page":1
}  
                ';
                $rest = ApiClient::EcWarehouseApi(Config::get("ec_wms_uri"), "getProductBarcodeMapList", $jsonString);
                if ($rest['code'] == 1) {
                    foreach ($post['warehouse_code'] as $warehouseId) {
                        $warehouseArea = WarehouseAreaModel::where(['warehouse_id' => $warehouseId])->find();
                        $sum = 0;
                        foreach ($rest['data'] as $datum) {
                            if ($datum['warehouse_code'] == $warehouseArea['warehouse_code']) {
                                $sum ++;
                                break;
                            }
                        }
                        if ($sum == 0) {
                            $warehouseNewId[] = '{"product_barcode":"' . $sku . '","warehouse_product_barcode":"' . $sku . '","barcode":"' . $sku . '","warehouse_id":' . $warehouseId . '}';
                        }
                    }
                }
            }

            if (!empty($warehouseNewId)) {
                $jsonString2 = '{"data":[' . implode(',', $warehouseNewId) . ']}';
                $rest2 = ApiClient::EcWarehouseApi(Config::get("ec_wms_uri"), "batchAddProductBarCodeMap", $jsonString2);
                if ($rest2['code'] == 1) {
                    echo json_encode(['code' => 1, 'msg' => '操作成功']);
                    exit();
                } else {
                    echo json_encode(['code' => 0, 'msg' => '操作失败，请重试']);
                    exit();
                }
            } else {
                echo json_encode(['code' => 1, 'msg' => '无需新增']);
                exit();
            }
        } else {
            $warehouseBarcode = WarehouseAreaModel::all();
            $this->assign('warehouseBarcode', $warehouseBarcode);

            return view();
        }
    }
}
