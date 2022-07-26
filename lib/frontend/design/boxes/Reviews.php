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
use common\classes\platform;
use common\helpers\Product;

class Reviews extends Widget
{
  use \common\helpers\SqlTrait;

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

    if ($this->settings[0]['params']) {
      $max = $this->settings[0]['params'];
    } else {
      $max = MAX_RANDOM_SELECT_REVIEWS;
    }

    $listing_sql_array = \frontend\design\ListingSql::get_listing_sql_array(FILENAME_ALL_PRODUCTS);
    $random_select = "select distinct r.reviews_id, r.reviews_rating, r.customers_name, r.date_added, p.products_id, p.products_image, if(length(pd1.products_name), pd1.products_name, pd.products_name) as products_name, substring(rd.reviews_text, 1, 200) as reviews_text from " . $listing_sql_array['from'] . " " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_REVIEWS . " r, " . TABLE_REVIEWS_DESCRIPTION . " rd, " . TABLE_PRODUCTS . " p left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd1.products_id = p.products_id and pd1.language_id='" . (int)$languages_id ."' and pd1.platform_id = '" . (int)Yii::$app->get('platform')->config()->getPlatformToDescription() . "' " . $listing_sql_array['left_join'] . " where r.status and p.products_id = r.products_id and r.reviews_id = rd.reviews_id and rd.languages_id = '" . (int)$languages_id . "' " . " and p.products_id = pd.products_id and pd.platform_id = '".intval(\common\classes\platform::defaultId())."' and pd.language_id = '" . (int)$languages_id . "'";

    if (isset($_GET['products_id'])) {
      $random_select .= " and p.products_id = '" . (int)$_GET['products_id'] . "'";
    }
    $random_select .= " " . $listing_sql_array['where'] . " order by r.reviews_id desc limit " . $max;
    $random_product = tep_db_query($random_select);


    $items = array();
    if (tep_db_num_rows($random_product) > 0) {

        while($res_rev = tep_db_fetch_array($random_product)){
            if (defined('TEXT_RATE_' . $res_rev['reviews_rating'])) {
              $res_rev['reviews_rating_description'] = constant('TEXT_RATE_' . $res_rev['reviews_rating']);
            }

            $res_rev['link'] = Yii::$app->urlManager->createUrl(['catalog/product', 'products_id' => $res_rev['products_id']]);

            $res_rev['img'] = \common\classes\Images::getImage($res_rev['products_id'], 'Small', $languages_id, 0, [], $this->settings[0]['lazy_load']);

            $res_rev['date'] = \common\helpers\Date::date_long($res_rev['date_added']);

            $items[] = $res_rev;

        }

      return IncludeTpl::widget([
        'file' => 'boxes/reviews.tpl',
        'params' => [
            'items' => $items,
            'lazy_load' => $this->settings[0]['lazy_load'],
        ],
      ]);
    }



    return '';
  }
}