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

use common\classes\design;
use frontend\design\Info;
use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use common\helpers\MenuHelper;
use yii\helpers\Html;
use frontend\design\EditData;
use common\models\Categories;
use common\classes\platform as Platform;
use common\classes\language as Language;

class Menu extends Widget
{

    public $params;
    public $settings;
    public $id;
    public $newCategories = [];
    public $jsSettingsList = ['burger_icon', 'position', 'limit_levels', 'limit_level_1', 'limit_level_2',
        'limit_level_3', 'limit_level_4', 'limit_level_5', 'limit_level_6', 'open_from', 'lev1_goto', 'lev2_goto', 'lev3_goto', 'lev4_goto', 'lev5_goto', 'lev6_goto', 'lev1_vis', 'lev2_vis', 'lev3_vis', 'lev4_vis', 'lev5_vis', 'lev6_vis', 'lev1_display', 'lev2_display', 'lev3_display', 'lev4_display', 'lev5_display', 'lev6_display', 'show_more_button', 'style', 'ofl_width', 'lev1_show_images', 'lev2_show_images', 'lev3_show_images', 'lev4_show_images', 'lev5_show_images', 'lev6_show_images'];

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        if (!$this->settings[0]['params']) {
            return '';
        }
        global $cPath_array;
        global $request_type;
        $languages_id = \Yii::$app->settings->get('languages_id');

        $sab_categories = array();

        $brands = \common\helpers\MenuHelper::getBrandsList();

        $customer_groups_id = (int) \Yii::$app->storage->get('customer_groups_id');

        $accountPages = MenuHelper::getAccountPages();

        $menuId = \common\models\Menus::findOne(['menu_name' => $this->settings[0]['params']])->id;
        $platformCurrentId = Platform::currentId();
        if ($menuId) {
            $sourcePlatformIid = \common\models\MenuSource::findOne(['id' => $menuId, 'platform_id' => $platformCurrentId])->source_platform_id ?? null;
            if ($sourcePlatformIid) {
                $platformCurrentId = $sourcePlatformIid;
            }
        }

        static $categories_all = array();
        $categories_join = '';
        if (!$categories_all){
            if ( Platform::activeId() ) {
                $categories_join .= " inner join " . TABLE_PLATFORMS_CATEGORIES . " plc on c.categories_id = plc.categories_id  and plc.platform_id = '" . $platformCurrentId . "' ";
            }
            if ($ext = \common\helpers\Acl::checkExtensionAllowed('UserGroupsRestrictions', 'isAllowed')) {
                $categories_join .= $ext::sqlCategoriesWhere();
            }
            $cats = Yii::$app->db->createCommand("
                select c.categories_id, c.parent_id, cd.categories_name
                from " . \common\models\Categories::tableName() . " c 
                  {$categories_join}
                  left join " . \common\models\CategoriesDescription::tableName() . " cd on cd.categories_id = c.categories_id and cd.language_id = " . (int)$languages_id . "
                where c.categories_status = 1 and cd.affiliate_id = 0
                order by c.sort_order, cd.categories_name
              ")->queryAll();
            foreach ($cats as $row) {
                $categories_all[$row['categories_id']] = $row;
            }
            unset($cats);
        }
        // init product counts
        if ((!Info::themeSetting('show_empty_categories') || $this->settings[0]['show_count_products'])
            && !empty($categories_all) && !(is_array($categories_all) && isset(reset($categories_all)['products'] )) ) {
            $counts = null;
            unset($this->settings[0]['show_count_products']);
            $counts = \common\components\CategoriesCache::getCPC()::getCategories(array_keys($categories_all), $platformCurrentId, $customer_groups_id);
            foreach ($categories_all as $catId => $row) {
                $categories_all[$catId]['products'] = $counts[$catId] ?? 1;
            }
            unset($counts);
        }

        $andWhere = '';
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('UserGroups', 'allowed')) {
            $andWhere .= " and (user_groups like '%#" . $customer_groups_id . "#%' or user_groups like '%#0#%')";
        }

