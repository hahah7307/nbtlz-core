<?php
namespace app\Manage\controller;

use app\Manage\model\AccountModel;
use app\Manage\model\ApiClient;
use app\Manage\model\ProductModel;
use app\Manage\model\ReplenishPlanDetailModel;
use app\Manage\model\ReplenishPlanModel;
use app\Manage\model\ReplenishPlanUserModel;
use app\Manage\model\UserAccountAccessModel;
use app\Manage\model\UserAccountModel;
use DateTime;
use think\Db;
use think\db\exception\BindParamException;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\exception\DbException;
use think\exception\PDOException;
use think\Session;
use think\Config;

class ReplenishController extends BaseController
{
    /**
     * @throws DbException
     */
    public function index(): \think\response\View
    {
        $where = [];
        $status = $this->request->get('status', 0, 'htmlspecialchars');
        $this->assign('status', $status);
        if ($status != '-1') {
            $where['status'] = $status;
        }

        $keyword = $this->request->get('keyword', '', 'htmlspecialchars');
        $this->assign('keyword', $keyword);
        if ($keyword) {
            $where['plan_title'] = ['like', '%' . $keyword . '%'];
        }

        $list = new ReplenishPlanModel();
        $list = $list->with(['admin_user'])->where($where)->paginate(Config::get('PAGE_NUM'));
        $this->assign('list', $list);

        Session::set(Config::get('BACK_URL'), $this->request->url(), 'manage');
        return view();
    }

