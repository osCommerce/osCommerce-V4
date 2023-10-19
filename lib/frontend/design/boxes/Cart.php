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

namespace frontend\design\boxes;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use common\classes\Images;

class Cart extends Widget {

	public $params;
	public $settings;

	public function init() {
		parent::init();
	}

	public function run() {
		if( GROUPS_DISABLE_CART ) {
			return '';
		}

		global $cart;
		$currencies = \Yii::$container->get('currencies');
		if( ! is_object( $cart ) || ! is_object( $currencies ) ) {
			return '';
		}
        
        if( $ext = \common\helpers\Extensions::isAllowed('MultiCart') ) {
            if($ext::getCartsAmount(false) > 0) {
                return $ext::cartsBlock([
                    'settings' => $this->settings,
                    'id' => $this->id
                ]);
            }
        }

        if (!isset($this->settings[0])) {
            $this->settings[0] = [];
        }

        if ($this->settings[0]['show_products']) {
            $cartDecorator = new \frontend\design\CartDecorator($cart);
            $products = $cartDecorator->getProducts();

            foreach ($products as $key => $item) {
                $products[$key]['price'] = $products[$key]['final_price'];
            }
        }

		if (!Yii::$app->user->isGuest){
		  $checkout_link = tep_href_link('checkout', '', 'SSL');
		} else {
		  $checkout_link = tep_href_link('checkout/login', '', 'SSL');
		}
		
		$params = [
			'total'          => (($this->settings[0]['total']??null) ? $currencies->format( $cart->show_total() ) : ''),
			'count_contents' => $cart->count_contents(),
			'settings'       => $this->settings,
			'products'       => $products,
			'is_multi_cart'  => false,
			'currencies'     => $currencies,
			'checkout_link'  => $checkout_link
		];

		return IncludeTpl::widget( [
			'file'   => 'boxes/cart.tpl',
			'params' => $params
		] );
	}
}