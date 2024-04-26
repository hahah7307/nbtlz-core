<?php

namespace app\Manage\model;

use think\Config;
use think\Model;

class StoreModel extends Model
{
    const STATUS_ACTIVE = 1;

    protected $name = 'store';

    protected $resultSetType = 'collection';

    protected $insert = ['created_at', 'updated_at'];

    protected $update = ['updated_at'];

    protected function setCreatedAtAttr()
    {
        return date('Y-m-d H:i:s');
    }

    protected function setUpdatedAtAttr()
    {
        return date('Y-m-d H:i:s');
    }

    static public function formatPostData($post): array
    {
        $arr['query_date'] = $post['query_date'];
        $arr['product_name'] = $post['product_name'];
        $arr['product_sku'] = $post['product_sku'];
        $arr['w_sale_proportion'] = implode(',', $post['sale_proportion']);
        $arr['sale_info'] = array_combine($post['month'], $post['sale']);
        $arr['w_info'] = [
            'basic_store'   =>  $post['w_basic_store'],
            'deliver'       =>  array_combine($post['w_deliver_date'], $post['w_deliver_num'])
        ];
        $arr['e_info'] = [
            'basic_store'   =>  $post['e_basic_store'],
            'deliver'       =>  array_combine($post['e_deliver_date'], $post['e_deliver_num'])
        ];
        $arr['s_info'] = [
            'basic_store'   =>  $post['s_basic_store'],
            'deliver'       =>  array_combine($post['s_deliver_date'], $post['s_deliver_num'])
        ];
        $arr['se_info'] = [
            'basic_store'   =>  $post['se_basic_store'],
            'deliver'       =>  array_combine($post['se_deliver_date'], $post['se_deliver_num'])
        ];

        return $arr;
    }

