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

use common\components\google\modules\AbstractGoogle;
use Yii;
use common\classes\platform;

final class adwords extends AbstractGoogle {

    public $config;
    public $code = 'adwords';

    public function getParams() {

        $this->config = [
            $this->code => [
                'name' => 'Google Adwords (Remarketing)',
                'fields' => [
                    [
                        'name' => 'code',
                        'value' => '',
                        'type' => 'text'
                    ],
                    [
                        'name' => 'language',
                        'value' => 'en',
                        'type' => 'text'
                    ],
                    [
                        'name' => 'color',
                        'value' => 'ffffff',
                        'type' => 'text'
                    ],
                    [
                        'name' => 'label',
                        'value' => '',
                        'type' => 'text'
                    ],
                ],
                'pages' => [
                    'checkout',
                ],
                'type' => [
                    'selected' => 'old',
                    'new' => 'Use Gtag (new version)',
                    'old' => 'Old Version',
                ],
                'priority' => 3,
                'example' => true,
            ],
        ];
        return $this->config;
    }

    private function oldVersion($elements) {
        $code = $elements['fields'][0]['value'];
        $language = $elements['fields'][1]['value'];
        $color = $elements['fields'][2]['value'];
        $label = $elements['fields'][3]['value'];
        $order = false;
        $manager = \common\services\OrderManager::loadManager();
        if ($manager->isInstance()) {
            $order = $manager->getOrderInstance();
        }
        $currency = 'GBP';
        $value = '1.00';
        if (is_object($order)) {
            $currency = $order->info['currency'];
            $value = $order->info['total'];
        }
        if (tep_not_null($code)) {
            $_return = '
<script type="text/javascript">
/* <![CDATA[ */
var google_conversion_id = ' . $code . ';
var google_custom_params = window.google_tag_params;
var google_conversion_format = "3";
var google_conversion_value = ' . $value . ';
var google_remarketing_only = false;
';
            if (!tep_not_null($language)) {
                $_return .= 'var google_conversion_language = "' . $language . '";' . "\n";
            }
            if (tep_not_null($color)) {
                $_return .= 'var google_conversion_color = "' . $color . '";' . "\n";
            }
            if (tep_not_null($label)) {
                $_return .= 'var google_conversion_label = "' . $label . '";' . "\n";
            }
            if (tep_not_null($currency)) {
                $_return .= 'var google_conversion_currency = "' . $currency . '";' . "\n";
            }
            $_return .= '
/* ]]> */
</script>
<script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js">
</script>
<noscript>
<div style="display:inline;">
<img height="1" width="1" style="border-style:none;" alt="" src="//www.googleadservices.com/pagead/conversion/' . $code . '/?value=1.00&amp;currency_code=' . $currency . '&amp;label=' . $label . '&amp;guid=ON&amp;script=0"/>
</div>
</noscript>
';
            return $_return;
        }
        return;
    }

