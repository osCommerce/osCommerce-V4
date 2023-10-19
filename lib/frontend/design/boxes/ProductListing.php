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
use yii\helpers\ArrayHelper;
use frontend\design\IncludeTpl;
use frontend\design\ListingSql;
use frontend\design\SplitPageResults;
use frontend\design\Info;
use backend\design\Style;
use common\classes\design;

class ProductListing extends Widget
{

    public $id;
    public $file;
    public $params;
    public $settings;
    public $products;
    public static $styles;
    public static $listType;

    public function init()
    {
        parent::init();
        \common\helpers\Translation::init('catalog/product');

        Info::includeJsFile('boxes/ProductListing');
        Info::includeJsFile('boxes/ProductListing/applyItemData');
        Info::includeJsFile('boxes/ProductListing/applyItemImage');
        Info::includeJsFile('boxes/ProductListing/applyItemPrice');
        Info::includeJsFile('boxes/ProductListing/applyItemStock');
        Info::includeJsFile('boxes/ProductListing/applyItemAttributes');
        Info::includeJsFile('boxes/ProductListing/applyItemBuyButton');
        Info::includeJsFile('boxes/ProductListing/applyItemQtyInput');
        Info::includeJsFile('boxes/ProductListing/applyItemCompare');
        Info::includeJsFile('boxes/ProductListing/applyItemBatchSelect');
        Info::includeJsFile('boxes/ProductListing/applyItemBatchRemove');
        Info::includeJsFile('boxes/ProductListing/applyItemProductGroup');
        Info::includeJsFile('boxes/ProductListing/applyItem');
        Info::includeJsFile('boxes/ProductListing/carousel');
        Info::includeJsFile('boxes/ProductListing/updateAttributes');
        Info::includeJsFile('boxes/ProductListing/addProductToCart');
        Info::includeJsFile('boxes/ProductListing/alignItems');
        Info::includeJsFile('boxes/ProductListing/productListingCols');
        Info::includeJsFile('boxes/ProductListing/fbl');

        Info::includeJsFile('reducers/products');
        Info::includeJsFile('reducers/productListings');
        Info::includeJsFile('reducers/widgets');

        Info::includeJsFile('modules/helpers/getUprid');

        Info::includeExtensionJsFile('Quotations/js/productListing');
//        Info::includeExtensionJsFile('Samples/js/productListing');
        Info::addBoxToCss('quantity');
        Info::addBoxToCss('slick');

        Info::addJsData(['GROUPS_DISABLE_CHECKOUT' => defined('GROUPS_DISABLE_CART') ? GROUPS_DISABLE_CART : false]);
    }