    static public function getStoreData($formatPostData, $data = [])
    {
        if (!empty($data)) {
            // 获取上一天的时间
            $array = array_keys($data['w']);
            $ymd = end($array);
            unset($array);

            // 当日时间
            $current_date = date('Ymd', strtotime($ymd) + 24 * 60 * 60);

            // 销量
            $ym = date('Ym', strtotime($current_date));
            $sale = $formatPostData['sale_info'][$ym];
            if ($sale == null)
            return $data;

            // 获取当天到货数
            $w_deliver_date_format = date('Y-m-d', strtotime($current_date) - 24 * 60 * 60 * Config::get('W_TRANSPORT_DAY'));
            $e_deliver_date_format = date('Y-m-d', strtotime($current_date) - 24 * 60 * 60 * Config::get('E_TRANSPORT_DAY'));
            $s_deliver_date_format = date('Y-m-d', strtotime($current_date) - 24 * 60 * 60 * Config::get('S_TRANSPORT_DAY'));
            $se_deliver_date_format = date('Y-m-d', strtotime($current_date) - 24 * 60 * 60 * Config::get('SE_TRANSPORT_DAY'));
            $w_new_store = $formatPostData['w_info']['deliver'][$w_deliver_date_format] ?? 0;
            $e_new_store = $formatPostData['e_info']['deliver'][$e_deliver_date_format] ?? 0;
            $s_new_store = $formatPostData['s_info']['deliver'][$s_deliver_date_format] ?? 0;
            $se_new_store = $formatPostData['se_info']['deliver'][$se_deliver_date_format] ?? 0;

            // 计算两地销量
            // 优先美东出单，然后美西、美南、美东南
            $sale_list = explode(',', $formatPostData['w_sale_proportion']);
            $w_sale = min(floor($sale * $sale_list[0] / array_sum($sale_list)), $data['w'][$ymd] + $w_new_store);
            $s_sale = min(floor($sale * $sale_list[2] / array_sum($sale_list)), $data['s'][$ymd] + $s_new_store);
            $se_sale = min(floor($sale * $sale_list[3] / array_sum($sale_list)), $data['se'][$ymd] + $se_new_store);
            $e_sale = min($sale - $w_sale - $s_sale - $se_sale, $data['e'][$ymd] + $e_new_store);
            if ($w_sale + $e_sale + $se_sale + $s_sale < $sale) {
                $e_sale = min(floor($sale * $sale_list[1] / array_sum($sale_list)), $data['e'][$ymd] + $e_new_store);
                $s_sale = min(floor($sale * $sale_list[2] / array_sum($sale_list)), $data['s'][$ymd] + $s_new_store);
                $se_sale = min(floor($sale * $sale_list[3] / array_sum($sale_list)), $data['se'][$ymd] + $se_new_store);
                $w_sale = min($sale - $e_sale - $s_sale - $se_sale, $data['w'][$ymd] + $w_new_store);
                if ($w_sale + $e_sale + $se_sale + $s_sale < $sale) {
                    $w_sale = min(floor($sale * $sale_list[0] / array_sum($sale_list)), $data['w'][$ymd] + $w_new_store);
                    $e_sale = min(floor($sale * $sale_list[1] / array_sum($sale_list)), $data['e'][$ymd] + $e_new_store);
                    $se_sale = min(floor($sale * $sale_list[3] / array_sum($sale_list)), $data['se'][$ymd] + $se_new_store);
                    $s_sale = min($sale - $w_sale - $e_sale - $se_sale, $data['s'][$ymd] + $s_new_store);
                    if ($w_sale + $e_sale + $se_sale + $s_sale < $sale) {
                        $w_sale = min(floor($sale * $sale_list[0] / array_sum($sale_list)), $data['w'][$ymd] + $w_new_store);
                        $e_sale = min(floor($sale * $sale_list[1] / array_sum($sale_list)), $data['e'][$ymd] + $e_new_store);
                        $s_sale = min(floor($sale * $sale_list[2] / array_sum($sale_list)), $data['s'][$ymd] + $s_new_store);
                        $se_sale = min($sale - $w_sale - $e_sale - $s_sale, $data['se'][$ymd] + $se_new_store);
                    }
                }
            }

            // 计算两地剩余库存
            $w_store = max($data['w'][$ymd] + $w_new_store - $w_sale, 0);
            $e_store = max($data['e'][$ymd] + $e_new_store - $e_sale, 0);
            $s_store = max($data['s'][$ymd] + $s_new_store - $s_sale, 0);
            $se_store = max($data['se'][$ymd] + $se_new_store - $se_sale, 0);
            $data['w'][$current_date] = $w_store;
            $data['e'][$current_date] = $e_store;
            $data['s'][$current_date] = $s_store;
            $data['se'][$current_date] = $se_store;
            $sum = $w_store + $e_store + $s_store + $se_store;
            if ($sum > $formatPostData['sale_info'][$ym] * Config::get('MAX_DAY_SALE_TIMES')) {
                $data['sum'][$current_date] = ['code' => 2, 'value' => $sum];
            } elseif ($sum < $formatPostData['sale_info'][$ym] * Config::get('MIN_DAY_SALE_TIMES')) {
                $data['sum'][$current_date] = ['code' => 1, 'value' => $sum];
            } else {
                $data['sum'][$current_date] = ['code' => 0, 'value' => $sum];
            }

            // 获取两个仓库送货的最后一天
            $w_date_list = array_keys($formatPostData['w_info']['deliver']);
            $w_last_date = end($w_date_list);
            $e_date_list = array_keys($formatPostData['e_info']['deliver']);
            $e_last_date = end($e_date_list);
            $s_date_list = array_keys($formatPostData['s_info']['deliver']);
            $s_last_date = end($s_date_list);
            $se_date_list = array_keys($formatPostData['se_info']['deliver']);
            $se_last_date = end($se_date_list);

            // 判断是否已没货和卖完
            if ($w_store == 0
                && $e_store == 0
                && $s_store == 0
                && $se_store == 0
                && $current_date >= date('Ymd', strtotime($w_last_date) + 24 * 60 * 60 * Config::get('W_TRANSPORT_DAY'))
                && $current_date >= date('Ymd', strtotime($e_last_date) + 24 * 60 * 60 * Config::get('E_TRANSPORT_DAY'))
                && $current_date >= date('Ymd', strtotime($s_last_date) + 24 * 60 * 60 * Config::get('S_TRANSPORT_DAY'))
                && $current_date >= date('Ymd', strtotime($se_last_date) + 24 * 60 * 60 * Config::get('SE_TRANSPORT_DAY'))
            ) {
                return $data;
            } else {
                return self::getStoreData($formatPostData, $data);
            }
        } else {
            $ymd = date('Ymd', strtotime($formatPostData['query_date']));
            $data = [
                'w' => [$ymd => intval($formatPostData['w_info']['basic_store'])],
                'e' => [$ymd => intval($formatPostData['e_info']['basic_store'])],
                's' => [$ymd => intval($formatPostData['s_info']['basic_store'])],
                'se' => [$ymd => intval($formatPostData['se_info']['basic_store'])]
            ];
            $ym = date('Ym', strtotime($formatPostData['query_date']));
            $sum = intval($formatPostData['w_info']['basic_store']) + intval($formatPostData['e_info']['basic_store']) + intval($formatPostData['s_info']['basic_store']) + intval($formatPostData['se_info']['basic_store']);
            if ($sum > $formatPostData['sale_info'][$ym] * Config::get('MAX_DAY_SALE_TIMES')) {
                $data['sum'][$ymd] = ['code' => 2, 'value' => $sum];
            } elseif ($sum < $formatPostData['sale_info'][$ym] * Config::get('MIN_DAY_SALE_TIMES')) {
                $data['sum'][$ymd] = ['code' => 1, 'value' => $sum];
            } else {
                $data['sum'][$ymd] = ['code' => 0, 'value' => $sum];
            }

            return self::getStoreData($formatPostData, $data);
        }
    }

