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

class FiltersSimple extends Widget {

    public $params;
    public $settings;

    public function init() {
        parent::init();
    }

    public function run() {
        if ($ext = \common\helpers\Acl::checkExtension('ProductPropertiesFilters', 'inFilters')) {
            if (!$ext::allowed()) {
                return '';
            }

            $currencies = \Yii::$container->get('currencies');

            if (!Yii::$app->has('productsFilterQuery')) {
                $q = new \common\components\ProductsQuery([
                    'get' => \Yii::$app->request->get(),
                ]);
                $count = $q->getCount();
                Yii::$app->set('productsFilterQuery', $q);

                $product_ids = $q->buildQuery()->getQuery()->column();
            }

            $this->settings['only_data'] = true;
            $data = $ext::inFilters($this->params, $this->settings);

            if (!is_array($data['filters_array']) || count($data['filters_array']) < 1) {
                return '';
            }

            foreach ($data['filters_array'] as $filter) {
                if ($filter['name'] == 'p' || $filter['name'] == 'price_data'){
                    if ($filter['min_price']){
                        $data['min_price'] = $currencies->display_price($filter['min_price'], 0);
                    }
                    if ($filter['max_price']) {
                        $data['max_price'] = $currencies->display_price($filter['max_price'], 0);
                    }
                    break;
                }
            }

            $filterItems = explode(';', $this->settings[0]['filter_items']);
            if (is_array($filterItems))
            foreach ($filterItems as $filterItem) {
                $item = str_replace('keywords-0', 'keywords', $filterItem);
                $item = str_replace('price-0', 'p', $item);
                $item = str_replace('category-0', 'cat', $item);
                $item = str_replace('attribute-', 'at', $item);
                $item = str_replace('property-', 'pr', $item);
                $data['added_filter_items'][] = $item;
            }

            $data['id'] = $this->id;

            $data['count'] = $count;

            if ($product_ids[0]) {
                $data['product_url'] = Yii::$app->urlManager->createUrl(['catalog/product', 'products_id' => $product_ids[0]]);
            }

            $data['list_url'] = preg_replace("/id=[0-9]+/", '', $data['filters_url_full']);
            $data['list_url'] = preg_replace("/get_json=1/", '', $data['list_url']);
            $data['list_url'] = preg_replace("/\&$/", '', $data['list_url']);
            $data['list_url'] = preg_replace("/\?$/", '', $data['list_url']);

            $data = array_merge($data, [
                'jsonData' => addslashes(json_encode($data))
            ]);

            return IncludeTpl::widget([
                'file' => 'boxes/filters-simple.tpl',
                'params' => $data
            ]);
        }
    }

}
