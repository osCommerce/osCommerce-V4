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

namespace frontend\design\boxes\invoice;

use Yii;
use yii\base\Widget;
use \common\helpers\Php8;
use frontend\design\IncludeTpl;

class ShippingAddress extends Widget
{

    public $file;
    public $params;
    public $settings;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        $address = \common\helpers\Address::address_format(Php8::getValue($this->params, 'order->delivery.format_id'), Php8::getValue($this->params, 'order->delivery'), 1, '', '<br>');
        $arr = explode('_', Php8::getValue($this->params, 'order->info.shipping_class'));
        $class = $arr[0] ?? null;
        $method = $arr[1] ?? null;
        try {
            $shipping = isset($this->params['order']->manager) ? $this->params['order']->manager->getShippingCollection()->get($class): null;
            if (is_object($shipping)) {
                $collect = $shipping->toCollect($method);
                if ($collect && method_exists($shipping, 'getAdditionalOrderParams')) {
                    $address = $shipping->getAdditionalOrderParams([], Php8::getValue($this->params, 'order.order_id'), Php8::getValue($this->params, 'order.table_prefix'));
                }
            }
        } catch (\Error $e) {
            restore_error_handler();
        }
        return (!empty($address) ? $address : TEXT_WITHOUT_SHIPPING_ADDRESS);
    }
}
