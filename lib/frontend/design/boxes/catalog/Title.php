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
use common\helpers\CategoriesDescriptionHelper;
use common\helpers\Affiliate;

class Title extends Widget
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
    $manufacturers_id = (int)Yii::$app->request->get('manufacturers_id', 0);
    $title = $h1 = null;
    if ($current_category_id > 0) {

      // Get the category name and description
        $category = CategoriesDescriptionHelper::getCategoriesDescriptionList($current_category_id, $languages_id, Affiliate::id());
        $title = $category['categories_name'];
        $h1 = $category['categories_h1_tag'];

    } elseif ($manufacturers_id > 0) {

      // Get the manufacturer name and image
      $manufacturer_query = tep_db_query("select m.manufacturers_name as categories_name, mi.manufacturers_h1_tag from " . TABLE_MANUFACTURERS . " m left join " . TABLE_MANUFACTURERS_INFO . " mi on (m.manufacturers_id = mi.manufacturers_id and mi.languages_id = '" . (int)$languages_id . "')  where m.manufacturers_id = '" . (int)$manufacturers_id . "'");
      $category = tep_db_fetch_array($manufacturer_query);
      $title = $category['categories_name'];
        $h1 = $category['manufacturers_h1_tag'];

    } elseif (Yii::$app->controller->action->id == 'all-products') {
        if (defined('HEAD_H1_TAG_PRODUCTS_ALL') && tep_not_null(HEAD_H1_TAG_PRODUCTS_ALL)) {
            $h1 = HEAD_H1_TAG_PRODUCTS_ALL;
        } else {
            $title = TEXT_ALL_PRODUCTS;
        }
    } elseif (Yii::$app->controller->action->id == 'products-new') {
        if (defined('HEAD_H1_TAG_WHATS_NEW') && tep_not_null(HEAD_H1_TAG_WHATS_NEW)) {
            $h1 = HEAD_H1_TAG_WHATS_NEW;
        } else {
            $title = NEW_PRODUCTS;
        }
    } elseif (Yii::$app->controller->action->id == 'featured-products') {
        if (defined('HEAD_H1_TAG_FEATURED') && tep_not_null(HEAD_H1_TAG_FEATURED)) {
            $h1 = HEAD_H1_TAG_FEATURED;
        } else {
            $title = FEATURED_PRODUCTS;
        }
    } elseif (Yii::$app->controller->action->id == 'sales') {
        if (defined('HEAD_H1_TAG_SPECIALS') && tep_not_null(HEAD_H1_TAG_SPECIALS)) {
            $h1 = HEAD_H1_TAG_SPECIALS;
        } else {
            $title = SPECIALS_PRODUCTS;
        }
    } elseif (Yii::$app->controller->action->id == 'free-samples') {
        if (defined('HEAD_H1_TAG_FREE_SAMPLES') && tep_not_null(HEAD_H1_TAG_FREE_SAMPLES)) {
            $h1 = HEAD_H1_TAG_FREE_SAMPLES;
        } else {
            $title = NAVBAR_TITLE;
        }
    }
    if ($title || $h1)
        \frontend\design\Info::addJsData(['page_title' => strip_tags($title ? $title : $h1)]);
        $this->settings[0]['show_heading'] = (isset($this->settings[0]['show_heading']) ? $this->settings[0]['show_heading'] : false);
        return IncludeTpl::widget(['file' => 'boxes/catalog/title.tpl', 'params' => [
            'title' => \common\helpers\Html::fixHtmlTags($title),
            'h1' => strip_tags($h1),
            'settings' => $this->settings,
        ]]);
  }
}
