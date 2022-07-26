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

namespace common\components\google;

use Yii;
use Google\Google_Client;
use Google\Google_Service_Analytics;
use Google\Google_Service_AnalyticsReporting;
use Google\Google_Service_AnalyticsReporting_DateRange;
use Google\Google_Service_AnalyticsReporting_Metric;
use Google\Google_Service_AnalyticsReporting_ReportRequest;
use Google\Google_Service_AnalyticsReporting_GetReportsRequest;
use Google\Google_Service_AnalyticsReporting_GetReportsResponse;
use Google\Google_Service_AnalyticsReporting_Dimension;
use Google\Google_Service_AnalyticsReporting_DimensionFilter;
use Google\Google_Service_AnalyticsReporting_DimensionFilterClause;
use common\classes\Order;

/*
 * Google Analytics Ecoomerce tracker Server Side
 */

class GoogleEcommerceSS {

  public $platform_id;
  public $order_id;
  public $ga;
  public $platform;
  protected $UACode = false;
  private $google_collect_url = 'https://www.google-analytics.com/collect';
  private $dimensions = [
    'Transaction ID' => 'ga:transactionId',
    'Affiliation' => 'ga:affiliation',
    'Sessions to Transaction' => 'ga:sessionsToTransaction',
    'Days to Transaction' => 'ga:daysToTransaction',
    'Product SKU' => 'ga:productSku',
    'Product' => 'ga:productName',
    'Product Category' => 'ga:productCategory',
    'Currency Code' => 'ga:currencyCode',
    'Checkout Options' => 'ga:checkoutOptions',
    'Internal Promotion Creative' => 'ga:internalPromotionCreative',
    'Internal Promotion ID' => 'ga:internalPromotionId',
    'Internal Promotion Name' => 'ga:internalPromotionName',
    'Internal Promotion Position' => 'ga:internalPromotionPosition',
    'Order Coupon Code' => 'ga:orderCouponCode',
    'Product Brand' => 'ga:productBrand',
    'Product Category (Enhanced Ecommerce)' => 'ga:productCategoryHierarchy',
    'Product Category Level XX' => 'ga:productCategoryLevelXX',
    'Product Coupon Code' => 'ga:productCouponCode',
    'Product List Name' => 'ga:productListName',
    'Product List Position' => 'ga:productListPosition',
    'Product Variant' => 'ga:productVariant',
    'Shopping Stage' => 'ga:shoppingStage',
  ];

  private $metrics = [
    'Transactions' => 'ga:transactions',
'Ecommerce Conversion Rate' => 'ga:transactionsPerSession',
'Revenue' => 'ga:transactionRevenue',
'Avg. Order Value' => 'ga:revenuePerTransaction',
'Per Session Value' => 'ga:transactionRevenuePerSession',
'Shipping' => 'ga:transactionShipping',
'Tax' => 'ga:transactionTax',
'Total Value' => 'ga:totalValue',
'Quantity' => 'ga:itemQuantity',
'Unique Purchases' => 'ga:uniquePurchases',
'Avg. Price' => 'ga:revenuePerItem',
'Product Revenue' => 'ga:itemRevenue',
'Avg. QTY' => 'ga:itemsPerPurchase',
'Local Revenue' => 'ga:localTransactionRevenue',
'Local Shipping' => 'ga:localTransactionShipping',
'Local Tax' => 'ga:localTransactionTax',
'Local Product Revenue' => 'ga:localItemRevenue',
'Buy-to-Detail Rate' => 'ga:buyToDetailRate',
'Cart-to-Detail Rate' => 'ga:cartToDetailRate',
'Internal Promotion CTR' => 'ga:internalPromotionCTR',
'Internal Promotion Clicks' => 'ga:internalPromotionClicks',
'Internal Promotion Views' => 'ga:internalPromotionViews',
'Local Product Refund Amount' => 'ga:localProductRefundAmount',
'Local Refund Amount' => 'ga:localRefundAmount',
'Product Adds To Cart' => 'ga:productAddsToCart',
'Product Checkouts' => 'ga:productCheckouts',
'Product Detail Views' => 'ga:productDetailViews',
'Product List CTR' => 'ga:productListCTR',
'Product List Clicks' => 'ga:productListClicks',
'Product List Views' => 'ga:productListViews',
'Product Refund Amount' => 'ga:productRefundAmount',
'Product Refunds' => 'ga:productRefunds',
'Product Removes From Cart' => 'ga:productRemovesFromCart',
'Product Revenue per Purchase' => 'ga:productRevenuePerPurchase',
'Quantity Added To Cart' => 'ga:quantityAddedToCart',
'Quantity Checked Out' => 'ga:quantityCheckedOut',
'Quantity Refunded' => 'ga:quantityRefunded',
'Quantity Removed From Cart' => 'ga:quantityRemovedFromCart',
'Refund Amount' => 'ga:refundAmount',
'Revenue per User' => 'ga:revenuePerUser',
'Refunds' => 'ga:totalRefunds',
'Transactions per User' => 'ga:transactionsPerUser'
  ];


