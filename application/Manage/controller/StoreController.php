<?php
namespace app\Manage\controller;

use app\Manage\model\AHS;
use app\Manage\model\DeliverFeeModel;
use app\Manage\model\StorageRuleModel;
use app\Manage\model\PriceModel;
use app\Manage\model\StoreModel;
use app\Manage\validate\PriceValidate;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Style_Fill;
use think\Controller;
use think\Exception;
use think\exception\DbException;
use think\Session;
use think\Config;

class StoreController extends BaseController
{
    public function index()
    {
        $sort = $this->request->get('sort', 'desc', 'htmlspecialchars');
        $this->assign('sort', $sort);

        $keyword = $this->request->get('keyword', '', 'htmlspecialchars');
        $this->assign('keyword', $keyword);
        if ($keyword) {
            $where['title'] = ['like', '%' . $keyword . '%'];
        }

        // 临时不显示
        $where['created_at'] = ['elt', '2023-12-00 00:00:00'];

        // 核价列表
        $list = new StoreModel;
        $list = StoreModel::where($where)->order('id '.$sort)->paginate(Config::get('PAGE_NUM'));
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
            $post = StoreModel::formatPostData($post);
            $post = StoreModel::getDeliverTip($post);
            $data = StoreModel::getStoreData($post);
            $storeData = [
                'query_date'            =>  $post['query_date'],
                'product_name'          =>  $post['product_name'],
                'product_sku'           =>  $post['product_sku'],
                'w_sale_proportion'     =>  $post['w_sale_proportion'],
                'sale_data'             =>  json_encode($post['sale_info']),
                'post_data'             =>  json_encode($post),
                'store_data'            =>  json_encode($data),
                'created_admin_id'      =>  session(Config::get('USER_LOGIN_FLAG'))
            ];
            $model = new StoreModel();
            if ($model->allowField(true)->save($storeData)) {
                echo json_encode(['code' => 1, 'msg' => '提交成功', 'id' => $model->id]);
                exit;
            } else {
                echo json_encode(['code' => 0, 'msg' => '提交失败，请重试']);
                exit;
            }
        } else {
            $id = input('id');
            $this->assign('id', $id);

            $info = StoreModel::get($id);
            $info['sale_data'] = json_decode($info['sale_data'], true);
            $info['post_data'] = json_decode($info['post_data'], true);
            $info['store_data'] = json_decode($info['store_data'], true);
            $this->assign('info', $info);

            $query_date_format = !empty($id) ? date('Y-m-d',strtotime($info['query_date'])) : date('Y-m-d');
            $this->assign('query_date', $query_date_format);

            return view();
        }
    }
}
