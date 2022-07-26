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

namespace common\components\google\widgets;

use common\components\GoogleTools;

class GoogleCommerce extends \yii\base\Widget
{
    public $order;
    public $ga = ['ec:addProduct' => [], 'ec:setAction' => [], 'userId' => [], /*, 'ecommerce:send' => []*/];
    public $gtag = ['transaction_id' => '', 'items' => []];
    public $installed_modules = [];

    public function init()
    {
        parent::init();
    }

    public function prepareData() {
        $provider = (new GoogleTools)->getModulesProvider();
        $this->installed_modules = $provider->getInstalledModules($this->order->info['platform_id']);

        $_tax = $_total = $_shipping = $_coupon = 0;
        foreach ($this->order->totals as $totals) {
            if ($totals['class'] == 'ot_total') {
                $_total = number_format($totals['value_inc_tax'], 2, ".", "");
            } else if ($totals['class'] == 'ot_tax') {
                $_tax = number_format($totals['value'], 2, ".", "");
            } else if ($totals['class'] == 'ot_shipping') {
                $_shipping = number_format($totals['value_exc_vat'], 2, ".", "");
            } else if ($totals['class'] == 'ot_coupon') {
                $ex = explode(":", $totals['text']);
                if (isset($ex[1])) {
                    $_coupon = trim($ex[1]);
                }
            }
        }
        if (array_key_exists('analytics', $this->installed_modules)) {
            $this->ga['ec:setAction'] = [
                'id' => $this->order->info['order_id'],
                'affiliation' => \common\classes\platform::name($this->order->info['platform_id']),
                'revenue' => $_total,
                'shipping'  => $_shipping,
                'tax' => $_tax,
                'coupon' => ($_coupon ? $_coupon : ''),
            ];
            $this->ga['userId'] = [$this->order->customer['id']];

            $this->gtag = [
                'transaction_id' => $this->order->info['order_id'],
                'affiliation' => \common\classes\platform::name($this->order->info['platform_id']),
                'value' => $_total,
                'shipping'  => $_shipping,
                'tax' => $_tax,
                'coupon' => ($_coupon ? $_coupon : ''),
                'items' => [],
            ];
        }

        if (is_array($this->order->products)  && sizeof($this->order->products)) {
            foreach($this->order->products as $item) {
                $p2cModel = \common\models\Products2Categories::findOne(['products_id' => (int)$item['id']]);
                $category_name = $p2cModel ? str_replace('"', '\"', \common\helpers\Categories::get_categories_name($p2cModel->categories_id)) : '';
                if (array_key_exists('analytics', $this->installed_modules)) {
                    $this->ga['ec:addProduct'][] = [
                        'id' => \common\helpers\Inventory::get_prid($item['id']),
                        'name' => str_replace('"', '\"', $item['name']),
                        'sku' => $item['model'],
                        'category' => $category_name,
                        'price' => number_format($item['final_price'], 2, ".", ""),
                        'quantity' => $item['qty']
                    ];

                    $manufacturers_id = \common\helpers\Product::get_products_info((int)$item['id'], 'manufacturers_id');
                    $brand = \common\helpers\Manufacturers::get_manufacturer_info('manufacturers_name', $manufacturers_id);
                    $attributes = '';
                    if (is_array($item['attributes']) && count($item['attributes'])) {
                        $map = [
                            'options' => \yii\helpers\ArrayHelper::getColumn($item['attributes'], 'option'),
                            'values' => \yii\helpers\ArrayHelper::getColumn($item['attributes'], 'value'),
                        ];
                        foreach($map['options'] as $key => $value) {
                            $attributes .= $value . ": " . $map['values'][$key]. ", ";
                        }
                        if (strlen($attributes) > 0) {
                            $attributes = substr($attributes, 0, -2);
                        }
                    }
                    $this->gtag['items'][] = [
                        'item_id' => \common\helpers\Inventory::get_prid($item['id']),
                        'item_name' => str_replace('"', '\"', $item['name']),
                        'price' => number_format($item['final_price'], 2, ".", ""),
                        'item_brand' => ($brand ? $brand : ''),
                        'item_category' => $category_name,
                        'item_variant' => $attributes,
                        'quantity' => $item['qty'],
                    ];
                }
            }
        }
    }

    public function run() {
        $this->prepareData();

        return $this->renderJs();
    }
  
    public function renderJs() {
        ob_start();
        //
        if (array_key_exists('analytics', $this->installed_modules)) {
?>
<script>
    tl(function() {
        var gtag_type = false;
        var ga_type = false;
    
        if (typeof gtag != 'undefined') {
          gtag_type = true;
        }
        if (typeof ga != 'undefined' && typeof ga.P == 'object') {
          //check id
          var _tracker = ga.getByName('t0');
          var _account = _tracker.b.get('trackingId');
          if ( _tracker.b.data.values.hasOwnProperty(':trackingId') && _account.length > 0 && _account.indexOf('UA') > -1) {
            ga_type = true;
          } 
        }
        if (!gtag_type && !ga_type) { //notify admin to set up analytics
          $.post('checkout/notify-admin', {
            'type': 'need_analytics',
          }, function(data, status) {
            
          });
        }

        if (gtag_type) {
          gtag('event', 'purchase', <?php echo json_encode($this->gtag); ?>);
        }
        if (ga_type) {
          ga('require', 'ec');
<?php 
            foreach ($this->ga as $key => $item) {
                if ($key == 'userId')
                    continue;
                if (!count(array_filter($item, 'is_array'))) {
                    echo 'ga(\'' . $key . '\', \'purchase\' , ' . json_encode($item) . ');' . "\r\n";
                } else {
                    foreach ($item as $item1) {
                        echo 'ga(\'' . $key . '\', ' . json_encode($item1) . ');' . "\r\n";
                    }
                }
            }
            if (!empty($this->ga['userId'])) {
                echo "ga('create', _account, { 'userId': '" . $this->ga['userId'][0] . "' });";
            }
?>
          ga('send', 'event', 'UX', 'purchase', 'checkout success');
          localStorage.removeItem('ga_cookie');
        }
    });
</script>
<?php
        }
        $buf = ob_get_contents();
        ob_clean();
        return $buf;
    }

}
