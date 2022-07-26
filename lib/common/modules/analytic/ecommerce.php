<?php

/**
 * This file is part of True Loaded.
 * 
 * @link http://www.holbi.co.uk
 * @copyright Copyright (c) 2005 Holbi Group LTD
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace common\modules\analytic;

use common\classes\platform;
use common\classes\Order;
use common\components\google\modules\AbstractGoogle;

final class ecommerce extends AbstractGoogle {

  public $config;
  public $code = 'ecommerce';

  public function getParams() {

    $this->config = [
      $this->code => [
        'name' => 'ECommerce Tracking',
        'fields' => [
          [
            'name' => 'code',
            'value' => 'UA-',
            'type' => 'text'
          ],
        ],
        'pages' => [
          'checkout',
        ],
        'type' => [
          'selected' => 'any',
          //'server' => 'ServerSide Transaction <div class="ord-total" style="text-align:left!important;float: right;"><div class="ord-total-info" style="left:0!important;">To Use ServerSide Api, load json credentials for service account and allow it to push data to Analytics, fill View ID, enable Analytics Api </div></div>',
          'js' => 'JS Transaction',
          'any' => 'API or JS Transaction',
          'api' => 'Force API (server side)',
        ],
        'pages_only' => true,
        'priority' => 2,
        'example' => true,
      //'flow' => [__NAMESPACE__ . '\ecommerce', 'serverSideProcess'],
      ],
    ];
    return $this->config;
  }

  public function renderWidget() {
    $manager = \common\services\OrderManager::loadManager();
    if ($manager->isInstance()) {
      $order = $manager->getOrderInstance();
      if (class_exists('\common\components\google\widgets\GoogleCommerce') && $order instanceof Order) {
        if (!$this->isTrackingAdded($order->getOrderId())) {
          $fields = $this->parseFields($this->config[$this->code]['fields']);
          if ($this->config[$this->code]['type']['selected'] == 'api' && strlen($fields['code'])>3) {
            $r = $this->forceServerSide($order, $fields['code']);
            return '<!-- ecommerce forceSS -->';
          }
          $ret = \common\components\google\widgets\GoogleCommerce::widget(['order' => $order]);
          if (!empty($ret)) {
            $this->saveTracking(['orders_id' => $order->getOrderId(), 'via' =>'js']);
          }
          return $ret;
        }
      }
    }
  }

  public function forceServerSide(Order $order, $ua_code = '', $revert = false, $dateShift=false) {
    if (empty($ua_code)) {
      $fields = $this->parseFields($this->config[$this->code]['fields']);
      $ua_code = $fields['code'];
    }
    $ess = new \common\components\google\GoogleEcommerceSS($order, null, $ua_code);
    $result = $ess->pushDataToAnalytics($order, $revert, $dateShift);
    if ($result) {
      $via = 'API';
      if ($dateShift) {
        $via .= '-shifted';
      }
      if ($revert) {
        $via .= '-reverted';
      }
      $this->saveTracking(['orders_id' => $order->getOrderId(), 'via' => $via]);
    }
    return $result;
  }

  public function renderExample() {
    $return = '';
    if ($this->params['platform_id']) {
      $installed = $this->provider->getInstalledModules($this->params['platform_id']);
      $deps = [];
      foreach (['analytics'] as $dep) {
        if (isset($installed[$dep])) {
          $deps[$dep] = $installed[$dep];
        }
      }
      if ($deps) {
        foreach ($deps as $dep) {
          if (!$dep->params['status']) {
            continue;
          }
          $installed = $this->provider->getInstalledById($dep->params['google_settings_id']);
          if ($installed instanceof analytics) {
            if ($installed->config['analytics']['fields'][1]['value'] && $installed->config['analytics']['fields'][1]['value'] != 'G-') {
                $return .= '<pre>' . <<<EOD
gtag('event', 'purchase', {
  "transaction_id": "228517",
  "affiliation": "Trueloaded",
  "value": 7.05,
  "tax": 0.76,
  "shipping": 2.50,
  "items": [
    {
      "item_id": "11715",
      "item_name": "Test product",
      "price": "3.79"
      "item_brand": "Brand name",
      "item_category": "Category name",
      "item_variant": "Black",
      "quantity": "1",
    }
  ]
});
EOD
                  . '</pre>';
            }
            if ($installed->config['analytics']['fields'][0]['value'] && $installed->config['analytics']['fields'][0]['value'] != 'UA-') {
                $return .= '<pre>' . <<<EOD
ga('require', 'ec');
ga('ec:addProduct', {"id":"11715","name":"Test product","sku":"402","category":"Category name","price":"3.79","quantity":"1"});
ga('ec:setAction', 'purchase' , {"id":"228517","affiliation":"Trueloaded","revenue":"7.05","shipping":"2.50","tax":"0.76","coupon":""});
ga('send', 'event', 'UX', 'purchase', 'checkout success');
EOD
                  . '</pre>';
            }
          } else {
            return 'Google Analytics ' . TEXT_FIELD_REQUIRED;
          }
        }
      } else {
        return 'Google Analytics ' . TEXT_FIELD_REQUIRED;
      }
    }
    return $return;
  }

  //Depricated
  public static function serverSideProcess(array $params = []) {
    return false;
  }

}
