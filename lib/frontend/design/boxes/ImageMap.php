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
use frontend\design\Info;
use backend\design\Style;

class ImageMap extends Widget
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
        if (false === \common\helpers\Acl::checkExtensionAllowed('ImageMaps', 'allowed')) {
            return '';
        }
        $languages_id = \Yii::$app->settings->get('languages_id');

        Info::addBlockToWidgetsList('image-maps');

        if ($this->params['mapsId']) {
            $mapsId = $this->params['mapsId'];
        } else {
            $mapsId = (isset($this->settings[0]['maps_id']) ? $this->settings[0]['maps_id'] : 0);
        }

        $map = \common\extensions\ImageMaps\models\ImageMaps::findOne($mapsId);

        if (!isset($map->image) || !is_file(Yii::getAlias('@webroot') . '/images/maps/' . $map->image)){
            return '';
        } else {
            $image = \common\helpers\Media::getAlias('@webCatalogImages/maps/'.$map->image);
        }

        $mapData = json_decode($map->svg_data, true);

        $paramsArr = [];
        $svg = '';
        $productIds = [];
        $categoryIds = [];
        $infoIds = [];
        $brandIds = [];
        $deliveryIds = [];
        $counter = 0;
        $allItems = [];
        $defaultLinks = [];
        foreach ($mapData['areas'] as $mapItem) {
            $counter++;

            $params = Style::paramsFromOneInput($mapItem['attributes']);

            $paramsFiltered = [];
            foreach ($params as $paramName => $paramValue){
                if(is_array($paramValue) && in_array($paramName, ['title', 'description', 'title_c', 'description_c'])) {
                    $paramValue = $paramValue[$languages_id];
                }
                if ($paramValue || is_array($paramValue)) {
                    $paramsFiltered[$paramName] = $paramValue;
                }
            }
            $paramsFiltered['id'] = $counter;
            $allItems[] = $paramsFiltered;

            switch ($params['link']) {
                case 'link-to-product':
                    if (is_array($params['products_id'])){
                        $productIds = array_merge($productIds,$params['products_id']);
                        $itemId = implode(',', $params['products_id']);
                    } else {
                        $productIds[] = $params['products_id'];
                        $itemId = $params['products_id'];
                    }
                    $type = 'products';
                    break;
                case 'link-to-category':
                    if (is_array($params['categories_id'])){
                        $categoryIds = array_merge($categoryIds,$params['categories_id']);
                        $itemId = implode(',', $params['categories_id']);
                    } else {
                        $categoryIds[] = $params['categories_id'];
                        $itemId = $params['categories_id'];
                    }
                    $type = 'categories';
                    break;
                case 'link-to-info':
                    if (is_array($params['information_id'])){
                        $infoIds = array_merge($infoIds,$params['information_id']);
                        $itemId = implode(',', $params['information_id']);
                    } else {
                        $infoIds[] = $params['information_id'];
                        $itemId = $params['information_id'];
                    }
                    $type = 'info';
                    break;
                case 'link-to-brand':
                    if (is_array($params['brand_id'])){
                        $brandIds = array_merge($brandIds,$params['brand_id']);
                        $itemId = implode(',', $params['brand_id']);
                    } else {
                        $brandIds[] = $params['brand_id'];
                        $itemId = $params['brand_id'];
                    }
                    $type = 'brands';
                    break;
                case 'link-to-delivery':
                    if (is_array($params['delivery_id'])){
                        $deliveryIds = array_merge($deliveryIds,$params['delivery_id']);
                        $itemId = implode(',', $params['delivery_id']);
                    } else {
                        $deliveryIds[] = $params['delivery_id'];
                        $itemId = $params['delivery_id'];
                    }
                    $type = 'locations';
                    break;
                case 'link-to-common':
                    $itemId = $counter;
                    $commonLinks[] = $paramsFiltered;
                    $type = 'common';

                    break;
                case '':
                default:
                    $itemId = $counter;
                    $defaultLinks[] = $paramsFiltered;
                    $type = 'defaults';
                    break;
            }

            $params['id'] = $itemId;
            $paramsArr[] = $params;

            if ($mapItem['type'] == 'rectangle') {

                $svg .= '<rect x="' . $mapItem['coords']['x'] . '" y="' . $mapItem['coords']['y'] . '" width="' . $mapItem['coords']['width'] . '" height="' . $mapItem['coords']['height'] . '" data-id="' . $itemId . '" data-type="' . $type . '"></rect>';

            } elseif ($mapItem['type'] == 'circle') {

                $svg .= '<circle cx="' . $mapItem['coords']['cx'] . '" cy="' . $mapItem['coords']['cy'] . '" r="' . $mapItem['coords']['radius'] . '" data-id="' . $itemId . '" data-type="' . $type . '"></circle>';

            } elseif ($mapItem['type'] == 'polygon') {

                $points = '';
                foreach ($mapItem['coords']['points'] as $point) {
                    $points .= ' ' . $point['x'] . ' ' . $point['y'];
                }
                $svg .= '<polygon points="' . $points . '" data-id="' . $itemId . '" data-type="' . $type . '"></polygon>';

            }

        }

        $size = getimagesize(Yii::getAlias('@webroot') . '/images/maps/' . $map->image);

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" version="1.2" baseProfile="tiny" id="svg" viewBox="0 0 ' . $size[0] . ' ' . $size[1] . '" preserveAspectRatio="xMinYMin meet">' . $svg . '</svg>';



        $q = new \common\components\ProductsQuery([
          'customAndWhere' =>  ['p.products_id' => $productIds],
          'currentCategory' => false,
          'orderBy' =>  ['p.products_id' => SORT_ASC],
        ]);

        $this->settings['listing_type'] = 'image-map';
        $products = Info::getListProductsDetails($q->buildQuery()->allIds(), $this->settings);


        //$products = Yii::$container->get('products')->getAllProducts($this->settings['listing_type']);

        $categories = \common\models\Categories::find()
            ->select([
                'id' => 'categories.categories_id',
                'image' => 'categories.categories_image',
                'title' => 'd.categories_name',
                'description' => 'd.categories_description',
            ])
            ->joinWith([
                'descriptions d' => function ($query) {
                    $query->onCondition(['language_id' => \Yii::$app->settings->get('languages_id')])
                        ->select(['d.categories_name']);
                },
            ])
            ->where(['d.categories_id' => $categoryIds])
            ->asArray()
            ->all();


        $categoriesArr = [];
        foreach ($categories as $category) {

            $category['href'] = tep_href_link('catalog', 'cPath=' . $category['id']);
            $category['img'] = \common\helpers\Media::getAlias('@webCatalogImages/'.$category['image']);
            if (!is_file(Yii::getAlias('@webroot') . '/images/' . $category['image'])) {
                $category['img'] = \frontend\design\Info::themeSetting('na_category', 'hide');
            }
            $category['description'] = substr(strip_tags($category['description']), 0, 200);
            unset($category['descriptions']);

            $categoriesArr[] = $category;
        }


        $info = \common\models\Information::find()
            ->select([
                'id' => 'information_id',
                'title' => 'info_title',
                'description',
            ])
            ->where([
                'languages_id' => (int)$languages_id,
                'information_id' => $infoIds,
                'platform_id' => (int)PLATFORM_ID
            ])
            ->asArray()
            ->all();
        $infoArr = [];
        foreach ($info as $item) {
            $item['description'] = substr(strip_tags($item['description']), 0, 200);
            $infoArr[] = $item;
        }


        $brands = \common\models\Manufacturers::find()
            ->select([
                'id' => 'manufacturers_id',
                'title' => 'manufacturers_name',
                'image' => 'manufacturers_image',
            ])
            ->where(['manufacturers_id' => $brandIds])
            ->asArray()
            ->all();
        $brandsArr = [];
        foreach ($brands as $brand) {
            $brand['img'] = \common\helpers\Media::getAlias('@webCatalogImages/'.$brand['image']);
            if (!is_file(Yii::getAlias('@webroot') . '/images/' . $brand['image'])) {
                $brand['img'] = \frontend\design\Info::themeSetting('na_category', 'hide');
            }
            $brandsArr[] = $brand;
        }


        $locations = \common\models\SeoDeliveryLocation::find()
            ->from(['dl' => \common\models\SeoDeliveryLocation::tableName()])
            ->select([
                'id' => 'dl.id',
                'title' => 't.location_name',
                'image' => 'dl.image_listing',
                'description' => 't.location_description',
            ])
            ->joinWith([
                'locationText t' => function ($query) {
                    $query->onCondition(['t.language_id' => \Yii::$app->settings->get('languages_id')])
                        ->select([
                            'text' => 't.location_name',
                        ]);
                }
            ])
            ->where([
                'dl.id' => $deliveryIds,
            ])
            ->asArray()
            ->all();
        $locationsArr = [];
        foreach ($locations as $location) {
            $location['img'] = \common\helpers\Media::getAlias('@webCatalogImages/'.$location['image']);
            if (!is_file(Yii::getAlias('@webroot') . '/images/' . $location['image'])) {
                $location['img'] = \frontend\design\Info::themeSetting('na_category', 'hide');
            }
            $location['description'] = substr(strip_tags($location['description']), 0, 200);
            $locationsArr[] = $location;
        }

        return IncludeTpl::widget(['file' => 'boxes/image-map.tpl', 'params' => [
            'image' => $image,
            'svg' => $svg,
            'products' => $products,
            'allItemsJson' => json_encode($allItems),
            'defaultLinks' => $defaultLinks,
            'commonLinks' => $commonLinks,
            'categories' => $categoriesArr,
            'info' => $infoArr,
            'brands' => $brandsArr,
            'locations' => $locationsArr,
            'settings' => $this->settings,
            'id' => $this->id,
        ]]);
    }
}