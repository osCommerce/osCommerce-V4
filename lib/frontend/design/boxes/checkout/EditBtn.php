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

use frontend\design\Info;
use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;

class EditBtn extends Widget
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
        
        switch ($this->settings[0]['link']) {
            case 'contact_information':
                $url = Yii::$app->urlManager->createUrl([Yii::$app->controller->id, '#' => 'contact_information']);
                break;
            case 'shipping_address':
                $url = Yii::$app->urlManager->createUrl([Yii::$app->controller->id, '#' => 'shipping_address']);
                break;
            case 'billing_address':
                $url = Yii::$app->urlManager->createUrl([Yii::$app->controller->id, '#' => 'billing_address']);
                break;
            case 'shipping_method':
                $url = Yii::$app->urlManager->createUrl([Yii::$app->controller->id, '#' => 'shipping_method']);
                break;
            case 'payment_method':
                $url = Yii::$app->urlManager->createUrl([Yii::$app->controller->id, '#' => 'payment_method']);
                break;
            case 'comments':
                $url = Yii::$app->urlManager->createUrl([Yii::$app->controller->id, '#' => 'comments-anchor']);
                break;
            case 'products':
                switch(Yii::$app->controller->id){
                    case 'quote-checkout':
                        $controller = 'quote-cart';
                    break;
                    case 'sample-checkout':
                        $controller = 'sample-cart';
                    break;
                    case 'checkout':
                    default:
                        $controller = 'shopping-cart';
                    break;
                    
                }
                $url = Yii::$app->urlManager->createUrl([$controller]);
                break;
        }

        return IncludeTpl::widget(['file' => 'boxes/account/account-link.tpl', 'params' => [
            'settings' => $this->settings,
            'text' => ($this->settings[0]['text'] ?? false) ? $this->settings[0]['text'] : EDIT,
            'url' => $url,
        ]]);
    }
}