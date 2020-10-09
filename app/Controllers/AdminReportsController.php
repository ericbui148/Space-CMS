<?php
namespace App\Controllers;

use App\Controllers\Components\UtilComponent;
use App\Models\OrderModel;
use App\Models\OrderStockModel;
use App\Models\ProductModel;

class AdminReportsController extends AdminController
{
    public function Index()
    {
        $this->checkLogin();
        if ($this->isAdmin() || $this->isEditor()) {
            $date_from = date('Y-m-01');
            $date_to = date('Y-m-t');
            if (isset($_GET['date_from']) && ! empty($_GET['date_from'])) {
                $date_from = UtilComponent::formatDate($_GET['date_from'], $this->option_arr['o_date_format']);
            }
            if (isset($_GET['date_to']) && ! empty($_GET['date_to'])) {
                $date_to = UtilComponent::formatDate($_GET['date_to'], $this->option_arr['o_date_format']);
            }
            $dates = NULL;
            if (isset($date_from) && isset($date_to)) {
                $dates = sprintf(" AND ('%1\$s' <= DATE(`created`) AND DATE(`created`) <= '%2\$s')", $date_from, $date_to);
            } else {
                if (isset($date_from)) {
                    $dates = sprintf(" AND DATE(`created`) >= '%s'", $date_from);
                }
                if (isset($date_to)) {
                    $dates = sprintf(" AND DATE(`created`) <= '%s'", $date_to);
                }
            }
            $OrderModel = OrderModel::factory();
            $OrderStockModel = OrderStockModel::factory();
            $total_orders = $OrderModel->where("t1.status = 'completed'" . $dates)
                ->findCount()
                ->getData();
            $total_amount = 0;
            $sub_arr = array();
            $sub_arr['price'] = 0;
            $sub_arr['discount'] = 0;
            $sub_arr['insurance'] = 0;
            $sub_arr['shipping'] = 0;
            $sub_arr['tax'] = 0;
            $amount = $OrderModel->reset()
                ->select('SUM(t1.total) AS amount,  SUM(t1.price) AS price,  SUM(t1.discount) AS discount,  SUM(t1.insurance) AS insurance,  SUM(t1.shipping) AS shipping,  SUM(t1.tax) AS tax')
                ->where("t1.status = 'completed'" . $dates)
                ->limit(1)
                ->findAll()
                ->getData();
            if (count($amount) == 1) {
                $total_amount = $amount[0]['amount'];
                $sub_arr['price'] = $amount[0]['price'];
                $sub_arr['discount'] = $amount[0]['discount'];
                $sub_arr['insurance'] = $amount[0]['insurance'];
                $sub_arr['insurance'] = $amount[0]['insurance'];
                $sub_arr['shipping'] = $amount[0]['shipping'];
                $sub_arr['tax'] = $amount[0]['tax'];
            }
            $unique_clients = 0;
            $unique_client_ids = '';
            $client_arr = $OrderModel->reset()
                ->select("COUNT(DISTINCT t1.client_id) AS clients, GROUP_CONCAT(`client_id` SEPARATOR ',') AS client_ids")
                ->where("t1.status = 'completed'" . $dates)
                ->limit(1)
                ->findAll()
                ->getData();
            if (count($client_arr) == 1) {
                $unique_clients = $client_arr[0]['clients'];
                $unique_client_ids = $client_arr[0]['client_ids'];
            }
            $first_time_clients = 0;
            $first_time_client_ids = '';
            $client_arr = $OrderModel->reset()
                ->select("COUNT(t1.client_id) AS clients, GROUP_CONCAT(`client_id` SEPARATOR ',') AS client_ids")
                ->where("t1.status = 'completed'" . $dates)
                ->where("t1.client_id IN (SELECT client_id FROM `" . $OrderModel->getTable() . "` GROUP BY client_id HAVING COUNT(id) = 1) ")
                ->limit(1)
                ->findAll()
                ->getData();
            if (count($client_arr) == 1) {
                $first_time_clients = $client_arr[0]['clients'];
                $first_time_client_ids = $client_arr[0]['client_ids'];
            }
            $avg_amount = 0;
            $min_amount = 0;
            $max_amount = 0;
            $o_arr = $OrderModel->reset()
                ->select('AVG(t1.total) AS avg_amount, MIN(t1.total) AS min_amount, MAX(t1.total) AS max_amount')
                ->where("t1.status = 'completed'" . $dates)
                ->limit(1)
                ->findAll()
                ->getData();
            if (count($o_arr) == 1) {
                $avg_amount = $o_arr[0]['avg_amount'];
                $min_amount = $o_arr[0]['min_amount'];
                $max_amount = $o_arr[0]['max_amount'];
            }
            $avg_product = 0;
            $min_product = 0;
            $max_product = 0;
            $o_arr = $OrderStockModel->reset()
                ->select('COUNT(product_id) AS cnt_products')
                ->where("t1.order_id IN (SELECT `TO`.id FROM `" . $OrderModel->getTable() . "` AS `TO` WHERE `TO`.status = 'completed'$dates)")
                ->limit(1)
                ->findAll()
                ->getData();
            if (count($o_arr) == 1) {
                if ($total_orders > 0) {
                    $avg_product = $o_arr[0]['cnt_products'] / $total_orders;
                }
            }
            $o_arr = $OrderStockModel->reset()
                ->select('order_id, COUNT(product_id) AS cnt_products')
                ->where("t1.order_id IN (SELECT `TO`.id FROM `" . $OrderModel->getTable() . "` AS `TO` WHERE `TO`.status = 'completed'$dates)")
                ->groupBy("t1.order_id")
                ->findAll()
                ->getDataPair('order_id', 'cnt_products');
            if (count($o_arr) > 0) {
                $min_product = min($o_arr);
                $max_product = max($o_arr);
            }
            $popular_arr = null;
            $times = 0;
            $o_arr = $OrderStockModel->reset()
                ->select('product_id, COUNT(product_id) AS cnt_products')
                ->where("t1.order_id IN (SELECT `TO`.id FROM `" . $OrderModel->getTable() . "` AS `TO` WHERE `TO`.status = 'completed'$dates)")
                ->groupBy("t1.product_id")
                ->findAll()
                ->getDataPair('product_id', 'cnt_products');
            if (count($o_arr) > 0) {
                $times = max($o_arr);
                $maxs = array_keys($o_arr, $times);
                $popular_arr = ProductModel::factory()->select('t1.id, t2.content as name')
                    ->join('MultiLang', "t2.model='Product' AND t2.foreign_id=t1.id AND t2.locale='" . $this->getLocaleId() . "' AND t2.field='name'", 'left outer')
                    ->find($maxs[0])
                    ->getData();
            }
            $this->set('total_orders', $total_orders)
                ->set('total_amount', $total_amount)
                ->set('sub_arr', $sub_arr)
                ->set('unique_clients', $unique_clients)
                ->set('first_time_clients', $first_time_clients)
                ->set('unique_client_ids', $unique_client_ids)
                ->set('first_time_client_ids', $first_time_client_ids)
                ->set('avg_amount', $avg_amount)
                ->set('min_amount', $min_amount)
                ->set('max_amount', $max_amount)
                ->set('avg_product', $avg_product)
                ->set('min_product', $min_product)
                ->set('max_product', $max_product)
                ->set('times', $times)
                ->set('popular_arr', $popular_arr)
                ->set('date_from', $date_from)
                ->set('date_to', $date_to);
            $this->appendJs('AdminReports.js');
        } else {
            $this->set('status', 2);
        }
    }
}
?>