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

namespace backend\controllers;

use backend\components\Information;
use common\models\MenuItems;
use common\models\Menus;
use common\models\Platforms;
use Yii;
use common\helpers\MenuHelper;
use common\models\MenuSource;
ini_set('memory_limit', '-1');
/**
 * default controller to handle user requests.
 */
class MenusController extends Sceleton {
  const MENU_CATEGORIES_COLLAPSED = true;
  const MENU_CATEGORIES_MAX_LEVEL = -1; //-1 - no restriction, starts from 1

    public $acl = ['BOX_HEADING_DESIGN_CONTROLS', 'FILENAME_CMS_MENUS'];

    public function actionIndex() {
        global $languages;
        $languages_id = \Yii::$app->settings->get('languages_id');

        \common\helpers\Translation::init('admin/design');

        $this->topButtons[] = '<span class="btn btn-confirm btn-save">' . IMAGE_SAVE . '</span>';
        $this->topButtons[] = '<span class="btn btn-primary btn-create-menu menu-ico">' . TEXT_CREATE_MENU . '</span>';

        $this->view->usePopupMode = false;
        if (Yii::$app->request->isAjax) {
          $this->layout = false;
          $this->view->usePopupMode = true;
        }

        $this->selectedMenu = array('design_controls', 'menus');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('menus/index'), 'title' => HEADING_TITLE);

        $this->view->headingTitle = HEADING_TITLE;

        $selected_platform_id = \common\classes\platform::firstId();
        $try_set_platform = Yii::$app->request->get('platform_id',0);
        if ( $try_set_platform>0 ) {
          foreach (\common\classes\platform::getList(false) as $_platform) {
            if ((int)$try_set_platform==(int)$_platform['id']){
              $selected_platform_id = (int)$try_set_platform;
            }
          }
        }

        $menu_id = (int)Yii::$app->request->get('menu', 0);
        if ($menu_id == 0){
          $sql = tep_db_fetch_array(tep_db_query("select id from " . TABLE_MENUS ." where 1 limit 1"));
          $menu_id = $sql['id'];
        }



        $sql=tep_db_query("SELECT information_id, info_title, page_title from " . TABLE_INFORMATION ." WHERE visible='1' and languages_id =".(int)$languages_id." and platform_id='".$selected_platform_id."' and affiliate_id=0 " . (Information::showHidePage() ? '' : " and hide=0 ") . " order by v_order");

        $info = array();
        while($row=tep_db_fetch_array($sql)){
            if ($row['info_title']) $row['title'] = $row['info_title'];
            elseif ($row['page_title']) $row['title'] = $row['page_title'];
           $info[] = $row;
        }

        $accountPages = \common\helpers\MenuHelper::getAccountPages();
        $components = \common\helpers\MenuHelper::getComponents($selected_platform_id);


        $sql = tep_db_query(
          "select c.categories_id, c.parent_id, if(length(cd1.categories_name), cd1.categories_name, cd.categories_name) as categories_name  ".
          "from " . TABLE_CATEGORIES_DESCRIPTION . " cd, " . TABLE_CATEGORIES . " c ".
          " inner join ".TABLE_PLATFORMS_CATEGORIES." pc on pc.categories_id=c.categories_id and pc.platform_id='" . $selected_platform_id . "' ".
          " left join " . TABLE_CATEGORIES_DESCRIPTION . " cd1 on cd1.categories_id = c.categories_id and cd1.language_id='" . (int)$languages_id ."' and cd1.affiliate_id = '" . (isset($_SESSION['affiliate_ref']) ? (int)$_SESSION['affiliate_ref'] : 0) . "' ".
          "where c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "' and cd.affiliate_id = 0 ".
            (self::MENU_CATEGORIES_MAX_LEVEL!=-1?" and c.categories_level<=". (int)self::MENU_CATEGORIES_MAX_LEVEL . " ":"") .
          "order by c.categories_level, c.categories_left, c.parent_id");

        $categories = [];
        while($row=tep_db_fetch_array($sql)){
          $parent_id = $row['parent_id'];
          $categories_id = $row['categories_id'];
          if ($parent_id!=0) {
            unset($row['parent_id']);
          }
          $categories[$categories_id] = $row;
          $categories[$parent_id]['children'][$categories_id] =  [];
          if ($parent_id>0) {
            $categories[$parent_id]['children'][$categories_id] = $row;
          }
        }
//echo "<pre>" . print_r($categories, 1). "</pre>"; die;
        $brands = \common\helpers\MenuHelper::getBrandsList();

