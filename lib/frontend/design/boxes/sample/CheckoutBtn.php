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

namespace frontend\design\boxes\sample;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use common\classes\payment;

class CheckoutBtn extends Widget
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
    global $sample;

    if (!Yii::$app->user->isGuest){
      $checkout_link = tep_href_link('sample-checkout', '', 'SSL');
    } else {
      $checkout_link = tep_href_link('sample-checkout/login', '', 'SSL');
    }
    
    $paypal_link = '';

    if ($sample->count_contents() > 0) {
      return IncludeTpl::widget(['file' => 'boxes/sample/checkout-btn.tpl', 'params' => ['link' => $checkout_link]]);
    } else {
      return '';
    }
  }
}