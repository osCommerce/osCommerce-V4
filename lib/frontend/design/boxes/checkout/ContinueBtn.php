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

class ContinueBtn extends Widget
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
        $initialize_checkout_methods = '';
        //if (defined('EXPRESS_PAYMENTS_AT_CHECKOUT') && EXPRESS_PAYMENTS_AT_CHECKOUT == 'True') {
            global $cart;
            $payment_modules = \common\services\OrderManager::loadManager($cart)->getPaymentCollection();
            $initialize_checkout_methods = $payment_modules->checkout_initialization_method();
        //}
        $this->params['link'] = tep_href_link('index');
        $this->params['inline'] = $initialize_checkout_methods;
        return IncludeTpl::widget(['file' => 'boxes/checkout/continue-btn.tpl', 'params' => $this->params]);
    }
}