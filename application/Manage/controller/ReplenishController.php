<?php
namespace app\Manage\controller;

use app\Manage\model\AccountModel;
use app\Manage\model\ApiClient;
use app\Manage\model\ReplenishPlanModel;
use app\Manage\model\ReplenishPlanUserModel;
use DateTime;
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
            $post['start_date']= date('Ymd', strtotime($post['start_date']));
            $post['end_date']= date('Ymd', strtotime($post['end_date']));
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
            $post['amount'] = self::get_user_amount($post['month'], $id);
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

            $start = $info['start_date'];
            $end   = $info['end_date'];
            // 转换为日期格式
            $startDate = DateTime::createFromFormat('Ymd', $start);
            $endDate   = DateTime::createFromFormat('Ymd', $end);
            // 设置到当月第一天
            $startDate->modify('first day of this month');
            $endDate->modify('first day of this month');
            $months = [];
            while ($startDate <= $endDate) {
                $months[] = $startDate->format('Y-m');
                $startDate->modify('+1 month');
            }
            $this->assign('month', $months);

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

    /**
     * @throws ModelNotFoundException
     * @throws DbException
     * @throws DataNotFoundException
     */
    public function balance()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            $model = new ReplenishPlanModel();
            $user = $model->find($post['id']);
            if ($user['status'] == 1) {
                echo json_encode(['code' => 0, 'msg' => '异常操作']);
                exit();
            }

            $userModel = new ReplenishPlanUserModel();
            $sum = $userModel->where(['plan_id' => $post['id'], 'status' => 1])->sum('amount');
            if ($model->where(['id' => $post['id']])->setField('status', 1)) {
                $model->where(['id' => $post['id']])->setField('amount', $sum);
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
