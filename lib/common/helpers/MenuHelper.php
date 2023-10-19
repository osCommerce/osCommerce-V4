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

use common\models\MenuTitles;
use Yii;
use common\models\Menus;
use common\models\MenuItems;


class MenuHelper {

    public const MENU_CACHE_LIFETIME = 5;

    public static function getUrlByLinkId($link_id, $link_type) {
        switch ($link_type) {
            case 'default':
                if ($link_id == '8888886') {
                    return tep_href_link('/');
                } elseif ($link_id == '8888887') {
                    if (!Yii::$app->user->isGuest) {
                        return tep_href_link('account/logoff', '', 'SSL');
                    } else {
                        return tep_href_link('account/login', '', 'SSL');
                    }
                } elseif ($link_id == '8888888') {
                    if (!Yii::$app->user->isGuest) {
                        return tep_href_link('account/index', '', 'SSL');
                    } else {
                        return tep_href_link('account/create', '', 'SSL');
                    }
                } elseif ($link_id == '8888884') {
                    return tep_href_link('checkout/index', '', 'SSL');
                } elseif ($link_id == '8888883') {
                    return tep_href_link('shopping-cart/index');
                } elseif ($link_id == '8888882') {
                    return tep_href_link('catalog/products-new');
                } elseif ($link_id == '8888881') {
                    return tep_href_link('catalog/featured-products');
                } elseif ($link_id == '8888880') {
                    return tep_href_link('catalog/sales');
                } elseif ($link_id == '8888879') {
                    return tep_href_link('catalog/gift-card');
                } elseif ($link_id == '8888878') {
                    return tep_href_link('catalog/all-products');
                }  elseif ($link_id == '8888877') {
                    return tep_href_link('sitemap');
                } elseif ($link_id == '8888876') {
                    return tep_href_link('promotions');
                } elseif ($link_id == '8888875') {
                    return tep_href_link('wedding-registry');
                } elseif ($link_id == '8888874') {
                    return tep_href_link('wedding-registry/manage');
                } elseif ($link_id == '8888873') {
                    return tep_href_link('quick-order');
                }
                break;
            case 'custom':
                $link = tep_db_fetch_array(tep_db_query("select link from " . TABLE_MENU_ITEMS . " where platform_id = '" . \common\classes\platform::currentId() . "' and link_id = '" . (int) $link_id . "'"));
                if ($link) {
                    return tep_href_link($link['link']);
                }
                break;
        }
        return false;
    }
    
    public static function getAllCustomPages($platform_id){
        $cusom_pages_query = tep_db_query("select ts.id, ts.setting_value from " . TABLE_THEMES_SETTINGS . " ts left join " . TABLE_THEMES . " t on ts.theme_name = t.theme_name inner join " . TABLE_PLATFORMS_TO_THEMES . " pt on pt.is_default = 1 and pt.theme_id = t.id where pt.platform_id = '" . (int)$platform_id . "' and ts.setting_group = 'added_page' and ts.setting_name='custom' order by ts.setting_value");
        $custom_pages = [];
        if (tep_db_num_rows($cusom_pages_query)){
            while($custom = tep_db_fetch_array($cusom_pages_query)){
                $custom_pages[$custom['id']] = $custom['setting_value'];
            }
        }
        return $custom_pages;
    }

    public static function getBrandsList() {
        static $brands;
        if ( is_array($brands) ) return $brands;

        $manufacturers_query = tep_db_query("select manufacturers_id, manufacturers_name, manufacturers_image from " . TABLE_MANUFACTURERS ." order by manufacturers_name asc");

        $brands = [];
        while ($item = tep_db_fetch_array($manufacturers_query)) {
            $brands[$item['manufacturers_id']] = $item;
        }

        return $brands;
    }

    public static function getExtensionsTreeItems() {
        $path = \Yii::getAlias('@common') . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR;
        $exItems = array();
        if ($dir = @dir($path)) {
            while ($file = $dir->read()) {
                if ($ext = \common\helpers\Acl::checkExtensionInstalled($file, 'getAdminMenu')) {
                    $Items = $ext::getAdminMenu();
                    if (is_array($Items)) {
                        foreach ($Items as $item) {
                            $exItems[] = $item;
                        }
                    }
                    unset($Items);
                }
            }
            $dir->close();
        }
        return $exItems;
    }

