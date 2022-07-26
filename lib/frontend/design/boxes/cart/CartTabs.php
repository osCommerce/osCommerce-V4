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

class CartTabs extends Widget
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
      
      $page = 'cart';
      $controller = Yii::$app->controller->id;
      $action = Yii::$app->controller->action->id;
      if ($controller == 'cart') {
          $page = 'cart';
      } elseif ($controller == 'quote-cart') {
          $page = 'quote';
      } elseif ($controller == 'sample-cart') {
          $page = 'sample';
      } elseif ($controller == 'account' && $action == 'wishlist') {
          $page = 'wishlist';
      }
      
        $samples = false;
        if ($ext = \common\helpers\Acl::checkExtension('Samples', 'allowed')) {
            $samples = $ext::allowed();
        }
        $quotations = false;
        if ($ext = \common\helpers\Acl::checkExtension('Quotations', 'allowed')) {
            $quotations = $ext::allowed();
        }

      return IncludeTpl::widget(['file' => 'boxes/cart/cart-tabs.tpl', 'params' => [
          'settings' => $this->settings,
          'params' => $this->params,
          'id' => $this->id,
          'page' => $page,
          'logged' => !Yii::$app->user->isGuest,
          'samples' => $samples,
          'quotations' => $quotations,
      ]]);
  }
}