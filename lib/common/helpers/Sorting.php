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

namespace common\helpers;


class Sorting
{
   const SORT_OPTIONS = [
      [TEXT_BY_MODEL, 'm', 'products_model', TEXT_BY_MODEL_TO_LESS],
      [TEXT_BY_NAME, 'n', 'products_name', TEXT_BY_NAME_TO_LESS],
      [TEXT_BY_MANUFACTURER, 'b', 'manufacturers_name', TEXT_BY_MANUFACTURER_TO_LESS],
      [TEXT_BY_PRICE, 'p', 'products_price', TEXT_BY_PRICE_TO_LESS],
      [TEXT_BY_QUANTITY, 'q', 'products_quantity', TEXT_BY_QUANTITY_TO_LESS],
      [TEXT_BY_WEIGHT, 'w', 'products_weight', TEXT_BY_WEIGHT_TO_LESS],
      [TEXT_BY_DATE, 'd', 'products_date_added', TEXT_BY_DATE_TO_LESS],
      [TEXT_BY_POPULARITY, 'y', 'products_popularity', TEXT_BY_POPULARITY_TO_LESS],
     ];

   /**
    * default sort order for selected category.
    * @param int $categoryId
    */
   public static function getDefaultSortOrder($categoryId = 0) {
     $ret = '';
     $categoryId = intval($categoryId);
     if ($categoryId>0) {
       $cat = \common\models\Categories::findOne($categoryId);
       if (!empty($cat->default_sort_order)) {
         $ret = $cat->default_sort_order;
       } elseif ($cat) {
         $parent = $cat->getParents()->addSelect('default_sort_order, categories_left, categories_id')
             ->andWhere('default_sort_order<>""')->orderBy('categories_left desc')
             ->one();
         if (!empty($parent->default_sort_order)) {
           $ret = $parent->default_sort_order;
         }

       }
     }

     if (empty($ret) && defined('PRODUCT_LISTING_DEFAULT_SORT_ORDER') && array_key_exists(PRODUCT_LISTING_DEFAULT_SORT_ORDER, static::getPossibleSortOptions())) {
       $ret = PRODUCT_LISTING_DEFAULT_SORT_ORDER;
     }
     return $ret;
   }

/**
 * list of supported sort orders (for admin configuration)
 * @return array [[ <c>[a|d] => 'title' ] ...]
 */
   public static function getPossibleSortOptions($forCat= 0) {
     $ret = [];
     if ($forCat) {
      $ret[''] = TEXT_NO_SORTING . TEXT_FROM_CONFIGURATION;
     }
    $ret['mark'] = defined('TEXT_MARKETING_SORT_ORDER')?TEXT_MARKETING_SORT_ORDER:'';
    $ret['gso'] = defined('TEXT_GSO_SORT_ORDER')?TEXT_GSO_SORT_ORDER:'';

     if (is_array(static::SORT_OPTIONS)) {
       foreach (static::SORT_OPTIONS as $opt) {
         $ret[$opt[1] . 'a'] = $opt[0];
         $ret[$opt[1] . 'd'] = $opt[3];
       }
     }
     return $ret;
   }

 /**
 *
 * @param array $settings
 * @param bool $iso which char-set is used (0/1)
 * @param bool $onlyVisible (for pull-down - true, false - configuration, so all options)
 * @return array
 */
    public static function getSorting($settings, $iso = false, $onlyVisible = false) {
      if ($iso) {
        $downChar = '&#9650;';
        $upChar = '&#9660;';

      } else {
        $downChar = '&#xe995;';
        $upChar = '&#xe996;';

      }

      $orders = [];

      //get all sort option positions from settings, if something's missed fill in with 0
      for($i=0; $i<17; $i++) {
        $orders['sort_pos_' . $i] = ($settings['sort_pos_' . $i]??0) ? $settings['sort_pos_' . $i] : 0;
      }
      // new to top ....
      asort($orders, SORT_NUMERIC);

      //re-number to get unique sort positions (new one have 0)
      $counter = 1;
      foreach ($orders as $key => $none) {
        $orders[$key] = $counter++;
      }

      for ($i = 0; $i < 17; $i++) {
        if ($settings['sort_hide_' . $i] ?? false) {
          $orders['sort_pos_' . $i] = 100 + $i;
        }
      }

      //if (!($settings['sort_hide_0'] ?? false)) {
          $sorting[$orders['sort_pos_0']] = ['title' => TEXT_NO_SORTING,
              'hide' => (($settings['sort_hide_0'] ?? 1) ? '0' : '1'),
              'name' => '0',
              'id' => '0'
          ];
      //}

      foreach (self::SORT_OPTIONS as $j => $d) {
        $k = 2*$j + 1;
        if (!$onlyVisible || !$settings['sort_hide_' . $k]) {
          $sorting[$orders['sort_pos_' . $k]] = ['title' => (empty($settings['hide_icons']) ? ' <span class="ico">' . $downChar . '</span>' : '') . $d[0],
            'hide' => ($settings['sort_hide_' . $k]? '0' : '1'), 'name' => $k, 'id' => $d[1] . 'a'];
        }
        $k++;
        if (!$onlyVisible || !$settings['sort_hide_' . $k]) {
          $sorting[$orders['sort_pos_' . $k]] = ['title' => (empty($settings['hide_icons']) ? ' <span class="ico">' . $upChar . '</span>' : '') . $d[3],
            'hide' => ($settings['sort_hide_' . $k]? '0' : '1'), 'name' => $k, 'id' => $d[1] . 'd'];
        }
      }

      ksort($sorting);

      return $sorting;
    }

