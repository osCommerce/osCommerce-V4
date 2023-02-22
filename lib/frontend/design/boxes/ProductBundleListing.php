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
use common\classes\design;
use frontend\design\IncludeTpl;
use frontend\design\Info;

class ProductBundleListing extends ProductListing
{

    public function run()
    {
        $productList = [];

        self::$listType = design::pageName(Info::listType($this->settings[0]));

        $itemStructure = static::getItemData(self::$listType);

        $cssClass = 'products-listing product-listing';

        if ($this->settings[0]['col_in_row'] && Info::get_gl() == 'grid'){
            $cssClass .= ' cols-' . $this->settings[0]['col_in_row'];
        } else {
            $cssClass .= ' cols-1';
        }
        $cssClass .= ' list-' . self::$listType;
        $cssClass .= ' w-list-' . self::$listType;

        Info::addBlockToWidgetsList('list-' . self::$listType);
        Info::addBlockToPageName(self::$listType);
        Info::addBoxToCss('products-listing');

        $html = '';
        $itmsArrey = [];
        foreach ($this->products as $product){
            $productList[$product['products_id']] = [
                'products_id' => $product['products_id']
            ];
            $item = '';
            $item .= '<div class="bundleComboItem" data-id="' . $product['products_id'] . '" data-name="' . $product['products_id'] . '"'.(isset($product['batchSelected']) && $product['batchSelected']?' data-batch-selected="1"':'').'>';

            $item .= static::createElementHtml([
                'name' => 'name',
                'class' => ''
            ], $product, $this->settings);

            $item .= static::createItem($itemStructure, $product, $this->settings);

            $item .= static::createElementHtml([
                'name' => 'price',
                'class' => ''
            ], $product, $this->settings);

            $item .= '</div>';
            $html .= $item;
            $itmsArrey[$product['products_id']] = $item;
            if (!$product['stock_indicator']['flags'] && $product['stock_indicator']) {
                $product['stock_indicator']['flags'] = $product['stock_indicator'];
            }
            if ( !isset(Info::$jsGlobalData['products']) || !isset(Info::$jsGlobalData['products'][$product['products_id']]) ) {
                Info::addJsData(['products' => [
                    $product['products_id'] => [
                        'products_id' => $product['products_id'],
                        'image' => $product['image'],
                        'image_alt' => $product['image_alt'],
                        'image_title' => $product['image_title'],
                        'srcset' => $product['srcset'],
                        'sizes' => $product['sizes'],
                        'products_name' => $product['products_name'],
                        'link' => $product['link'],
                        'is_virtual' => $product['is_virtual'],
                        'stock_indicator' => $product['stock_indicator'],
                        'product_has_attributes' => $product['product_has_attributes'],
                        'isBundle' => !$product['products_status_bundle'],
                        'bonus_points_price' => floor($product['bonus_points_price']),
                        'bonus_points_cost' => floor($product['bonus_points_cost']),
                        'product_in_cart' => $product['product_in_cart'],
                        'show_attributes_quantity' => $product['show_attributes_quantity'],
                        'in_wish_list' => $product['in_wish_list'],
                        'price' => [
                            'current' => $product['price'],
                            'special' => $product['price_special'],
                            'old' => $product['price_old'],
                        ],
                    ]
                ]]);
            }

            if ($product['in_wish_list']) {
                Info::addJsData(['productListings' => [
                    'wishList' => ['products' => [
                        $product['products_id'] => '1'
                    ]]
                ]]);
            }
        }

        Info::addJsData(['productListings' => [
            $this->settings['listing_type'] => [
                'productListing' => $productList,
                'itemElements' => static::itemElements($itemStructure)
            ],
        ]]);

        if ($this->settings['mainListing']) {
            Info::addJsData(['productListings' => [
                'mainListing' => $this->id
            ]]);
        }
        Info::addJsData(['widgets' => [
            $this->id => [
                'listingName' => $this->settings['listing_type'],
                'listingType' => self::$listType,
                'listingTypeCol' => design::pageName($this->settings[0]['listing_type']),
                'listingTypeRow' => design::pageName($this->settings[0]['listing_type_rows']),
                'listingTypeB2b' => design::pageName($this->settings[0]['listing_type_b2b']),
                'colInRow' => $this->settings[0]['col_in_row'],
                'colInRowSizes' => $this->settings['visibility']['col_in_row'],
                'colInRowCarousel' => $this->settings['colInRowCarousel'],
                'productListingCols' => $this->settings[0]['col_in_row'],
                'listingSorting' => \common\helpers\Output::xss_clean(Yii::$app->request->get('sort', '')),
                'productsOnPage' => (int)$this->params['listing_split']->number_of_rows_per_page ?? '',
                'pageCount' => (int)$this->params['listing_split']->current_page_number ?? '',
                'numberOfProducts' => (int)$this->params['listing_split']->number_of_rows ?? '',
                'fbl' => ($this->settings[0]['fbl'] && !Info::isAdmin() ? 1 : 0),
                'viewAs' => $this->settings[0]['view_as'],
            ]]]);

        Info::addJsData(['tr' => [
            'BOX_HEADING_COMPARE_LIST' => BOX_HEADING_COMPARE_LIST,
            'TEXT_LISTING_ADDED' => TEXT_LISTING_ADDED,
        ]]);

        if ($this->settings['onlyProducts']) {

            $returnData = [
                'entryData' => Info::$jsGlobalData,
                'html' => $html,
                'css' => self::getStyles()
            ];
            return json_encode($returnData);
        } elseif ($this->settings['productsInArray']) {
            return json_encode($itmsArrey);
        } else {
            return
                '<div class="' . $cssClass . '" data-listing-name="' . $this->settings['listing_type'] . '" data-listing-type="' . self::$listType . '" '.
                (!empty($this->settings[0]['listing_param'])?' data-listing-param="'.$this->settings[0]['listing_param'].'"':'').
                (!empty($this->settings[0]['listing_callback'])?' data-listing-callback="'.$this->settings[0]['listing_callback'].'"':'').
                '>' . $html . '</div>'.
                (!empty($this->settings[0]['listing_callback_js'])?$this->settings[0]['listing_callback_js']:'')
                ;
        }
    }

    public static function createItem($itemStructure, $product, $settings = [])
    {
        return BundleProducts::widget([
            'params' => [
                'products_id' => $product['products_id'],
                'settings' => $settings,
            ],
            'settings' => $settings,
        ]);
/*
        $html = '';
        foreach ($itemStructure as $element) {
            if ($element['name'] == 'BlockBox') {
                $html .= '<div class="type-' . $element['type'] . ' BlockBox ' . $element['class'] . '">';
                foreach ($element['children'] as $col) {
                    if ($element['type'] != 1) $html .= '<div class="col">';
                    $html .= static::createItem($col, $product, $settings);
                    if ($element['type'] != 1) $html .= '</div>';
                }
                $html .= '</div>';
            } else {
//                if (static::isSwitchOff($element['name'], $settings)) {
//                    continue;
//                }
                $html .= static::createElementHtml($element, $product, $settings);
            }
        }
        return $html;
*/
    }

    protected function createElementHtml($element, $product, $settings)
    {
        $html = '';
        $html .= '<div class="' . $element['name'] . ' ' . $element['class'] . '">';
        $html .= IncludeTpl::widget([
            'file' => 'boxes/listing-product/element/' . $element['name'] . '.tpl',
            'params' => [
                'settings' => $settings,
                'product' => $product,
                'element' => $element,
            ]
        ]);
        $html .= '</div>';
        return $html;
    }

}