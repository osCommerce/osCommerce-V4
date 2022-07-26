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

class ShippingChoice extends Widget
{


    public $manager;
    public $params;
    public $settings;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        if (!$this->manager) {
            $this->manager = $this->params['manager'];
        }

        $_shippingChoice = $this->manager->getPickupOrDeliveryChoice();

        $pickupShippingQuote = $this->manager->getPickupShippingQuotes();

        if (!$_shippingChoice || !$pickupShippingQuote){
            return '';
        }

        return IncludeTpl::widget([
            'file' => 'boxes/checkout/shipping-choice.tpl',
            'params' => [
                'params' => $this->params,
                'model' => $_shippingChoice,
                'url' => Yii::$app->getUrlManager()->createUrl('checkout/worker'),
                'manager' => $this->manager
            ],
        ]);

    }
}