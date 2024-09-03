<?php
namespace app\Manage\controller;

use app\Manage\model\ApiClient;
use app\Manage\model\ProductModel;
use app\Manage\model\UserModel;
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
                        if ($product['sellerId']) {
                            $userList = array_filter(explode(",", $product['sellerId']));
                            $userArr = [];
                            $sellerId = [];
                            foreach ($userList as $userId) {
                                $user = $userModel->where(['user_id' => $userId])->find();
                                if ($userAdd['user_code'] != $user['user_code']) {
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
}