  public function __construct($order='', $platform_id = null, $ua_code = '') {

    $this->ga = null;
    $this->UACode = $ua_code;
    
    if (is_object($order) && method_exists($order, 'getOrderId')) {
      $this->order_id = $order->getOrderId();
      $this->platform_id = $order->info['platform_id'];
    } else {
      $this->order_id = $order;
      $this->platform_id = $platform_id;
    }
    
    if (!$this->platform_id)
      $this->platform_id = \common\classes\platform::defaultId();

    if (!$this->platform_id) {
      throw new \Exception('Invalid Platform Id');
    }

    $this->platform = (new \common\classes\platform())->config($this->platform_id);
    
  }

  public function setPlatformId($platform_id) {
    $this->platform_id = $platform_id;
    $this->platform = (new \common\classes\platform())->config($this->platform_id);
    $this->ga = null;
    $this->UACode = false;
  }

  public function initGA() {
    $analytics = (new \common\components\GoogleTools)->getAnalyticsProvider();
    try {
      $confFile = $analytics->getFileKey($this->platform_id);
      if (!empty($confFile)) {
        $this->ga = new GoogleAnalytics($confFile, $analytics->getViewId($this->platform_id));
        if ($this->ga) {
          $this->ga->prepareReporting();
          $this->UACode = $this->ga->getUACode();
        }
      }
    } catch (\Exception $ex) {
      $noty = new \backend\models\AdminNotifier();
      $noty->addNotification(null, $ex->getMessage(), 'warning');
      \Yii::info($ex->getMessage(), 'Google analytics Exception');
    }
  }

  public function getFilters() {
    return [
      'date_range' => ['5daysAgo', 'today'],
      'dimensions' => ['ga:transactionId'],
      'dimensionsFilter' => [
        [
          'dimension' => 'ga:transactionId',
          'expression' => (string) $this->order_id,
          'operator' => 'EXACT'
        ],
      ],
      'metrics' => ['ga:transactions'],
    ];
  }

/**
 *
 * @param array $range of dates
 * @return array|false - false if no response received
 */
  public function getTransactionsReport($range = []) {
    if (is_null($this->ga)) {
      $this->initGA();
    }
    $ret = false;
    
    if ($this->ga) {
      $start = date("Y-m-d", strtotime("-1 month"));
      $end = date("Y-m-d");
      if (is_array($range) ) {
        if (!empty($range[0])) {
          $start = $range[0];
        }
        if (!empty($range[1])) {
          $end = $range[1];
        }
      }
      
      try {
        $response = $this->ga->getReport(
              [
                'date_range' => [$start, $end],
                'dimensions' => ['ga:transactionId'],
                'metrics' => ['ga:transactionRevenue'],
              ]
            );

        if (is_object($response) && property_exists($response, 'reports')) {

          $ret = [];
          $report = $response->reports[0];
          
          //$this->printResults($response->reports);


          if ($report instanceof \Google\Google_Service_AnalyticsReporting_Report) {
            $header = $report->getColumnHeader();
            $dimensionHeaders = $header->getDimensions();
            $metricHeaders = $header->getMetricHeader()->getMetricHeaderEntries();
            $rows = $report->getData()->getRows();
            if ($rows) {
              foreach ($rows as $row) {
                $dimensions = $row->getDimensions();
                $metrics = $row->getMetrics();
                $oid = 0;
                $tmp = [];
                for ($i = 0; $i < count($dimensionHeaders) && $i < count($dimensions); $i++) {
                  $tmp[$dimensionHeaders[$i]] = $dimensions[$i];
                  if ($dimensionHeaders[$i] == 'ga:transactionId') {
                    $oid = $dimensions[$i];
                  }
                }

                for ($j = 0; $j < count($metrics); $j++) {
                  $values = $metrics[$j]->getValues();
                  for ($k = 0; $k < count($values); $k++) {
                    $entry = $metricHeaders[$k];
                    $tmp[$entry->getName()] = $values[$k];
                  }
                }

                $ret[$oid] = $tmp;
              }
            }
          }
        }
      } catch (\Exception $ex) {
        \Yii::info($ex->getMessage(), 'Google analytics Exception');
        //var_dump($ex->getMessage());
      }
    }
    return $ret;
  }
  
