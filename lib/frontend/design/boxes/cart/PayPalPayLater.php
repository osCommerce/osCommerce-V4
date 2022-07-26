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

namespace frontend\design\boxes\cart;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use common\helpers\Tax;

class PayPalPayLater extends Widget {

    public $file;
    public $params;
    public $settings;
    public $frameUrl;


    public function run() {
        if (!defined('MODULE_PAYMENT_PAYPAL_PARTNER_PAY_LATER') || MODULE_PAYMENT_PAYPAL_PARTNER_PAY_LATER != 'True') {
            return '';
        }
        global $cart;

        $manager = $this->params['manager'];

        if ( is_object($manager) && $manager->hasCart() ){
            $is_empty_cart = $manager->getCart()->count_contents() == 0;
        } else {
            $is_empty_cart = $cart->count_contents() == 0;
        }
        $amt = 0;

        if (!$is_empty_cart) {
            $result = $manager->getTotalOutput(true, 'TEXT_SHOPPING_CART');
            if (!empty($result) && is_array($result)) {
                foreach ( $result as $total) {
                    if ($total['code'] == 'ot_total') {
                        $amt = $total['value_inc_tax'];
                        break;
                    }
                }
            }
        }

        if ( $amt > 0 ) {
            return IncludeTpl::widget([
                'file' => 'boxes/cart/paypal_partner.tpl',
                'params' => [
                    'price' => round($amt, 2),
                ]
            ]);

        }

        return '';

    }

}