    static public function getDeliverTip($data)
    {
        if ($data) {
            foreach ($data['w_info']['deliver'] as $date => $value) {
                if ($date) {
                    $date1 = date('Y-m-d', strtotime($date) - Config::get('AMERICAN_WEST_TRANSFER_DAY') * 24 * 60 * 60);
                    $date2 = date('Y-m-d', strtotime($date) - Config::get('AMERICAN_WEST_ORDER_DAY') * 24 * 60 * 60);
                    $data['w_info']['tip'][] = "出运日期" . $date1 . "，下单日期" . $date2;
                    unset($date1);
                    unset($date2);
                } else {
                    $data['w_info']['tip'][] = "";
                }
            }
            foreach ($data['e_info']['deliver'] as $date => $value) {
                if ($date) {
                    $date1 = date('Y-m-d', strtotime($date) - Config::get('AMERICAN_EAST_TRANSFER_DAY') * 24 * 60 * 60);
                    $date2 = date('Y-m-d', strtotime($date) - Config::get('AMERICAN_EAST_ORDER_DAY') * 24 * 60 * 60);
                    $data['e_info']['tip'][] = "出运日期" . $date1 . "，下单日期" . $date2;
                    unset($date1);
                    unset($date2);
                } else {
                    $data['e_info']['tip'][] = "";
                }
            }
            foreach ($data['s_info']['deliver'] as $date => $value) {
                if ($date) {
                    $date1 = date('Y-m-d', strtotime($date) - Config::get('AMERICAN_SOUTH_TRANSFER_DAY') * 24 * 60 * 60);
                    $date2 = date('Y-m-d', strtotime($date) - Config::get('AMERICAN_SOUTH_ORDER_DAY') * 24 * 60 * 60);
                    $data['s_info']['tip'][] = "出运日期" . $date1 . "，下单日期" . $date2;
                    unset($date1);
                    unset($date2);
                } else {
                    $data['s_info']['tip'][] = "";
                }
            }
            foreach ($data['se_info']['deliver'] as $date => $value) {
                if ($date) {
                    $date1 = date('Y-m-d', strtotime($date) - Config::get('AMERICAN_SOUTH_EAST_TRANSFER_DAY') * 24 * 60 * 60);
                    $date2 = date('Y-m-d', strtotime($date) - Config::get('AMERICAN_SOUTH_EAST_ORDER_DAY') * 24 * 60 * 60);
                    $data['se_info']['tip'][] = "出运日期" . $date1 . "，下单日期" . $date2;
                    unset($date1);
                    unset($date2);
                } else {
                    $data['se_info']['tip'][] = "";
                }
            }
            return $data;
        } else {
            return false;
        }
    }
}