    public static function prepareAdminTree($data, $exItems) {
        $response = [];
        foreach ($data->item as $item) {
            $row = [
                'sort_order' => (int)$item->sort_order,
                'box_type' => (int)$item->box_type,
                'acl_check' => (string)$item->acl_check,
                'config_check' => (string)$item->config_check,
                'path' => (string)$item->path,
                'title' => (string)$item->title,
                'filename' => (string)$item->filename,
            ];
            if ((int)$item->box_type == 1) {
                if (isset($item->child)) {
                    $row['child'] = self::prepareAdminTree($item->child, $exItems);
                }
                foreach ($exItems as $key => $value) {
                    if ($value['parent'] == $row['title']) {
                        $row['child'][] = $value;
                        unset($exItems[$key]);
                    }
                }
            }
            $response[] = $row;
        }
        return $response;
    }

    /**
     * Get menu chain from id
     * @param $menuId
     * @return null|array ['BOX_HEADING_CUSTOMERS', 'BOX_CUSTOMERS_CUSTOMERS']
     */
    private static function getAdminMenuChain($menuId)
    {
        $res = [];
        do {
            $menu = \common\models\AdminBoxes::findOne($menuId);
            if (empty($menu)) {
                \Yii::warning(__FUNCTION__ . " - cannot create menu chain for $menuId");
                return;
            }
            $res[] = $menu->title;
            $menuId = $menu->parent_id;
        } while ($menuId != 0);
        return array_reverse($res);
    }

    private static function forceAclForMenu($menuId)
    {
        $menuChain = self::getAdminMenuChain($menuId);
        if (empty($menuChain)) return;
        $parentId = 0;
        foreach ($menuChain as $menuTitle) {
            $acl = \common\models\AccessControlList::findOne(['access_control_list_key' => $menuTitle, 'parent_id' => $parentId]);
            if (empty($acl)) {
                $acl = new \common\models\AccessControlList();
                $acl->parent_id = $parentId;
                $acl->access_control_list_key = $menuTitle;
                $acl->sort_order = 10;
                $acl->save(false);
            }
            $parentId = $acl->access_control_list_id;
        }
        return $acl;
    }

    /* extracted from below importAdminTree */
    private static function createAdminMenuItemAndCheckAcl($item)
    {
        $parent = (int)($item['parent_id'] ?? 0);
        if (!empty($parent)) {
            $row = \common\models\AdminBoxes::findOne($parent);
            if ($row->box_type == 0) {
                $row->box_type = 1;
                $row->save(false);
            }
            unset($row);
        }

        $object = new \common\models\AdminBoxes();
        $object->parent_id = $parent;
        $object->sort_order = (int)($item['sort_order'] ?? null);
        $object->box_type = (int)($item['box_type'] ?? null);
        $object->acl_check = (string)($item['acl_check'] ?? null);
        $object->config_check = (string)($item['config_check'] ?? null);
        $object->path = (string)($item['path'] ?? null); // maybe null if box_type=1
        $object->title = (string)$item['title'];
        $object->filename = (string)($item['filename'] ?? null);
        $object->save(false);
        //--- update acl
        $cnt = \common\models\AccessControlList::find()->where(['access_control_list_key' => $object->title])->count();
        if ( $cnt != 1) {
            $acl = self::forceAclForMenu($object->box_id);
        } else {
            $acl = \common\models\AccessControlList::findOne(['access_control_list_key' => $object->title]);
        }
        $acl->sort_order = $object->sort_order;
        $acl->save();
        return $object;
    }

    public static function importAdminTree($data, $parent = 0) {
        foreach ($data as $item) {
            $item['parent_id'] = $parent;
            $object = self::createAdminMenuItemAndCheckAcl($item);
            //--- update acl
            if (isset($item['child']) && is_array($item['child']) && (int)($item['box_type']??0) == 1) {
                self::importAdminTree($item['child'], $object->box_id);
            }
        }
    }

