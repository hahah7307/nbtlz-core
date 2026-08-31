<?php
namespace app\Manage\controller;

use app\Manage\model\AccountModel;
use app\Manage\model\AlibabaCloudCredentialsWrapper;
use app\Manage\model\ProductInstallVideoModel;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\exception\DbException;
use think\Session;
use think\Config;

class VideoController extends BaseController
{
    /**
     * @throws DbException
     * @throws Exception
     */
    public function product_install(): \think\response\View
    {
        $where = [];
        $keyword = $this->request->get('keyword', '', 'htmlspecialchars');
        $this->assign('keyword', $keyword);
        if ($keyword) {
            $where['sku|product_name'] = ['like', $keyword];
        }

        if (in_array('Amazon Operation Specialist', AccountModel::account_role())) {
            $where['created_id'] = Session::get(Config::get('USER_LOGIN_FLAG'));
        }

        $product = new ProductInstallVideoModel();
        $list = $product->with(['seller'])->where($where)->order('id desc')->paginate(Config::get('PAGE_NUM'), false, ['query' => ['keyword' => $keyword]]);
        $this->assign('list', $list);

        Session::set(Config::get('BACK_URL'), $this->request->url(), 'manage');
        return view();
    }

    // 配对
    /**
     * @throws DbException
     */
    public function install_edit($id)
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            $post['sku'] = strtoupper($post['sku']);
            $model = new ProductInstallVideoModel();
            if ($model->save($post, ['id' => $id])) {
                echo json_encode(['code' => 1, 'msg' => '修改成功']);
            } else {
                echo json_encode(['code' => 0, 'msg' => '修改失败，请重试']);
            }
            exit;
        } else {
            $info = ProductInstallVideoModel::get(['id' => $id,]);
            $this->assign('info', $info);

            return view();
        }
    }

    /**
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws DataNotFoundException
     */
    public function createTmpUrl($id)
    {
        if ($this->request->isPost()) {
            $filesObj = new ProductInstallVideoModel();
            $file = $filesObj->find($id);
            $signUrl = AlibabaCloudCredentialsWrapper::signUrl($file['file_path']);

            $newData = [
                'file_tmp_url'      =>  $signUrl['url'],
                'file_tmp_expire'   =>  $signUrl['expire'],
            ];
            $model = new ProductInstallVideoModel();
            if ($model->save($newData, ['id' => $id])) {
                echo json_encode(['code' => 1, 'msg' => '生成成功']);
            } else {
                echo json_encode(['code' => 0, 'msg' => '生成失败，请重试']);
            }
        } else {
            echo json_encode(['code' => 0, 'msg' => '操作异常！']);
            exit;
        }
    }
}