    public function run()
    {
        $productList = [];

        self::$listType = design::pageName(Info::listType($this->settings[0]));

        $itemStructure = self::getItemData(self::$listType);

        $cssClass = 'products-listing product-listing';

        if (ArrayHelper::getValue($this->settings, [0, 'col_in_row']) && Info::get_gl() == 'grid'){
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
            $product['buttonArray'] = [
                $product['products_id'] => [
                    'buttonId' => ('b_atc_' . preg_replace('/[^\d]/', '_', $product['products_id'])),
                    'quantity' => '1',
                ]
            ];
            $item = '';
            $item .= '<div class="item" data-id="' . $product['products_id'] . '" data-name="' . $product['products_id'] . '">';
            $item .= static::createItem($itemStructure, $product, $this->settings);
            $item .= '</div>';
            $html .= $item;
            $itmsArrey[$product['products_id']] = $item;
            if (!$product['stock_indicator']['flags'] && $product['stock_indicator']) {
                $product['stock_indicator']['flags'] = $product['stock_indicator'];
            }
            if ( true || !isset(Info::$jsGlobalData['products']) || !isset(Info::$jsGlobalData['products'][$product['products_id']]) ) {
                Info::addJsData(['products' => [
                    $product['products_id'] => [
                        'products_id' => $product['products_id'],
                        'please_login' => $product['please_login'],
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
                        'isBundle' => $product['is_bundle'],
                        'bonus_points_price' => floor($product['bonus_points_price']),
                        'bonus_points_cost' => floor($product['bonus_points_cost']),
                        'product_in_cart' => $product['product_in_cart'],
                        'show_attributes_quantity' => (isset($product['show_attributes_quantity']) ? $product['show_attributes_quantity'] : 0),
                        'in_wish_list' => (isset($product['in_wish_list']) ? $product['in_wish_list'] : 0),
                        'price' => [
                            'current' => $product['price'] ?? 0,
                            'special' => (isset($product['price_special']) ? $product['price_special'] : 0),
                            'old' => (isset($product['price_old']) ? $product['price_old'] : 0),
                        ],
                        'products_model' => $product['products_model'],
                        'calculated_price' => $product['calculated_price'],
                        'calculated_price_exc' => $product['calculated_price_exc'],
                        'products_pctemplates_id' => $product['products_pctemplates_id'],
                    ]
                ]]);
            }

            if (isset($product['in_wish_list']) && $product['in_wish_list']) {
                Info::addJsData(['productListings' => [
                    'personalCatalog' => ['products' => [
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

        if (ArrayHelper::getValue($this->settings, 'mainListing')) {
            Info::addJsData(['productListings' => [
                'mainListing' => $this->id
            ]]);
        }
        Info::addJsData(['widgets' => [
            $this->id => [
                'listingName' => $this->settings['listing_type'],
                'listingType' => self::$listType,
                'listingTypeCol' => design::pageName(ArrayHelper::getValue($this->settings, [0,'listing_type'])),
                'listingTypeRow' => design::pageName(ArrayHelper::getValue($this->settings, [0,'listing_type_rows'])),
                'listingTypeB2b' => design::pageName(ArrayHelper::getValue($this->settings, [0,'listing_type_b2b'])),
                'colInRow' => ArrayHelper::getValue($this->settings, [0,'col_in_row']),
                'colInRowSizes' => ArrayHelper::getValue($this->settings, ['visibility', 'col_in_row'], 0),
                'colInRowCarousel' => ArrayHelper::getValue($this->settings, ['colInRowCarousel'], 0),
                'productListingCols' => ArrayHelper::getValue($this->settings, [0, 'col_in_row'], 0),
                'listingSorting' => \common\helpers\Output::xss_clean(Yii::$app->request->get('sort', '')),
                'productsOnPage' => ArrayHelper::getValue($this->params, 'listing_split.number_of_rows_per_page'),
                'pageCount' => (int)ArrayHelper::getValue($this->params, 'listing_split.current_page_number', 0),
                'numberOfProducts' => (int)ArrayHelper::getValue($this->params, 'listing_split.number_of_rows', 0),
                'fbl' => (isset($this->settings[0]['fbl']) && $this->settings[0]['fbl'] && !Info::isAdmin() ? 1 : 0),
                'viewAs' => (isset($this->settings[0]['view_as']) ? $this->settings[0]['view_as'] : 0),
                'showPopup' => (isset($this->settings[0]['show_popup']) ? $this->settings[0]['show_popup'] : 0),
                'hideAttributes' => ($this->settings[0]['show_attributes'] ?? ''),
        ]]]);

        Info::addJsData(['tr' => [
            'BOX_HEADING_COMPARE_LIST' => BOX_HEADING_COMPARE_LIST,
            'TEXT_LISTING_ADDED' => TEXT_LISTING_ADDED,
            'ENTRY_FIRST_NAME_MIN_LENGTH' => ENTRY_FIRST_NAME_MIN_LENGTH,
            'NAME_IS_TOO_SHORT' => NAME_IS_TOO_SHORT,
            'ENTER_VALID_EMAIL' => ENTER_VALID_EMAIL,
            'PLEASE_CHOOSE_ATTRIBUTES' => PLEASE_CHOOSE_ATTRIBUTES,
            'BACK_IN_STOCK' => BACK_IN_STOCK,
            'TEXT_NAME' => TEXT_NAME,
            'ENTRY_EMAIL_ADDRESS' => ENTRY_EMAIL_ADDRESS,
            'NOTIFY_ME' => NOTIFY_ME,
        ]]);

        if (!in_array(\common\components\google\widgets\GoogleTagmanger::getEvent(), ['indexPage', 'productPage'])) {
            \common\components\google\widgets\GoogleTagmanger::setEvent('productListing');
        }

        foreach (\common\helpers\Hooks::getList('box/product-listing') as $filename) {
            include($filename);
        }

        if (isset($this->settings['onlyProducts']) && $this->settings['onlyProducts']) {

            $returnData = [
                'entryData' => Info::$jsGlobalData,
                'html' => $html,
                'css' => self::getStyles()
            ];
            return json_encode($returnData);
        } elseif (isset($this->settings['productsInArray']) && $this->settings['productsInArray']) {
            return json_encode($itmsArrey);
        } else {
            if (!count($this->products)) {
                $html = '<div class="no-found">' . ITEM_NOT_FOUND . '</div>';
            }
            return
                '<div class="' . $cssClass . '" data-listing-name="' . $this->settings['listing_type'] . '" data-listing-type="' . self::$listType . '" '.
                (!empty($this->settings[0]['listing_param'])?' data-listing-param="'.$this->settings[0]['listing_param'].'"':'').
                (!empty($this->settings[0]['listing_callback'])?' data-listing-callback="'.$this->settings[0]['listing_callback'].'"':'').
                (!empty($this->settings[0]['listing_pre_callback'])?' data-listing-pre-callback="'.$this->settings[0]['listing_pre_callback'].'"':'').
                '>' . $html . '</div>'.
                (!empty($this->settings[0]['listing_callback_js'])?$this->settings[0]['listing_callback_js']:'')
                ;
        }
    }

    public static function createItem($itemStructure, $product, $settings = [])
    {
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
                if (self::isSwitchOff($element['name'], $settings)) {
                    continue;
                }
                $html .= '<div class="' . $element['name'] . (isset($element['class']) ? ' ' . $element['class']: '') . '">';
                //VL widgets from extensions to check
                if (strpos($element['name'], '\\') !== false ){
                  $ext_widget = \common\helpers\Acl::runExtensionWidget($element['name'] , [
                        'settings' => $settings,
                        'product' => $product,
                      ]);
                }
                if (strpos($element['name'], '\\') !== false && !empty($ext_widget)) {
                  $html .= $ext_widget;
                } else
                //VL widgets from extensions to check ==
                $html .= IncludeTpl::widget([
                    'file' => 'boxes/listing-product/element/' . $element['name'] . '.tpl',
                    'params' => [
                        'settings' => $settings,
                        'product' => $product,
                        'element' => $element,
                    ]
                ]);
                $html .= '</div>';

                if (isset($element['settings'][0])) {
                    Info::addJsData(['productListings' => [
                        $settings['listing_type'] => [
                            'itemElementSettings' => [
                                $element['name'] => $element['settings'][0],
                            ]
                        ],
                    ]]);
                }
            }
        }
        return $html;
    }

    public static function getStyles(){
        return \backend\design\Style::getStylesWrapper(self::$styles);
    }

    public static function getItemElements($name)
    {
        self::$listType = design::pageName($name);
        $itemStructure = self::getItemData($name);

        return static::itemElements($itemStructure);
    }

    protected static function itemElements($itemStructure)
    {
        $elements = [];

        foreach ($itemStructure as $element) {
            if ($element['name'] == 'BlockBox') {
                foreach ($element['children'] as $col) {
                    $elements = array_merge($elements, static::itemElements($col));
                }
            } else {
                $elements[$element['name']] = $element['name'];
            }
        }

        return $elements;
    }

    protected static function getItemData($name)
    {
        static $cache = [];
        if (isset($cache[$name])) {
            return $cache[$name];
        }

        defined('THEME_NAME') or define('THEME_NAME', 'theme-1');

        static $themeName = THEME_NAME;
        if (substr($name, 0, 6) != 'block-') {
            $themeName = THEME_NAME;
        }

        $elementsArray = [];
        $elements = \common\models\DesignBoxes::find()->where([
            'block_name' => \common\classes\design::pageName($name),
            'theme_name' => $themeName,
        ])->asArray()->orderBy('sort_order')->all();

        if (is_array($elements) && count($elements) == 0 && substr($name, 0, 6) != 'block-') {
            foreach (Info::$themeMap as $theme) {
                if ($theme == THEME_NAME || $theme == 'basic') {
                    continue;
                }
                $elements = \common\models\DesignBoxes::find()->where([
                    'block_name' => \common\classes\design::pageName($name),
                    'theme_name' => $theme,
                ])->asArray()->orderBy('sort_order')->all();
                if (is_array($elements) && count($elements)) {
                    $themeName = $theme;
                    break;
                }
            }
        }

        foreach ($elements as $element) {
            $item = [];
            $item['name'] =  str_replace('productListing\\', '', $element['widget_name']);

            $settings = [];
            $visibility = [];
            $settingsQuery = \common\models\DesignBoxesSettings::find()->where([
                'box_id' => $element['id'],
            ])->asArray()->all();
            foreach ($settingsQuery as $set) {
                if ($set['visibility'] > 0){
                    $visibility[$set['visibility']][$set['language_id']][$set['setting_name']] = $set['setting_value'];
                } else {
                    $settings[$set['language_id']][$set['setting_name']] = $set['setting_value'];
                    if (!in_array($set['setting_name'], Style::$attributesHaveRules)
                        && !in_array($set['setting_name'], Style::$attributesNoRules)
                        && !in_array($set['setting_name'], Style::$attributesHasMeasure)
                    ) {
                        $item['settings'][$set['language_id']][$set['setting_name']] = $set['setting_value'];
                    }
                }
            }

            $type = (isset($settings[0]['block_type']) ? $settings[0]['block_type'] : '');
            $block_id = $element['id'];
            if (isset($settings[0]['style_class']) && $settings[0]['style_class']) {
                $item['class'] = $settings[0]['style_class'];
            } elseif ($element['widget_name'] == 'BlockBox') {
                $item['class'] = 'bb-' . $element['id'];
            }

            self::fieldStyles($item, $settings, $visibility);

            if ($element['widget_name'] == 'BlockBox') {
                $item['children'] = [];
                $item['type'] = $type;
                if ($type == 2 || $type == 4 || $type == 5 || $type == 6 || $type == 7 || $type == 9 || $type == 10 || $type == 11 || $type == 12){
                    $item['children'][] = self::getItemData('block-' . $block_id);
                    $item['children'][] = self::getItemData('block-' . $block_id . '-2');
                } elseif ($type == 3 || $type == 8 || $type == 13){
                    $item['children'][] = self::getItemData('block-' . $block_id);
                    $item['children'][] = self::getItemData('block-' . $block_id . '-2');
                    $item['children'][] = self::getItemData('block-' . $block_id . '-3');
                } elseif ($type == 14){
                    $item['children'][] = self::getItemData('block-' . $block_id);
                    $item['children'][] = self::getItemData('block-' . $block_id . '-2');
                    $item['children'][] = self::getItemData('block-' . $block_id . '-3');
                    $item['children'][] = self::getItemData('block-' . $block_id . '-4');
                } elseif ($type == 15){
                    $item['children'][] = self::getItemData('block-' . $block_id);
                    $item['children'][] = self::getItemData('block-' . $block_id . '-2');
                    $item['children'][] = self::getItemData('block-' . $block_id . '-3');
                    $item['children'][] = self::getItemData('block-' . $block_id . '-4');
                    $item['children'][] = self::getItemData('block-' . $block_id . '-5');
                } elseif ($type == 1){
                    $item['children'][] = self::getItemData('block-' . $block_id);
                }
            }
            $elementsArray[] = $item;
        }
        $cache[$name] = $elementsArray;

        return $elementsArray;
    }

    private static function isSwitchOff($elementName, $settings){
        switch ($elementName) {
            case 'name':           return (isset($settings[0]['show_name']) && $settings[0]['show_name'] ? true : false);
            case 'image':          return (isset($settings[0]['show_image']) && $settings[0]['show_image'] ? true : false);
            case 'stock':          return (isset($settings[0]['show_stock']) && $settings[0]['show_stock'] ? true : false);
            case 'description':    return (isset($settings[0]['show_description']) && $settings[0]['show_description'] ? true : false);
            case 'model':          return (isset($settings[0]['show_model']) && $settings[0]['show_model'] ? true : false);
            case 'properties':     return (isset($settings[0]['show_properties']) && $settings[0]['show_properties'] ? true : false);
            case 'rating':         return (isset($settings[0]['show_rating']) && $settings[0]['show_rating'] ? true : false);
            case 'ratingCounts':   return (isset($settings[0]['show_rating_counts']) && $settings[0]['show_rating_counts'] ? true : false);
            case 'price':          return (isset($settings[0]['show_price']) && $settings[0]['show_price'] ? true : false);
            case 'bonusPoints':    return (isset($settings[0]['show_bonus_points']) && $settings[0]['show_bonus_points'] ? true : false);
            case 'buyButton':      return (isset($settings[0]['show_buy_button']) && $settings[0]['show_buy_button'] ? true : false);
          //case 'quoteButton':    return (isset($settings[0]['show_qty_input']) && $settings[0]['show_qty_input'] ? true : false);
          //case 'sampleButton':   return (isset($settings[0]['']) && $settings[0][''] ? true : false);
            case 'qtyInput':       return (isset($settings[0]['show_qty_input']) && $settings[0]['show_qty_input'] ? true : false);
            case 'viewButton':     return (isset($settings[0]['show_view_button']) && $settings[0]['show_view_button'] ? true : false);
            case 'wishlistButton': return (isset($settings[0]['show_wishlist_button']) && $settings[0]['show_wishlist_button'] ? true : false);
            case 'compare':        return (isset($settings[0]['show_compare']) && $settings[0]['show_compare'] ? true : false);
            case 'attributes':     return (isset($settings[0]['show_attributes']) && $settings[0]['show_attributes'] ? true : false);
            case 'paypalButton':   return (isset($settings[0]['show_paypal_button']) && $settings[0]['show_paypal_button'] ? true : false);
            case 'amazonButton':   return (isset($settings[0]['show_amazon_button']) && $settings[0]['show_amazon_button'] ? true : false);
            default: return false;
        }
    }

    private static function fieldStyles($item, $settings, $visibility){
        $name = '.' . $item['name'] . (isset($item['class']) && !empty($item['class']) ? '.' . $item['class'] : '');
        $mediaArr = \common\models\ThemesSettings::find()
            ->cache(Style::STYLE_CACHE_LIFETIME)
            ->where([
                'theme_name' => THEME_NAME,
                'setting_name' => 'media_query',
            ])->orderBy('setting_value')->asArray()->all();

        $style = Style::getAttributes(@$settings[0]);
        $hover = Style::getAttributes(@$visibility[1][0]);
        $active = Style::getAttributes(@$visibility[2][0]);
        $before = Style::getAttributes(@$visibility[3][0]);
        $after = Style::getAttributes(@$visibility[4][0]);
        if (!isset(self::$styles[0])) {
            self::$styles[0] = '';
        }
        if ($style) {
            self::$styles[0] .= '.list-' . self::$listType . ' ' . $name . '{' . $style . '}';
        }
        if ($hover) {
            self::$styles[0] .= '.list-' . self::$listType . ' ' . $name . ':hover{' . $hover . '}';
        }
        if ($active) {
            self::$styles[0] .= '.list-' . self::$listType . ' ' . $name . '.active{' . $active . '}';
        }
        if ($before) {
            self::$styles[0] .= '.list-' . self::$listType . ' ' . $name . ':before{' . $before . '}';
        }
        if ($after) {
            self::$styles[0] .= '.list-' . self::$listType . ' ' . $name . ':after{' . $after . '}';
        }
        foreach ($mediaArr as $item2){
            if (!isset(self::$styles[$item2['id']])) {
                self::$styles[$item2['id']] = '';
            }
            $style = Style::getAttributes(@$visibility[$item2['id']][0]);
            if ($style){
                self::$styles[$item2['id']] .= '.list-' . self::$listType . ' ' . $name . '{' . $style . '}';
            }
            if (isset($visibility[$item2['id']][0]['schema']) && $visibility[$item2['id']][0]['schema']){
                self::$styles[$item2['id']] .= \backend\design\Style::schema(
                    $visibility[$item2['id']][0]['schema'],
                    '.list-' . self::$listType . ' ' . $name
                );
                //self::$styles[$item2['id']] .= $this->schema($visibility[$item2['id']][0]['schema'], $item['id']);
            }
        }
    }
}
