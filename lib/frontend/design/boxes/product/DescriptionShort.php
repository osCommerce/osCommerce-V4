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

class DescriptionShort extends Widget
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

    if ($params['products_id']) {
      
        $products = Yii::$container->get('products');
        $product = $products->getProduct($params['products_id']);

        $this->settings[0]['cat_description'] = (isset($this->settings[0]['cat_description']) ? $this->settings[0]['cat_description'] : 0);
        $this->settings[0]['link_description'] = (isset($this->settings[0]['link_description']) ? $this->settings[0]['link_description'] : 0);
        
      if (!$product['products_description_short'] && $this->settings[0]['cat_description']){
        $strip = strip_tags($product['products_description']);
        if ($this->settings[0]['length_description']){
          $length = $this->settings[0]['length_description'];
        } else {
          $length  = 200;
        }
        if (strlen($strip) > $length) {
          $description = substr($strip, 0, $length) . '...';
        } else {
          $description = $strip;
        }
        if ($this->settings[0]['link_description']){
          $description = '<a href="' . tep_href_link(FILENAME_PRODUCT_INFO, \common\helpers\Output::get_all_get_params()) . '#description">' . $description . '</a>';
        }
      } else {
        $description = $product['products_description_short'];
      }


      return IncludeTpl::widget(['file' => 'boxes/product/description-short.tpl', 'params' => [
        'description' => $description
      ]]);
    } else {
      return '';
    }
  }
}
