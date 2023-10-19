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
use frontend\design\Info;

class ShippingByChoice extends Widget
{    
    public $manager;
    public $params;

    public function init()
    {
        parent::init();
    }

    public function run()
    {   
        $params = ['manager' => $this->manager ];
        if (Info::themeSetting('checkout_view') == 1 && !$this->manager->is('\common\extensions\Samples\classes\SampleCart')){
            if (true){
                $params['page_name'] = 'index';
            } else {
                $params['page_name'] = 'index_2';
            }
            
            $response['page'] = [
                'blocks' => [
                    'shipping-step' => \frontend\design\Block::widget(['name' => $this->getStepTemplate('checkout_delivery'), 'params' => ['type' => 'checkout', 'params' => $params]]),
                    'payment-step' => \frontend\design\Block::widget(['name' => $this->getStepTemplate('checkout_payment'), 'params' => ['type' => 'checkout', 'params' => $params]]),
                    'products-totals' => \frontend\design\Block::widget(['name' => $this->getStepTemplate('checkout_step_bottom'), 'params' => ['type' => 'checkout', 'params' => $params]])
                ] 
            ];
        } else {
            $params['page_name'] = 'index';
            //$response['page'] = \frontend\design\Block::widget(['name' => $this->manager->getTemplate(), 'params' => ['type' => 'checkout', 'params' => $params]]);


            $response['page'] = [
                'widgets' => [
                    '.w-checkout-shipping' => \frontend\design\boxes\checkout\Shipping::widget(['params' => $params]),
                    '.w-checkout-shipping-address' => \frontend\design\boxes\checkout\ShippingAddress::widget(['params' => $params]),
                    '.w-checkout-totals' => \frontend\design\boxes\checkout\Totals::widget(['params' => $params]),
                    '.w-checkout-payment-method' => \frontend\design\boxes\checkout\PaymentMethod::widget(['params' => $params]),
                    '.w-checkout-billing-address' => \frontend\design\boxes\checkout\BillingAddress::widget(['params' => $params]),
                    '.w-checkout-shipping-choice' => \frontend\design\boxes\checkout\ShippingChoice::widget(['params' => $params]),
                ]
            ];
            if (\common\helpers\Acl::checkExtensionAllowed('DelayedDespatch', 'allowed')) {
                $response['page']['widgets']['.w-delayed-despatch-checkout'] = \common\extensions\DelayedDespatch\widgets\Checkout\Checkout::widget(['params' => $params]);
            }
            if (\common\helpers\Acl::checkExtensionAllowed('Neighbour', 'allowed')) {
                $response['page']['widgets']['.w-neighbour-checkout'] = \common\extensions\Neighbour\widgets\Checkout\Checkout::widget(['params' => $params]);
            }
        }
        return $response;
    }
    
    private function getStepTemplate($name){
        $_template = $this->manager->getTemplate();
        if ($_template){
            return preg_replace("/checkout/", $name, $_template);
        }
        return $name;
    }
    
}