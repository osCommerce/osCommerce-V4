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

class PropertiesIcons extends Widget
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
    $languages_id = \Yii::$app->settings->get('languages_id');
    $params = Yii::$app->request->get();

    if (!$params['products_id']) return '';

    $properties_array = array();
    $values_array = array();
    $properties_query = tep_db_query("select p.properties_id, if(p2p.values_id > 0, p2p.values_id, p2p.values_flag) as values_id from " . TABLE_PROPERTIES_TO_PRODUCTS . " p2p, " . TABLE_PROPERTIES . " p where p2p.properties_id = p.properties_id and p.display_product = '1' and p2p.products_id = '" . (int)$params['products_id'] . "'");
    while ($properties = tep_db_fetch_array($properties_query)) {
        if (!in_array($properties['properties_id'], $properties_array)) {
            $properties_array[] = $properties['properties_id'];
        }
        $values_array[$properties['properties_id']][] = $properties['values_id'];
    }
    $properties_tree_array = \common\helpers\Properties::generate_properties_tree(0, $properties_array, $values_array);
    if ( count($properties_array)>0  ) {
      return IncludeTpl::widget([
        'file' => 'boxes/product/properties-icons.tpl',
        'params' => [
          'properties_tree_array' => $properties_tree_array,
          'languages_id' => $languages_id,
        ]
      ]);
    }
    return '';
  }

}