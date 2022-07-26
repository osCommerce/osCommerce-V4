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

namespace frontend\design\boxes\checkout;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use common\helpers\Tax;

class PayPalPayLater extends Widget {

    public $file;
    public $params;
    public $settings;
    public $manager;

    public function run() {
        if (!defined('MODULE_PAYMENT_PAYPAL_PARTNER_PAY_LATER') || MODULE_PAYMENT_PAYPAL_PARTNER_PAY_LATER != 'True') {
            return '';
        }
        if (is_object($this->manager)){
            $this->params['manager'] = $this->manager;
        }

        $amt = 0;
        $result = $this->params['manager']->getTotalOutput(true, 'TEXT_CHECKOUT');
        if (!empty($result) && is_array($result)) {
            foreach ( $result as $total) {
                if ($total['code'] == 'ot_total') {
                    $amt = $total['value_inc_tax'];
                    break;
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
