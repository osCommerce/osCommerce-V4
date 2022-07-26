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
use frontend\design\CartDecorator;

class Products extends Widget
{

    public $type;
    public $settings;
    public $params;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        global $cart;
        $groupId = (int) \Yii::$app->storage->get('customer_groups_id');
        if (isset($this->params['sender']) && $this->params['sender'] == 'worker') {
            if (\frontend\design\Info::widgetSettings('cart\Products', 'editable_products', 'checkout')){
                $this->settings[0]['editable_products'] = true;
            }
        }
        
        if ((Yii::$app->controller->id == 'checkout' && !($this->settings[0]['editable_products'] ?? false) )
            || Yii::$app->controller->id == 'sample-checkout'
            || Yii::$app->controller->id == 'quote-checkout' ) {
            $this->type = 2;
        }

        $cartDecorator = new CartDecorator($cart);
        
        $multiCart = ['enabled' => false,];
        if($ext = \common\helpers\Acl::checkExtension('MultiCart', 'allowed')){
            if ($ext::allowed()){
                $multiCart['enabled'] = !$ext::isEmpty();
            }
        }

        if($ext = \common\helpers\Acl::checkExtension('MultiCart', 'allowed')){
            if ($ext::allowed()){
                $multiCart['script'] = $ext::actionScript();
            }
        }

        \frontend\design\Info::addBlockToWidgetsList('cart-listing');
        
        $bounded = false;
        if ($cartDecorator->bound_quantity_ordered){
            $bounded = true;
            $boundMessage = TEXT_INSTOCK_BOUND_MESSAGE;
        } else if ($cart->hasBlockedProducts()){
            $bounded = true;
            $boundMessage = TEXT_INFO_CART_HAS_LIMITED_PRODUCTS;
        }
        
        if ($cart->count_contents() > 0) {
            $popup = (int)Yii::$app->request->get('popup', 0);
            return IncludeTpl::widget(['file' => 'boxes/cart/products' . ($this->type ? '-' . $this->type : '') . '.tpl', 'params' => [
              'products' => $cartDecorator->getProducts(),
              'allow_checkout' => !($cartDecorator->oos_product_incart || $bounded),
              'oos_product_incart' => $cartDecorator->oos_product_incart,
              'bound_quantity_ordered' => $bounded,
              'bonus_points' => (is_object($this->params['manager'])? $this->params['manager']->getBonusesDetails(): null),
              'promoMessage' => \common\helpers\Acl::checkExtensionAllowed('Promotions') ? \common\models\promotions\PromotionService::getMessage() : null,
              'boundMessage' => $boundMessage ?? '',
              'popupMode' => ($popup == 1),
              'multiCart' => $multiCart,
              'settings' => $this->settings,
              'groupId' => $groupId,
            ]]);
        } else {
            return '<div class="empty">' . CART_EMPTY . '</div>';
        }
    }
}