    public static function getAdminMenuItemByTitle($array_or_title)
    {
        if (is_array($array_or_title)) {
            $title = $array_or_title['title'] ?? null;
            $parent_id = $array_or_title['parent_id'] ?? null;
            if (is_null($parent_id) && isset($array_or_title['parent'])) {
                $parent_id = self::getAdminMenuItemByTitle($array_or_title['parent']);
            }
        } else {
            $title = (string) $array_or_title;
            $parent_id = null;
            $cnt = \common\models\AdminBoxes::find()->where(['title' => $title])->count();
            if ($cnt > 1) {
                \Yii::warning(__FUNCTION__ . " - inconsistent search result for $title. More than one row: $cnt");
            }
        }
        if (!empty($title)) {
            return \common\models\AdminBoxes::find()
                    ->where(['title' => $title])
                    ->andFilterWhere(['parent_id' => $parent_id])
                    ->one();
        }
    }

    public static function getAdminMenuItemLast($parent_id = 0)
    {
        return \common\models\AdminBoxes::find()
                ->where(['parent_id' => $parent_id])
                ->orderBy('sort_order DESC')
                ->limit(1)
                ->one();
    }

    public static function getAdminMenuChild($array_or_title)
    {
        $row = self::getAdminMenuItemByTitle($array_or_title);
        if (!empty($row)) {
            return \common\models\AdminBoxes::find()
                    ->where(['parent_id' => $row->box_id])
                    ->all();
        }
    }

    public static function removeAdminMenuItems($itemsArray)
    {
        if (is_array($itemsArray)) {
            foreach ($itemsArray as $item) {
                if (is_array($item)) {
                    self::removeAdminMenuItem($item);
                }
            }
        }
    }

    /**
     * @param type $array_or_title
     * @return null if success or error message
     */
    public static function removeAdminMenuItem($array_or_title)
    {
        if (is_array($array_or_title['child'] ?? null))
            self::removeAdminMenuItems($array_or_title['child']);

        $row = self::getAdminMenuItemByTitle($array_or_title);
        if (empty($row)) {
            return 'removeAdminMenu: cant find item';
        }
        if (!empty($child = self::getAdminMenuChild($array_or_title))) {
            return 'removeAdminMenu: cant delete item because of child: ' . count($child);
        }
        $row->delete();
    }

    public static function resortAdminMenu($parent_id, $resort_after_id)
    {
        \common\models\AdminBoxes::updateAll(
                ['sort_order' => new \yii\db\Expression('sort_order+1')],
                'parent_id = :parent_id AND sort_order >= :shift_sort_order',
                ['parent_id' => $parent_id, 'shift_sort_order' => $resort_after_id++]
        );
    }

    public static function createAdminMenuItems($itemsArray, array $params = [])
    {
        if (is_array($itemsArray)) {
            foreach ($itemsArray as $item) {
                if (is_array($item)) {
                    self::createAdminMenuItem($item, $params);
                }
            }
        }
    }

    public static function resetAdminMenu()
    {
        $path = \Yii::getAlias('@webroot');
        $filename = $path . DIRECTORY_SEPARATOR . 'includes' .DIRECTORY_SEPARATOR . 'default_menu.xml';

        $xmlfile = file_get_contents($filename);
        $ob= simplexml_load_string($xmlfile);
        if (isset($ob)) {
            $exItems = \common\helpers\MenuHelper::getExtensionsTreeItems();
            $obPrepared = \common\helpers\MenuHelper::prepareAdminTree($ob, $exItems);
            tep_db_query("TRUNCATE TABLE admin_boxes;");
            \common\helpers\MenuHelper::importAdminTree($obPrepared);
        }
    }

