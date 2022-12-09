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
use common\helpers\Affiliate;

class Description extends Widget
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
    global $current_category_id, $manufacturers_id;
    $languages_id = \Yii::$app->settings->get('languages_id');

    if ($current_category_id > 0) {
      // Get the category name and description
      $category_query = tep_db_query("select if(length(cd1.categories_description), cd1.categories_description, cd.categories_description) as categories_description from " . TABLE_CATEGORIES_DESCRIPTION . " cd, " . TABLE_CATEGORIES . " c left join " . TABLE_CATEGORIES_DESCRIPTION . " cd1 on cd1.categories_id = c.categories_id and cd1.language_id = '" . (int)$languages_id . "' and cd1.affiliate_id = '" . Affiliate::id() . "' where c.categories_id = '" . (int)$current_category_id . "' and cd.categories_id = '" . (int)$current_category_id . "' and cd.language_id = '" . (int)$languages_id . "'");
      $category = tep_db_fetch_array($category_query);
      if ( !is_array($category) ) return '';
      $description = $category['categories_description'];
    } elseif ($manufacturers_id > 0) {
      $description = \common\helpers\Manufacturers::getManufacturerDescription($manufacturers_id, $languages_id);
    }
    $description = \common\classes\TlUrl::replaceUrl($description);
      $description = \frontend\design\Info::widgetToContent($description);
    
    return IncludeTpl::widget(['file' => 'boxes/catalog/description.tpl', 'params' => ['description' => $description]]);
  }
}