  function printResults($reports) {
    for ( $reportIndex = 0; $reportIndex < count( $reports ); $reportIndex++ ) {
      $report = $reports[ $reportIndex ];
      $header = $report->getColumnHeader();
      $dimensionHeaders = $header->getDimensions();
      $metricHeaders = $header->getMetricHeader()->getMetricHeaderEntries();
      $rows = $report->getData()->getRows();

      for ( $rowIndex = 0; $rowIndex < count($rows); $rowIndex++) {
        $row = $rows[ $rowIndex ];
        $dimensions = $row->getDimensions();
        $metrics = $row->getMetrics();
        echo  "<br>Dimensions ";
        for ($i = 0; $i < count($dimensionHeaders) && $i < count($dimensions); $i++) {
          print($dimensionHeaders[$i] . ": " . $dimensions[$i] . "\n");
        }
        echo  "<br>Metrics ";

        for ($j = 0; $j < count($metrics); $j++) {
          $values = $metrics[$j]->getValues();
          for ($k = 0; $k < count($values); $k++) {
            $entry = $metricHeaders[$k];
            print($entry->getName() . ": " . $values[$k] . "\n");
          }
        }
      }
    }
  }




/**
 * @depricated use ecommerce widget save only for EP module
 * checks order in analytics report in last 5 days :( (full shit for old orders, useless if order was placed few minutes ago....
 * @return boolean
 */
  public function isOrderPlacedToAnalytics() {
    if (is_null($this->ga)) {
      $this->initGA();
    }
    if ($this->ga) {
      try {
        $response = $this->ga->getReport($this->getFilters());
        if (is_object($response) && property_exists($response, 'reports')) {
          $report = $response->reports[0];

          if ($report instanceof \Google\Google_Service_AnalyticsReporting_Report) {
            $rows = $report->getData()->getRows();
            if ($rows) {
              $row = $rows[0];
              $placed = $row->getDimensions();
              return in_array($this->order_id, $placed);
            }
          }
        }
      } catch (\Exception $ex) {
        \Yii::info($ex->getMessage(), 'Google analytics Exception');
        //var_dump($ex->getMessage());
      }
    }
    return false;
  }

/**
 *
 * @param Order $order
 * @param type $revert https://support.google.com/analytics/answer/1037443?hl=en
 * @return boolean
 * @throws \Exception
 */
  public function pushDataToAnalytics(Order $order = null, $revert=false, $dateShift=false) {
    if (strlen($this->UACode)>3) {
      $gCommerce = new \common\components\google\widgets\GoogleCommerce();
      if (!$order) {
        $gCommerce->order = new Order($this->order_id);
      } else {
        $gCommerce->order = $order;
      }
      if (!is_object($gCommerce->order)) {
        throw new \Exception('Order is not loaded');
      }

      if ($revert) {
        $rr =-1;
      } else {
        $rr = 1;
      }


      $gCommerce->prepareData();
      $uaCode = $this->UACode;
      if (isset($gCommerce->used_module) && $uaCode !== false) {
        $languageCode = \common\helpers\Language::get_language_code($gCommerce->order->info['language_id']);
        if ($languageCode) {
          $languageCode = $languageCode['code'];
        } else {
          $languageCode = 'en';
        }
        $data = [
          'v' => 1, //protocol
          'ds' => 'web', //
          'uid' => $gCommerce->order->customer['id'],
          'ua' => '', //user agent, can be ua from payment bot
          'geoid' => $gCommerce->order->billing['country']['iso_code_2'], //get country ISO-2
          'de' => 'UTF-8',
          'ul' => $languageCode . "-" . $languageCode,
          't' => 'pageview',
          'dl' => $this->platform->getCatalogBaseUrl(true),
          'ti' => $this->order_id,
          'pa' => 'purchase',
          'tid' => $uaCode,
          'cu' => $gCommerce->order->info['currency'],
          'dp' => '/checkout/success?order_id=' . $this->order_id
        ];
        
        if ($dateShift) {
          $qt = time()-strtotime($order->info['date_purchased']);
          if ($qt < 60*60*24*120) {// 4 month then int32 unsigned
            $data['qt'] = $qt*1000;
          }
        }

        if (($ga = \common\helpers\System::get_ga_detection($this->order_id)) !== false) {
          $data['cn'] = $ga['utmccn'];
          $data['cid'] = $ga['utmcmd'];
          $data['ck'] = $ga['utmctr'];
          $data['gclid'] = $ga['utmgclid'];
          //$data['ua'] = $ga['user_agent'];//serialized
          $data['sr'] = $ga['resolution'];
          $data['uip'] = $ga['ip_address'];
          if ($data['sr']) {
            $data['je'] = 1;
          }
        }
        if ($gCommerce->used_module == 'tagmanger') {
          $data['ta'] = $gCommerce->gtm['actionField']['affiliation'];
          $data['tr'] = $rr*$gCommerce->gtm['actionField']['revenue'];
          $data['ts'] = $rr*$gCommerce->gtm['actionField']['shipping'];
          $data['tt'] = $rr*$gCommerce->gtm['actionField']['tax'];
          $data += $this->getGtmProducts($gCommerce);
        } else {//analytics
          if (is_array($gCommerce->ga['ecommerce:addTransaction']) && count($gCommerce->ga['ecommerce:addTransaction'])) { //ga
            $data['ta'] = $gCommerce->ga['ecommerce:addTransaction']['affiliation'];
            $data['tr'] = $rr*$gCommerce->ga['ecommerce:addTransaction']['revenue'];
            $data['ts'] = $rr*$gCommerce->ga['ecommerce:addTransaction']['shipping'];
            $data['tt'] = $rr*$gCommerce->ga['ecommerce:addTransaction']['tax'];
            $data += $this->getGaProducts($gCommerce);
          } else { //_gaq
            $data['ta'] = $gCommerce->_gaq['_addTrans'][1]; //affiliate
            $data['tr'] = $rr*$gCommerce->_gaq['_addTrans'][2]; //total
            $data['tt'] = $rr*$gCommerce->_gaq['_addTrans'][3]; //tax
            $data['ts'] = $rr*$gCommerce->_gaq['_addTrans'][4]; //shipping
            $data += $this->getGaqProducts($gCommerce);
          }
        }
        if ($data) {
          return $this->collectGoogleData($data);
        }
      }
    }
    return false;
  }