    /**
     * @param array $array - admin menu item
     * @param array $params - ['removeIfExists' => true(default)/false, 'addAfterMenuTitle' => null(default)/'__last__'/'Title']
     */
    public static function createAdminMenuItem(array $array, array $params = [])
    {
        $array['sort_order'] = $array['sort_order'] ?? 0;
        $array['box_type'] = $array['box_type'] ?? 0;
        $array['acl_check'] = $array['acl_check'] ?? '';
        $array['config_check'] = $array['config_check'] ?? '';
        $array['filename'] = $array['filename'] ?? '';

        // find parent_id
        if (!empty($array['parent'])) {
            $parent = self::getAdminMenuItemByTitle ($array['parent']);
            if (!empty($parent)) {
                $array['parent_id'] = $parent->box_id;
            }
        }
        $array['parent_id'] = $array['parent_id'] ?? 0;

        // if already exists
        if ($params['removeIfExists'] ?? true) {
            self::removeAdminMenuItem($array);
        } 
        if (empty(self::getAdminMenuItemByTitle($array))) {

            // resort
            $addAfterTitle = $params['addAfterMenuTitle'] ?? null;
            if ($addAfterTitle == '__last__') {
                $menu = self::getAdminMenuItemLast($array['parent_id']);
            } elseif (!empty($addAfterTitle)) {
                $menu = self::getAdminMenuItemByTitle($addAfterTitle);
            }
            if (!empty($menu)) {
                $array['parent_id'] = $menu->parent_id;
                $array['sort_order'] = $menu->sort_order + 1;
            }
            self::resortAdminMenu($array['parent_id'], $array['sort_order']);

            // create
            $res = self::createAdminMenuItemAndCheckAcl($array);
        }
        if (is_array($array['child'] ?? null)) {

            foreach($array['child'] as $key => $child) {
                if (!empty($res) && $res->box_id > 0) {
                    $array['child'][$key]['parent_id'] = $res->box_id;
                } else { // $res->box_id = 0 if parent was already exist
                    $array['child'][$key]['parent'] = $array['title'];
                }
            }
            self::createAdminMenuItems($array['child']);
        }
    }

    public static function categoriesToMenuMessage() {
        if (!\common\helpers\Acl::rule(['BOX_HEADING_DESIGN_CONTROLS', 'FILENAME_CMS_MENUS'])){
            return '';
        }
        
        $lastModifiedBrands = \common\models\Manufacturers::find()->max('date_added');

        $platforms = \common\classes\platform::getList(false);
        foreach ($platforms as $platform) {

            $lastModifiedCategories = \common\models\Categories::find()
                ->alias('c')->leftJoin(['pc' => \common\models\PlatformsCategories::tableName()],
                    "c.categories_id = pc.categories_id")
                ->where(['pc.platform_id' => $platform['id']])
                ->max('date_added');

            $category = Menus::find()
                ->alias('m')
                ->select(['m.id', 'mi.platform_id', 'm.last_modified'])
                ->leftJoin(['mi' => \common\models\MenuItems::tableName()], "m.id = mi.menu_id")
                ->where(['mi.link_id' => '999999999', 'mi.platform_id' => $platform['id']])
                ->andWhere(['<', 'm.last_modified', $lastModifiedCategories])
                ->asArray()
                ->one();

            if ($category) {
                $message = CATEGORIES_TO_MENU;
            } else {
                $brand = Menus::find()
                    ->alias('m')
                    ->select(['m.id', 'mi.platform_id', 'm.last_modified'])
                    ->leftJoin(['mi' => \common\models\MenuItems::tableName()], "m.id = mi.menu_id")
                    ->where(['mi.link_id' => '999999998', 'mi.platform_id' => $platform['id']])
                    ->andWhere(['<', 'm.last_modified', $lastModifiedBrands])
                    ->asArray()
                    ->one();

                if (!$brand) {
                    continue;
                }
                $message = BRANDS_TO_MENU;
            }

            $message = sprintf($message, Yii::$app->urlManager->createUrl([
                'menus',
                'menu' => $category['id']??false,
                'platform_id' => $platform['id']??false,
            ]));
            \Yii::$container->get('message_stack')->add($message, 'alert', 'info menu-message', 'menu-message');
            return '';
        }
    }