    private function getEvents($elements, $send_to) {
        $action = Yii::$app->controller->id . '_' . Yii::$app->controller->action->id;
        $currencies = Yii::$container->get('currencies');
        $currency = Yii::$app->settings->get('currency');
        $page_totalvalue = 0;
        switch ($action) {
            case 'catalog_index':
            case 'catalog_products-new':
            case 'catalog_specials':
            case 'catalog_featured-products':
                global $current_category_id;
                $page_type = 'category';
                $products = Yii::$container->get('products')->getAllProducts();
                if (is_array($products) && count($products)) {
                    $ids = array_keys(\yii\helpers\ArrayHelper::getColumn($products, 'products_id'));
                    $ids = array_map('intval', $ids);
                    $ecomm_prodid = "[" . implode(",", $ids) . "]";
                    if ($current_category_id) {
                        $category_name = addslashes(\common\helpers\Categories::get_categories_name($current_category_id));
                    } else {
                        $category_name = '';
                    }

                    return <<<EOD
gtag('event', 'page_view', {
    ecomm_pagetype: '{$page_type}',
    send_to: '{$send_to}',
    ecomm_prodid: {$ecomm_prodid},
    ecomm_category: '{$category_name}',
    });
EOD;
                }
                break;
            case 'catalog_all-products':
                $page_type = 'searchresults';
                $products = Yii::$container->get('products')->getAllProducts();
                if (is_array($products) && count($products)) {
                    $ids = array_keys(\yii\helpers\ArrayHelper::getColumn($products, 'products_id'));
                    $ids = array_map('intval', $ids);
                    $ecomm_prodid = "[" . implode(",", $ids) . "]";
                    return <<<EOD
gtag('event', 'page_view', {
    ecomm_pagetype: '{$page_type}',
    send_to: '{$send_to}',
    ecomm_prodid: {$ecomm_prodid},
    });
EOD;
                }
                break;
            case 'catalog_product':
                $page_type = 'product';
                $product = Yii::$container->get('products')->getProduct($_GET['products_id']);
                if ($product) {
                    $page_totalvalue = $currencies->display_price_clear($product['products_price'], \common\helpers\Tax::get_tax_rate($product['products_tax_class_id']));
                    return <<<EOD
gtag('event', 'page_view', {
    ecomm_pagetype: '{$page_type}',
    send_to: '{$send_to}',
    ecomm_prodid: {$product['products_id']},
    ecomm_totalvalue: {$page_totalvalue}
    });
EOD;
                }
                break;
            case 'shopping-cart_index':
                global $cart;
                $products = $cart->get_products();
                $page_type = 'cart';
                if (is_array($products) && count($products)) {
                    $ids = \yii\helpers\ArrayHelper::getColumn($products, 'id');
                    $ids = array_map('intval', $ids);
                    $ecomm_prodid = "[" . implode(",", $ids) . "]";
                    $rate = $currencies->currencies[$currency]['value'];
                    $decimal_places = $currencies->currencies[$currency]['decimal_places'];
                    $page_totalvalue = $cart->show_total();
                    return <<<EOD
gtag('event', 'page_view', {
    ecomm_pagetype: '{$page_type}',
    send_to: '{$send_to}',
    ecomm_prodid: {$ecomm_prodid},
    ecomm_totalvalue: {$page_totalvalue}
    });
EOD;
                }
                break;
            //case 'checkout_index':
            //case 'checkout_confirmation':
            case 'checkout_success':
                $manager = \common\services\OrderManager::loadManager();
                if ($manager->isInstance()) {
                    $order = $manager->getOrderInstance();
                    $page_type = 'purchase';
                    if (is_object($order)) {
                        $ids = \yii\helpers\ArrayHelper::getColumn($order->products, 'id');
                        $ids = array_map('intval', $ids);
                        $ecomm_prodid = "[" . implode(",", $ids) . "]";
                        $page_totalvalue = $order->info['total_inc_tax'];
                        return <<<EOD
gtag('event', 'page_view', {
    ecomm_pagetype: '{$page_type}',
    send_to: '{$send_to}',
    ecomm_prodid: {$ecomm_prodid},
    ecomm_totalvalue: {$page_totalvalue}
    });
EOD;
                    }
                }

                break;
        }
        return;
    }

    private function newVersion($elements) {
        $code = $elements['fields'][0]['value'];
        $events = $this->getEvents($elements, $code);
        return <<<EOD

<script async src="https://www.googletagmanager.com/gtag/js?id={$code}"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '{$code}');
  {$events}
</script>
EOD;
    }

    public function renderWidget() {
        $elements = $this->config[$this->code];

        if ($elements['type']['selected'] == 'new') {
            return $this->newVersion($elements);
        } else {
            return $this->oldVersion($elements);
        }
    }

    public function renderExample() {
        if ($this->params['platform_id']){
            $elements = $this->config[$this->code];
            $code = $elements['fields'][0]['value'];
            if ($elements['type']['selected'] == 'new') {
                return "<pre>" .
                        <<<EOD
gtag('event', 'page_view', {
    ecomm_pagetype: 'product',
    ecomm_prodid: 34592212,
    ecomm_totalvalue: 29.99,
    ecomm_category: 'Home & Garden'
  });
EOD
                        . "</pre>";
            } else {
                return "<style> .disabled { background-color: #ccc!important; }</style><pre>" .
                        <<<EOD
var google_conversion_id = $code;
var google_custom_params = window.google_tag_params;
var google_remarketing_only = false;
var google_conversion_format = "3";
var google_conversion_value = 1.00;
var google_conversion_language = "en";
var google_conversion_color = "ffffff";
var google_conversion_currency = "GBP";
var google_conversion_label = "myy_CJeu8QcQofSa5AM";
EOD
                        . "</pre>
            <script>
        $(document).ready(function(){
        function toggle(){
            $('[name=\'fields[1][language]\']').toggleClass('disabled');
            $('[name=\'fields[2][color]\']').toggleClass('disabled');
            $('[name=\'fields[3][label]\']').toggleClass('disabled');
        }
          if ($('input[name=type]').val() == 'new'){
            toggle();
          }
          $('input[name=type]:radio').change(function(){
            toggle();
          });
        })
      </script>";
            }
        }
        return;
    }

}