    // 添加
    /**
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            if (empty($post['warehouse_sku'])) {
                echo json_encode(['code' => 0, 'msg' => '仓库SKU不能为空']);
                exit;
            }


            if (empty($post['end_date'])) {
                echo json_encode(['code' => 0, 'msg' => '仓库SKU不能为空']);
                exit;
            }

            $productModel = new ProductModel();
            $product = $productModel->where(['productSku' => $post['warehouse_sku']])->find();
            if (empty($product)) {
                echo json_encode(['code' => 0, 'msg' => '仓库SKU不存在']);
                exit;
            }
            $post['plan_title'] = $post['warehouse_sku'] . ' ' . $product['productTitle'] . ' 补货至' . $post['end_date'];
            $post['end_date']= date('Ym', strtotime($post['end_date']));
            $post['create_time'] = date('Y-m-d H:i:s', time());
            $post['create_id'] = Session::get(Config::get('USER_LOGIN_FLAG'));
            $model = new ReplenishPlanModel();
            if ($model->allowField(true)->save($post)) {
                echo json_encode(['code' => 1, 'msg' => '提交成功']);
                exit;
            } else {
                echo json_encode(['code' => 0, 'msg' => '提交失败，请重试']);
                exit;
            }
        } else {

            return view();
        }
    }

    /**
     * @throws DbException
     * @throws Exception
     * @throws \SoapFault
     */
    public function user_list($id): \think\response\View
    {
        $where['plan_id'] = $id;
        $role = AccountModel::account_role();
        if (in_array('Replenish', $role)) {
            $where['status'] = 1;
        } else {
            $where['user_id'] = Session::get(Config::get('USER_LOGIN_FLAG'));
        }

        $plan = ReplenishPlanModel::get($id);
        $this->assign('plan', $plan);
        if ($plan['status'] == 1) {
            $postParam = ['sale_start' => date('Y-m-d', strtotime($plan['start_date'])), 'sale_end' => date('Y-m-d', strtotime($plan['end_date'])), 'sku' => $plan['warehouse_sku']];
            $actualList = ApiClient::httpCurl("http://139.224.106.228/Home/Api/getSkuDailySales", "POST", $postParam);
            $actualListRes = json_decode($actualList, true);
            if ($actualListRes['code'] == 200) {
                $actualList = $actualListRes['data']['list'];
            } else {
                $actualList = [];
            }
        } else {
            $actualList = [];
        }

        $list = new ReplenishPlanUserModel();
        $this->assign('sum', $list->where($where)->sum('amount'));
        $list = $list->with(['plan', 'user'])->where($where)->paginate(Config::get('PAGE_NUM'));
        $actual_list = [];
        foreach ($list as $item) {
            $planUser = ReplenishPlanUserModel::get($item['id']);
            $actual_list[$item['id']] = self::get_actual_sales($planUser, $actualList, $plan['warehouse_sku']);
        }
        $this->assign('actual_list', $actual_list);
        $this->assign('list', $list);
        $this->assign('id', $id);

        $leStore = 0;
        $leOnWay = 0;
        $wydStore = 0;
        $wydOnWay = 0;
        $lcdStore = 0;
        $lcOnWay = 0;
        // 乐歌库存
        $leApiRes = ApiClient::LeWarehouseApi("https://app.lecangs.com/api/oms/inventoryOverview/apiPage", "POST", ['pageNum' => 1, 'goodsCode' => $plan['warehouse_sku']]);
        if ($leApiRes['code'] == 1) {
            foreach ($leApiRes['data']['list'] as $item) {
                if ($item['lecangsCode'] == 'NBTLZ-' . $plan['warehouse_sku']) {
                    $leStore += $item['uesNum'];
                    $leOnWay += $item['onWayNum'];
                }
            }
        }

        // 无忧达库存
        $wydApiRes = ApiClient::WydWarehouseApi("/oms/openapi/stock/v1/warehouseInventory/query", "POST", ['pageNo' => 1, 'pageSize' => 50, 'sku' => $plan['warehouse_sku']]);
        if ($wydApiRes['code'] == 1) {
            $resArr = json_decode($wydApiRes['data'], true);
            foreach ($resArr['records'] as $item) {
                if ($item['masterSku'] == $plan['warehouse_sku']) {
                    $wydStore += $item['inventoryAvailableNum'];
                    $wydOnWay += $item['inventoryTransportationNum'];
                }
            }
        }

        // 良仓库存
        $lcApiRes = ApiClient::LcWarehouseApi("getProductInventory", '{"pageSize":1,"page":50,"product_sku":"' . $plan['warehouse_sku'] . '"}');
        if ($lcApiRes['code'] == 1) {
            foreach ($lcApiRes['data'] as $item) {
                if ($item['product_sku'] == $plan['warehouse_sku']) {
                    $lcdStore += $item['sellable'];
                    $lcOnWay += $item['onway'];
                }
            }
        }

        $this->assign('leStore', $leStore);
        $this->assign('leOnWay', $leOnWay);
        $this->assign('wydStore', $wydStore);
        $this->assign('wydOnWay', $wydOnWay);
        $this->assign('lcStore', $lcdStore);
        $this->assign('lcOnWay', $lcOnWay);

        Session::set(Config::get('BACK_URL'), $this->request->url(), 'manage');
        return view();
    }

    /**
     * @throws DbException
     * @throws Exception
     * @throws \SoapFault
     */
    public function detail($id): \think\response\View
    {
        $model = new ReplenishPlanModel();
        $plan = $model->with(['plan_detail.user'])->where(['id' => $id])->find();
//        dump($plan->toArray());exit();
        $this->assign('plan', $plan);

        Session::set(Config::get('BACK_URL'), $this->request->url(), 'manage');
        return view();
    }