        $groups = [];
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('UserGroups', 'allowed')) {
            $groups = $ext::getGroupsArray();
        }

        $sql = tep_db_query("select mi.*, if(i.info_title='', i.page_title, ifnull(i.info_title,'')) as name, ifnull(mt.title, '') as shown "
            . " from " . TABLE_MENU_ITEMS ." mi left join " . TABLE_INFORMATION . " i on i.information_id=mi.link_id and i.visible='1' and i.languages_id =" . (int)$languages_id . " and i.platform_id='" . (int)$selected_platform_id . "' and i.affiliate_id=0 left join " . TABLE_MENU_TITLES . " mt on mt.item_id=mi.id and mt.language_id = " . (int)$languages_id
            . " where mi.platform_id='" . (int)$selected_platform_id . "' and mi.menu_id='" . $menu_id . "' order by sort_order");
        $new_categories = array();
        $new_brands = array();
        $menu = array();
        while($row=tep_db_fetch_array($sql)){

            if ($ext = \common\helpers\Acl::checkExtensionAllowed('UserGroups', 'allowed')) {
                $menuGroups = [];
                $groupsArr = explode(',', $row['user_groups']);
                foreach ($groupsArr as $group) {
                    $menuGroups[] = trim($group, '#');
                }
                $row['groups'] = $menuGroups;
            }

            $row['name'] = 'item #' . $row['id'];

            if ($row['link_type'] == 'info'){

                $row1=tep_db_fetch_array(tep_db_query("SELECT information_id, info_title, page_title from " . TABLE_INFORMATION ." WHERE visible='1' and languages_id =".(int)$languages_id." and information_id='" . $row['link_id'] . "' and platform_id='".$selected_platform_id."' and affiliate_id=0"));

                if (!isset($row1) || !is_array($row1)) {
                    $row1=tep_db_fetch_array(tep_db_query("SELECT information_id, info_title, page_title from " . TABLE_INFORMATION ." WHERE visible='1' and languages_id =".(int)$languages_id." and information_id='" . $row['link_id'] . "' and affiliate_id=0"));
                }

                if ($row1['info_title'] ?? null) {
                    $row['name'] = $row1['info_title'];
                } elseif ($row1['page_title'] ?? null) {
                    $row['name'] = $row1['page_title'];
                }

              $sql1=tep_db_query("select title from " . TABLE_MENU_TITLES . " where item_id = " . (int)$row['id'] . " and language_id = " . $languages_id);
              if ($row1=tep_db_fetch_array($sql1)){
                $row['shown'] = $row1['title'];
              }

            } elseif ($row['link_type'] == 'component') {

                $row['name'] = TEXT_COMPONENT . ': "' . $row['link'] . '"';

            } elseif ($row['link_type'] == 'categories'){
                if ($row['link_id'] == '999999999'){
                  $row['name'] = TEXT_ALL_CATEGORIES;
                  $query = tep_db_fetch_array(tep_db_query("select last_modified from " . TABLE_MENUS . " where id = '" . $menu_id . "'"));
                  $sql3 = tep_db_query(
                    "select c.categories_id, c.parent_id, cd.categories_name ".
                    "from " . TABLE_CATEGORIES . " c  ".
                    " inner join ".TABLE_PLATFORMS_CATEGORIES." pc on pc.categories_id=c.categories_id and pc.platform_id='".$selected_platform_id."' ".
                    " left join " . TABLE_CATEGORIES_DESCRIPTION . " cd on c.categories_id = cd.categories_id  ".
                    "where c.date_added > '" . $query['last_modified'] . "' and cd.language_id = '" . $languages_id . "' and cd.affiliate_id=0"
                  );
                  if (tep_db_num_rows($sql3) > 0){
                    while ($item = tep_db_fetch_array($sql3)){
                      $new_categories[] = $item;
                    }
                  }
                } else {
                  $sql1=tep_db_query("SELECT categories_name from " . TABLE_CATEGORIES_DESCRIPTION ." WHERE language_id =".(int)$languages_id." and categories_id='" . $row['link_id'] . "' and affiliate_id=0");
                  if ($row1=tep_db_fetch_array($sql1)){
                    $row['name'] = $row1['categories_name'];
                  }
/*
                  $sql1=tep_db_query("select title from " . TABLE_MENU_TITLES . " where item_id = " . (int)$row['id'] . " and language_id = " . $languages_id);
                  if ($row1=tep_db_fetch_array($sql1)){
                    $row['shown'] = $row1['title'];
                  }*/
                }
                if (count($new_categories) > 0){
                    if ($row['link_id'] == 999999999)$current = 0;
                    else $current = $row['link_id'];
                    foreach ($new_categories as $item){
                        if ($item['parent_id'] == $current){
                            $menuItem = array(
                                'parent_id' => $row['id'],
                                'link_type' => 'categories',
                                'name' => $item['categories_name'],
                                'link_id' => $item['categories_id'],
                                'new_category' => $item['categories_id'],
                            );
                            if ($ext = \common\helpers\Acl::checkExtensionAllowed('UserGroups', 'allowed')) {
                                $menuItem['groups'] = [0];
                            }
                            $menu[] = $menuItem;
                        }
                    }
                }

            } elseif ($row['link_type'] == 'brands'){
                if ($row['link_id'] == '999999998'){
                    $row['name'] = 'All Brands';
                    $query = tep_db_fetch_array(tep_db_query("select last_modified from " . TABLE_MENUS . " where id = '" . $menu_id . "'"));
                    $manufacturers_query = tep_db_query("select manufacturers_id, manufacturers_name, manufacturers_image from " . TABLE_MANUFACTURERS ." where date_added > '" . $query['last_modified'] . "' order by manufacturers_name asc");

                    if (tep_db_num_rows($manufacturers_query) > 0){
                        while ($item = tep_db_fetch_array($manufacturers_query)){
                            $new_brands[] = $item;
                        }
                    }
                } else {
                    $row['name'] = $brands[$row['link_id']]['manufacturers_name'];

/*                    $sql1=tep_db_query("select title from " . TABLE_MENU_TITLES . " where item_id = " . (int)$row['id'] . " and language_id = " . $languages_id);
                    if ($row1=tep_db_fetch_array($sql1)){
                        $row['shown'] = $row1['title'];
                    }*/
                }

                if (count($new_brands) > 0){
                    if ($row['link_id'] == 999999998) {
                        $current = 0;
                    } else {
                        $current = $row['link_id'];
                    }
                    foreach ($new_brands as $item){
                        if (isset($item['parent_id']) && $item['parent_id'] == $current){
                            $menu[] = array(
                                'parent_id' => $row['id'],
                                'link_type' => 'brands',
                                'name' => $item['manufacturers_name'],
                                'link_id' => $item['manufacturers_id'],
                                'new_brand' => $item['manufacturers_id'],
                            );
                        }
                    }
                }

            } elseif ($row['link_type'] == 'custom'){

                $sql1=tep_db_query("select title from " . TABLE_MENU_TITLES . " where item_id = " . (int)$row['id'] . " and language_id = " . $languages_id);
                if ($row1=tep_db_fetch_array($sql1)){
                    $row['name'] = $row1['title'];
                }
            } elseif ($row['link_type'] == 'default'){
                if ($row['link_id'] == '8888886'){
                  $row['name'] = TEXT_HOME;
                  
                } elseif ($row['link_id'] == '8888887'){
                  $row['name'] = TEXT_SIGN_IN .' / '. TEXT_HEADER_LOGOUT;
                } elseif ($row['link_id'] == '8888888'){
                  $row['name'] = TEXT_MY_ACCOUNT .' / '. TEXT_MY_ACCOUNT;
                } elseif ($row['link_id'] == '8888884'){
                  $row['name'] = TEXT_CHECKOUT;
                } elseif ($row['link_id'] == '8888883'){
                  $row['name'] = TEXT_SHOPPING_CART;
                } elseif ($row['link_id'] == '8888882'){
                  $row['name'] = IMAGE_NEW_PRODUCT;
                } elseif ($row['link_id'] == '8888881'){
                  $row['name'] = BOX_CATALOG_FEATURED;
                } elseif ($row['link_id'] == '8888880'){
                  $row['name'] = TEXT_SPECIALS_PRODUCTS;
                } elseif ($row['link_id'] == '8888879'){
                  $row['name'] = TEXT_GIFT_CARD;
                } elseif ($row['link_id'] == '8888878'){
                  $row['name'] = TEXT_ALL_PRODUCTS;
                } elseif ($row['link_id'] == '8888877'){
                  $row['name'] = TEXT_SITE_MAP;
                } elseif ($row['link_id'] == '8888876'){
                  $row['name'] = BOX_PROMOTIONS;
                } elseif ($row['link_id'] == '8888875'){
                  $row['name'] = TEXT_WEDDING_REGISTRY;
                } elseif ($row['link_id'] == '8888874'){
                  $row['name'] = MANAGE_YOUR_WEDDING_REGISTRY;
                } elseif ($row['link_id'] == '8888873'){
                  $row['name'] = TEXT_QUICK_ORDER;
                }
                //TODO: After added page add to Sceleton-bindActionParams exceptions for non logged users

              $sql1=tep_db_query("select title from " . TABLE_MENU_TITLES . " where item_id = " . (int)$row['id'] . " and language_id = " . $languages_id);
              if ($row1=tep_db_fetch_array($sql1)){
                $row['shown'] = $row1['title'];
              }
            } elseif ($row['link_type'] == 'account'){
                foreach ($accountPages as $accountPage) {
                    if ($row['link_id'] == $accountPage['type_id']){
                        $row['name'] = $accountPage['name'];
                    }
                }
                $sql1=tep_db_query("select title from " . TABLE_MENU_TITLES . " where item_id = " . (int)$row['id'] . " and language_id = " . $languages_id);
                if ($row1=tep_db_fetch_array($sql1)){
                    $row['shown'] = $row1['title'];
                }
            }

            $titles = tep_db_query("select language_id, title from " . TABLE_MENU_TITLES . " where item_id = " . (int)$row['id']);
            $row['titles'] = array();

            while ($item = tep_db_fetch_array($titles)){
                $row['titles'][$item['language_id']] = $item['title'];
            }
            
            $row['customFilters'] = '';
            if (!empty($row['custom_categories'])) {
                if ($ext = \common\helpers\Acl::checkExtension('ProductPropertiesFilters', 'inFilters')) {
                    if ($ext::allowed()) {
                        $output = [];
                        parse_str($row['custom_categories'], $output);
                        $row['customFilters'] = $ext::buildAdminMenuRow($output);
                    }
                  }
            }

            $sql1 = tep_db_fetch_array(tep_db_query("SELECT count(*) as total from " . TABLE_CATEGORIES . " where categories_id='" . $row['link_id'] . "'"));
            $sql2 = tep_db_fetch_array(tep_db_query("SELECT count(*) as total from " . TABLE_INFORMATION . " where information_id='" . $row['link_id'] . "'"));
            if ($row['link_type'] != 'categories' || $sql1['total'] > 0 || $row['link_id'] == '999999999') {
              if ($row['link_type'] != 'info' || $sql2['total'] > 0) {
                $menu[] = $row;
              }
            }
        }

        $languages = \common\helpers\Language::get_languages();
        $lang = array();
        for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
            $languages[$i]['logo'] = $languages[$i]['image'];
            $lang[] = $languages[$i];
        }


        $menus = array();
        $sql = tep_db_query("select * from " . TABLE_MENUS . " where 1 order by id");
        while ($row=tep_db_fetch_array($sql)){
          $menus[] = $row;
        }

        $current_menu = array();
        $sql = tep_db_query("select * from " . TABLE_MENUS . " where id = " . (int)$menu_id);
        if ($row=tep_db_fetch_array($sql)){
          $current_menu = $row;
        }

        $default_pages = array(
          array('type_id' => 8888886, 'name' => TEXT_HOME, 'opt_need_login' => false),
          array('type_id' => 8888888, 'name' => TEXT_MY_ACCOUNT . ' / ' . TEXT_MY_ACCOUNT, 'opt_need_login' => false),
          array('type_id' => 8888887, 'name' => TEXT_SIGN_IN . ' / ' . TEXT_HEADER_LOGOUT, 'opt_need_login' => false),
          array('type_id' => 8888884, 'name' => TEXT_CHECKOUT, 'opt_need_login' => false),
          array('type_id' => 8888883, 'name' => TEXT_SHOPPING_CART, 'opt_need_login' => false),
          array('type_id' => 8888882, 'name' => IMAGE_NEW_PRODUCT, 'opt_need_login' => true),
          array('type_id' => 8888881, 'name' => BOX_CATALOG_FEATURED, 'opt_need_login' => true),
          array('type_id' => 8888880, 'name' => TEXT_SPECIALS_PRODUCTS, 'opt_need_login' => true),
          array('type_id' => 8888879, 'name' => TEXT_GIFT_CARD, 'opt_need_login' => false),
          array('type_id' => 8888878, 'name' => TEXT_ALL_PRODUCTS, 'opt_need_login' => true),
          array('type_id' => 8888877, 'name' => TEXT_SITE_MAP, 'opt_need_login' => true),
          array('type_id' => 8888876, 'name' => BOX_PROMOTIONS, 'opt_need_login' => false),
          array('type_id' => 8888875, 'name' => TEXT_WEDDING_REGISTRY, 'opt_need_login' => false),
          array('type_id' => 8888874, 'name' => MANAGE_YOUR_WEDDING_REGISTRY, 'opt_need_login' => false),
          array('type_id' => 8888873, 'name' => TEXT_QUICK_ORDER, 'opt_need_login' => false),
        );
        
        $custom_pages = \common\helpers\MenuHelper::getAllCustomPages($selected_platform_id);

        $customFilters = '';
        if ($ext = \common\helpers\Acl::checkExtension('ProductPropertiesFilters', 'inFilters')) {
          if ($ext::allowed()) {
              $customFilters = $ext::buildAdminMenuRow([]);
          }
        }

        $sourcePlatform = Yii::$app->request->get('source_platform', 0);
        if ($sourcePlatform === 0) {
            $sourcePlatform = MenuSource::findOne(['id' => $menu_id, 'platform_id' => $selected_platform_id])->source_platform_id ?? null;
        }
        
        return $this->render('index', [
          'default_pages' => $default_pages,
          'account_pages' => $accountPages,
          'components' => $components,
          'current_menu' => $current_menu,
          'custom_pages' => $custom_pages,
          'menus' => $menus,
          'menu' => $menu,
          'info' => $info,
          'categories' => $categories,
          'brands' => $brands,
          'languages' => $lang,
          'languages_id' => $languages_id,
          'new_categories' => count($new_categories),
          'new_brands' => count($new_brands),
          'platforms' => array_map(function($platform){
            $platform['link'] = Yii::$app->urlManager->createUrl(['menus/index','platform_id'=>$platform['id']]);
            return $platform;
          },\common\classes\platform::getList(false)),
          'isMultiPlatforms' => \common\classes\platform::isMulti(),
          'selected_platform_id' => $selected_platform_id,
          'action_url_select_menu' => Yii::$app->urlManager->createUrl(['menus','platform_id'=>$selected_platform_id]),
          'action_url_save_menu' => Yii::$app->urlManager->createUrl(['menus/save','platform_id'=>$selected_platform_id]),
          'customFilters' => $customFilters,
          'source_platform_id' => $sourcePlatform,
          'groups' => $groups,
        ]);
    }

    public function actionSave() {

        \common\helpers\Translation::init('admin/design');
        
        $selected_platform_id = \common\classes\platform::firstId();
        $try_set_platform = Yii::$app->request->get('platform_id',0);
        if ( $try_set_platform>0 ) {
          foreach (\common\classes\platform::getList(false) as $_platform) {
            if ((int)$try_set_platform==(int)$_platform['id']){
              $selected_platform_id = (int)$try_set_platform;
            }
          }
        }

        $params = Yii::$app->request->post();

        if (MENU_DATA_LIKE_ONE_INPUT == 'True'){
            $list = \backend\design\Style::paramsFromOneInput($params['list']);
        } else {
            $list = $params['list'] ?? null;
        }
        
        $new_menu = false;
        $menu_id = tep_db_prepare_input($params['menu_id']);
        $sql_data_array = array(
          'menu_name' => tep_db_prepare_input($params['menu_name']),
        );

        if ($params['menu_name']) {
          if ($menu_id == 0) {
            tep_db_perform(TABLE_MENUS, $sql_data_array);

            $sql = tep_db_query("select id from " . TABLE_MENUS . " where menu_name = '" . tep_db_input(tep_db_prepare_input($params['menu_name'])) . "'");
            if ($row = tep_db_fetch_array($sql)) {
              $menu_id = $row['id'];
            }

            $new_menu = true;
          } else {
            tep_db_perform(TABLE_MENUS, $sql_data_array, 'update', "id = " . (int)$menu_id);
          }

            $sourcePlatform = Yii::$app->request->post('source_platform', 0);
            if ($sourcePlatform) {
                $menuSource = MenuSource::findOne([
                    'id' => $menu_id,
                    'platform_id' => $selected_platform_id
                ]);
                if (!$menuSource) {
                    $menuSource = new MenuSource();
                    $menuSource->id = $menu_id;
                    $menuSource->platform_id = $selected_platform_id;
                }
                $menuSource->source_platform_id = $sourcePlatform;
                $menuSource->save();
            } else {
                MenuSource::deleteAll([
                    'id' => $menu_id,
                    'platform_id' => $selected_platform_id
                ]);
            }

          if ($menu_id != 0 && !$new_menu) {
            $old = array();
            $new = array();
            $sql = tep_db_query("SELECT id from " . TABLE_MENU_ITEMS . " WHERE platform_id='".$selected_platform_id."' and menu_id='" . $menu_id . "'");
            while ($row = tep_db_fetch_array($sql)) {
              $old[] = $row['id'];
            }
            tep_db_perform(TABLE_MENUS, array('last_modified' => 'now()'), 'update', "id = '" . (int)$menu_id . "'");

            $order = 0;
            if (isset($list) && is_array($list)) foreach ($list as $item) {
              $link_type = tep_db_prepare_input($item['type']);
              $link = isset($item['link']) ? tep_db_prepare_input($item['link']) : '';
              $link_id = tep_db_prepare_input($item['type_id']??null);
              $target_blank = tep_db_prepare_input($item['target_blank']);
              $nofollow = tep_db_prepare_input($item['nofollow']);
              $no_logged = tep_db_prepare_input($item['no_logged']);
              $class = tep_db_prepare_input($item['class']);
              $sub_categories = tep_db_prepare_input($item['sub_categories']);
              $parent_link_type = isset($item['parent']['type']) ? tep_db_prepare_input($item['parent']['type']) : '';
              $parent_link = isset($item['parent']['type_id']) ? tep_db_prepare_input($item['parent']['type_id']) : 0;
              $custom_page = tep_db_prepare_input($item['custom_page']);

              $custom_categories = tep_db_prepare_input($item['custom_categories']);
              
              if (isset($item['parent']['id'])) {
                $parent_id = tep_db_prepare_input($item['parent']['id']);
              } elseif (isset($item['parent']['type_id'])) {
                $id = tep_db_fetch_array(tep_db_query("
                            select id from " . TABLE_MENU_ITEMS . "
                            where menu_id='" . (int)$menu_id . "' and link_type = '" . tep_db_input($parent_link_type) . "' and link_id = '" . (int)$parent_link . "'
                              and platform_id='".(int)$selected_platform_id."'
                            order by id desc"));
                $parent_id = $id['id'];
              } else {
                $parent_id = 0;
              }
              $sql_data_array = array(
                'menu_id' => $menu_id,
                'parent_id' => $parent_id,
                'platform_id' => $selected_platform_id,
                'link' => $link,
                'link_id' => $link_id,
                'link_type' => $link_type,
                'target_blank' => $target_blank,
                'nofollow' => $nofollow,
                'no_logged' => $no_logged,
                'class' => $class,
                'sub_categories' => $sub_categories,
                'custom_categories' => $custom_categories,
                'sort_order' => $order,
                'theme_page_id' => (int)$custom_page,
              );

                if (\common\helpers\Acl::checkExtensionAllowed('UserGroups', 'allowed')) {
                    $sql_data_array['user_groups'] = tep_db_prepare_input($item['user_groups']);
                }
              
              if (isset($item['id'])) {
                tep_db_perform(TABLE_MENU_ITEMS, $sql_data_array, 'update', "id = '" . (int)$item['id'] . "'");
                $new[(int)$item['id']] = $item['id'];

                $id['id'] = $item['id'];
              } else {
                tep_db_perform(TABLE_MENU_ITEMS, $sql_data_array);

                $id = array(
                  'id' => tep_db_insert_id(),
                );
                /*
                $id = tep_db_fetch_array(tep_db_query("
                            select id from " . TABLE_MENU_ITEMS . "
                            where menu_id='" . $menu_id . "' and link_type = '" . $link_type . "' and link_id = '" . $link_id . "'
                              and platform_id='".$selected_platform_id."'
                            order by id desc"));
                */
                $new[(int)$id['id']] = $id['id'];
              }
              
              if ($ext = \common\helpers\Acl::checkExtensionAllowed('SeoRedirectsNamed', 'allowed')){
                $ext::saveMenuLinks($id['id'], (isset($item['custom']) ? $item['custom'] : ''));
              }

                if ($item['titles']??null) {
                    foreach ($item['titles'] as $title) {
                        $sql_data_array = array(
                          'language_id' => $title['language_id'],
                          'item_id' => $id['id'],
                          'title' => $title['title'],
                        );

                        $sql = tep_db_query("
                            select id from " . TABLE_MENU_TITLES . "
                            where language_id = " . $title['language_id'] . " and item_id = " . $id['id']);
                        if (tep_db_num_rows($sql) > 0) {
                            if ($title['title']) {
                                tep_db_perform(TABLE_MENU_TITLES, $sql_data_array, 'update', "language_id = '" . (int)$title['language_id'] . "' and item_id = " . (int)$id['id']);
                            } else {
                                tep_db_query("delete from " . TABLE_MENU_TITLES . " where language_id = '" . (int)$title['language_id'] . "' and item_id = " . (int)$id['id']);
                            }
                        } else {
                            if ($title['title']) {
                                tep_db_perform(TABLE_MENU_TITLES, $sql_data_array);
                            }
                        }
                    }
                }

              $order++;
            }


            foreach ($old as $id) {
              if (/*!in_array($id, $new)*/ !isset($new[(int)$id])) {
                tep_db_query("delete from " . TABLE_MENU_ITEMS . " where id = '" . (int)$id . "'");
                tep_db_query("delete from " . TABLE_MENU_TITLES . " where item_id = '" . (int)$id . "'");
                if ($ext = \common\helpers\Acl::checkExtensionAllowed('SeoRedirectsNamed', 'allowed')){
                    $ext::deleteMenuLinks($id);
                }
              }
            }

            $response = MESSAGE_SAVED;
          } else {
            $response = array(MESSAGE_ADDED, $menu_id);
          }
        } else {

          tep_db_query("delete from " . TABLE_MENUS . " where id = '" . (int)$params['menu_id'] . "'");

          $sql = tep_db_query("select id from " . TABLE_MENU_ITEMS . " where menu_id = " . (int)$params['menu_id']);
          while ($row = tep_db_fetch_array($sql)) {
            tep_db_query("delete from " . TABLE_MENU_TITLES . " where item_id = '" . (int)$row['id'] . "'");
            if ($ext = \common\helpers\Acl::checkExtensionAllowed('SeoRedirectsNamed', 'allowed')){
                $ext::deleteMenuLinks((int)$row['id']);
            }
          }

          tep_db_query("delete from " . TABLE_MENU_ITEMS . " where menu_id = '" . (int)$params['menu_id'] . "'");
          $response = MESSAGE_DELETED;
        }
        return json_encode( $response);
    }


  public function actionSaveName() {

      $params = Yii::$app->request->get();

      $sql_data_array = array(
          'menu_name' => $params['name'],
      );

      tep_db_perform(TABLE_MENUS, $sql_data_array, 'update', "id = " . (int)$params['id']);

      return json_encode( array('name' => $params['name']));
  }

    public function actionExport()
    {
        $menuName = Yii::$app->request->get('name');
        $platformId = Yii::$app->request->get('platform_id');

        $fileName = \common\classes\design::pageName($menuName);

        header('Content-Type: application/json');
        header("Content-Transfer-Encoding: utf-8");
        header('Content-disposition: attachment; filename="' . $fileName . '.json"');
        return json_encode(\common\helpers\MenuHelper::menuTree($menuName, $platformId));
    }

    public function actionImport()
    {
        $menuName = Yii::$app->request->get('name');
        $platformId = Yii::$app->request->get('platform_id');

        if ($_FILES['file']['error'] != UPLOAD_ERR_OK  || !is_uploaded_file($_FILES['file']['tmp_name'])) {
            return '';
        }

        $data = json_decode(file_get_contents($_FILES['file']['tmp_name']), true);
        if (!is_array($data) || !count($data)){
            return '';
        }

        $menuId = Menus::findOne(['menu_name' => $menuName])->id;
        if ($menuId) {
            \common\helpers\MenuHelper::removeMenuItems($menuId, $platformId);
            MenuHelper::createMenuItems($data, $menuId, $platformId);
        } else {
            $menuId = \common\helpers\MenuHelper::createMenu($menuName, $data, $platformId);
        }

        return $menuId;
    }

    public function actionCopyFrom()
    {
        \common\helpers\Translation::init('admin/menus');
        $menuId = Yii::$app->request->get('menu');
        $isEmpty = MenuItems::find()->where(['menu_id' => $menuId])->count() ? false : true;

        $this->layout = false;
        return $this->render('copy-from', [
            'menu' => $menuId,
            'platform_id' => Yii::$app->request->get('platform_id'),
            'menus' => Menus::find()->asArray()->all(),
            'isMultiPlatforms' => \common\classes\platform::isMulti(),
            'platforms' => \common\classes\platform::getList(false),
            'isEmpty' => $isEmpty,
        ]);
    }

    public function actionCopyFromAction()
    {
        $menuId = Yii::$app->request->post('menu');
        $platform_id = Yii::$app->request->post('platform_id', false);
        $menu_from = Yii::$app->request->post('menu_from');
        $platform_id_from = Yii::$app->request->post('platform_id_from', false);

        $data = MenuHelper::menuTree($menu_from, $platform_id_from);
        MenuHelper::removeMenuItems($menuId, $platform_id);
        MenuHelper::createMenuItems($data, $menuId, $platform_id, 0, true);
        $menus = Menus::findOne($menuId);
        $menus->last_modified = new \yii\db\Expression('NOW()');
        $menus->save();
    }

    public function actionSwap()
    {
        \common\helpers\Translation::init('admin/menus');
        $menuId = Yii::$app->request->get('menu_id');
        $platformId = Yii::$app->request->get('platform_id', false);

        $menu = Menus::findOne($menuId);
        $platform = Platforms::findOne(['platform_id' => $platformId]);

        if (!$menu) {
            return json_encode(['error' => 'no menu']);
        }
        if (!$platform) {
            return json_encode(['error' => 'no platform']);
        }

        $this->layout = false;
        $html = $this->render('swap', [
            'menuId' => $menuId,
            'menuName' => $menu->menu_name,
            'platformId' => $platformId,
            'platformName' => $platform->platform_name,
            'menus' => Menus::find()->asArray()->all(),
            'platforms' => \common\classes\platform::getList(false),
            'isMultiPlatforms' => \common\classes\platform::isMulti(),
        ]);
        return json_encode(['html' => $html]);
    }

    public function actionSwapAction()
    {
        $menuId = Yii::$app->request->post('menu_id');
        $platformId = Yii::$app->request->post('platform_id', false);

        $menuId2 = Yii::$app->request->post('menu_id_2');
        $platformId2 = Yii::$app->request->post('platform_id_2', false);

        $tmpMenuId = MenuItems::find()->max('menu_id') + 1;

        MenuItems::updateAll(['menu_id' => $tmpMenuId],
            ['menu_id' => $menuId, 'platform_id' => $platformId]);

        MenuItems::updateAll(['menu_id' => $menuId, 'platform_id' => $platformId],
            ['menu_id' => $menuId2, 'platform_id' => $platformId2]);

        MenuItems::updateAll(['menu_id' => $menuId2, 'platform_id' => $platformId2],
            ['menu_id' => $tmpMenuId, 'platform_id' => $platformId]);
    }

}