    public static function createMenu($menuName, $data = [], $platformId = false)
    {
        if (Menus::findOne(['menu_name' => $menuName])) return '';

        if ($platformId === false) {
            $platformId = \common\classes\platform::defaultId();
        }

        $menu = new Menus();
        $menu->menu_name = $menuName;
        $menu->last_modified = new \yii\db\Expression('NOW()');
        $menu->save();
        $menuId = $menu->getPrimaryKey();

        self::createMenuItems($data, $menuId, $platformId);

        return $menuId;
    }

    public static function createMenuItems($data, $menuId, $platformId, $parentId = 0, $local = false)
    {
        if (!is_array($data)) return false;

        $languageId = Language::get_default_language_id();

        foreach ($data as $item){
            $menuItem = new MenuItems();

            $link_type = $item['link_type'];
            if ($local) {
                $link_id = $item['link_id_local'];
            } else {
                $link_id = $item['link_id'];
                if ($item['link_type'] == 'info') {
                    $link_id = \common\models\Information::findOne([
                        'platform_id' => $platformId,
                        'seo_page_name' => $item['link_id'],
                    ])->information_id ?? null;
                    if (!$link_id) {
                        $link_id = \common\models\Information::findOne([
                            'seo_page_name' => $item['link_id'],
                        ])->information_id ?? null;
                    }
                }
                if ($item['link_type'] == 'categories' && $link_id != '999999999') {
                    $link_id = \common\models\CategoriesDescription::findOne([
                        'categories_seo_page_name' => $link_id,
                        'language_id' => $languageId
                    ])->categories_id;
                }
                if ($item['link_type'] == 'brands' && $link_id != '999999998') {
                    $link_id = \common\models\Manufacturers::findOne(['manufacturers_name' => $link_id])->manufacturers_id;
                }
                if (!$link_id && in_array($item['link_type'], ['info', 'categories', 'brands'])) {
                    if (
                        is_array($item['titles']) && count($item['titles'])
                        || is_array($item['children']) && count($item['children'])
                    ) {
                        $link_type = 'custom';
                    } else {
                        continue;
                    }
                }
            }

            $menuItem->attributes = [
                'platform_id' => $platformId,
                'menu_id' => $menuId,
                'parent_id' => $parentId,
                'link' => $item['link'],
                'link_id' => $link_id,
                'link_type' => $link_type,
                'target_blank' => $item['target_blank'],
                'sub_categories' => $item['sub_categories'],
                'custom_categories' => $item['custom_categories'],
                'class' => $item['class'],
                'sort_order' => $item['sort_order'],
                'no_logged' => $item['no_logged'],
            ];
            $menuItem->save();
            $menuItemId = $menuItem->getPrimaryKey();

            if (is_array($item['titles']))
            foreach ($item['titles'] as $langeageKey => $vals) {

                $language = Language::get_language_id($langeageKey);
                $menuTitles = new \common\models\MenuTitles();
                $menuTitles->attributes = [
                    'language_id' => (int)$language['languages_id'],
                    'item_id' => (int)$menuItemId,
                    'title' => $vals['title'],
                    'link' => $vals['link'],
                ];
                $menuTitles->save();
            }

            if ($item['children']) {
                self::createMenuItems($item['children'], $menuId, $platformId, $menuItemId);
            }
        }
    }

    public static function menuTree($menuName, $platformId = false)
    {
        if ($platformId === false) {
            $platformId = \common\classes\platform::defaultId();
        }


        $languages = [];
        $menuId = Menus::findOne(['menu_name' => $menuName])->id;

        $menuItems = MenuItems::find()
            ->where(['menu_id' => $menuId, 'platform_id' => $platformId])
            ->asArray()->all();

        foreach ($menuItems as $key => $menuItem) {
            $menuTitles  = \common\models\MenuTitles::find()
                ->where(['item_id' => $menuItem['id']])
                ->asArray()->all();
            foreach ($menuTitles as $menuTitle) {
                if (!isset($languages[$menuTitle['language_id']]) || !$languages[$menuTitle['language_id']]) {
                    $lng = Language::get_language_code($menuTitle['language_id']);
                    $languages[$menuTitle['language_id']] = $lng['code'];
                }
                $menuItems[$key]['titles'][$languages[$menuTitle['language_id']]] = [
                    'title' => $menuTitle['title'],
                    'link' => $menuTitle['link'],
                ];
            }
        }

        return self::menuTreeItems($menuItems, $platformId);
    }

