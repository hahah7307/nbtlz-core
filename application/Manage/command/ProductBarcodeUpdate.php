<?php
namespace app\Manage\command;

use app\Manage\model\ApiClient;
use app\Manage\model\ProductBarcodeModel;
use app\Manage\model\ProductBarcodeUpdateModel;
use app\Manage\model\WarehouseAreaModel;
use Exception;
use think\Config;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;

class ProductBarcodeUpdate extends Command
{
    protected function configure()
    {
        $this->setName('ProductBarcodeUpdate')->setDescription('Here is the ProductBarcodeUpdate');
    }

    /**
     * @throws Exception
     */
    protected function execute(Input $input, Output $output)
    {
        // 加载自定义配置
        Config::load(APP_PATH . 'Manage/config.php');

        $productObj = new ProductBarcodeUpdateModel();
        $barcodeUpdate = $productObj->find(1);
        if (date('Ymd') == $barcodeUpdate['date']
            && $barcodeUpdate['is_finished'] == 1
        ) {
            echo "success";exit();
        }

        // 当日产品数据开始清表更新
        if (date('Ymd') > $barcodeUpdate['date']) {
            Db::execute("TRUNCATE TABLE nbtlz_ecang_product_barcode");
            ProductBarcodeUpdateModel::update(['id' => $barcodeUpdate['id'], 'date' => date('Ymd'), 'page' => 1, 'is_finished' => 0]);
            $barcodeUpdate['page'] = 1;
        }

        Db::startTrans();
        try {
            // 易仓三方仓映射更新
            if (date('Ymd') == $barcodeUpdate['date'] && $barcodeUpdate['is_finished'] == 1){
                echo "success";
            } else {
                $warehouseAreaObj = WarehouseAreaModel::all();
                $warehouseArea = array_column($warehouseAreaObj->toArray(),'warehouse_code');
                $warehouseCombine = implode('","', $warehouseArea);
                $ecProductBarcodeRes = ApiClient::EcWarehouseApi(Config::get("ec_wms_uri"), "getProductBarcodeMapList", '{"warehouse_code":["' . $warehouseCombine . '"],"page":' . $barcodeUpdate['page'] . ',"pageSize":1000}');
                if (empty($ecProductBarcodeRes['data'])) {
                    throw new Exception("no data");
                }
                $ecProductBarcodeList = $ecProductBarcodeRes['data'];
                if (count($ecProductBarcodeList) <= 0) {
                    ProductBarcodeUpdateModel::update(['id' => $barcodeUpdate['id'], 'is_finished' => 1]);
                } else {
                    $addData = [];
                    foreach ($ecProductBarcodeList as $item) {
                        $productBarcodeInfo = ProductBarcodeModel::get(['product_barcode' => $item['product_barcode'], 'warehouse_code' => $item['warehouse_code']]);
                        if (!empty($productBarcodeInfo)) {
                            continue;
                        }
                        $productBarcodeDetail = $item;
                        $productBarcodeDetail['barcode_id'] = $item['id'];
                        unset($productBarcodeDetail['id']);
                        unset($item);
                        $addData[] = $productBarcodeDetail;
                    }
                    unset($ecProductBarcodeList);
                    $productObj = new ProductBarcodeModel();
                    $productObj->saveAll($addData);
                    ProductBarcodeUpdateModel::update(['id' => $barcodeUpdate['id'], 'page' => $barcodeUpdate['page'] + 1]);
                    unset($addData);
                }
            }

            Db::commit();
            echo "success";
        } catch (\SoapFault $e) {
            Db::rollback();
            echo $e->getMessage();
        } catch (\Exception $e) {
            Db::rollback();
            echo $e->getMessage();
        }
    }
}