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
                'created_admin_id'      =>  \session(Config::get('USER_LOGIN_FLAG'))
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

    // 保存
    public function save()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            $post['storage_info'] = json_encode($post['data']);

            //
            $dataValidate = new PriceValidate();
            if ($dataValidate->scene('add')->check($post)) {
                $model = new PriceModel();
                if ($model->allowField(true)->save($post)) {
                    echo json_encode(['code' => 1, 'msg' => '添加成功']);
                    exit;
                } else {
                    echo json_encode(['code' => 0, 'msg' => '添加失败，请重试']);
                    exit;
                }
            } else {
                echo json_encode(['code' => 0, 'msg' => $dataValidate->getError()]);
                exit;
            }
        } else {
            echo json_encode(['code' => 0, 'msg' => '异常操作']);
            exit;
        }
    }

    /**
     * @throws DbException
     * @throws Exception
     */
    public function info($id)
    {
        $info = PriceModel::get(['id' => $id, 'state' => PriceModel::STATUS_ACTIVE]);
        $info['storage_info'] = json_decode(json_decode($info['storage_info']), true);

        $this->assign('info', $info);
        return view();
    }

    // 删除
    public function delete()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            $block = PriceModel::get($post['id']);
            if ($block->delete()) {
                echo json_encode(['code' => 1, 'msg' => '操作成功']);
                exit;
            } else {
                echo json_encode(['code' => 0, 'msg' => '操作失败，请重试']);
                exit;
            }
        } else {
            echo json_encode(['code' => 0, 'msg' => '异常操作']);
            exit;
        }
    }

    // 校验三边长度
    public function ajaxLengthRule() {
        header('Content-Type:application/json; charset=utf-8');
        if ($this->request->isPost()) {
            $length = $this->request->post('length');
            $width = $this->request->post('width');
            $height = $this->request->post('height');
            if (!empty($length) && !empty($width) && !empty($height)) {
                if (!is_numeric($length) || !is_numeric($width) || !is_numeric($height)) {
                    echo json_encode(['code' => 2, 'info' => '请输入正确的数字！']);
                    exit;
                }
                $arr = [$length, $width, $height];
                $maxLength = max($arr);
                // verify max length
                if ($maxLength >= Config::get('min_3leng')) {
                    echo json_encode(['code' => 2, 'info' => '最长边不得超过' . Config::get('min_3leng') . 'cm！']);
                    exit;
                }
                // verify volume
                $volume = 0;
                $isMax = 0;
                foreach ($arr as $value) {
                    if ($value == $maxLength && $isMax == 0) {
                        $volume += $maxLength;
                        $isMax ++;
                    } else {
                        $volume += 2 * $value;
                    }
                }
                if ($volume >= Config::get('min_5leng')) {
                    echo json_encode(['code' => 2, 'info' => '最长边与其他边的两倍之和为' . $volume . 'cm超过' . Config::get('min_5leng') . 'cm！']);
                    exit;
                }
                echo json_encode(['code' => 1, 'info' => 'success']);
                exit;
            } else {
                echo json_encode(['code' => 0, 'info' => 'Incomplete data']);
                exit;
            }
        } else {
            echo json_encode(['code' => 0, 'msg' => 'Abnormal operation']);
            exit;
        }
    }

    /**
     * @throws \PHPExcel_Exception
     */
    public function excel()
    {
        $data = input('data');
        $dataArr = json_decode($data, true);
//        echo "<pre>";var_dump($dataArr);exit;

        // phpexcel
        require_once './static/classes/PHPExcel/Classes/PHPExcel.php';
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

//        // Set properties
//        $objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
//            ->setLastModifiedBy("Maarten Balliauw")
//            ->setTitle("Office 2007 XLSX Test Document")
//            ->setSubject("Office 2007 XLSX Test Document")
//            ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
//            ->setKeywords("office 2007 openxml php")
//            ->setCategory("Test result file");
//        $objPHPExcel->getActiveSheet()->mergeCells('A1:O1');
//        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
//
//        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
//        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
//        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
//        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(8);
//        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(8);
//        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);

        // Set background color
        // A1 - Z1
        for ($s = 65; $s <= 90; $s ++) {
            $objPHPExcel->getActiveSheet()->getStyle(chr($s) . '1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('BDD7EE');
        }
        // AA1 - AL1
        for ($s = 65; $s <= 76; $s ++) {
            $objPHPExcel->getActiveSheet()->getStyle('A' . chr($s) . '1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('BDD7EE');
        }

        // Add some data
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', '仓库')
            ->setCellValue('B1', '产品名称')
            ->setCellValue('C1', '关税率')
            ->setCellValue('D1', '包装长cm')
            ->setCellValue('E1', '包装宽cm')
            ->setCellValue('F1', '包装高cm')
            ->setCellValue('G1', '毛重kg')
            ->setCellValue('H1', '派送方式')
            ->setCellValue('I1', '汇率')
            ->setCellValue('J1', '头程价格标准(元/CBM)')
            ->setCellValue('K1', '采购成本')
            ->setCellValue('L1', '最低市场售价')
            ->setCellValue('M1', '目标定价')
            ->setCellValue('N1', '广告费占比')
            ->setCellValue('O1', '退货率')
            ->setCellValue('P1', '平台费占比')

            ->setCellValue('Q1', '体积m3')
            ->setCellValue('R1', '毛重lbs')
            ->setCellValue('S1', '体积重LBS')
            ->setCellValue('T1', '装箱数')
            ->setCellValue('U1', '装柜数')
            ->setCellValue('V1', 'FOB成本')
            ->setCellValue('W1', '头程成本')
            ->setCellValue('X1', '头程成本占比')
            ->setCellValue('Y1', '关税')
            ->setCellValue('Z1', '关税占比')
            ->setCellValue('AA1', '4个月仓储费')
            ->setCellValue('AB1', '仓储费占比')
            ->setCellValue('AC1', '尾程')
            ->setCellValue('AD1', '尾程占比')
            ->setCellValue('AE1', '广告费')
            ->setCellValue('AF1', '退货费')
            ->setCellValue('AG1', '平台费')
            ->setCellValue('AH1', '0利润售价')
            ->setCellValue('AI1', '最低售价利润')
            ->setCellValue('AJ1', '最低售价利润率')
            ->setCellValue('AK1', '目标定价利润')
            ->setCellValue('AL1', '目标定价利润率')
        ;

        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A2', $dataArr['storage'][0]['storage_name'])
            ->setCellValue('B2', '')
            ->setCellValue('C2', $dataArr['tariff_rate'])
            ->setCellValue('D2', $dataArr['length'])
            ->setCellValue('E2', $dataArr['width'])
            ->setCellValue('F2', $dataArr['height'])
            ->setCellValue('G2', $dataArr['gross_weight'])
            ->setCellValue('H2', $dataArr['delivery'])
            ->setCellValue('I2', $dataArr['exchange_rate'])
            ->setCellValue('J2', $dataArr['flp_standard'])
            ->setCellValue('K2', $dataArr['cost'])
            ->setCellValue('L2', $dataArr['min_price'])
            ->setCellValue('M2', $dataArr['target_pricing'])
            ->setCellValue('N2', $dataArr['ad_rate'])
            ->setCellValue('O2', $dataArr['return_rate'])
            ->setCellValue('P2', $dataArr['platform_rate'])

            ->setCellValue('Q2', $dataArr['storage'][0]['data']['volume'])
            ->setCellValue('R2', $dataArr['storage'][0]['data']['gross_weight_lbs'])
            ->setCellValue('S2', $dataArr['storage'][0]['data']['volume_lbs'])
            ->setCellValue('T2', 1)
            ->setCellValue('U2', $dataArr['storage'][0]['data']['loading_qty'])
            ->setCellValue('V2', $dataArr['storage'][0]['data']['fob'])
            ->setCellValue('W2', $dataArr['storage'][0]['data']['initial_cost'])
            ->setCellValue('X2', $dataArr['storage'][0]['data']['initial_cost_rate'])
            ->setCellValue('Y2', $dataArr['storage'][0]['data']['tariff'])
            ->setCellValue('Z2', $dataArr['storage'][0]['data']['tariff_proportion'])
            ->setCellValue('AA2', $dataArr['storage'][0]['data']['storage_charge'])
            ->setCellValue('AB2', $dataArr['storage'][0]['data']['storage_charge_proportion'])
            ->setCellValue('AC2', $dataArr['storage'][0]['data']['tail_end'])
            ->setCellValue('AD2', $dataArr['storage'][0]['data']['tail_end_proportion'])
            ->setCellValue('AE2', $dataArr['storage'][0]['data']['advertising_expenses'])
            ->setCellValue('AF2', $dataArr['storage'][0]['data']['return_fee'])
            ->setCellValue('AG2', $dataArr['storage'][0]['data']['platform_fees'])
            ->setCellValue('AH2', $dataArr['storage'][0]['data']['no_profit_price'])
            ->setCellValue('AI2', $dataArr['storage'][0]['data']['min_selling_profit'])
            ->setCellValue('AJ2', $dataArr['storage'][0]['data']['min_selling_profit_rate'])
            ->setCellValue('AK2', $dataArr['storage'][0]['data']['target_pricing_profit'])
            ->setCellValue('AL2', $dataArr['storage'][0]['data']['target_pricing_profit_rate'])
        ;

        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A3', $dataArr['storage'][1]['storage_name'])
            ->setCellValue('B3', '')
            ->setCellValue('C3', $dataArr['tariff_rate'])
            ->setCellValue('D3', $dataArr['length'])
            ->setCellValue('E3', $dataArr['width'])
            ->setCellValue('F3', $dataArr['height'])
            ->setCellValue('G3', $dataArr['gross_weight'])
            ->setCellValue('H3', $dataArr['delivery'])
            ->setCellValue('I3', $dataArr['exchange_rate'])
            ->setCellValue('J3', $dataArr['flp_standard'])
            ->setCellValue('K3', $dataArr['cost'])
            ->setCellValue('L3', $dataArr['min_price'])
            ->setCellValue('M3', $dataArr['target_pricing'])
            ->setCellValue('N3', $dataArr['ad_rate'])
            ->setCellValue('O3', $dataArr['return_rate'])
            ->setCellValue('P3', $dataArr['platform_rate'])

            ->setCellValue('Q3', $dataArr['storage'][1]['data']['volume'])
            ->setCellValue('R3', $dataArr['storage'][1]['data']['gross_weight_lbs'])
            ->setCellValue('S3', $dataArr['storage'][1]['data']['volume_lbs'])
            ->setCellValue('T3', 1)
            ->setCellValue('U3', $dataArr['storage'][1]['data']['loading_qty'])
            ->setCellValue('V3', $dataArr['storage'][1]['data']['fob'])
            ->setCellValue('W3', $dataArr['storage'][1]['data']['initial_cost'])
            ->setCellValue('X3', $dataArr['storage'][1]['data']['initial_cost_rate'])
            ->setCellValue('Y3', $dataArr['storage'][1]['data']['tariff'])
            ->setCellValue('Z3', $dataArr['storage'][1]['data']['tariff_proportion'])
            ->setCellValue('AA3', $dataArr['storage'][1]['data']['storage_charge'])
            ->setCellValue('AB3', $dataArr['storage'][1]['data']['storage_charge_proportion'])
            ->setCellValue('AC3', $dataArr['storage'][1]['data']['tail_end'])
            ->setCellValue('AD3', $dataArr['storage'][1]['data']['tail_end_proportion'])
            ->setCellValue('AE3', $dataArr['storage'][1]['data']['advertising_expenses'])
            ->setCellValue('AF3', $dataArr['storage'][1]['data']['return_fee'])
            ->setCellValue('AG3', $dataArr['storage'][1]['data']['platform_fees'])
            ->setCellValue('AH3', $dataArr['storage'][1]['data']['no_profit_price'])
            ->setCellValue('AI3', $dataArr['storage'][1]['data']['min_selling_profit'])
            ->setCellValue('AJ3', $dataArr['storage'][1]['data']['min_selling_profit_rate'])
            ->setCellValue('AK3', $dataArr['storage'][1]['data']['target_pricing_profit'])
            ->setCellValue('AL3', $dataArr['storage'][1]['data']['target_pricing_profit_rate'])
        ;

        // Rename sheet
        $objPHPExcel->getActiveSheet()->setTitle('核价模板');

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);

        // Redirect output to a client’s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel');
        $filename = date("YmdHis") . time() . mt_rand(100000, 999999);
        ob_end_clean();
        header('Content-Disposition:attachment;filename="'.$filename.'.xls"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
    }
}
