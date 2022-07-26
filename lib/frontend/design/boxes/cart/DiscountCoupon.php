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

class DiscountCoupon extends Widget
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
      if (( (isset($this->settings[0]['hide']) && $this->settings[0]['hide']) || defined(HIDE_DISCOUNT_COUPON_WIDGET) && HIDE_DISCOUNT_COUPON_WIDGET == 'true') && !\frontend\design\Info::isAdmin()) {
          return '';
      }

      if ($ext = \common\helpers\Acl::checkExtensionAllowed('CouponsAndVauchers', 'allowed')) {
          return $ext::cartDiscountCoupon($this->params['manager']);
      }
  }
}