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
use frontend\design\ListingSql;

class Filters extends Widget {

    public $params;
    public $settings;

    public function init() {
        parent::init();
    }

    static function products_filters_cmp($a, $b) {
        if ($a['selected'] == $b['selected']) {
            $a['count'] = ($a['count'] > 0);
            $b['count'] = ($b['count'] > 0);
            if ($a['count'] == $b['count']) {
                return (($a['text'] < $b['text'])) ? -1 : 1;
            }
            return (($a['count'] > $b['count'])) ? -1 : 1;
        }
        return (($a['selected'] > $b['selected'])) ? -1 : 1;
    }

    public function run() {

        if (!\common\helpers\Acl::checkExtensionAllowed('ProductPropertiesFilters')) {
            return '';
        }
        \frontend\design\Info::includeJsFile('reducers/filters');
        \frontend\design\Info::includeExtensionJsFile('ProductPropertiesFilters/js/filters');

        \frontend\design\Info::addJsData(['filters' => []]);
        \frontend\design\Info::addJsData(['tr' => ['TEXT_MORE' => TEXT_MORE]]);
        \frontend\design\Info::addJsData(['tr' => ['FILTER_LESS' => TEXT_LESS]]);

        if (isset($this->settings[0]['ajax_load']) && $this->settings[0]['ajax_load'] == 1) {
            if (\frontend\design\Info::isAdmin()) {
                return '<form></form>';
            }
            return '<div class="preloader"></div>';
        }


      /* @var $ext \common\extensions\ProductPropertiesFilters\ProductPropertiesFilters */
        if ($ext = \common\helpers\Acl::checkExtension('ProductPropertiesFilters', 'inFilters')) {
          if ($ext::allowed()) {
            return $ext::inFilters($this->params, $this->settings);
          }
        }
        /**
         * old backward compatibility
         */

        $languages_id = \Yii::$app->settings->get('languages_id');
        $currencies = \Yii::$container->get('currencies');
        $exclude_params = array('page');
        $filters_array = array();
        
        $listing_sql_array = \frontend\design\ListingSql::get_listing_sql_array($this->params['this_filename']);
        $listing_sql_array['left_join'] = " left join " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c on p.products_id = p2c.products_id left join " . TABLE_MANUFACTURERS . " m on p.manufacturers_id = m.manufacturers_id left join " . TABLE_PRODUCTS_DESCRIPTION . " pd on pd.products_id = p.products_id and pd.language_id = '" . (int) $languages_id . "' and pd.platform_id = '".intval(\common\classes\platform::defaultId())."' left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd1.products_id = p.products_id and pd1.language_id = '" . (int) $languages_id . "' and pd1.platform_id = '" . (int)Yii::$app->get('platform')->config()->getPlatformToDescription() . "' " . $listing_sql_array['left_join'];
        if ($this->params['this_filename'] == FILENAME_SPECIALS) {
            $listing_sql_array['left_join'] = " left join " . TABLE_SPECIALS . " s on p.products_id = s.products_id " . $listing_sql_array['left_join'];
        }
        
        if (!(Yii::$app->user->isGuest && \common\helpers\PlatformConfig::getFieldValue('platform_please_login'))) {
            $name = 'p';
            $min_price = $max_price = 0;
            $filters_sql_array = \frontend\design\ListingSql::get_filters_sql_array($name);
            $products_query = tep_db_query("select p.products_id, p.products_tax_class_id, p.products_price from " . $listing_sql_array['from'] . " " . TABLE_PRODUCTS . " p " . $listing_sql_array['left_join'] . " " . $filters_sql_array['left_join'] . " where p.products_price > 0 " . $listing_sql_array['where'] . " " . $filters_sql_array['where']);
            while ($products = tep_db_fetch_array($products_query)) {
                $special_price = \common\helpers\Product::get_products_special_price($products['products_id']);
                $price = \common\helpers\Product::get_products_price($products['products_id'], 1, $products['products_price']);
                if ($special_price) {
                    $price = $special_price;
                }
                $price = $currencies->display_price_clear($price, \common\helpers\Tax::get_tax_rate($products['products_tax_class_id']));
                if ($min_price == 0 || $price < $min_price) {
                    $min_price = (int) floor($price);
                }
                if ($max_price == 0 || $price > $max_price) {
                    $max_price = (int) ceil($price);
                }
            }

            if ($max_price > $min_price) {
                $filters_array[] = array(
                    'title' => TEXT_PRICE,
                    'name' => $name,
                    'type' => 'slider',
                    'step' => 1,
                    'min' => (int) $min_price,
                    'max' => (int) $max_price,
                    'paramfrom' => (float)($_GET[$name . 'from'] > 0 && $_GET[$name . 'from'] > $min_price ? $_GET[$name . 'from'] : ''),
                    'paramto' => (float)($_GET[$name . 'to'] > 0 && $_GET[$name . 'to'] < $max_price ? $_GET[$name . 'to'] : ''),
                );
                $exclude_params[] = $name;
                $exclude_params[] = $name . 'from';
                $exclude_params[] = $name . 'to';
            }
        }
     
        $name = 'brand';
        $manufacturers_array = array();
        $manufacturers_query = tep_db_query("select count(distinct p.products_id) as products_count, b.manufacturers_id, b.manufacturers_name from " . $listing_sql_array['from'] . " " . TABLE_MANUFACTURERS . " b, " . TABLE_PRODUCTS . " p " . $listing_sql_array['left_join'] . " where b.manufacturers_id = p.manufacturers_id and p.manufacturers_id > 0 " . $listing_sql_array['where'] . " group by b.manufacturers_id order by b.manufacturers_name");
        while ($manufacturers = tep_db_fetch_array($manufacturers_query)) {
            $manufacturers_array[$manufacturers['manufacturers_id']] = array(
                'id' => $manufacturers['manufacturers_id'],
                'text' => $manufacturers['manufacturers_name'],
                'count' => 0, // next step // (int)$manufacturers['products_count'],
                'selected' => in_array($manufacturers['manufacturers_id'], is_array($_GET[$name]) ? $_GET[$name] : array($_GET[$name])),
            );
        }

        $products_count = 0;
        $filters_sql_array = \frontend\design\ListingSql::get_filters_sql_array($name);
        $manufacturers_query = tep_db_query("select count(distinct p.products_id) as products_count, b.manufacturers_id, b.manufacturers_name from " . $listing_sql_array['from'] . " " . TABLE_MANUFACTURERS . " b, " . TABLE_PRODUCTS . " p " . $listing_sql_array['left_join'] . " " . $filters_sql_array['left_join'] . " where b.manufacturers_id = p.manufacturers_id and p.manufacturers_id > 0 " . $listing_sql_array['where'] . " " . $filters_sql_array['where'] . " group by b.manufacturers_id order by b.manufacturers_name");
        while ($manufacturers = tep_db_fetch_array($manufacturers_query)) {
            $products_count += (int) $manufacturers['products_count'];
            $manufacturers_array[$manufacturers['manufacturers_id']]['count'] = (int) $manufacturers['products_count'];
        }

        if (count($manufacturers_array) > 0 && $products_count > 0) {
            usort($manufacturers_array, array('self', 'products_filters_cmp'));
            $filters_array[] = array(
                'title' => TEXT_BRAND,
                'name' => $name,
                'type' => 'boxes',
                'values' => $manufacturers_array,
                'params' => $_GET[$name],
            );
            $exclude_params[] = $name;
        }

        $name = 'keywords';
        $filters_array[] = array(
            'title' => TEXT_KEYWORDS,
            'name' => $name,
            'type' => 'input',
            'params' => $_GET[$name],
        );
        $exclude_params[] = $name;
        
        if (count($filters_array) > 0) {
            return IncludeTpl::widget([
                'file' => 'boxes/filters.tpl',
                'params' => [
                  'filters_url' => tep_href_link(tep_not_null($this->params['this_filename']) ? $this->params['this_filename'] : FILENAME_ADVANCED_SEARCH_RESULT, \common\helpers\Output::get_all_get_params($exclude_params)),
                  'filters_hiddens' => \common\helpers\Output::get_all_get_params(array_merge(array('cPath'), $exclude_params), true),
                  'filters_array' => $filters_array,
                  'settings' => $this->settings,
                ]
              ]);
        } else {
            return '<div class="no-filters">&nbsp;</div>';
        }
    }

}
