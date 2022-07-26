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

namespace frontend\design\boxes\catalog;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use frontend\design\Info;
use backend\design\Style;

class ImageMapCategory extends Widget
{

    public $file;
    public $params;
    public $content;
    public $settings;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        global $current_category_id;
        $languages_id = \Yii::$app->settings->get('languages_id');

        Info::addBlockToWidgetsList('image-maps');

        $mapsId = \common\models\Categories::findOne($current_category_id)->maps_id ?? null;
        $filterMapIds = array();
        
        if (!$mapsId) {
          if ($_GET['manufacturers_id'] > 0) {
              $category_filters = \common\helpers\Manufacturers::get_manufacturer_filters($_GET['manufacturers_id']);
              $mapsId = \common\models\Manufacturers::findOne($_GET['manufacturers_id'])->maps_id;
          } else {
              $category_filters = \common\helpers\Categories::get_category_filters($current_category_id);
          }

          if (is_array($category_filters)) {

            foreach ($category_filters as $filter) {

                switch ($filter['filters_type']) {
                  case 'category':
                      $name = 'cat';
                      if (!empty($_GET[$name])) {
                        $ids = is_array($_GET[$name]) ? $_GET[$name] : array($_GET[$name]);
                        foreach ($ids as $id) {
                          if ($tmp = \common\models\Categories::findOne($id)->maps_id) {
                            $filterMapIds[$name][] = $tmp;
                          }
                        }
                      }
                      break;
                  case 'brand':
                      $name = 'brand';
                      if (!empty($_GET[$name])) {
                        $ids = is_array($_GET[$name]) ? $_GET[$name] : array($_GET[$name]);
                        foreach ($ids as $id) {
                          if ($tmp = \common\models\Manufacturers::findOne($id)->maps_id ?? null) {
                            $filterMapIds[$name][] = $tmp;
                          }
                        }
                      }
                      break;
                  case 'attribute':
                      /*$option = tep_db_fetch_array(tep_db_query("select po.products_options_id, po.products_options_name from " . TABLE_PRODUCTS_OPTIONS . " po where po.products_options_id = '" . (int) $filter['options_id'] . "' and po.language_id = '" . (int) $languages_id . "'"));
                      $name = 'at' . $option['products_options_id'];

                      break;*/
                  case 'property':
                      $name = 'pr' . (int)$filter['properties_id'];
                      if (!empty($_GET[$name])) {
                        $ids = array_map('intval', is_array($_GET[$name]) ? $_GET[$name] : array($_GET[$name]));
                        $values_query = tep_db_query("select maps_id from " . TABLE_PROPERTIES_VALUES . " pv where pv.properties_id='" . (int) $filter['properties_id'] . "' and pv.values_id in ('" . implode("','", $ids) . "') and pv.language_id = '" . (int) $languages_id . "' and pv.maps_id<>'' order by sort_order, values_text ");
                        while ($tmp = tep_db_fetch_array($values_query)) {
                          $filterMapIds['pr'][] = $tmp['maps_id'];
                        }
                      }
                      break;
                }
              }

              if (count($filterMapIds)>0 /* filter map is preferable && !$mapsId*/) {
                foreach (array('pr', 'brand', 'cat') as $v ) {
                  if (is_array($filterMapIds[$v])) {
                    if (count($filterMapIds[$v])==1) {
                      $mapsId = $filterMapIds[$v][0];
                    } else {
                      $mapsId = $filterMapIds[$v][array_rand($filterMapIds[$v])];
                    }
                    break;
                  }
                }

              }
            }

          }


        return \frontend\design\boxes\ImageMap::widget([
            'params' => ['mapsId' => $mapsId],
            'settings' => $this->settings,
            'id' => $this->id
        ]);

    }
}