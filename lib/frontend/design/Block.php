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

namespace frontend\design;

use backend\design\Style;
use frontend\design\Info;
use Yii;
use yii\base\Widget;
use yii\helpers\ArrayHelper;
use common\models\DesignBoxes;
use common\models\DesignBoxesTmp;
use common\models\DesignBoxesSettings;
use common\models\DesignBoxesSettingsTmp;
use common\models\ThemesSettings;
use common\models\DesignBoxesCache;

class Block extends Widget
{
    public $name;
    public $params;
    public $theme_name;
    public static $widgetsList = [];

    private static $media_query = [];
    private static $mediaNames = [];
    private static $maxWidth = [];

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        global $block_styles, $allWidgetsOnPage;
        $adminDesign = null;
        if ($this->params['params']['theme_name']?? false) {
            $this->theme_name = $this->params['params']['theme_name'];
        } else {
            $this->theme_name = THEME_NAME;
        }

        if (defined("DESIGN_BOXES_CACHE")) {
            $designBoxesCacheSetting = DESIGN_BOXES_CACHE;
        } else {
            $designBoxesCacheSetting = 'json';
        }

        if (isset($this->params['params']['blockTreeData'])) {
            $treeData = $this->params['params']['blockTreeData'];
        } elseif (Info::isAdmin()) {
            $this->mediaSettings();
            $treeData = $this->treeData($this->name, $this->theme_name);
        } else {
            $this->mediaSettings();

            $designBoxesCache = DesignBoxesCache::findOne([
                'block_name' => $this->name,
                'theme_name' => $this->theme_name,
            ]);

            if ($designBoxesCache) {
                $cache = $designBoxesCache->$designBoxesCacheSetting;
            } else {
                $designBoxesCache = new DesignBoxesCache();
            }

            if (isset($cache) && $cache != '') {
                $treeData = ($designBoxesCacheSetting == 'json' ? json_decode($cache, true) : unserialize($cache));
            } else {
                $treeData = $this->treeData($this->name, $this->theme_name);
                $designBoxesCache->block_name = $this->name;
                $designBoxesCache->theme_name = $this->theme_name;
                if ($designBoxesCacheSetting == 'json'){
                    $designBoxesCache->json = json_encode($treeData);
                    $designBoxesCache->serialize = $designBoxesCache->serialize ?? '';
                } else {
                    $designBoxesCache->json = $designBoxesCache->json ?? json_encode([]);
                    $designBoxesCache->serialize = serialize($treeData);
                }
                $designBoxesCache->date_modified = new \yii\db\Expression('NOW()');
                $designBoxesCache->save();
            }
        }

        if (!(isset($treeData) && is_array($treeData) && count($treeData) > 0) && !Info::isAdmin()) {
            return '';
        }

        $blockHtml = '';