        $infoPagePublicStatusIds = \common\helpers\PageStatus::getIds('public', 'information');
        $is_menu = false;
        $menu = array();
        $sql = tep_db_query("
            select i.id, i.parent_id, i.link, i.link_id, i.link_type, i.target_blank, i.nofollow, i.class, i.sub_categories, i.custom_categories, i.sort_order, t.title, i.menu_id
            , m.last_modified, cd.categories_name
            from " . TABLE_MENUS . " m
              inner join " . TABLE_MENU_ITEMS . " i on i.menu_id = m.id and i.platform_id='".$platformCurrentId."'
              left join " . TABLE_MENU_TITLES . " t on t.item_id = i.id and t.language_id = " . (int)$languages_id . "
              left join " . TABLE_CATEGORIES_DESCRIPTION . " cd on cd.language_id =" . (int)$languages_id . " and cd.categories_id=i.link_id and i.link_type='categories' 
            where
              m.menu_name = '" . $this->settings[0]['params'] . "' ".$andWhere." 
            order by i.sort_order
          ");
        while ($row = tep_db_fetch_array($sql)) {

            if ($row['link_type'] == 'info') {

                if (!in_array($row['link_id'], $infoPagePublicStatusIds)) {
                    continue;
                }

                if (!$row['title']) {
                    $row1 = $this->getTitles($row['link_id'], $languages_id, $platformCurrentId);

                    if (!$row1['info_title'] && !$row1['page_title'] &&
                        Language::defaultId() != $languages_id
                    ) {
                        $row1 = $this->getTitles($row['link_id'], Language::defaultId(), $platformCurrentId);
                    }

                    if (!$row1['info_title'] && !$row1['page_title'] &&
                        $platformCurrentId != Platform::defaultId()
                    ) {
                        $row1 = $this->getTitles($row['link_id'], $languages_id, Platform::defaultId());
                    }

                    if ( !$row1['info_title'] && !$row1['page_title'] &&
                        $platformCurrentId != Platform::defaultId() &&
                        Language::defaultId() != $languages_id
                    ) {
                        $row1 = $this->getTitles($row['link_id'], Language::defaultId(), Platform::defaultId());
                    }

                    if ($row1['info_title']) {
                        $row['title'] = $row1['info_title'];
                    } elseif ($row1['page_title']) {
                        $row['title'] = $row1['page_title'];
                    }
                }

                $row['link'] = tep_href_link('info', 'info_id=' . $row['link_id']);

                if (Yii::$app->controller->id == 'info' && Yii::$app->request->get('info_id', 0) == $row['link_id']){
                    $row['class'] .= ($row['class'] ? ' ' : '') . 'active';
                }

            } elseif ($row['link_type'] == 'component') {

                if (Info::isAdmin()) {
                    $row['title'] = 'Component "' . $row['link'] . '"';
                } elseif (!empty($row['link'])) {
                    $row['title'] = \frontend\design\Block::widget(['name' => \common\classes\design::pageName($row['link']), 'params' => ['type' => 'components']]);
                }
                $row['class'] = ($row['class'] ? $row['class'] : ' ') . 'component-item';

            } elseif ($row['link_type'] == 'categories') {
                if (!$row['title']) {
                    if (isset($this->settings[0]['show_new'])
                        && $this->settings[0]['show_new']
                        && $row['link_id'] == '999999999'
                        && count($this->newCategories) == 0
                    ) {
                        //$row['title'] = 'All categories';
                        /// new categories (added after menu update) are shown in any case.
                        $join = '';
                        if ($ugr = \common\helpers\Acl::checkExtensionAllowed('UserGroupsRestrictions')) {
                            $join = $ugr::sqlCategoriesWhere();
                        }
                        $sql3 = tep_db_query(
                            "select c.categories_id, c.parent_id, cd.categories_name ".
                            "from " . TABLE_CATEGORIES . " c ".
                            " inner join ".TABLE_PLATFORMS_CATEGORIES." pc on pc.categories_id=c.categories_id and pc.platform_id='".$platformCurrentId."' ".
                            $join.
                            " left join " . TABLE_CATEGORIES_DESCRIPTION . " cd on c.categories_id = cd.categories_id  ".
                            "where c.date_added > '" . $row['last_modified'] . "' and cd.language_id = '" . $languages_id . "' and cd.affiliate_id=0 and c.categories_status=1"
                        );

                        if (tep_db_num_rows($sql3) > 0){
                            while ($item = tep_db_fetch_array($sql3)){
                                $count = $categories_all[$item['categories_id']]['products'];
                                if ($this->settings[0]['show_count_products']) {
                                    $title = $item['categories_name'] . '<span class="count-products-wrap"> (<span class="count-products">' . (int) $count . '</span>)</span>';
                                } else {
                                    $title = $item['categories_name'];
                                }
                                $this->newCategories[] = [
                                    'count' => $count,
                                    'link_type' => 'categories',
                                    'parent_id' => $item['parent_id'],
                                    'name' => $item['categories_name'],
                                    'link_id' => $item['categories_id'],
                                    'new_category' => $item['categories_id'],
                                    'title' => $title,
                                    'link' => tep_href_link('catalog', 'cPath=' . $item['categories_id']),
                                ];
                            }
                        }
                    } else {
                        if (!empty($row['categories_name'])) {
                            $row['title'] = $row['categories_name'];
                        }
                    }
                }

                if ($row['sub_categories']){
                    $sab_categories[] = $row['id'];
                }
                $row['count'] = $categories_all[$row['link_id']]['products'] ?? 0;

                $row['link'] = tep_href_link('catalog', 'cPath=' . $row['link_id']);

                if ($this->settings[0]['show_count_products']) {
                    $row['title'] = $row['title'] . '<span class="count-products-wrap">(<span class="count-products">' . $row['count'] . '</span>)</span>';
                }

                if (Yii::$app->controller->id == 'catalog'){
                    if (is_array($cPath_array)){
                        $cp = $cPath_array;
                    } else {
                        $cp = explode('_', filter_var(Yii::$app->request->get('cPath', ''), FILTER_SANITIZE_STRING));
                    }
                    if (in_array($row['link_id'], $cp)) {
                        $row['class'] .= ($row['class'] ? ' ' : '') . 'active';
                    }
                }

            } elseif ($row['link_type'] == 'brands') {

                $row['link'] = Yii::$app->urlManager->createUrl(['catalog', 'manufacturers_id' => $row['link_id']]);
                if (!$row['title']) {
                    if ($row['link_id'] != '999999998') {
                        $row['title'] = $brands[$row['link_id']]['manufacturers_name'];
                    }
                    $m = \common\helpers\Manufacturers::get_manufacturer_info('manufacturers_name', $row['link_id']);
                    $row['title'] = $m;
                }
                //$row['count'] = count(\common\helpers\Manufacturers::products_ids_manufacturer($row['link_id'], false, $platformCurrentId)) === 0 ? -1 : 0;
                $row['count'] = \common\helpers\Manufacturers::hasAnyProduct($row['link_id'], false, $platformCurrentId)? -1 : 0;



                $row['link'] = Yii::$app->urlManager->createUrl(['catalog', 'manufacturers_id' => $row['link_id']]);

                if (Yii::$app->controller->id == 'catalog'){
                    if (
                        //link_id => manufacturer_id products_ids_manufacturer returns products_id
                        //in_array($row['link_id'], \common\helpers\Manufacturers::products_ids_manufacturer($item['manufacturers_id'], false, $platformCurrentId)) ||
                        $row['link_id'] == (int)Yii::$app->request->get('manufacturers_id', 0)
                    ) {
                        $row['class'] .= ($row['class'] ? ' ' : '') . 'active';
                    }
                }

            } elseif ($row['link_type'] == 'all-products') {

                $output = [];
                parse_str($row['custom_categories'], $output);
                foreach ($output as $ok => $ov) {
                    if (empty($ov)) {
                        unset($output[$ok]);
                    }
                }

                $row['link'] = tep_href_link('catalog/all-products', http_build_query($output));

                if (!$row['title']) {
                    if (!$row['link']) {
                        $row['title'] = $row['link'];
                    }
                }

            } elseif ($row['link_type'] == 'custom') {

                if (!$row['title']) {
                    if (!$row['link']) {
                        $row['title'] = $row['link'];
                    }
                }

                if (strpos($row['link'], 'http') !== 0 && strpos($row['link'], '//') !== 0 && $row['link']){
                    $arr = explode('?', $row['link']);
                    //$row['link'] = tep_href_link($arr[0], $arr[1],preg_match('/^(account|checkout)/', $arr[0])?'SSL':'NONSSL');
                    $row['link'] = tep_href_link($arr[0], $arr[1],$request_type);
                }

                if (str_replace('//', '', str_replace('http://', '', str_replace('https://', '', $row['link']))) == $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']){
                    $row['class'] .= ($row['class'] ? ' ' : '') . 'active';
                }

            } elseif ($row['link_type'] == 'account') {

                $pageName = '';
                foreach ($accountPages as $accountPage) {
                    if ($row['link_id'] == $accountPage['type_id']){
                        $pageName = $accountPage['name'];
                        $row['title'] = $row['title'] ? $row['title'] : $accountPage['name'];
                        $row['page'] = \common\classes\design::pageName($accountPage['name']);
                    }
                }
                $is_multi = \Yii::$app->get('storage')->get('is_multi');
                if ($is_multi) {
                    if ($CustomersMultiEmails = \common\helpers\Acl::checkExtensionAllowed('CustomersMultiEmails', 'allowed')) {
                        if (!$CustomersMultiEmails::checkLink($pageName)) {
                            continue;
                        }
                    }
                    if ($DealersMultiCustomers = \common\helpers\Acl::checkExtensionAllowed('DealersMultiCustomers', 'allowed')) {
                        if (!$DealersMultiCustomers::checkLink($pageName)) {
                            continue;
                        }
                    }
                }
                if($row['page'] == 'wishlist'){
                    $row['link'] = Yii::$app->urlManager->createUrl(['account', 'page_name' => 'personal_catalog', 'show' => 'wishlist']);
                }else{
                    $row['link'] = Yii::$app->urlManager->createUrl(['account', 'page_name' => $row['page']]);
                }

            } elseif ($row['link_type'] == 'default'){

                if ($row['link_id'] == '8888886'){
                    $row['title'] = $row['title'] ? EditData::addEditDataTegTranslation($row['title'], 'TEXT_HOME') : TEXT_HOME;
                    $row['link'] = MenuHelper::getUrlByLinkId($row['link_id'], $row['link_type']);
                    if (Yii::$app->controller->id == 'index' && Yii::$app->controller->action->id == 'index'){
                        $row['class'] .= ($row['class'] ? ' ' : '') . 'active';
                    }
                } elseif ($row['link_id'] == '8888887'){
                    if (!Yii::$app->user->isGuest){
                        $row['title'] = $row['title'] ? EditData::addEditDataTegTranslation($row['title'], 'TEXT_HEADER_LOGOUT') : TEXT_HEADER_LOGOUT;
                    } else {
                        $row['title'] = $row['title'] ? EditData::addEditDataTegTranslation($row['title'], 'TEXT_SIGN_IN') : TEXT_SIGN_IN;
                    }
                    $row['link'] = MenuHelper::getUrlByLinkId($row['link_id'], $row['link_type']);
                } elseif ($row['link_id'] == '8888888'){
                    if (!Yii::$app->user->isGuest){
                        $row['title'] = $row['title'] ? EditData::addEditDataTegTranslation($row['title'], 'TEXT_MY_ACCOUNT') : TEXT_MY_ACCOUNT;
                        if (Yii::$app->controller->id == 'account' && Yii::$app->controller->action->id == 'index'){
                            $row['class'] .= ($row['class'] ? ' ' : '') . 'active';
                        }
                        $row['link'] = MenuHelper::getUrlByLinkId($row['link_id'], $row['link_type']);
                    } else {
                        $row['title'] = $row['title'] ? EditData::addEditDataTegTranslation($row['title'], 'TEXT_CREATE_ACCOUNT') : TEXT_CREATE_ACCOUNT;
                        if (Yii::$app->controller->id == 'account' && Yii::$app->controller->action->id == 'login'){
                            $row['class'] .= ($row['class'] ? ' ' : '') . 'active';
                        }
                        $row['link'] = MenuHelper::getUrlByLinkId($row['link_id'], $row['link_type']);
                    }
                } elseif ($row['link_id'] == '8888884'){
                    $row['title'] = $row['title'] ? EditData::addEditDataTegTranslation($row['title'], 'NAVBAR_TITLE_CHECKOUT') : NAVBAR_TITLE_CHECKOUT;
                    $row['link'] = MenuHelper::getUrlByLinkId($row['link_id'], $row['link_type']);
                    if (Yii::$app->controller->id == 'checkout' && Yii::$app->controller->action->id == 'index'){
                        $row['class'] .= ($row['class'] ? ' ' : '') . 'active';
                    }
                } elseif ($row['link_id'] == '8888883'){
                    $row['title'] = $row['title'] ? EditData::addEditDataTegTranslation($row['title'], 'TEXT_HEADING_SHOPPING_CART') : TEXT_HEADING_SHOPPING_CART;
                    $row['link'] = MenuHelper::getUrlByLinkId($row['link_id'], $row['link_type']);
                    if (Yii::$app->controller->id == 'shopping-cart' && Yii::$app->controller->action->id == 'index'){
                        $row['class'] .= ($row['class'] ? ' ' : '') . 'active';
                    }
                } elseif ($row['link_id'] == '8888882'){
                    $row['title'] = $row['title'] ? EditData::addEditDataTegTranslation($row['title'], 'NEW_PRODUCTS') : NEW_PRODUCTS;
                    $row['link'] = MenuHelper::getUrlByLinkId($row['link_id'], $row['link_type']);
                    if (Yii::$app->controller->id == 'catalog' && Yii::$app->controller->action->id == 'products-new'){
                        $row['class'] .= ($row['class'] ? ' ' : '') . 'active';
                    }
                } elseif ($row['link_id'] == '8888881'){
                    $row['title'] = $row['title'] ? EditData::addEditDataTegTranslation($row['title'], 'FEATURED_PRODUCTS') : FEATURED_PRODUCTS;
                    $row['link'] = MenuHelper::getUrlByLinkId($row['link_id'], $row['link_type']);
                    if (Yii::$app->controller->id == 'catalog' && Yii::$app->controller->action->id == 'featured-products'){
                        $row['class'] .= ($row['class'] ? ' ' : '') . 'active';
                    }
                } elseif ($row['link_id'] == '8888880'){
                    $row['title'] = $row['title'] ? EditData::addEditDataTegTranslation($row['title'], 'SPECIALS_PRODUCTS') : SPECIALS_PRODUCTS;
                    $row['link'] = MenuHelper::getUrlByLinkId($row['link_id'], $row['link_type']);
                    if (Yii::$app->controller->id == 'catalog' && Yii::$app->controller->action->id == 'sales'){
                        $row['class'] .= ($row['class'] ? ' ' : '') . 'active';
                    }
                } elseif ($row['link_id'] == '8888879'){
                    $row['title'] = $row['title'] ? EditData::addEditDataTegTranslation($row['title'], 'TEXT_GIFT_CARD') : TEXT_GIFT_CARD;
                    $row['link'] = MenuHelper::getUrlByLinkId($row['link_id'], $row['link_type']);
                    if (Yii::$app->controller->id == 'catalog' && Yii::$app->controller->action->id == 'gift-card'){
                        $row['class'] .= ($row['class'] ? ' ' : '') . 'active';
                    }
                } elseif ($row['link_id'] == '8888878'){
                    $row['title'] = $row['title'] ? EditData::addEditDataTegTranslation($row['title'], 'TEXT_ALL_PRODUCTS') : TEXT_ALL_PRODUCTS;
                    $row['link'] = MenuHelper::getUrlByLinkId($row['link_id'], $row['link_type']);
                    if (Yii::$app->controller->id == 'catalog' && (Yii::$app->controller->action->id == 'all-products')    ){
                        $row['class'] .= ($row['class'] ? ' ' : '') . 'active';
                    }
                } elseif ($row['link_id'] == '8888877'){
                    $row['title'] = $row['title'] ? EditData::addEditDataTegTranslation($row['title'], 'TEXT_SITE_MAP') : TEXT_SITE_MAP;
                    $row['link'] = MenuHelper::getUrlByLinkId($row['link_id'], $row['link_type']);
                    if (Yii::$app->controller->id == 'sitemap'){
                        $row['class'] .= ($row['class'] ? ' ' : '') . 'active';
                    }
                } elseif ($row['link_id'] == '8888876'){
                    $row['title'] = $row['title'] ? EditData::addEditDataTegTranslation($row['title'], 'TEXT_PROMOTIONS') : TEXT_PROMOTIONS;
                    $row['link'] = MenuHelper::getUrlByLinkId($row['link_id'], $row['link_type']);
                    if (Yii::$app->controller->id == 'promotions'){
                        $row['class'] .= ($row['class'] ? ' ' : '') . 'active';
                    }
                } elseif ($row['link_id'] == '8888875'){
                    $row['title'] = $row['title'] ? EditData::addEditDataTegTranslation($row['title'], 'TEXT_WEDDING_REGISTRY') : TEXT_WEDDING_REGISTRY;
                    $row['link'] = MenuHelper::getUrlByLinkId($row['link_id'], $row['link_type']);
                    if (Yii::$app->controller->id == 'wedding-registry'){
                        $row['class'] .= ($row['class'] ? ' ' : '') . 'active';
                    }
                } elseif ($row['link_id'] == '8888874'){
                    $row['title'] = $row['title'] ? EditData::addEditDataTegTranslation($row['title'], 'MANAGE_YOUR_WEDDING_REGISTRY') : MANAGE_YOUR_WEDDING_REGISTRY;
                    $row['link'] = MenuHelper::getUrlByLinkId($row['link_id'], $row['link_type']);
                    if (Yii::$app->controller->id == 'wedding-registry/manage'){
                        $row['class'] .= ($row['class'] ? ' ' : '') . 'active';
                    }
                } elseif ($row['link_id'] == '8888873'){
                    if (!\common\helpers\Customer::check_customer_groups($customer_groups_id, 'groups_is_reseller')) {
                        continue;
                    }
                    $row['title'] = $row['title'] ? EditData::addEditDataTegTranslation($row['title'], 'TEXT_QUICK_ORDER') : TEXT_QUICK_ORDER;
                    $row['link'] = MenuHelper::getUrlByLinkId($row['link_id'], $row['link_type']);
                    if (Yii::$app->controller->id == 'quick-order'){
                        $row['class'] .= ($row['class'] ? ' ' : '') . 'active';
                    }
                }
            }

            $row['title'] = \frontend\design\EditData::addEditDataTeg($row['title'], 'menu', $row['link_type'], $row['id']);
            if ($row['link_type'] != 'categories' || isset($categories_all[$row['link_id']]) || $row['link_id'] == '999999999') {
                $sql2 = ['total' => \common\components\InformationPage::getFrontendDataVisible($row['link_id'])?1:0];
                if ($row['link_type'] != 'info' || $sql2['total'] > 0) {
                    $menu[] = $row;
                }
            }
        }

        $categories = array_values($categories_all);

        $hide_size = array();
        $media_query_arr = tep_db_query("select t.setting_value from " . TABLE_THEMES_SETTINGS . " t, " . (Info::isAdmin() ? TABLE_DESIGN_BOXES_SETTINGS_TMP : TABLE_DESIGN_BOXES_SETTINGS) . " b where b.setting_name = 'hide_menu' and  b.visibility = t.id and  b.box_id = '" . (int)$this->id . "'");
        while ($item = tep_db_fetch_array($media_query_arr)){
            $hide_size[] = explode('w', $item['setting_value']);
        }

        $menuHtm = $this->menuTree($menu);
        $params = [
            'settings' => $this->settings,
            'menu_htm' => $menuHtm,
            'id' => $this->id,
        ];
        if ($menuBuilder = $this->menuBuilder($params)){
            return $menuBuilder;
        }

        Info::addBlockToWidgetsList((isset($this->settings[0]['class']) ? $this->settings[0]['class'] : ''));

        $this->settings[0]['class'] = (isset($this->settings[0]['class']) ? $this->settings[0]['class'] : 0);
        return IncludeTpl::widget(['file' => 'boxes/menu.tpl', 'params' => [
            'menu' => $menu,
            'categories' => $categories,
            'is_menu' => ($this->settings[0]['params'] ? true : false),
            'settings' => $this->settings,
            'menu_htm' => $menuHtm,
            'hide_size' => $hide_size,
            'id' => $this->id ? $this->id : rand (999999 , 9999999),
        ]]);
    }

