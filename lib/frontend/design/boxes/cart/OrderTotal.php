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

class OrderTotal extends Widget
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
        global $cart;

        $manager = $this->params['manager'];

        $coupon_remove_action = Yii::$app->urlManager->createUrl(['shopping-cart', 'action' => 'remove_cart_total']);
        if ( is_object($manager) && $manager->hasCart() ){
            $is_empty_cart = $manager->getCart()->count_contents() == 0;
            if ( $manager->getCart() instanceof \common\extensions\Quotations\QuoteCart ){
                $coupon_remove_action = Yii::$app->urlManager->createUrl(['quote-cart', 'action' => 'remove_cart_total']);
            }
        }else{
            $is_empty_cart = $cart->count_contents() == 0;
        }

        $result = [];
        if ($is_empty_cart) {
            $result[] = [
                'title' => TEXT_TOTAL,
                'text' => \Yii::$container->get('currencies')->format( 0 ),
                'code' => 'empty_cart',
            ];
        } else {
            $result = $manager->getTotalOutput(true, 'TEXT_SHOPPING_CART');
        }

        foreach ($result as $key => $value) {
            if ($value['code'] == 'ot_coupon') {
                $result[$key]['coupon'] = '';
                $ex = [];
                preg_match("/\((.*)\):$/", $value['title'], $ex);
                if (is_array($ex) && isset($ex[1])) {
                    $result[$key]['coupon'] = $ex[1];
                }
            }
        }

        $this->params['order_total_output'] = $result;
        $this->params['coupon_remove_action'] = $coupon_remove_action;

        return IncludeTpl::widget([
            'file' => 'boxes/cart/order-total.tpl',
            'params' => $this->params
        ]);
    }
}