        foreach ($treeData as $widget) {

            if ($this->hideWidget($widget['settings'])) continue;

            $widgetName = 'frontend\design\boxes\\' . $widget['widget_name'];
            $widgetArray['params'] = (isset($this->params['params']) ? $this->params['params'] : []);
            $widgetArray['id'] = $widget['id'];
            $widgetArray['settings'] = $widget['settings'];
            $widgetArray['params']['blockTreeData'] = $widget['children'] ?? null;
            $widgetArray['params']['themeName'] = $this->theme_name;
            $widgetArray['params']['microtime'] = $widget['microtime'];
            $settings = $widget['settings'];

            if (Yii::$app->id != 'app-console') {
                $toPdf = (int)Yii::$app->request->get('to_pdf', 0);
                if ($toPdf) {
                    $settings[0]['p_width'] = Info::blockWidth($widget['id']);
                }
            }

            if (isset($settings[0]['ajax']) && !Info::isAdmin()){
                $widgetHtml = '
<div class="preloader"></div>
<script type="text/javascript">
  tl(function(){
    $.get("' . tep_href_link('get-widget/one') . '", {
          id: "' . $widget['id'] . '",
          action: "' . Yii::$app->controller->id . '/' . Yii::$app->controller->action->id . '",
          ' . (count($_GET) > 0 ? str_replace('{', '', str_replace('}', '', json_encode($_GET))) : '') . '
    }, function(d){
      $("#box-' . $widget['id'] . '").html(d)
    })
  });
</script>
';
            } else {
                if (is_file(DIR_FS_CATALOG . 'lib' . DIRECTORY_SEPARATOR . 'frontend' . DIRECTORY_SEPARATOR . 'design' . DIRECTORY_SEPARATOR . 'boxes' . DIRECTORY_SEPARATOR .  str_replace('\\', DIRECTORY_SEPARATOR, $widget['widget_name']) . '.php')){
                    $widgetHtml = $widgetName::widget($widgetArray);
                } elseif (($ext_widget = \common\helpers\Acl::runExtensionWidget($widget['widget_name'], $widgetArray)) !== false){
                    $widgetHtml = $ext_widget;
                } elseif (is_file(Yii::getAlias('@backend') . DIRECTORY_SEPARATOR . 'design' . DIRECTORY_SEPARATOR . 'orders' . DIRECTORY_SEPARATOR .  str_replace('\\', DIRECTORY_SEPARATOR, $widget['widget_name']) . '.php')) {
                    if ($this->params['params']['manager']?? false) {
                        $widgetHtml = $this->params['params']['manager']->render($widget['widget_name'], [
                            'manager' => $this->params['params']['manager'],
                            'order' => $this->params['params']['order']
                        ]);
                    } else {
                        $widgetHtml = '';
                    }
                    $adminDesign = true;
                } elseif ($this->params['params'][$widget['widget_name']] ?? false) {
                    $widgetHtml = $this->params['params'][$widget['widget_name']];
                    $adminDesign = true;
                } else {
                    $widgetHtml = '';
                }
            }
            if (isset($settings[0]['style_class'])) {
                $styleClass = $settings[0]['style_class'];
                $styleClass = preg_replace('/[\s]+/', ' ', $styleClass);
                $classes = explode(' ', $styleClass);
                foreach ($classes as $class) {
                    Info::addCustomClassToCss($class);
                }
            }

            $assetName = '\frontend\assets\boxes\\' . $widget['widget_name'] . 'Asset';
            if (class_exists($assetName)){
                $assetName::register($this->view);
            }

            self::addToWidgetsList($widget['widget_name']);

            if (!isset($settings[0]['ajax'])) {
                $allWidgetsOnPage[$widget['widget_name']] = $widget['widget_name'];
            }

            $page_block = $this->params['params']['page_block'] ?? Info::pageBlock();

            if ($widgetHtml != '' || Info::isAdmin()) {
                $blockHtml .=
                    '<div class="box' .
                    ($widget['widget_name'] == 'BlockBox' || $widget['widget_name'] == 'Tabs' || $widget['widget_name'] == 'invoice\Container' || $widget['widget_name'] == 'email\BlockBox' || $widget['widget_name'] == 'ClosableBox' ? '-block type-' . (isset($settings[0]['block_type']) ? $settings[0]['block_type'] : '') : '') .
                    ($widget['widget_name'] == 'Tabs' ? ' tabs' : '') .
                    (isset($settings[0]['style_class']) ? ' ' . $settings[0]['style_class'] : '') .
                    self::nameToClass($widget['widget_name']) .
                    self::inlineBlockHTags(@$settings[0]['hTagsInline']) .
                    '" ' .
                    ($widget['widget_name'] == 'BlockBox' && $widget['widget_params'] ? ' data-placeholder="' . $widget['widget_params'] . '"' : '') .
                    ($page_block == 'orders' || $page_block == 'email' || $page_block == 'packingslip' || $page_block == 'invoice' || $page_block == 'pdf' || $page_block == 'pdf_cover' || $page_block == 'gift_card' || $page_block == 'trade_form_pdf' || $page_block == 'trade_form_direct_debit_pdf' || @$this->params['params']['inline_styles'] ? self::styles($settings, true, $this->theme_name) : '') . ' data-name="' . $widget['widget_name'] . '" id="box-' . $widget['id'] . '">';
            }
            $style = self::styles($settings, false, $this->theme_name);
            $hover = self::styles(@$settings['visibility'][1], false, $this->theme_name);
            $active = self::styles(@$settings['visibility'][2], false, $this->theme_name);
            $before = self::styles(@$settings['visibility'][3], false, $this->theme_name);
            $after = self::styles(@$settings['visibility'][4], false, $this->theme_name);
            if (!isset($block_styles[0])) {
                $block_styles[0] = '';
            }
            if ($style) {
                $block_styles[0] .= '#box-' . $widget['id'] . '{' . $style . '}';
            }
            if ($hover) {
                $block_styles[0] .= '#box-' . $widget['id'] . ':hover{' . $hover . '}';
            }
            if ($active) {
                $block_styles[0] .= '#box-' . $widget['id'] . '.active{' . $active . '}';
            }
            if ($before) {
                $block_styles[0] .= '#box-' . $widget['id'] . ':before{' . $before . '}';
            }
            if ($after) {
                $block_styles[0] .= '#box-' . $widget['id'] . ':after{' . $after . '}';
            }
            if (isset($settings[0]['col_in_row']) && $settings[0]['col_in_row']){
                $block_styles[0] .= $this->colInRow($settings[0]['col_in_row'], $widget['id']);
            }
            foreach (self::$media_query as $item2){
                if (!isset($block_styles[$item2['id']])) {
                    $block_styles[$item2['id']] = '';
                }
                $style = self::styles(@$settings['visibility'][$item2['id']], false, $this->theme_name);
                if ($style){
                    $block_styles[$item2['id']] .= '#box-' . $widget['id'] . '{' . $style . '}';
                }
                if (isset($settings['visibility'][$item2['id']][0]['only_icon']) && $settings['visibility'][$item2['id']][0]['only_icon']){
                    $block_styles[$item2['id']] .= '#box-' . $widget['id'] . ' .no-text {display:none;}';
                }
                if (isset($settings['visibility'][$item2['id']][0]['schema']) && $settings['visibility'][$item2['id']][0]['schema']){
                    $block_styles[$item2['id']] .= \backend\design\Style::schema($settings['visibility'][$item2['id']][0]['schema'], '#box-' . $widget['id']);
                }
                if (isset($settings['visibility'][$item2['id']][0]['col_in_row']) && $settings['visibility'][$item2['id']][0]['col_in_row']){
                    $block_styles[$item2['id']] .= $this->colInRow($settings['visibility'][$item2['id']][0]['col_in_row'], $widget['id']);
                }
            }

            if ($widgetHtml == ''){
                if (Info::isAdmin() && !$adminDesign) {
                    $pos = strripos($widget['widget_name'], '\\') + 1;
                    $prefix = '<span class="no-widget-prefix">' . substr($widget['widget_name'], 0, $pos) . '</span>';
                    $widgetName = '<span class="no-widget-title">' .substr($widget['widget_name'], $pos) . '</span>';
                    $blockHtml .= '
                    <div class="no-widget-name" title="' . $widget['widget_name'] . '">
                        <span class="no-widget-text">
                        ' . sprintf('Here added %s  widget', '</span>' . $prefix . $widgetName . '<span class="no-widget-text">') . '
                        </span>
                    </div>';
                }
            } else {
                $blockHtml .= $widgetHtml;
            }
            if ($widgetHtml != '' || Info::isAdmin()) $blockHtml .= '</div>';
        }