    public function menuTree ($menu, $parent = 0, $ul = true, $level = 0, $liImg = false, $parentCategory = false) {
        $htm = '';
        if (isset($this->settings[0]['limit_levels']) &&
            $this->settings[0]['limit_levels'] !== '' &&
            $this->settings[0]['limit_levels'] < $level + 1
        ) {
            return $htm;
        }
        if ($liImg) $htm .= '<li class="category-menu-image">' . $liImg . '</li>';
        foreach ($menu as $item){
            if ($item['parent_id'] == $parent){
                if ($item['link_id'] == 999999999 || $item['link_id'] == 999999998){
                    $htm .= $this->menuTree($menu, $item['id'], false, $level, false, true);
                } else {
                    if (
                        (@$item['count'] != 0 || Info::themeSetting('show_empty_categories') || $item['link_type'] != 'categories')
                        && (@$item['count'] != 0 || !Info::themeSetting('hide_empty_brands') || $item['link_type'] != 'brands')
                    ){
                        [$img, $imgDropdown] = $this->getImage($level+1, $item);
                        $children = '';
                        if ($item['link_type'] != 'categories' || $item['sub_categories'] == 1){
                            $children = $this->menuTree($menu, $item['id'], true, ($level+1), $imgDropdown,
                                ($parentCategory ? $item['link_id'] : false));
                        }
                        $htm .= '<li' . (($item['class'] || $children) ? ' class="' . ($children ? 'parent ' : '') . ' ' . $item['class'] . '"' : '') . '>';
                        if ($item['title']){
                            if ($item['link'] && $item['link_type'] == 'component'){
                                $htm .= $item['title'];
                            } elseif ($item['link']){
                                $htm .= '<a href="' . $item['link'] . '"' . ($item['target_blank'] == 1 ? ' target="_blank"' : '') . ($item['nofollow'] == 1 ? ' rel="nofollow"' : '') . '>' . $img . $item['title'] . '</a>';
                            } else {
                                $htm .= '<span class="no-link">' . $img . $item['title'] . '</span>';
                            }
                        }

                        if ($children){
                            $htm .= '<span class="open-close-ico"></span>';
                            $htm .= $children;
                        }
                        $htm .= '</li>';
                    }
                }
            }
        }
        if (count($this->newCategories) && $parentCategory) {
            foreach ($this->newCategories as $newCategory) {
                if ($parentCategory && $newCategory['parent_id'] === $parentCategory || $parentCategory === true && $newCategory['parent_id'] == 0){
                    [$img, $imgDropdown] = $this->getImage($level+1, $newCategory);

                    $children = $this->menuTree($menu, $newCategory['link_id'], true, ($level+1), $imgDropdown,
                        ($parentCategory ? $newCategory['link_id'] : false));
                    $htm .= '<li class="new-category ' . ($children ? 'parent' : '') . '">';
                    $htm .= '<a href="' . $newCategory['link'] . '">' . $img . $newCategory['title'] . '</a>';
                    if ($children) {
                        $htm .= '<span class="open-close-ico"></span>';
                    }
                    $htm .= $children;
                    $htm .= '</li>';
                }
            }
        }
        if ($ul && $htm) $htm = '<ul class="level-' . ($level+1) . '">' . $htm . '</ul>';

        return $htm;
    }