    private static function menuTreeItems($menuData, $platformId, $parentId = 0)
    {
        $languageId = Language::get_default_language_id();

        $tree = [];
        foreach ($menuData as $item) {
            if ($item['parent_id'] != $parentId) continue;

            $link_id = $item['link_id'];
            $link_id_local = $item['link_id'];
            if ($item['link_type'] == 'info') {
                $link_id = \common\models\Information::findOne([
                    'platform_id' => $platformId,
                    'information_id' => $item['link_id'],
                    'languages_id' => $languageId,
                ])->seo_page_name ?? null;
            }
            if ($item['link_type'] == 'categories') {
                $link_id = \common\models\CategoriesDescription::findOne([
                        'categories_id' => $item['link_id'],
                        'language_id' => $languageId
                    ])->categories_seo_page_name ?? $link_id;
            }
            if ($item['link_type'] == 'brands') {
                $link_id = \common\models\Manufacturers::findOne($link_id)->manufacturers_name ?? $link_id;
            }

            $tree[] = [
                'link' => $item['link'],
                'link_id' => $link_id,
                'link_id_local' => $link_id_local,
                'link_type' => $item['link_type'],
                'target_blank' => $item['target_blank'],
                'sub_categories' => $item['sub_categories'],
                'custom_categories' => $item['custom_categories'],
                'class' => $item['class'],
                'sort_order' => $item['sort_order'],
                'no_logged' => $item['no_logged'],
                'titles' => $item['titles'] ?? null,
                'children' => self::menuTreeItems($menuData, $platformId, $item['id']),
            ];
        }

        return $tree;
    }

    public static function getAccountPages()
    {
        $pages = \common\models\ThemesSettings::find()->select(['setting_value'])->distinct()->where([
            'setting_group' => 'added_page',
            'setting_name' => 'account',
        ])->asArray()
            ->cache(self::MENU_CACHE_LIFETIME)
            ->all();

        $accountPages = [];
        foreach ($pages as $page) {
            $accountPages[] = [
                'type_id' => hexdec(substr( md5($page['setting_value']), 0, 7 )),
                'name' => $page['setting_value']
            ];
        }

        return $accountPages;
    }

    public static function getComponents($platformId)
    {
        $theme = \common\models\PlatformsToThemes::findOne(['platform_id' => $platformId]);
        if (!$theme || !$theme->theme_id) {
            return [];
        }
        $themeId = $theme->theme_id;
        $theme = \common\models\Themes::findOne(['id' => $themeId]);
        if (!$theme || !$theme->theme_name) {
            return [];
        }
        $themeName = $theme->theme_name;

        $pages = \common\models\ThemesSettings::find()->select(['setting_value'])->distinct()->where([
            'setting_group' => 'added_page',
            'setting_name' => 'components',
            'theme_name' => $themeName,
        ])->asArray()->all();

        $components = [];
        foreach ($pages as $page) {
            $components[$page['setting_value']] = $page['setting_value'];
        }

        return $components;
    }

    public static function removeMenu($menuId)
    {
        Menus::findOne($menuId)->delete();

        self::removeMenuItems($menuId);
    }

    public static function removeMenuItems($menuId, $platformId = false)
    {
        $conditions = ['menu_id' => $menuId];
        if ($platformId) {
            $conditions['platform_id'] = $platformId;
        }
        $menus = MenuItems::find()->select('id')->where($conditions)->asArray()->all();
        foreach ($menus as $menu) {
            MenuTitles::deleteAll(['item_id' => $menu['id']]);
            if ($ext = \common\helpers\Acl::checkExtensionAllowed('SeoRedirectsNamed', 'allowed')){
                $ext::deleteMenuLinks((int)$menu['id']);
            }
        }

        MenuItems::deleteAll($conditions);
    }
}