        $blockOpen = '<div class="block' . (isset($this->params['type']) ? ' ' . $this->params['type'] : '') . '"' . (Info::isAdmin() ? ' data-name="' . $this->name . '"' . (isset($this->params['type']) ? ' data-type="' . $this->params['type'] . '"' : '') . (isset($this->params['cols']) ? ' data-cols="' . $this->params['cols'] . '"' : '') : '') . (isset($this->params['tabs']) ? ' id="tab-' . $this->name . '"' : '') . '>';

        if ($blockHtml){
            $blockHtml = $blockOpen . $blockHtml . '</div>';
        } elseif (Info::isAdmin()) {
            $blockHtml = $blockOpen . '&nbsp;</div>';
        }

        return $blockHtml;
    }

    private function treeData($blockName, $theme_name)
    {
        if (Info::isAdmin()) {
            $designBoxes = DesignBoxesTmp::find();
            $designBoxesSettings = DesignBoxesSettingsTmp::find();
        } else {
            $designBoxes = DesignBoxes::find();
            $designBoxesSettings = DesignBoxesSettings::find();
        }

        $boxes = $this->getBoxes($designBoxes, $blockName);

        foreach ($boxes as $key => $box) {
            $boxes[$key]['settings'] = $this->getBoxesSettings($designBoxesSettings, $box);

            if (is_file(Yii::getAlias('@frontend') . DIRECTORY_SEPARATOR . 'design' . DIRECTORY_SEPARATOR . 'boxes' . DIRECTORY_SEPARATOR .  str_replace('\\', DIRECTORY_SEPARATOR, $box['widget_name']) . '.php')){
                $widget = 'frontend\design\boxes\\' . $box['widget_name'];
                if (method_exists ($widget, 'children')) {
                    foreach ($widget::children($box['id'], $boxes[$key]['settings'], $theme_name) as $child) {
                        $boxes[$key]['children'][$child] = $this->treeData($child, $theme_name);
                    }
                }
            }
        }

        return $boxes;
    }

    private function getBoxes($designBoxes, $blockName)
    {
        $boxes = $designBoxes
            ->where([
                'theme_name' => $this->theme_name,
                'block_name' => $blockName,
            ])
            ->orderBy('sort_order')
            ->asArray()->all();

        if (!isset($boxes) || !is_array($boxes) || count($boxes) === 0) {

            if (Info::isAdmin() || substr($blockName, 0, 6) == 'block-') {
                return [];
            }

            foreach (Info::$themeMap as $theme) {
                if ($theme == $this->theme_name || $theme == 'basic') {
                    continue;
                }
                $boxes = $designBoxes
                    ->where([
                        'theme_name' => $theme,
                        'block_name' => $blockName,
                    ])
                    ->orderBy('sort_order')
                    ->asArray()->all();
                if (isset($boxes) && is_array($boxes) && count($boxes) > 0) {
                    $this->theme_name = $theme;
                    break;
                } else {
                    $boxes = [];
                }
            }
        }

        return $boxes;
    }

    private function getBoxesSettings($designBoxesSettings, $box)
    {

        $defaultLanguageId = \common\helpers\Language::get_default_language_id();
        $allLanguages = \Yii::$app->getCache()->getOrSet('block_languages_list',function(){
            return \common\helpers\Language::get_languages();
        },600);

        $boxSettings = $designBoxesSettings
            ->where([
                'box_id' => $box['id']
            ])
            ->asArray()->all();

        $settings = [];

        foreach ($boxSettings as $set) {
            if ($set['visibility'] > 0){
                $settings['visibility'][$set['visibility']][$set['language_id']][$set['setting_name']] = $set['setting_value'];
                if (isset($set['visibility']) && ArrayHelper::getValue(self::$mediaNames, $set['visibility'])) {
                    $settings['visibility'][$set['setting_name']][self::$mediaNames[$set['visibility']]] = $set['setting_value'];
                }
                if ($set['setting_name'] == 'col_in_row' && self::$maxWidth[$set['visibility']]) {
                    $colInRowCarousel[self::$maxWidth[$set['visibility']]] = $set['setting_value'];
                }
            } else {
                $settings[$set['language_id']][$set['setting_name']] = $set['setting_value'];
            }
        }

        if (isset($settings[$defaultLanguageId]) && is_array($settings[$defaultLanguageId])) {
            foreach ($settings[$defaultLanguageId] as $name => $value) {
                foreach ($allLanguages as $language) {
                    if (!isset($settings[$language['id']][$name])) {
                        $settings[$language['id']][$name] = $value;
                    }
                }
            }
        }

        $settings[0]['params'] = $box['widget_params'];
        if (isset($this->params['params']['page_block'])) {
            $settings[0]['page_block'] = $this->params['params']['page_block'];
        }
        $settings['colInRowCarousel'] = $colInRowCarousel ?? null;

        return $settings;
    }

    private function mediaSettings()
    {
        $mediaQueryArr = ThemesSettings::find()->where([
            'theme_name' => $this->theme_name,
            'setting_name' => 'media_query',
        ])->asArray()->all();

        foreach ($mediaQueryArr as $mediaQuery) {
            self::$media_query[] = $mediaQuery;
            self::$mediaNames[$mediaQuery['id']] = $mediaQuery['setting_value'];
            $sizes = explode('w', $mediaQuery['setting_value']);
            if (!$sizes[0] && $sizes[1]) {
                self::$maxWidth[$mediaQuery['id']] = $sizes[1];
            }
        }
    }

    private function hideWidget($settings)
    {
        if (Yii::$app->id == 'app-console' || Info::isAdmin()) {
            return false;
        }
        $cookies = Yii::$app->request->cookies;

        if (isset($settings[0]['status']) && $settings[0]['status'] == 'hidden') {
            return true;
        }

        $hide = false;
        foreach (\common\helpers\Hooks::getList('box/block/hide-widget') as $filename) {
            include($filename);
            if ($hide) {
                return true;
            }
        }

        if (
                (
                    @$settings[0]['visibility_first_view'] && Yii::$app->user->isGuest && !$cookies->has('was_visit') ||
                    @$settings[0]['visibility_more_view'] && Yii::$app->user->isGuest && $cookies->has('was_visit') ||
                    @$settings[0]['visibility_logged'] && !Yii::$app->user->isGuest ||
                    @$settings[0]['visibility_not_logged'] && Yii::$app->user->isGuest
                ) ||

                Yii::$app->controller->id == 'index' && Yii::$app->controller->action->id == 'index' && ($settings[0]['visibility_home'] ?? false) ||
                Yii::$app->controller->id == 'catalog' && Yii::$app->controller->action->id == 'product' && ($settings[0]['visibility_product'] ?? false) ||
                Yii::$app->controller->id == 'catalog' && Yii::$app->controller->action->id == 'index' && ($settings[0]['visibility_catalog'] ?? false) ||
                Yii::$app->controller->id == 'info' && Yii::$app->controller->action->id == 'index' && ($settings[0]['visibility_info'] ?? false) ||
                Yii::$app->controller->id == 'shopping-cart' && Yii::$app->controller->action->id == 'index' && ($settings[0]['visibility_cart'] ?? false) ||
                Yii::$app->controller->id == 'checkout' && Yii::$app->controller->action->id != 'success' && ($settings[0]['visibility_checkout'] ?? false) ||
                Yii::$app->controller->id == 'checkout' && Yii::$app->controller->action->id == 'success' && ($settings[0]['visibility_success'] ?? false) ||
                Yii::$app->controller->id == 'account' && Yii::$app->controller->action->id != 'login' && ($settings[0]['visibility_account'] ?? false) ||
                Yii::$app->controller->id == 'account' && Yii::$app->controller->action->id == 'login' && ($settings[0]['visibility_login'] ?? false)
            ){
            return true;
        } elseif(
                !(Yii::$app->controller->id == 'index' && Yii::$app->controller->action->id == 'index' ||
                    Yii::$app->controller->id == 'index' && Yii::$app->controller->action->id == 'design' ||
                    Yii::$app->controller->id == 'catalog' && Yii::$app->controller->action->id == 'product' ||
                    Yii::$app->controller->id == 'catalog' && Yii::$app->controller->action->id == 'index' ||
                    Yii::$app->controller->id == 'info' && Yii::$app->controller->action->id == 'index' ||
                    Yii::$app->controller->id == 'cart' && Yii::$app->controller->action->id == 'index' ||
                    Yii::$app->controller->id == 'checkout' && Yii::$app->controller->action->id != 'success' ||
                    Yii::$app->controller->id == 'checkout' && Yii::$app->controller->action->id == 'success' ||
                    Yii::$app->controller->id == 'account' && Yii::$app->controller->action->id != 'login' ||
                    Yii::$app->controller->id == 'account' && Yii::$app->controller->action->id == 'login') &&
                ($settings[0]['visibility_other'] ?? false)
            ) {
            return true;
        } else {
            return false;
        }
    }

    public static function getStyles(){
        global $block_styles;

        return \backend\design\Style::getStylesWrapper($block_styles);
    }

    public static function styles($settings, $teg = false, $theme_name = '')
    {
        $style = '';

        if (isset($settings[0]) && is_array($settings[0]) && $theme_name) {
            $mainStyles = Style::mainStyles($theme_name);

            foreach ($settings[0] as $key => $val) {
                if (isset($val) && isset($mainStyles[$val])) {
                    $settings[0][$key] = $mainStyles[$val];
                }
            }
        }

        $style .= \backend\design\Style::getAttributes(@$settings[0]);

        if (isset($settings[0]['display_none']) && $settings[0]['display_none']){
            $style .= 'display:none;';
        }
        if (isset($settings[0]['status']) && $settings[0]['status'] == 'hidden'){ // for admin area
            $style .= 'opacity:0.5;z-index:1000;';
        }

        if (isset($settings[0]['box_align']) && $settings[0]['box_align']){
            if ($settings[0]['box_align'] == 1){
                $style .= 'float: left;clear: none;';
            }
            if ($settings[0]['box_align'] == 2){
                $style .= 'display: inline-block;';
            }
            if ($settings[0]['box_align'] == 3){
                $style .= 'float: right;clear: none;';
            }
        }


        if ($style && $teg) {
            $style = ' style="' . $style . '"';
        }


        return $style;
    }

    public function colInRow($val, $id)
    {

        $htm = '#box-' . $id . ' .products-listing.cols-' . $val . ' div.item:nth-child(n){clear:none;width:' . round(100/$val, 4) . '%}';
        $htm .= '#box-' . $id . ' .products-listing.cols-' . $val . ' div.item:nth-child(' . $val . 'n+1){clear: both}';
        $htm .= '#box-' . $id . ' .products-listing.cols-1 div.item:nth-child(n){clear:none;width:100%}';
        $htm .= '#box-' . $id . ' .products-listing.cols-1 div.item{clear: both}';

        $htm .= '#box-' . $id . ' .items-list .item{width:' . (floor(10000/$val) / 100) . '%}';

        return $htm;
    }

    public static function nameToClass($name)
    {
        $class = preg_replace('/([A-Z])/', "-\$1", $name);
        $class = str_replace('\\', '-', $class);
        $class = ' w-' . $class;
        $class = str_replace('--', '-', $class);
        $class = strtolower($class);

        return $class;
    }

    public static function inlineBlockHTags($setting)
    {
        $class = '';
        if ($setting == 'inline') {
            $class = ' h-inline';
        }
        if ($setting == 'block') {
            $class = ' h-block';
        }

        return $class;
    }

    public static $blockNamesStr;

    public static function addBlockName($name)
    {
        if (substr($name, 0, 6) != 'block-' && $name != 'header' && $name != 'footer') {
            self::$blockNamesStr .= '_' . $name;
        }
    }

    public static function addToWidgetsList($name)
    {
        if (!in_array($name, self::$widgetsList)){
            self::$widgetsList[] = $name;
        }
    }

}
