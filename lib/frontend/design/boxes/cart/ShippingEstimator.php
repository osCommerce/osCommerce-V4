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
use frontend\controllers\ShoppingCartController;

class ShippingEstimator extends Widget
{

  public $file;
  public $params;
  public $settings;
  public $manager;

  public function init()
  {
    parent::init();
  }

  public function run()
  {
      global $cart, $request_type;
      if ($cart->count_contents() == 0) {
          return IncludeTpl::widget(['file' => 'boxes/hide-box.tpl','params' => [
              'settings' => $this->settings,
              'id' => $this->id
          ]]);
      }
      
      $manager = $this->params['manager'];
      
      if (!$manager->isShippingNeeded()) {
          return IncludeTpl::widget(['file' => 'boxes/hide-box.tpl','params' => [
              'settings' => $this->settings,
              'id' => $this->id
          ]]);
      };

      return IncludeTpl::widget(['file' => 'boxes/cart/shipping-estimator.tpl', 'params' => [
          'params' => $manager->prepareEstimateData(),
          'estimate_ajax_server_url' => tep_href_link(FILENAME_SHOPPING_CART, '', $request_type)]
      ]);
  }
}