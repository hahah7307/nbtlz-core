<?php
namespace app\Manage\controller;

use app\Manage\model\AdminRoleModel;

class CheckController extends BaseController
{
    public function smsSend()
    {
        $phone = $this->request->get('phone');
        // 短信api


        return json(['code' => 1, 'msg' => $phone .'验证码：7307']);
    }

    // 获取所有用户角色
    public function get_full_adminRole()
    {
        if ($this->request->isPost()) {
            $keyword = $this->request->param('keyword');
            $cid = $this->request->param('cid');
            $category = new AdminRoleModel();
            if (!empty($keyword)) {
                $category = $category->where(['status' => AdminRoleModel::STATUS_ACTIVE, 'name' => ['like', '%'.$keyword.'%']])->field('name, id as value')->select();
                if (!empty($cid)) {
                    $cid = explode(',', $cid);
                    array_pop($cid);
                    array_shift($cid);
                    foreach ($category as $k => $v) {
                        if (in_array($v['value'], $cid)) {
                            $category[$k]['selected'] = true;
                        }
                    }
                }
                echo json_encode(array('msg' => 'success', 'code' => 0, 'data' => $category));
                exit;
            } else{
                $category = $category->where(['status' => AdminRoleModel::STATUS_ACTIVE])->field('name, id as value')->select();
                if (!empty($cid)) {
                    $cid = explode(',', $cid);
                    foreach ($category as $k => $v) {
                        if (in_array($v['value'], $cid)) {
                            $category[$k]['selected'] = true;
                        }
                    }
                }
                echo json_encode(array('msg' => 'success', 'code' => 0, 'data' => $category));
                exit;
            }
        } else {
            echo json_encode(array('msg' => 'failed', 'code' => 1, 'data' => '您的操作有误！'));
            exit;
        }
    }
}
