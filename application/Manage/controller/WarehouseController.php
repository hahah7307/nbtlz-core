<?php
namespace app\Manage\controller;

use app\Manage\model\AccountModel;
use app\Manage\model\ProductModel;
use app\Manage\model\UserModel;
use app\Manage\model\WarehouseInventoryReviewModel;
use app\Manage\model\WarehouseModel;
use app\Manage\validate\WarehouseValidate;
use http\Client\Curl\User;
use think\db\exception\BindParamException;
use think\exception\DbException;
use think\exception\PDOException;
use think\Session;
use think\Config;

class WarehouseController extends BaseController
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
            $where['username|nickname|phone|email'] = ['like', '%' . $keyword . '%'];
        }

        // 仓库列表
        $storage = new WarehouseModel();
        $list = $storage->where($where)->order('id asc')->paginate(Config::get('PAGE_NUM'));
        $this->assign('list', $list);

        Session::set(Config::get('BACK_URL'), $this->request->url(), 'manage');
        return view();
    }

    // 添加
    public function add()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            $post['state'] = WarehouseModel::STATE_ACTIVE;
            $dataValidate = new WarehouseValidate();
            if ($dataValidate->scene('add')->check($post)) {
                $model = new WarehouseModel();
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
            $post = $this->request->post();
            $dataValidate = new WarehouseValidate();
            if ($dataValidate->scene('edit')->check($post)) {
                $model = new WarehouseModel();
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
            $info = WarehouseModel::get(['id' => $id,]);
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
            $block = WarehouseModel::get($post['id']);
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

    // 状态切换

    /**
     * @throws DbException
     */
    public function status()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            $user = WarehouseModel::get($post['id']);
            $user['state'] = $user['state'] == WarehouseModel::STATE_ACTIVE ? 0 : WarehouseModel::STATE_ACTIVE;
            $user->save();
            echo json_encode(['code' => 1, 'msg' => '操作成功']);
        } else {
            echo json_encode(['code' => 0, 'msg' => '异常操作']);
        }
        exit;
    }

    /**
     * @throws PDOException
     * @throws DbException
     * @throws BindParamException
     */
    public function store(): \think\response\View
    {
        if (Session::get(Config::get('USER_LOGIN_FLAG')) == 1) {
            $where = '';
        } else {
            $user = AccountModel::get(Session::get(Config::get('USER_LOGIN_FLAG')));
            $model = new UserModel();
            $ecang_user = $model->where(['user_name' => $user['nickname']])->find();
            $where = "AND b.productSku IN(SELECT productSku FROM nbtlz_ecang_product WHERE saleStatus != 18 AND saleStatus != 19 AND (FIND_IN_SET(". $ecang_user['user_id'] .", sellerId) > 0 OR personSellerId = " . $ecang_user['user_id'] . "))";
        }

        $sale_day = $this->request->get('sale_day', date('Y-m-d'), 'htmlspecialchars');
        $this->assign('sale_day', $sale_day);
        $sale_day_num = date('Ymd', strtotime($sale_day));

        $model = new ProductModel();
        $storeList = $model->query('
SELECT
	`name`,
	SUM( value ) value
FROM
	(
	SELECT
	CASE
		WHEN
			inventoryAge >= 0 
			AND inventoryAge < 30 THEN 30 WHEN inventoryAge >= 30 
				AND inventoryAge < 60 THEN 60 WHEN inventoryAge >= 60 
					AND inventoryAge < 90 THEN 90 WHEN inventoryAge >= 90 
						AND inventoryAge < 120 THEN 120 WHEN inventoryAge >= 120 
							AND inventoryAge < 150 THEN 150 WHEN inventoryAge >= 150 
								AND inventoryAge < 180 THEN 180 WHEN inventoryAge >= 180 
									AND inventoryAge < 210 THEN 210 WHEN inventoryAge >= 210 
										AND inventoryAge < 240 THEN 240 WHEN inventoryAge >= 240 
											AND inventoryAge < 270 THEN 270 WHEN inventoryAge >= 270 
												AND inventoryAge < 300 THEN 300 WHEN inventoryAge >= 300 
													AND inventoryAge < 330 THEN 330 WHEN inventoryAge >= 330 
														AND inventoryAge < 360 THEN 360 WHEN inventoryAge >= 360 
															AND inventoryAge < 450 THEN 450 WHEN inventoryAge >= 450 
																AND inventoryAge < 540 THEN 540 WHEN inventoryAge >= 540 
																	AND inventoryAge < 630 THEN 630 WHEN inventoryAge >= 630 
																		AND inventoryAge < 720 THEN
																			720 ELSE 750 
																			END AS name,
	SUM( goodsNum ) AS value
FROM
	nbtlz_le_inventory_batch a
	LEFT JOIN nbtlz_ecang_product b ON SUBSTRING( a.lecangsCode, 7 ) = b.productSku 
WHERE
	created_date = ' . $sale_day_num . '  
	AND b.saleStatus != 18
	AND b.saleStatus != 19
	' . $where . '
GROUP BY name UNION ALL
SELECT
CASE
	WHEN
		stock_age >= 0 
		AND stock_age < 30 THEN 30 WHEN stock_age >= 30 
			AND stock_age < 60 THEN 60 WHEN stock_age >= 60 
				AND stock_age < 90 THEN 90 WHEN stock_age >= 90 
					AND stock_age < 120 THEN 120 WHEN stock_age >= 120 
						AND stock_age < 150 THEN 150 WHEN stock_age >= 150 
							AND stock_age < 180 THEN 180 WHEN stock_age >= 180 
								AND stock_age < 210 THEN 210 WHEN stock_age >= 210 
									AND stock_age < 240 THEN 240 WHEN stock_age >= 240 
										AND stock_age < 270 THEN 270 WHEN stock_age >= 270 
											AND stock_age < 300 THEN 300 WHEN stock_age >= 300 
												AND stock_age < 330 THEN 330 WHEN stock_age >= 330 
													AND stock_age < 360 THEN 360 WHEN stock_age >= 360 
														AND stock_age < 450 THEN 450 WHEN stock_age >= 450 
															AND stock_age < 540 THEN 540 WHEN stock_age >= 540 
																AND stock_age < 630 THEN 630 WHEN stock_age >= 630 
																	AND stock_age < 720 THEN
																		720 ELSE 750 
																		END AS name,
			SUM( sellable_quantity ) AS value
		FROM
			nbtlz_lc_inventory_batch a
	LEFT JOIN nbtlz_ecang_product b ON a.product_sku = b.productSku 
WHERE
    created_date = ' . $sale_day_num . '  
	AND b.saleStatus != 18
	AND b.saleStatus != 19
	' . $where . '
		GROUP BY
		name  UNION ALL
SELECT
CASE
	WHEN
		storageAge >= 0 
		AND storageAge < 30 THEN 30 WHEN storageAge >= 30 
			AND storageAge < 60 THEN 60 WHEN storageAge >= 60 
				AND storageAge < 90 THEN 90 WHEN storageAge >= 90 
					AND storageAge < 120 THEN 120 WHEN storageAge >= 120 
						AND storageAge < 150 THEN 150 WHEN storageAge >= 150 
							AND storageAge < 180 THEN 180 WHEN storageAge >= 180 
								AND storageAge < 210 THEN 210 WHEN storageAge >= 210 
									AND storageAge < 240 THEN 240 WHEN storageAge >= 240 
										AND storageAge < 270 THEN 270 WHEN storageAge >= 270 
											AND storageAge < 300 THEN 300 WHEN storageAge >= 300 
												AND storageAge < 330 THEN 330 WHEN storageAge >= 330 
													AND storageAge < 360 THEN 360 WHEN storageAge >= 360 
														AND storageAge < 450 THEN 450 WHEN storageAge >= 450 
															AND storageAge < 540 THEN 540 WHEN storageAge >= 540 
																AND storageAge < 630 THEN 630 WHEN storageAge >= 630 
																	AND storageAge < 720 THEN
																		720 ELSE 750 
																		END AS name,
			SUM( inventoryAvailableNum ) AS value
		FROM
			nbtlz_wyd_inventory_batch a
	LEFT JOIN nbtlz_ecang_product b ON a.masterSku = b.productSku 
WHERE
    created_date = ' . $sale_day_num . '
	AND b.saleStatus != 18
	AND b.saleStatus != 19
	' . $where . '
		GROUP BY
		name
		) a 
	GROUP BY
		`name` 
ORDER BY
	`name`;
        ');
        $this->assign('storeList', json_encode($storeList));

        $storeData[] = [
            'date',
            $sale_day
        ];
        foreach ($storeList as $key => $item) {
            $storeData[] = [
                $item['name'],
                $item['value']
            ];
        }
        $this->assign('storeData', json_encode($storeData));

        $reviewData = [
            'user_id'       =>  Session::get(Config::get('USER_LOGIN_FLAG')),
            'review_url'    =>  $this->request->url(),
            'created_date'  =>  date('Ymd'),
            'created_time'  =>  date('Y-m-d H:i:s')
        ];
        $reviewModel = new WarehouseInventoryReviewModel();
        $reviewModel->insert($reviewData);

        Session::set(Config::get('BACK_URL'), $this->request->url(), 'manage');
        return view();
    }

    /**
     * @throws PDOException
     * @throws BindParamException
     * @throws DbException
     */
    public function inventory($date, $num = 30): \think\response\View
    {
        if (Session::get(Config::get('USER_LOGIN_FLAG')) == 1) {
            $where = '';
        } else {
            $user = AccountModel::get(Session::get(Config::get('USER_LOGIN_FLAG')));
            $model = new UserModel();
            $ecang_user = $model->where(['user_name' => $user['nickname']])->find();
            $where = "WHERE
    b.productSku IN(SELECT productSku FROM nbtlz_ecang_product WHERE saleStatus != 18 AND saleStatus != 19 AND (FIND_IN_SET(". $ecang_user['user_id'] .", sellerId) > 0 OR personSellerId = " . $ecang_user['user_id'] . "))";
        }

        $seller = empty(input('seller')) ? '' : 'AND c.user_name = "' . input('seller') . '"';
        if ($seller) {
            $this->assign('seller', input('seller'));
        }

        if ($num > 360) {
            $numStart = $num - 90;
        } else {
            $numStart = $num - 30;
        }
        $this->assign('numStart', $numStart);
        $this->assign('num', $num);

        $date = date('Ymd', strtotime($date));
        $this->assign('date', $date);

        $model = new ProductModel();
        $list = $model->query('
SELECT
	SUM( num ) num,
	sku,
	warehouse,
	user_name,
	b.productImages,
	b.productTitle 
FROM
	(
		(
		SELECT
			SUBSTRING( a.lecangsCode, 7 ) sku,
			SUM( a.goodsNum ) num,
			inventoryAge age,
			warehouseCode warehouse,
			c.user_name,
			b.productTitle,
			a.created_date 
		FROM
			nbtlz_le_inventory_batch a
			LEFT JOIN nbtlz_ecang_product b ON SUBSTRING( a.lecangsCode, 7 ) = b.productSku
			LEFT JOIN nbtlz_ecang_user c ON b.personSellerId = c.user_id
		WHERE
			a.inventoryAge >= ' . $numStart . ' 
			AND a.inventoryAge < ' . $num . '  
			AND a.created_date = ' . $date . ' 
			AND b.saleStatus != 18 
			AND b.saleStatus != 19 
		GROUP BY
			sku,
			inventoryAge,
			warehouseCode,
			user_name,
			productTitle,
			created_date 
		ORDER BY
			user_name DESC 
		) UNION ALL
		(
		SELECT
			a.product_sku sku,
			SUM( sellable_quantity ) num,
			stock_age age,
			a.warehouse_code warehouse,
			c.user_name,
			b.productTitle,
			a.created_date 
		FROM
			nbtlz_lc_inventory_batch a
			LEFT JOIN nbtlz_ecang_product b ON a.product_sku = b.productSku
			LEFT JOIN nbtlz_ecang_user c ON b.personSellerId = c.user_id
		WHERE
			a.stock_age >= ' . $numStart . '  
			AND a.stock_age < ' . $num . '  
			AND a.created_date = ' . $date . ' 
			AND b.saleStatus != 18 
			AND b.saleStatus != 19 
		GROUP BY
			sku,
			stock_age,
			a.warehouse_code,
			user_name,
			b.productTitle,
			created_date 
		ORDER BY
			user_name DESC 
		) UNION ALL
		(
		SELECT
			a.masterSku sku,
			SUM( a.inventoryAvailableNum ) num,
			storageAge age,
			warehouseCode warehouse,
			c.user_name,
			b.productTitle,
			a.created_date 
		FROM
			nbtlz_wyd_inventory_batch a
			LEFT JOIN nbtlz_ecang_product b ON a.masterSku = b.productSku
			LEFT JOIN nbtlz_ecang_user c ON b.personSellerId = c.user_id
		WHERE
			a.storageAge >= ' . $numStart . '  
			AND a.storageAge < ' . $num . '  
			AND a.created_date = ' . $date . ' 
			AND b.saleStatus != 18 
			AND b.saleStatus != 19 
		GROUP BY
			sku,
			storageAge,
			warehouseCode,
			user_name,
			productTitle,
			created_date 
		ORDER BY
			user_name DESC 
		) 
	) a
	LEFT JOIN nbtlz_ecang_product b ON a.sku = b.productSku 
' . $where . '
GROUP BY
	sku,
	warehouse,
	user_name,
	productImages,
	productTitle 
ORDER BY
	num DESC;
        ');
        $this->assign('list', $list);

        return view();
    }

    /**
     * @throws PDOException
     * @throws BindParamException
     */
    public function reviewed(): \think\response\View
    {
        $sale_day = $this->request->get('sale_day', date('Y-m-d'), 'htmlspecialchars');
        $this->assign('sale_day', $sale_day);
        $sale_day_num = date('Ymd', strtotime($sale_day));

        $model = new ProductModel();
        $list = $model->query('
SELECT
	MAX( a.created_time ) created_time,
	b.nickname,
	a.created_date 
FROM
	nbtlz_warehouse_inventory_review a
	LEFT JOIN nbtlz_admin_user b ON a.user_id = b.id 
WHERE
	a.created_date = ' . $sale_day_num . ' 
GROUP BY
	nickname,
	created_date
        ');
        $this->assign('list', $list);

        return view();
    }
}
