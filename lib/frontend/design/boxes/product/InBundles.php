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

namespace frontend\design\boxes\product;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use frontend\design\Info;

class InBundles extends Widget
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
    $params = Yii::$app->request->get();

    if ( !$params['products_id'] ) return '';

    $bundle_products = array();

    if ($ext = \common\helpers\Acl::checkExtensionAllowed('ProductBundles', 'allowed')) {
        $bundle_products = $ext::inBundles($params);
    }
    
    if ( count($bundle_products)>0 ) {
      foreach ($bundle_products as $key => $product) {
        $details = \common\helpers\Bundles::getDetails(['products_id' => $product['products_id']], [], false);
        if ($details['full_bundle_price_clear'] > $details['actual_bundle_price_clear']) {
          $product['price_old'] = $details['full_bundle_price'];
          $product['price_special'] = $details['actual_bundle_price'];
          unset($product['price']);
        } else {
          $product['price'] = $details['actual_bundle_price'];
        }
        $bundle_products[$key] = $product;
      }
      return IncludeTpl::widget([
        'file' => 'boxes/product/in-bundles.tpl',
        'params' => ['products' => $bundle_products]
      ]);
    }

    return '';
  }
}