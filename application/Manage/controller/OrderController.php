<?php
namespace app\Manage\controller;

use app\Manage\model\AccountModel;
use app\Manage\model\AmazonOrderSaveModel;
use app\Manage\model\UserAccountAccessModel;
use app\Manage\model\UserAccountModel;
use DateTime;
use DateTimeZone;
use Exception;
use PHPExcel_IOFactory;
use PHPExcel_Reader_Exception;
use think\Db;
use think\exception\DbException;
use think\Session;
use think\Config;

class OrderController extends BaseController
{
    /**
     * @throws DbException
     */
    public function save(): \think\response\View
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
                $where['order_id|sku'] = ['like', '%' . $keyword . '%'];
            }
        }

        $userModel = new AccountModel();
        $user = $userModel->where(['id'=>Session::get(Config::get('USER_LOGIN_FLAG')), 'status' => AccountModel::STATUS_ACTIVE])->find();

        // 查看权限
        $access_ids = AccountModel::account_access_ids();
        $where['seller_id'] = ['in', $access_ids];

        // 店铺权限
        if ($user['super'] == 0 && $user['manage'] == 0) {
            $userAccountAccessModel = new UserAccountAccessModel();
            $userAccess = $userAccountAccessModel->where(['admin_user_id' => $user['id']])->column('user_account_id');
            $where['user_account'] = ['in', $userAccess];
        }

        // 列表
        $orderModel = new AmazonOrderSaveModel();
        $list = $orderModel->with(['user'])->where($where)->order('id desc')->paginate(Config::get('PAGE_NUM'), false, ['query' => []]);
        $this->assign('list', $list);

        Session::set(Config::get('BACK_URL'), $this->request->url(), 'manage');
        return view();
    }

    /**
     * @throws PHPExcel_Reader_Exception
     * @throws \PHPExcel_Reader_Exception
     */
    public function save_import()
    {
        // phpexcel
        require_once './static/classes/PHPExcel/Classes/PHPExcel.php';

        $filename = input('filename');
        $user_account = input('user_account');
        $file= "./upload/excel/" . $filename;
        $excelReader = PHPExcel_IOFactory::createReaderForFile($file);
        $excelObj = $excelReader->load($file);
        $worksheet = $excelObj->getSheet(0);
        $data = $worksheet->toArray();
        unset($data[0]);

        Db::startTrans();
        try {
            $orderData = [];
            $amazonOrderSaveObj = new AmazonOrderSaveModel();
            foreach ($data as $item) {
                $order = $amazonOrderSaveObj->where(['order_id' => $item[0]])->find();
                if (!empty($order)) {
                    continue;
                }
                $orderData[] = [
                    "order_id"                              =>  $item[0],
                    "order_item_id"                         =>  $item[1],
                    "purchase_date"                         =>  self::dateTimeFormat($item[2]),
                    "payment_date"                          =>  self::dateTimeFormat($item[3]),
                    "buyer_email"                           =>  $item[4],
                    "buyer_name"                            =>  $item[5],
                    "cpf"                                   =>  $item[6],
                    "buyer_phone_number"                    =>  $item[7],
                    "sku"                                   =>  $item[8],
                    "product_name"                          =>  $item[9],
                    "quantity_purchase"                     =>  $item[10],
                    "currency"                              =>  $item[11],
                    "item_price"                            =>  $item[12],
                    "item_tax"                              =>  $item[13],
                    "shipping_price"                        =>  $item[14],
                    "shipping_tax"                          =>  $item[15],
                    "ship_service_level"                    =>  $item[16],
                    "recipient_name"                        =>  $item[17],
                    "ship_address_1"                        =>  $item[18],
                    "ship_address_2"                        =>  $item[19],
                    "ship_address_3"                        =>  $item[20],
                    "ship_city"                             =>  $item[21],
                    "ship_state"                            =>  $item[22],
                    "ship_postal_code"                      =>  $item[23],
                    "ship_country"                          =>  $item[24],
                    "ship_phone_number"                     =>  $item[25],
                    "delivery_start_date"                   =>  $item[26],
                    "delivery_end_date"                     =>  $item[27],
                    "delivery_time_zone"                    =>  $item[28],
                    "delivery_instructions"                 =>  $item[29],
                    "is_business_order"                     =>  $item[30],
                    "purchase_order_number"                 =>  $item[31],
                    "price_designation"                     =>  $item[32],
                    "signature_confirmation_recommended"    =>  $item[33],
                    "seller_id"                             =>  Session::get(Config::get('USER_LOGIN_FLAG')),
                    "user_account"                          =>  $user_account
                ];
            }
            $amazonOrderSaveObj->insertAll($orderData);

            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            $this->error($e->getMessage(), url('save'));
        }
        $this->redirect(url('save'));
    }

    /**
     * @throws Exception
     */
    static public function dateTimeFormat($time): string
    {
        $date = new DateTime($time);

        // 转换为北京时间（Asia/Shanghai）
        $date->setTimezone(new DateTimeZone('Asia/Shanghai'));

        // 输出成指定格式，比如 Y-m-d H:i:s
        return $date->format('Y-m-d H:i:s');  // 输出：2025-01-29 20:06:52
    }
}