    public function menuBuilder($params)
    {
        if (!isset($this->settings[0]['type']) || $this->settings[0]['type'] != 'builder') {
            return false;
        }

        Info::includeJsFile('reducers/widgets');
        Info::addBoxToCss('menu-style');
        $jsSettings = [];
        $params['attributes'] = '';
        if (isset($this->settings[0]) && is_array($this->settings[0])) {
            foreach ($this->settings[0] as $setting => $value) {
                if (!in_array($setting, $this->jsSettingsList)) {
                    continue;
                }
                Info::includeJsFile('boxes/menu/' . $setting);
                Info::includeJsFile('boxes/menu/' . $setting . '-' . $value);
                Info::includeJsFile('boxes/menu/' . preg_replace('/[0-9]/', '', $setting));
                Info::includeJsFile('boxes/menu/' . preg_replace('/[0-9]/', '', $setting) . '-' . $value);
                Info::addBoxToCss('menu-' . $setting);
                Info::addBoxToCss('menu-' . $setting . '-' . $value);
                Info::addBoxToCss('menu-' . preg_replace('/[0-9]/', '', $setting));
                Info::addBoxToCss('menu-' . preg_replace('/[0-9]/', '', $setting) . '-' . $value);
                $params['attributes'] .= ' data-' . $setting . '="' . $value . '"';
                $jsSettings[0][$setting] = $value;
            }
        }

        if (isset($this->settings['visibility']) && is_array($this->settings['visibility'])){
            foreach ($this->settings['visibility'] as $setting => $value) {
                if (!in_array($setting, $this->jsSettingsList)) {
                    continue;
                }

                Info::includeJsFile('boxes/menu/' . $setting);
                Info::includeJsFile('boxes/menu/' . preg_replace('/[0-9]/', '', $setting));
                Info::addBoxToCss('menu-' . $setting);
                Info::addBoxToCss('menu-' . preg_replace('/[0-9]/', '', $setting));

                foreach ($value as $val) {
                    Info::includeJsFile('boxes/menu/' . $setting . '-' . $val);
                    Info::includeJsFile('boxes/menu/' . preg_replace('/[0-9]/', '', $setting) . '-' . $val);
                    Info::addBoxToCss('menu-' . $setting . '-' . $val);
                    Info::addBoxToCss('menu-' . preg_replace('/[0-9]/', '', $setting) . '-' . $val);
                }

                uksort($value, function($a, $b){
                    $_a = explode('w', $a);
                    $_b = explode('w', $b);

                    if ((int)$_a[1] == (int)$_b[1]) {
                        if ((int)$_a[0] < (int)$_b[0]) {
                            $result = -1;
                        } else {
                            $result = 1;
                        }
                    } elseif ((int)$_a[1] < (int)$_b[1]) {
                        $result = 1;
                    } else {
                        $result = -1;
                    }
                    return $result;
                });

                $jsSettings['visibility'][$setting] = $value;
            }
        }

        $params['jsSettings'] = $jsSettings;

        Info::addJsData(['widgets' => [
            $this->id => [
                'settings' => $jsSettings,
            ]]]);

        Info::addJsData(['tr' => [
            'GOTO_BUTTON_TEXT' => defined("GOTO_BUTTON_TEXT") ? GOTO_BUTTON_TEXT : '',
            'TEXT_CLOSE' => defined("TEXT_CLOSE") ? TEXT_CLOSE : '',
            'TEXT_SHOW_MORE' => defined("TEXT_SHOW_MORE") ? TEXT_SHOW_MORE : '',
            'TEXT_SHOW_LESS' => defined("TEXT_SHOW_LESS") ? TEXT_SHOW_LESS : '',
        ]]);

        return IncludeTpl::widget(['file' => 'boxes/menu-builder.tpl', 'params' => $params]);
    }

