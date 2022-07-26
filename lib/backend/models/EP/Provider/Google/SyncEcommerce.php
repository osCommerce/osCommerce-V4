<?php

/**
 * This file is part of osCommerce ecommerce platform.
 * osCommerce the ecommerce
 *
 * @link https://www.oscommerce.com
 * @copyright Copyright (c) 2000-2022 osCommerce LTD
 *
 * Released under the GNU General Public License
 * For the full copyright and license information, please view the LICENSE.TXT file that was distributed with this source code.
 */

namespace backend\models\EP\Provider\Google;

use Yii;
use backend\models\EP\Exception;
use backend\models\EP\Messages;
use backend\models\EP\Provider\DatasourceInterface;
use backend\models\EP\Tools;
use common\classes\language;
use backend\models\EP\Directory;
use yii\db\Query;
use common\models\Orders;
use common\models\EcommerceTracking;

class SyncEcommerce implements DatasourceInterface {

    protected $total_count = 0;
    protected $row_count = 0;
    protected $orders_list;
    protected $config = [];//2do period, latencity
    protected $platform_id = false;
    protected $gess = false;
    protected $installed_modules = false;
    protected $order_statuses = false;
    public $job_id;    
    
    function __construct($config) {
      $this->config = $config;
      if (empty($this->config['delays']['latencity']) || (int)$this->config['delays']['latencity']<1) {
        $this->config['delays']['latencity'] = 2;
      }
      if (empty($this->config['delays']['outdated']) || (int)$this->config['delays']['outdated']<1) {
        $this->config['delays']['outdated'] = 3;
      }

      $configured_statuses_string = is_array($this->config['order']['export_statuses'])?implode(",", $this->config['order']['export_statuses']):$this->config['order']['export_statuses'];
      if ( strpos($configured_statuses_string,'*')!==false ) {
        $this->order_statuses = true;
      } else {
        $this->order_statuses = \common\helpers\Order::extractStatuses($configured_statuses_string);
      }

    }

    public function allowRunInPopup() {
        return true;
    }

    public function getProgress() {
        if ($this->total_count > 0) {
            $percentDone = min(100, ($this->row_count / $this->total_count) * 100);
        } else {
            $percentDone = 100;
        }
        return number_format($percentDone, 1, '.', '');
    }

    public function prepareProcess(Messages $message) {
      /// import lost for last day(s)
      $dateStart = strtotime((int)$this->config['delays']['outdated'] . ' day ago');
      $gess = new \common\components\google\GoogleEcommerceSS();
      foreach (\common\classes\platform::getList(false) as $platform) {
        $gess->setPlatformId($platform['id']);
        $orders = $gess->getTransactionsReport([date('Y-m-d', $dateStart)]);
        if (is_array($orders)) {
          $oids = Orders::find()->select('orders_id')->where([
            'orders_id' => array_map('intval', array_keys($orders)),
            'platform_id' => $platform['id']
            ])->asArray()->column();
          foreach ($orders as $orders_id => $order) {
            if (in_array($orders_id, $oids )) {
              $et = EcommerceTracking::findOne(['orders_id' => $orders_id]);
              if (!$et) {
                $message->info('Imported Transaction: ' . $orders_id);
                $et = new EcommerceTracking();
                $et->setAttributes([
                  'orders_id' => (int) $orders_id,
                  'via' => 'report',
                  'date_added' => new \yii\db\Expression('now()'),
                  'verified' => new \yii\db\Expression('now()'),
                  'verified_amount' => (float) $order['ga:transactionRevenue']
                    ], false);
                $et->save(false);
              }
            }
          }
        }
      }


      $orders_query_raw = (new \yii\db\Query())->from(['o' => TABLE_ORDERS])
            ->select("o.orders_id, o.date_purchased, o.payment_method,o.department_id, o.admin_id, o.platform_id, o.api_client_order_id ")
            ->leftJoin('ecommerce_tracking et', 'et.orders_id=o.orders_id')
            ->addSelect('services, message_type, via, date_added, extra_info, id, verified, verified_amount')
            ;
      $orders_query_raw->andWhere(['is', 'et.orders_id', null]);
      $orders_query_raw->andWhere(['<=', 'o.date_purchased', date(\common\helpers\Date::DATABASE_DATETIME_FORMAT, strtotime((int)$this->config['delays']['latencity'] . ' hour ago'))]); //not to fresh
      $orders_query_raw->andWhere(['>', 'o.date_purchased', date(\common\helpers\Date::DATABASE_DATETIME_FORMAT, strtotime((int)$this->config['delays']['outdated'] . ' days ago'))]); //not to old
      $orders_query_raw->orderBy('platform_id, orders_id');
      if (is_array($this->order_statuses)) {
        $orders_query_raw->andWhere(['o.orders_status' => $this->order_statuses]);
      }
//$message->info($orders_query_raw->createCommand()->rawSql);
      $this->orders_list = $orders_query_raw->all();
//$message->info(print_r($this->orders_list, 1));
      $this->total_count = count($this->orders_list);
//$message->info('count ' . $this->total_count);
      if ($this->total_count ) {
        $this->provider = (new \common\components\GoogleTools())->getModulesProvider();
        $this->gess = new \common\components\google\GoogleEcommerceSS();
      }
        
    }

    public function processRow(Messages $message) {
        set_time_limit(0);

        $item = current($this->orders_list);

        if (!$item || !$this->gess || !$this->provider)
            return false;


        try {

          if ($item['platform_id'] != $this->platform_id) {
            $this->platform_id = $item['platform_id'];
            $this->gess->setPlatformId($this->platform_id);
            $this->installed_modules = $this->provider->getInstalledModules($this->platform_id);
          }

          if (isset($this->installed_modules['ecommerce'])) {
            $order = new \common\classes\Order($item['orders_id']);
            $this->installed_modules['ecommerce']->forceServerSide($order);
            $message->info('Processed Transaction: ' . $item['orders_id']);
          }
            
        } catch (\Exception $ex) {

          throw new \Exception('Processing order error ' . $ex->getMessage() . " Trace:".  $ex->getTraceAsString());
        }

        $this->row_count++;
        next($this->orders_list);
        return true;
    }

    public function postProcess(Messages $message) {
        return;
    }



}