    /**
     */
    public function user_add($id)
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            foreach ($post['month'] as $item) {
                if ($item == "") {
                    echo json_encode(['code' => 0, 'msg' => '请把表单内容填写完整']);
                    exit;
                }
            }
            $post['json_detail'] = json_encode($post['month']);
            unset($post['month']);
            $post['plan_id'] = $id;
            $post['user_id'] = Session::get(Config::get('USER_LOGIN_FLAG'));
            $post['created_time'] = date('Y-m-d H:i:s');
            $model = new ReplenishPlanUserModel();
            if ($model->save($post)) {
                echo json_encode(['code' => 1, 'msg' => '提交成功']);
                exit;
            } else {
                echo json_encode(['code' => 0, 'msg' => '提交失败，请重试']);
                exit;
            }
        } else {
            $model = new ReplenishPlanModel();
            $info = $model->find($id);
            $this->assign('info', $info);
            $this->assign('id', $id);

            $start = date('Ym');
            $end   = $info['end_date'];

            $months = [];

            $current = DateTime::createFromFormat('Ym', $start);
            $endDate = DateTime::createFromFormat('Ym', $end);

            while ($current <= $endDate) {
                $months[] = $current->format('Y-m');
                $current->modify('+1 month');
            }
            $this->assign('month', $months);

            $platform = UserAccountModel::getPlatformByUser(Session::get(Config::get('USER_LOGIN_FLAG')));
            $this->assign('platform', $platform);

            return view();
        }
    }

    /**
     */
    public function user_add_addition($id)
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            $accountModel = new AccountModel();
            $user = $accountModel->where(['nickname' => $post['user_name']])->find();
            $post['user_id'] = $user['id'];
            $post['status'] = 1;
            $post['plan_id'] = $id;
            $post['created_time'] = date('Y-m-d H:i:s');
            unset($post['user_name']);
            $model = new ReplenishPlanUserModel();
            if ($model->save($post)) {
                echo json_encode(['code' => 1, 'msg' => '提交成功']);
                exit;
            } else {
                echo json_encode(['code' => 0, 'msg' => '提交失败，请重试']);
                exit;
            }
        } else {
            $model = new ReplenishPlanModel();
            $info = $model->find($id);
            $this->assign('info', $info);
            $this->assign('id', $id);

            return view();
        }
    }

    /**
     * @throws ModelNotFoundException
     * @throws DbException
     * @throws DataNotFoundException
     */
    public function user_status()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            $model = new ReplenishPlanUserModel();
            $user = $model->with(['plan'])->find($post['id']);
            if ($user['plan']['status'] == 1) {
                echo json_encode(['code' => 0, 'msg' => '补货数据已结存，不可修改状态']);
                exit();
            }

            if ($user['user_id'] != Session::get(Config::get('USER_LOGIN_FLAG'))) {
                echo json_encode(['code' => 0, 'msg' => '异常操作']);
                exit();
            }
            $model->where(['plan_id' => $user['plan_id'], 'user_id' => $user['user_id']])->setField('status', 0);
            if ($user['status'] != 1) {
                $model->where(['id' => $post['id']])->setField('status', 1);
            }

            echo json_encode(['code' => 1, 'msg' => '操作成功']);
        } else {
            echo json_encode(['code' => 0, 'msg' => '异常操作']);
        }
        exit;
    }

    // 删除

    /**
     * @throws DbException
     */
    public function delete()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            $block = ReplenishPlanModel::get($post['id']);
            $list = ReplenishPlanUserModel::all(['plan_id' => $post['id']]);
            if (count($list) > 0) {
                echo json_encode(['code' => 0, 'msg' => '有补货记录，不可删除']);
                exit;
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

    /**
     * @throws ModelNotFoundException
     * @throws DbException
     * @throws DataNotFoundException
     * @throws \Exception
     */
    public function balance()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            if ($post['unproduced'] == '') {
                echo json_encode(['code' => 0, 'msg' => '请填写工厂下单未包数']);
                exit;
            }
            if ($post['pendingship'] == '') {
                echo json_encode(['code' => 0, 'msg' => '请填写工厂待出数']);
                exit;
            }

            $model = new ReplenishPlanModel();
            $user = $model->find($post['id']);
            if ($user['status'] == 1) {
                echo json_encode(['code' => 0, 'msg' => '异常操作']);
                exit();
            }

            $planModel = new ReplenishPlanModel();
            $plan = $planModel->where(['id' => $post['id']])->find();
            $userModel = new ReplenishPlanUserModel();
            $userList = $userModel->where(['plan_id' => $post['id'], 'status'=> 1])->select();
            $planDetailModel = new ReplenishPlanDetailModel();

            // 获取每月剩余天数
            $current = new DateTime(date('Y-m-d'));
            $end = DateTime::createFromFormat('Ym', $plan['end_date']);
            $end->modify('last day of this month')->setTime(0, 0, 0);
            $dayArr = [];
            while ($current <= $end) {
                $yearMonth = $current->format('Y-m');
                $monthEnd = new DateTime($current->format('Y-m-t'));
                $monthEnd->setTime(0, 0, 0);
                if ($monthEnd > $end) {
                    $monthEnd = clone $end;
                }
                $days = $current->diff($monthEnd)->days + 1;
                $dayArr[$yearMonth] = $days;
                $current = (clone $current)->modify('first day of next month');
            }

            $detailArr = [];
            $all_day_sale_list = [];
            $all_month_sale_list = [];
            foreach ($userList as $item) {
                $month_sale_list = [];
                if (!empty($item['json_detail'])) {
                    $sale_plan = json_decode($item['json_detail'], true);
                    foreach ($sale_plan as $mon => $day_sale) {
                        $month_sale_list[$mon] = $day_sale * $dayArr[$mon];
                        $all_day_sale_list[$mon] += $day_sale;
                        $all_month_sale_list[$mon] += $day_sale * $dayArr[$mon];
                    }
                    $detailArr[] = [
                        'type'                  =>  0,
                        'plan_id'               =>  $plan['id'],
                        'plan_user_id'          =>  $item['id'],
                        'user_id'               =>  $item['user_id'],
                        'warehouse_sku'         =>  $plan['warehouse_sku'],
                        'platform'              =>  $item['platform'],
                        'day_sale_sum_json'     =>  $item['json_detail'],
                        'month_sale_sum_json'   =>  json_encode($month_sale_list),
                        'month_sale_sum'        =>  array_sum($month_sale_list),
                        'settled_inventory'     =>  0
                    ];

                    unset($month_sale_list);
                } else {
                    $detailArr[] = [
                        'type'                  =>  0,
                        'plan_id'               =>  $plan['id'],
                        'plan_user_id'          =>  $item['id'],
                        'user_id'               =>  $item['user_id'],
                        'warehouse_sku'         =>  $plan['warehouse_sku'],
                        'platform'              =>  $item['platform'],
                        'day_sale_sum_json'     =>  json_encode(''),
                        'month_sale_sum_json'   =>  json_encode(''),
                        'month_sale_sum'        =>  $item['amount'],
                        'settled_inventory'     =>  0
                    ];
                    $all_month_sale_list[$item['platform']] = $item['amount'];
                }
            }

            $detailArr[] = [
                'type'                  =>  1,
                'plan_id'               =>  $plan['id'],
                'plan_user_id'          =>  0,
                'user_id'               =>  0,
                'warehouse_sku'         =>  $plan['warehouse_sku'],
                'platform'              =>  '',
                'day_sale_sum_json'     =>  json_encode($all_day_sale_list),
                'month_sale_sum_json'   =>  json_encode($all_month_sale_list),
                'month_sale_sum'        =>  array_sum($all_month_sale_list),
                'settled_inventory'     =>  array_sum($all_month_sale_list) - ($post['unproduced'] + $post['pendingship'] + $post['onWay'] + $post['store'])
            ];


            Db::startTrans();
            try {
                $planUpdate = [
                    'status'        =>  1,
                    'unproduced'    =>  $post['unproduced'],
                    'pendingship'   =>  $post['pendingship'],
                    'onWay'         =>  $post['onWay'],
                    'store'         =>  $post['store'],
                    'amount'        =>  array_sum($all_month_sale_list) - $post['unproduced'] + $post['pendingship'] + $post['onWay'] + $post['store']
                ];
                if ($planModel->update($planUpdate, ['id' => $plan['id']])) {
                    if ($planDetailModel->insertAll($detailArr)) {
                        Db::commit();
                        echo json_encode(['code' => 1, 'msg' => '合并成功']);
                        exit;
                    } else {
                        throw new Exception('操作失败！');
                    }
                } else {
                    throw new Exception('操作失败！');
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
            $model = new ReplenishPlanUserModel();
            $user = $model->find($post['id']);
            if ($user['is_reject'] == 1 || $user['status'] == 0) {
                echo json_encode(['code' => 0, 'msg' => '异常操作']);
                exit();
            }

            if ($model->where(['id' => $post['id']])->setField('is_reject', 1)) {
                $model->where(['id' => $post['id']])->setField('status', 0);
                echo json_encode(['code' => 1, 'msg' => '操作成功']);
            } else {
                echo json_encode(['code' => 0, 'msg' => '异常操作']);
            }
            exit();
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
    public function deprecated()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            $model = new ReplenishPlanModel();
            $user = $model->find($post['id']);
            if ($user['status'] == 2) {
                echo json_encode(['code' => 0, 'msg' => '异常操作']);
                exit();
            }

            if ($model->where(['id' => $post['id']])->setField('status', 2)) {
                echo json_encode(['code' => 1, 'msg' => '操作成功']);
            } else {
                echo json_encode(['code' => 0, 'msg' => '异常操作']);
            }
            exit();
        } else {
            echo json_encode(['code' => 0, 'msg' => '异常操作']);
            exit;
        }
    }

    /**
     * @throws DataNotFoundException
     * @throws BindParamException
     * @throws PDOException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    static public function get_actual_sales($planUser, $list, $sku): array
    {
        $userModel = new AccountModel();
        $user = $userModel->find($planUser['user_id']);
        $relationList = $userModel->query('
SELECT
	a.id,
	a.seller_sku,
	b.warehouse_sku,
	b.qty,
	e.sp_unit_price,
	e.productTitle,
	b.percent,
	a.warehouse_name,
	d.user_account,
	c.nickname,
	a.created_time,
	a.updated_time,
	a.`status`
FROM
	nbtlz_sku_relation a
	LEFT JOIN nbtlz_sku_relation_item b ON a.ss_code = b.ss_code AND a.wsg_code = b.wsg_code
	LEFT JOIN nbtlz_admin_user c ON a.seller_id = c.id
	LEFT JOIN nbtlz_user_account d ON a.user_account = d.id
	LEFT JOIN nbtlz_ecang_product e ON b.warehouse_sku = e.productSku
WHERE nickname = "' . $user['nickname'] . '"
AND warehouse_sku = "' . $sku . '"
ORDER BY
	a.`status` ASC,
	a.updated_time DESC;
        ');

        $map = [];
        foreach ($relationList as $item) {
            $key = $item['user_account'] . '_' . $item['warehouse_sku'];
            $map[$key] = true;
        }

        $saleRes = [];
        foreach ($list as $value) {
            $relation = $value['userAccount'] . '_' . $sku;
            if (isset($map[$relation])) {
                $saleRes[$value['month']] = $saleRes[$value['month']] ?? 0;
                $saleRes[$value['month']] += $value['avg_daily_qty'];
            }
        }

        return $saleRes;
    }

    /**
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws DataNotFoundException
     * @throws \Exception
     */
    static public function get_user_amount($data, $plan_id)
    {
        $planModel = new ReplenishPlanModel();
        $plan = $planModel->find($plan_id);
        $monthDays = self::getMonthDays($plan['start_date'], $plan['end_date']);
        $amount = 0;
        foreach ($data as $k => $v) {
            $amount += $v * $monthDays[$k];
        }

        return $amount;
    }

    /**
     * @throws \Exception
     */
    static public function getMonthDays($startDate, $endDate): array
    {
        // 转换格式
        $startDate = self::formatDate($startDate);
        $endDate   = self::formatDate($endDate);

        $start = new DateTime($startDate);
        $end = new DateTime($endDate);
        $end->modify('+1 day'); // 包含结束日期

        $result = [];
        while ($start < $end) {
            $month = $start->format('Y-m');
            $monthEnd = (clone $start)->modify('last day of this month');
            if ($monthEnd >= $end) {
                $monthEnd = (clone $end)->modify('-1 day');
            }

            $days = $start->diff($monthEnd)->days + 1;
            $result[$month] = $days;
            $start = $monthEnd->modify('+1 day');
        }

        return $result;
    }

    static public function formatDate($date): string
    {
        // 支持 int 或字符串
        $date = (string)$date;
        return substr($date, 0, 4) . '-' .
            substr($date, 4, 2) . '-' .
            substr($date, 6, 2);
    }
}