    public function getTitles($information_id, $languages_id, $platform_id)
    {
        $info_data = \common\components\InformationPage::getFrontendDataVisible($information_id, $languages_id, $platform_id);
        if ( $info_data ) {
            return [
                'information_id' => $info_data['information_id'],
                'info_title' => $info_data['info_title'],
                'page_title' => $info_data['page_title'],
            ];
        }

        return \common\models\Information::find()->select(['information_id', 'info_title', 'page_title'])->where([
            'visible' => '1',
            'languages_id' => $languages_id,
            'information_id' => $information_id,
            'platform_id' => $platform_id
        ])->asArray()->one();
    }

    public function getImage($level, $item)
    {
        $img = '';
        $imgDropdown = false;
        if ($item['link_type'] != 'categories') {
            return [$img, $imgDropdown];
        }
        if (($level != 1 || !isset($this->settings[0]['show_images'])) &&
            !isset($this->settings[0]['lev' . $level . '_show_images'])
        ) {
            return [$img, $imgDropdown];
        }

        $image = Categories::findOne($item['link_id'])->categories_image_4;
        if (!$image) {
            return [$img, $imgDropdown];
        }
        $img = \common\classes\Images::getImageSet(
            $image,
            'Category menu',
            ['alt' => $item['title'], 'title' => $item['title']],
            false
        );
        if ($this->settings[0]['show_images'] == '10' || $this->settings[0]['lev' . $level . '_show_images'] == '2'){
            $imgDropdown = $img;
            $img = '';
        }

        return [$img, $imgDropdown];
    }
}