  /**
   * @deprecated (PRODUCT_LIST_* constant do not work and will be removed)
   * @return string
   */
    public static function getSortingList(){
        $sorting = [];

        $sorting[] = array('id' => '0', 'title' => TEXT_NO_SORTING);

        if (PRODUCT_LIST_MODEL) {
            $sorting[] = array('id' => 'ma', 'title' => TEXT_BY_MODEL . ' &darr;');
            $sorting[] = array('id' => 'md', 'title' => TEXT_BY_MODEL_TO_LESS . ' &uarr;');
        }
        if (PRODUCT_LIST_NAME) {
            $sorting[] = array('id' => 'na', 'title' => TEXT_BY_NAME . ' &darr;');
            $sorting[] = array('id' => 'nd', 'title' => TEXT_BY_NAME_TO_LESS . ' &uarr;');
        }
        if (PRODUCT_LIST_MANUFACTURER) {
            $sorting[] = array('id' => 'ba', 'title' => TEXT_BY_MANUFACTURER . ' &darr;');
            $sorting[] = array('id' => 'bd', 'title' => TEXT_BY_MANUFACTURER_TO_LESS . ' &uarr;');
        }
        if (PRODUCT_LIST_PRICE) {
            $sorting[] = array('id' => 'pa', 'title' => TEXT_BY_PRICE . ' &darr;');
            $sorting[] = array('id' => 'pd', 'title' => TEXT_BY_PRICE_TO_LESS . ' &uarr;');
        }
        if (PRODUCT_LIST_QUANTITY) {
            $sorting[] = array('id' => 'qa', 'title' => TEXT_BY_QUANTITY . ' &darr;');
            $sorting[] = array('id' => 'qd', 'title' => TEXT_BY_QUANTITY_TO_LESS . ' &uarr;');
        }
        if (PRODUCT_LIST_WEIGHT) {
            $sorting[] = array('id' => 'wa', 'title' => TEXT_BY_WEIGHT . ' &darr;');
            $sorting[] = array('id' => 'wd', 'title' => TEXT_BY_WEIGHT_TO_LESS . ' &uarr;');
        }

        if (PRODUCT_LIST_POPULARITY) {
            $sorting[] = array('id' => 'ya', 'title' => TEXT_BY_POPULARITY . ' &darr;');
            $sorting[] = array('id' => 'yd', 'title' => TEXT_BY_POPULARITY_TO_LESS . ' &uarr;');
        }

        return $sorting;

    }

    /**
     *
     * @param string $shortKey
     * @return array
     */
    public static function getOrderByArray($shortKey){
        $shortKey = trim($shortKey);
        $ret = '';
        if (strlen($shortKey)==1) {
            $shortKey .= 'a';
        }
        if (strlen($shortKey)==2) {
            $f = substr($shortKey, 0, 1);
            $d = substr($shortKey, 1);
            foreach (\common\helpers\Sorting::SORT_OPTIONS as $so) {
                if ($so[1] == $f) {
                    $ret = [$so[2] => ($d=='d' ? SORT_DESC : SORT_ASC)];
                }
            }
        } elseif ($shortKey=='gso') {
          $ret = ['gso' => SORT_DESC]; //direction's ignored
          
        } elseif ($shortKey=='mark') {
          $ret = ['mark' => SORT_ASC]; //direction's ignored
        }

        return $ret;
    }
}