  public function getGtmProducts($gCommerce, $revert=false) {
    $data = [];
    if ($revert) {
      $rr =-1;
    } else {
      $rr = 1;
    }
    if (is_array($gCommerce->gtm['products'])) {
      $i = 1;
      foreach ($gCommerce->gtm['products'] as $product) {
        $data["pr{$i}id"] = $product['id'];
        $data["pr{$i}nm"] = $product['name'];
        $data["pr{$i}ca"] = $product['category'];
        $data["pr{$i}br"] = $product['brand'];
        $data["pr{$i}va"] = $product['variant'];
        $data["pr{$i}pr"] = $product['price'];
        $data["pr{$i}qt"] = $rr*$product['quantity'];
        $data["pr{$i}ps"] = $i;
        $i++;
      }
    }
    return $data;
  }

  public function getGaProducts($gCommerce, $revert=false) {
    $data = [];
    if ($revert) {
      $rr =-1;
    } else {
      $rr = 1;
    }
    if (is_array($gCommerce->ga['ecommerce:addItem'])) {
      $i = 1;
      foreach ($gCommerce->ga['ecommerce:addItem'] as $product) {
        $data["pr{$i}id"] = $product['sku'];
        $data["pr{$i}nm"] = $product['name'];
        $data["pr{$i}ca"] = $product['category'];
        $data["pr{$i}pr"] = $product['price'];
        $data["pr{$i}qt"] = $rr*$product['quantity'];
        $data["pr{$i}ps"] = $i;
        $i++;
      }
    }
    return $data;
  }

  public function getGaqProducts($gCommerce, $revert=false) {
    $data = [];
    if ($revert) {
      $rr =-1;
    } else {
      $rr = 1;
    }
    if (is_array($gCommerce->ga['_addItem'])) {
      $i = 1;
      foreach ($gCommerce->ga['_addItem'] as $product) {
        $data["pr{$i}id"] = $product[1];
        $data["pr{$i}nm"] = $product[2];
        $data["pr{$i}ca"] = $product[3];
        $data["pr{$i}pr"] = $product[4];
        $data["pr{$i}qt"] = $rr*$product[5];
        $data["pr{$i}ps"] = $i;
        $i++;
      }
    }
    return $data;
  }

  public function collectGoogleData($data = []) {
    $httpClient = new \yii\httpclient\Client;
    $request = $httpClient->post($this->google_collect_url, $data);
    $response = $request->send();
    if ($response->isOk) {
      return true;
    } else {
      return false;
    }
  }

}
