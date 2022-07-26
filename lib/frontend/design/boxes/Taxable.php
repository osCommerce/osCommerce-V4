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

class Taxable extends Widget
{

  public $params;
  public $settings;

  public function init()
  {
    parent::init();
  }

  public function run()
  {
      //hide on account and checkout pages
      if ( in_array(Yii::$app->controller->id, ['checkout', 'account']) ) return '';

    // if customer is logged in and his group is NOT taxable then tax is hever caclulated/added Switcher doesn't work (useless)
      $customer_groups_id = (int) \Yii::$app->storage->get('customer_groups_id');
      /** @var \common\extensions\BusinessToBusiness\BusinessToBusiness $extb2b */
      if ($extb2b = \common\helpers\Acl::checkExtensionAllowed('BusinessToBusiness', 'allowed')) {
          if ($extb2b::checkTaxRate($customer_groups_id)) {
              return '';
          }
      }

      $url = \yii\helpers\Url::current(['action' => 'taxabeling']);

      $def = (defined('DISPLAY_PRICE_WITH_TAX') && DISPLAY_PRICE_WITH_TAX=='true')?1:0;
      /** @var \common\extensions\CustomerTaxable\CustomerTaxable $ext */
      if ($ext = \common\helpers\Acl::checkExtensionAllowed('CustomerTaxable', 'allowed')) {
        $def = $ext::getDefaultStatus();
      }

      $taxable = Yii::$app->storage->has('taxable') ? Yii::$app->storage->get('taxable') : $def;
      $tList = [TEXT_EXC_VAT, TEXT_INC_VAT];
      return IncludeTpl::widget([
                'file' => 'boxes/taxable.tpl',
                'params' => [
                    'taxable' => $taxable,
                    'settings' => $this->settings,
                    'id' => $this->id,
                    'tList' => $tList,
                    'url' => $url,
                ]
            ]);
  }
}