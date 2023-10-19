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

namespace common\extensions\UserGroups;

use common\classes\platform as Platform;
use Yii;
use common\models\Groups;
use common\models\GroupsDiscounts;
use yii\helpers\Html;
use common\extensions\UserGroups\Render;

// User Groups
class UserGroups extends \common\classes\modules\ModuleExtensions {

    public static function getDescription() {
        return 'This extention allows to use groups for customers.';
    }
    
    public static function allowed() {
        return self::enabled();
    }
   
    public static function allowedGroupDiscounts() {
        if (!self::allowed()) {
            return '';
        }
        return true;
    }

/**
 * save group details in $app->controller->view->groups
 * @param string $typeCode
 */
    public static function getGroups($typeCode = '') {
        if (!self::allowed()) {
            return '';
        }
      if (\common\helpers\Extensions::isCustomerGroupsAllowed()) {
        Yii::$app->controller->view->groups = self::getGroupsArray($typeCode);
      }
    }

/**
 * select customers groups by types code (if extension installed)
 * @param string $typeCode
 * @return array
 */
    public static function getGroupsArray($typeCode = '') {
        if (!self::allowed()) {
            return '';
        }
      $ret = [];
      if (\common\helpers\Extensions::isCustomerGroupsAllowed()) {
        $q = Groups::find()->orderBy('sort_order, groups_name')->indexBy('groups_id')->asArray();
        /** @var \common\extensions\ExtraGroups\ExtraGroups $ext */
        if ($ext = \common\helpers\Acl::checkExtension('ExtraGroups', 'allowed')) {
          if ($ext::allowed() && !empty($typeCode)) {
            $q->andWhere(['groups_type_id' => $ext::getIdByCode($typeCode)]);
          } elseif ($ext::allowed()) {
            $q->andWhere(['groups_type_id' => 0]);
          }
        }
        $ret = $q->all();
      }
      return $ret;
    }

///VL v topku
    public static function getMarketGroupDiscounts($products_id, $currencie_id) {
        if (!self::allowed()) {
            return '';
        }
        if (\common\helpers\Extensions::isCustomerGroupsAllowed()) {
            $groups_data_query = tep_db_query("select * from " . TABLE_GROUPS . " order by groups_id");
            while ($groups_data = tep_db_fetch_array($groups_data_query)) {
                $products_price_data = tep_db_fetch_array(tep_db_query("select products_group_discount_price, products_group_discount_price_pack_unit, products_group_discount_price_packaging from " . TABLE_PRODUCTS_PRICES . " where products_id = '" . (int) $products_id . "' and groups_id = '" . (int) $groups_data['groups_id'] . "' and currencies_id = '" . (int) $currencie_id . "'"));
                if (isset($products_price_data['products_group_discount_price'])) {
                    foreach (explode(';', $products_price_data['products_group_discount_price']) as $qty_discount) {
                        list($qty, $price) = explode(':', $qty_discount);
                        if ($qty > 0 && $price > 0) {
                            Yii::$app->controller->view->qty_discounts[$currencie_id][$groups_data['groups_id']][$qty] = $price;
                        }
                    }
                }
                if (isset($products_price_data['products_group_discount_price_pack_unit'])) {
                    foreach (explode(';', $products_price_data['products_group_discount_price_pack_unit']) as $qty_discount) {
                        list($qty, $price) = explode(':', $qty_discount);
                        if ($qty > 0 && $price > 0) {
                            Yii::$app->controller->view->qty_discounts_pack_unit[$currencie_id][$groups_data['groups_id']][$qty] = $price;
                        }
                    }
                }
                if (isset($products_price_data['products_group_discount_price_packaging'])) {
                    foreach (explode(';', $products_price_data['products_group_discount_price_packaging']) as $qty_discount) {
                        list($qty, $price) = explode(':', $qty_discount);
                        if ($qty > 0 && $price > 0) {
                            Yii::$app->controller->view->qty_discounts_packaging[$currencie_id][$groups_data['groups_id']][$qty] = $price;
                        }
                    }
                }
            }
        }
    }
///VL v topku
    public static function getGroupDiscounts($products_id, $currencies) {
        if (!self::allowed()) {
            return '';
        }
        if (\common\helpers\Extensions::isCustomerGroupsAllowed()) {
            $groups_data_query = tep_db_query("select * from " . TABLE_GROUPS . " order by groups_id");
            while ($groups_data = tep_db_fetch_array($groups_data_query)) {
                $products_price_data = tep_db_fetch_array(tep_db_query("select * from " . TABLE_PRODUCTS_PRICES . " where products_id = '" . (int) $products_id . "' and groups_id = '" . (int) $groups_data['groups_id'] . "' and currencies_id = '0'"));
                Yii::$app->controller->view->products_group_price_pack_unit[$groups_data['groups_id']] = ($products_price_data['products_group_price_pack_unit'] > 0 ? $products_price_data['products_group_price_pack_unit'] : '');
                Yii::$app->controller->view->products_group_price_packaging[$groups_data['groups_id']] = ($products_price_data['products_group_price_packaging'] > 0 ? $products_price_data['products_group_price_packaging'] : '');
                foreach (explode(';', $products_price_data['products_group_discount_price']) as $qty_discount) {
                    list($qty, $price) = explode(':', $qty_discount);
                    if ($qty > 0 && $price > 0) {
                        Yii::$app->controller->view->qty_discounts[$groups_data['groups_id']][$qty] = $price;
                    }
                }
                foreach (explode(';', $products_price_data['products_group_discount_price_pack_unit']) as $qty_discount) {
                    list($qty, $price) = explode(':', $qty_discount);
                    if ($qty > 0 && $price > 0) {
                        Yii::$app->controller->view->qty_discounts_pack_unit[$groups_data['groups_id']][$qty] = $price;
                    }
                }
                foreach (explode(';', $products_price_data['products_group_discount_price_packaging']) as $qty_discount) {
                    list($qty, $price) = explode(':', $qty_discount);
                    if ($qty > 0 && $price > 0) {
                        Yii::$app->controller->view->qty_discounts_packaging[$groups_data['groups_id']][$qty] = $price;
                    }
                }
            }
        }
    }
    
    public static function saveMarketGroup($products_id, $currencies, $key) {
        if (!self::allowed()) {
            return '';
        }
        if (\common\helpers\Extensions::isCustomerGroupsAllowed()) {
                    $data_query = tep_db_query("select * from " . TABLE_GROUPS . " order by groups_id");
                    while ($data = tep_db_fetch_array($data_query)) {

                        $sql_data_array = array('products_id' => $products_id,
                            'groups_id' => $data['groups_id'],
                            'products_group_price' => $_POST['products_groups_prices_' . $currencies->currencies[$key]['id'] . '_' . $data['groups_id']] ? tep_db_prepare_input($_POST['products_groups_prices_' . $currencies->currencies[$key]['id'] . '_' . $data['groups_id']]) : '-2',
                            'products_group_discount_price' => '', //$_POST['products_price_discount_' . $currencies->currencies[$key]['id'] .  '_' . $data['groups_id']] ? tep_db_prepare_input($_POST['products_price_discount_' . $currencies->currencies[$key]['id'] .  '_' . $data['groups_id']]) : '',
                            'currencies_id' => $currencies->currencies[$key]['id']);
                        if (Yii::$app->request->post('bonus_points_status')) {
                            $sql_data_array['bonus_points_price'] = Yii::$app->request->post('bonus_points_price_' . $currencies->currencies[$key]['id'] . '_' . $data['groups_id']);
                            $sql_data_array['bonus_points_cost'] = Yii::$app->request->post('bonus_points_cost_' . $currencies->currencies[$key]['id'] . '_' . $data['groups_id']);
                        } else {
                            $sql_data_array['bonus_points_price'] = 0;
                            $sql_data_array['bonus_points_cost'] = 0;
                        }
                        if (Yii::$app->request->post('qty_discount_status')) {
                            $products_price_discount = '';
                            $products_price_discount_array = array();
                            $discount_qty = Yii::$app->request->post('discount_qty_' . $currencies->currencies[$key]['id'] . '_' . $data['groups_id'], array());
                            $discount_price = Yii::$app->request->post('discount_price_' . $currencies->currencies[$key]['id'] . '_' . $data['groups_id'], array());
                            foreach ($discount_qty as $qtykey => $val) {
                                if ($discount_qty[$qtykey] > 0 && $discount_price[$qtykey] > 0) {
                                    $products_price_discount_array[$discount_qty[$qtykey]] = $discount_price[$qtykey];
                                }
                            }
                            ksort($products_price_discount_array, SORT_NUMERIC);
                            foreach ($products_price_discount_array as $qty => $price) {
                                $products_price_discount .= $qty . ':' . $price . ';';
                            }
                            $sql_data_array['products_group_discount_price'] = $products_price_discount;
                        }
                        tep_db_perform(TABLE_PRODUCTS_PRICES, $sql_data_array);
                    }
                }
    }
    
    public static function saveGroup($products_id, $specials_id) {
        if (!self::allowed()) {
            return '';
        }
        if (\common\helpers\Extensions::isCustomerGroupsAllowed()) {
            $pack_unit_full_prices = Yii::$app->request->post('pack_unit_full_prices', array());
            $packaging_full_prices = Yii::$app->request->post('packaging_full_prices', array());
                $groups_data_query = tep_db_query("select * from " . TABLE_GROUPS . " order by groups_id");
                while ($groups_data = tep_db_fetch_array($groups_data_query)) {
                    $sql_data_array = [];
                    $sql_data_array['products_group_price'] = Yii::$app->request->post('products_groups_prices_' . $groups_data['groups_id'], '-2');
                    if (Yii::$app->request->post('bonus_points_status')) {
                        $sql_data_array['bonus_points_price'] = Yii::$app->request->post('bonus_points_price_' . $groups_data['groups_id']);
                        $sql_data_array['bonus_points_cost'] = Yii::$app->request->post('bonus_points_cost_' . $groups_data['groups_id']);
                    } else {
                        $sql_data_array['bonus_points_price'] = 0;
                        $sql_data_array['bonus_points_cost'] = 0;
                    }
                    if (Yii::$app->request->post('qty_discount_status')) {
                        $products_price_discount = '';
                        $products_price_discount_array = array();
                        $discount_qty = Yii::$app->request->post('discount_qty_' . $groups_data['groups_id'], array());
                        $discount_price = Yii::$app->request->post('discount_price_' . $groups_data['groups_id'], array());
                        foreach ($discount_qty as $key => $val) {
                            if ($discount_qty[$key] > 0 && $discount_price[$key] > 0) {
                                $products_price_discount_array[$discount_qty[$key]] = $discount_price[$key];
                            }
                        }
                        ksort($products_price_discount_array, SORT_NUMERIC);
                        foreach ($products_price_discount_array as $qty => $price) {
                            $products_price_discount .= $qty . ':' . $price . ';';
                        }
                        $sql_data_array['products_group_discount_price'] = $products_price_discount;
                    } else {
                        $sql_data_array['products_group_discount_price'] = '';
                    }

                    if (Yii::$app->request->post('ifpopt_pack_unit_' . $groups_data['groups_id']) == -2) {
                        $sql_data_array['products_group_price_pack_unit'] = -2;
                    } elseif (Yii::$app->request->post('ifpopt_pack_unit_' . $groups_data['groups_id']) == -1) {
                        $sql_data_array['products_group_price_pack_unit'] = -1;
                    } else {
                        $sql_data_array['products_group_price_pack_unit'] = $pack_unit_full_prices[$groups_data['groups_id']];
                    }

                    if (Yii::$app->request->post('ifpopt_packaging_' . $groups_data['groups_id']) == -2) {
                        $sql_data_array['products_group_price_packaging'] = -2;
                    } elseif (Yii::$app->request->post('ifpopt_packaging_' . $groups_data['groups_id']) == -1) {
                        $sql_data_array['products_group_price_packaging'] = -1;
                    } else {
                        $sql_data_array['products_group_price_packaging'] = $packaging_full_prices[$groups_data['groups_id']];
                    }

                    $check = tep_db_fetch_array(tep_db_query("select count(*) as products_price_exists from " . TABLE_PRODUCTS_PRICES . " where products_id = '" . (int) $products_id . "' and groups_id = '" . (int) $groups_data['groups_id'] . "' and currencies_id = '0'"));
                    if ($check['products_price_exists']) {
                        tep_db_perform(TABLE_PRODUCTS_PRICES, $sql_data_array, 'update', "products_id = '" . (int) $products_id . "' and groups_id = '" . (int) $groups_data['groups_id'] . "' and currencies_id = '0'");
                    } else {
                        $sql_data_array['products_id'] = $products_id;
                        $sql_data_array['groups_id'] = $groups_data['groups_id'];
                        $sql_data_array['currencies_id'] = 0;
                        tep_db_perform(TABLE_PRODUCTS_PRICES, $sql_data_array);
                    }

                    if (Yii::$app->request->post('specials_status')) {
                        self::saveSpecialPrices($specials_id, $groups_data['groups_id'], 0, Yii::$app->request->post('specials_groups_prices_' . $group_id, '-2'));
                    }
                }
            }
    }
    
    public static function saveSpecialPrices($specials_id, $group_id, $currencies_id = 0,$specials_groups_prices = 0){
        if (!self::allowed()) {
            return '';
        }
        //used not only group but also market price
        \common\helpers\Product::save_specials_prices($specials_id, $group_id, $currencies_id,$specials_groups_prices);
    }
    
    public static function adminGroups() {
        if (!self::allowed()) {
            return '';
        }
        Yii::$app->controller->topButtons[] = '<a href="'.\yii\helpers\Url::to(['groups/itemedit', 'item_id' => 0, 'groups_type_id'=> Yii::$app->request->get('groups_type_id', 0)]).'" class="create_item"><i class="icon-file-text"></i>' . TEXT_INS_CUS_GROUP . '</a>';
        $messages = Yii::$app->session->getAllFlashes();
        $row = Yii::$app->request->get('row', 0);
        $html = \common\extensions\UserGroups\Render::widget(['template' => 'index.tpl', 'params' => ['messages' => $messages, 'row' => $row]]);
        return Yii::$app->controller->renderContent($html);
    }

    public static function adminPreeditGroups() {
        if (!self::allowed()) {
            return '';
        }
        global $language;
        $languages_id = \Yii::$app->settings->get('languages_id');

        \common\helpers\Translation::init('admin/groups');

        $item_id = (int) Yii::$app->request->post('item_id');

        $mInfo = Groups::findOne((int) $item_id);

        if (!$mInfo) {
            die("Please select group.");
        }
        $typeId = 0;

        /** @var \common\extensions\ExtraGroups\ExtraGroups $ext */
        if ($ext = \common\helpers\Acl::checkExtension('ExtraGroups', 'allowed')) {
          if ($ext::allowed()) {
            $typeId = $ext::getGroupType($item_id);
          }
        }
        $ret = '';

        $ret .= '<div class="or_box_head">' . $mInfo->groups_name . '</div>';
        $ret .= '<div class="row_or"><div>' . TEXT_DATE_ADDED . '</div><div>' . \common\helpers\Date::date_short($mInfo->date_added) . '</div></div>';
        if (tep_not_null($mInfo->last_modified)) {
          $ret .= '<div class="row_or"><div>' . TEXT_LAST_MODIFIED . '</div><div>' . \common\helpers\Date::date_short($mInfo->last_modified) . '</div></div>';
        }
        if ($typeId==0) {
          //$data = tep_db_fetch_array(tep_db_query("select count(customers_id) as total_customers from " . TABLE_CUSTOMERS . " where groups_id = '" . (int) $mInfo->groups_id . "'"));
          $total_customers = \common\models\Customers::find()->andWhere(['groups_id' => (int) $mInfo->groups_id])->count();
        }
        $ret .= '<div class="btn-toolbar btn-toolbar-order">';
        $ret .= '<a href="' . \yii\helpers\Url::to(['groups/itemedit', 'item_id' => $item_id, 'row_id' => Yii::$app->request->post('row_id',0)]) . '" class="btn btn-edit btn-primary btn-process-order ">' . IMAGE_EDIT . '</a>';
        if ($typeId==0) {
          $ret .= '<a class="btn btn-no-margin btn-process-order " onclick="customersGroupEdit(' . $mInfo->groups_id . ')" href="javascript:void(0)">' . TEXT_EDIT_CUSTOMERS . '&nbsp;(' . (int) $total_customers . ')</a>';
        }
        $ret .= '<button onclick="return deleteItemConfirm(' . $item_id . ')" class="btn btn-delete btn-no-margin btn-process-order ">' . IMAGE_DELETE . '</button></div>';
        return $ret;
    }

    public static function adminEditGroups() {
        if (!self::allowed()) {
            return '';
        }
        $popup = (int) Yii::$app->request->get('popup');

        global $language;
        $languages_id = \Yii::$app->settings->get('languages_id');
        
        \common\helpers\Translation::init('admin/categories');

        $item_id = (int) Yii::$app->request->get('item_id');
        $row_id = (int) Yii::$app->request->get('row_id');
        
        $mInfo = Groups::find()->where('groups_id =:id', [':id' => (int) $item_id ])->with('additionalDiscounts')->one();

        if (!$mInfo) {
            $mInfo = new Groups();
            $mInfo->setAttributes([
                'groups_is_tax_applicable' => 1,
                'groups_is_show_price' => 1,
            ], false);
        }
        
        /** @var \common\extensions\ExtraGroups\ExtraGroups $ext */
        if ($ext = \common\helpers\Acl::checkExtension('ExtraGroups', 'allowed')) {
          if ($ext::allowed()) {
            $showOtherGroups = $ext::getGroupType($item_id);
          }
        }

        foreach (\common\helpers\Hooks::getList('customergroups/groupedit/before-render') as $filename) {
            include($filename);
        }

        return \common\extensions\UserGroups\Render::widget(['template' => 'edit.tpl', 'params' => [
            'popup' => $popup,
            'page' => \Yii::$app->request->get('page'),
            'mInfo' => $mInfo,
            'active' => is_file(DIR_FS_CATALOG_IMAGES . 'icons/' . $mInfo->image_active)? DIR_FS_CATALOG_IMAGES . 'icons/' . $mInfo->image_active : '',
            'inactive' => is_file(DIR_FS_CATALOG_IMAGES . 'icons/' . $mInfo->image_inactive)? DIR_FS_CATALOG_IMAGES . 'icons/' . $mInfo->image_inactive : '',
            'row_id' => $row_id,
            'item_id' => $item_id,
            'showOtherGroups' => $showOtherGroups ?? null,
        ]]);
        //return Yii::$app->controller->renderPartial($html);
    }
    
    public static function adminConfirmDeleteGroups() {
        if (!self::allowed()) {
            return '';
        }
        \common\helpers\Translation::init('admin/groups');

        $item_id = (int) Yii::$app->request->post('item_id');
        
        $groups_query = tep_db_query("select * from " . TABLE_GROUPS . " where groups_id = '" . (int) $item_id . "'");
        $groups = tep_db_fetch_array($groups_query);

        $mInfo = new \objectInfo($groups);

        echo '<div class="or_box_head">' . TEXT_HEADING_DELETE_GROUP . '</div>';
        echo tep_draw_form('groups', FILENAME_GROUPS, 'page=' . \Yii::$app->request->get('page') . '&gID=' . $mInfo->groups_id . '&action=deleteconfirm', 'post', 'id="item_delete" onsubmit="return deleteItem();"');
        echo '<div class="row_fields">' . TEXT_DELETE_INTRO . '</div>';
        echo '<div class="row_fields"><b>' . $mInfo->groups_name . '</b></div>';
        echo '<div class="btn-toolbar btn-toolbar-order"><button class="btn btn-delete btn-no-margin">' . IMAGE_DELETE . '</button><input type="button" class="btn btn-cancel" value="' . IMAGE_CANCEL . '" onClick="return cancelStatement()"></div>';
        echo tep_draw_hidden_field('item_id', $item_id);
        echo '</form>';
    }

    public static function adminSubmitGroups() {
        if (!self::allowed()) {
            return '';
        }
        $popup = (int) Yii::$app->request->post('popup');
        \common\helpers\Translation::init('admin/groups');

        $groups_id = 0;
        if (isset($_POST['item_id']))
            $groups_id = tep_db_prepare_input($_POST['item_id']);
        $groups_name = tep_db_prepare_input($_POST['groups_name']);
        $groups_discount = tep_db_prepare_input($_POST['groups_discount'] ?? null);
        $apply_groups_discount_to_specials = isset($_POST['apply_groups_discount_to_specials']) ? 1 : 0;
        $groups_is_reseller = tep_db_prepare_input($_POST['groups_is_reseller'] ?? null);
        $new_approve = tep_db_prepare_input($_POST['new_approve'] ?? null);
        $bonusPointsCurrencyRate = (float)\Yii::$app->request->post('bonus_points_currency_rate', 0.0);
        $groups_default = $_POST['default'] ?? null;
        $groups_use_more_discount = $_POST['groups_use_more_discount'] ?? null;
        $superdiscount_summ = tep_db_prepare_input($_POST['superdiscount_summ']??null);
        $per_product_price = (int)Yii::$app->request->post('per_product_price', 0);
        $default_landing_page = trim(Yii::$app->request->post('default_landing_page', ''));
        $sql_data_array = [
            'groups_name' => $groups_name,
            'groups_discount' => $groups_discount,
            'groups_commission' => (float)\Yii::$app->request->post('groups_commission', 0.0),
            'apply_groups_discount_to_specials' => $apply_groups_discount_to_specials,
            'new_approve' => (int) $new_approve,
            'groups_is_reseller' => (int) $groups_is_reseller,
            'disable_watermark' => (int) Yii::$app->request->post('disable_watermark'),
            'cart_for_logged_only' => (int) Yii::$app->request->post('cart_for_logged_only'),
            'groups_use_more_discount' => (int)$groups_use_more_discount,
            'superdiscount_summ' => (float)$superdiscount_summ,
            'bonus_points_currency_rate' => $bonusPointsCurrencyRate,
            'per_product_price' => $per_product_price,
            'default_landing_page' => $default_landing_page,
        ];

        foreach (\common\helpers\Hooks::getList('customergroups/groupedit/before-save') as $filename) {
            include($filename);
        }
        /* NOT FIXED
        $image_active = new \upload('image_active');
        $image_active->set_destination(DIR_FS_CATALOG_IMAGES . 'icons/');
        if ($image_active->parse() && $image_active->save()) {
            $sql_data_array['image_active'] = $image_active->filename;
        }
        $image_inactive = new \upload('image_inactive');
        $image_inactive->set_destination(DIR_FS_CATALOG_IMAGES . 'icons/');
        if ($image_inactive->parse() && $image_inactive->save()) {
            $sql_data_array['image_inactive'] = $image_inactive->filename;
        }*/

        if ($groups_id == 0) {
            $insert_sql_data = array('date_added' => 'now()');

            $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

            tep_db_perform(TABLE_GROUPS, $sql_data_array);
            $groups_id = tep_db_insert_id();
        } else {
            $update_sql_data = array('last_modified' => 'now()');
            $sql_data_array = array_merge($sql_data_array, $update_sql_data);
            tep_db_perform(TABLE_GROUPS, $sql_data_array, 'update', "groups_id = '" . (int) $groups_id . "'");
        }
        
        $groups_discounts_amount = Yii::$app->request->post('groups_discounts_amount', []);
        $groups_discounts_value = Yii::$app->request->post('groups_discounts_value', []);
        $check_supersum_hidden = Yii::$app->request->post('check_supersum_hidden', []);
        
        if (is_array($groups_discounts_amount) && $groups_use_more_discount){
            GroupsDiscounts::deleteAll('groups_id =:id', [':id' => $groups_id]);
            if (count($groups_discounts_amount)){
                foreach($groups_discounts_amount as $key => $dsc){
                    if(!empty($dsc) && !empty($groups_discounts_value[$key])) {
                        $nDS = new GroupsDiscounts();
                        $nDS->setAttributes([
                            'groups_id' => $groups_id,
                            'groups_discounts_amount' => $dsc,
                            'groups_discounts_value' => $groups_discounts_value[$key],
                            'check_supersum' => $check_supersum_hidden[$key]? 1:0,
                        ], false);
                        $nDS->save(false);
                    }
                }
            }            
        } else {
            GroupsDiscounts::deleteAll('groups_id =:id', [':id' => $groups_id]);
        }

        /** @var \common\extensions\UserGroupsExtraDiscounts\UserGroupsExtraDiscounts $ext */
        if ($ext = \common\helpers\Extensions::isAllowed('UserGroupsExtraDiscounts')) {
            $ext::adminSubmitGroups($groups_id);
        }

        if ($popup == 1) {
            $q = Groups::find()->orderBy('groups_name')->asArray()->select('groups_name, groups_id')->indexBy('groups_id');

            /** @var \common\extensions\ExtraGroups\ExtraGroups $extraGroups */
            if ($extraGroups = \common\helpers\Extensions::isAllowed('ExtraGroups')) {
                $group_type_id = $extraGroups::saveGroup($groups_id);
                $q->andWhere(['groups_type_id' => $group_type_id]);
            }
            echo '<option value=""></option>';
            
            foreach ($q->all() as $status ) {
                echo '<option value="' . $status['groups_id'] . '"' . ($status['groups_id'] == $groups_id ? ' selected' : '') . '>' . $status['groups_name'] . '</option>';
            }
        } else {
            Yii::$app->session->setFlash('success', TEXT_MESSEAGE_SUCCESS);
        }
    }
    
    public static function adminDeleteGroups() {
        if (!self::allowed()) {
            return '';
        }
        $groups_id = (int) Yii::$app->request->post('item_id');
        \common\components\CategoriesCache::getCPC()::invalidateGroups($groups_id);

        if (DEFAULT_USER_GROUP == $groups_id) {
            tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '0' where configuration_key = 'DEFAULT_USER_GROUP'");
        }
        if (DEFAULT_USER_LOGIN_GROUP == $groups_id) {
            tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '0' where configuration_key = 'DEFAULT_USER_LOGIN_GROUP'");
        }

        tep_db_query("delete from " . TABLE_GROUPS . " where groups_id = '" . (int) $groups_id . "'");

        tep_db_query("update " . TABLE_CUSTOMERS . " set groups_id = '0' where groups_id = '" . (int) $groups_id . "'");
        tep_db_query("delete from " . TABLE_PRODUCTS_PRICES . " where groups_id = '" . (int) $groups_id . "'");
        tep_db_query("delete from " . TABLE_PRODUCTS_ATTRIBUTES_PRICES . " where groups_id = '" . (int) $groups_id . "'");
        tep_db_query("delete from " . TABLE_SPECIALS_PRICES . " where groups_id = '" . (int) $groups_id . "'");
        GroupsDiscounts::deleteAll('groups_id=:id', [':id' => $groups_id]);
        /** @var \common\extensions\ExtraGroups\ExtraGroups $ext */
        if ($ext = \common\helpers\Acl::checkExtension('ExtraGroups', 'allowed')) {
          if ($ext::allowed()) {
            $ext::deleteGroup($groups_id);
          }
        }
    }
    
    public static function adminCustomersGroups() {
        if (!self::allowed()) {
            return '';
        }
        $customers_id_in_group = array();
        $data_query = tep_db_query("select g.groups_name, c.customers_id from " . TABLE_GROUPS . " g, " . TABLE_CUSTOMERS . " c where g.groups_id = c.groups_id and g.groups_id = '" . (int) $_GET['groups_id'] . "'");
        while ($data = tep_db_fetch_array($data_query)) {
            $group_name = $data['groups_name'];
            $customers_id_in_group[] = $data['customers_id'];
        }

        $filter = \Yii::$app->request->get('filter');
        $query = tep_db_query("select c.customers_id, customers_firstname, customers_lastname, count(a.address_book_id) from " . TABLE_CUSTOMERS . " c, " . TABLE_ADDRESS_BOOK . " a where a.customers_id = c.customers_id and c.customers_default_address_id = a.address_book_id " . (strlen($filter) > 0 ? ' and (customers_lastname like "%' . $filter . '%" or customers_firstname like "%' . $filter . '%" or customers_email_address like "%' . $filter . '%" or entry_company like "%' . $filter . '%" or entry_street_address like "%' . $filter . '%" or entry_suburb like "%' . $filter . '%" or entry_postcode like "%' . $filter . '%" or entry_city like "%' . $filter . '%" or entry_state like "%' . $filter . '%" or customers_telephone like "%' . $filter . '%" or customers_fax like "%' . $filter . '%" )' : '') . " group by c.customers_id order by customers_lastname");

        $customers_in_group_options = '';
        $customers_not_in_group_options = '';
        while ($db_Row = tep_db_fetch_array($query)) {
            if (in_array($db_Row["customers_id"], $customers_id_in_group)) {
                $customers_in_group_options .= "<option value=\"" . $db_Row["customers_id"] . "\">" . $db_Row["customers_lastname"] . ", " . $db_Row["customers_firstname"] . "</option>";
            } else {
                $customers_not_in_group_options .= "<option value=\"" . $db_Row["customers_id"] . "\">" . $db_Row["customers_lastname"] . ", " . $db_Row["customers_firstname"] . "</option>";
            }
        }
?>

                <input type="hidden" name="groups_id" id="groups_id" value="<?php echo $_GET['groups_id']; ?>">

                <table border="0">
                    <tr>
                        <td><font class=main><b><?php echo TEXT_FILTER_CUSTOMERS; ?>:</b></font></td>
                        <td><input type="text" name="filter" class="form-control" id="customers_filter" value="<?php echo htmlspecialchars($filter); ?>"></td>
                        <td valign="bottom"><input type="submit" class="btn btn-primary" value="<?= TEXT_FILTER_CUSTOMERS ?>" onclick="customersGroupFilter()"></td>
                    </tr>
                    <tr>
                        <td><font class=main><b><?php echo TEXT_CUS_IN_GROUP; ?>:</b></font></td>
                        <td><select name="customers_id" id="del" class="form-control"><?php echo $customers_in_group_options; ?></select></td>
                        <td valign="bottom"><input type="submit" class="btn btn-delete" value="<?php echo IMAGE_DELETE; ?>" onclick="customersGroupDelete()"></td>
                    </tr>
                    <tr>
                        <td><font class=main><b><?php echo TEXT_CUS_NOT_GROUP; ?>:</b></font></td>
                        <td><select name="customers_id" id="add" class="form-control"><?php echo $customers_not_in_group_options; ?></select></td>
                        <td valign="bottom"><input type="submit" class="btn btn-primary" value="<?php echo IMAGE_INSERT; ?>" onclick="customersGroupAdd()" ></td>
                    </tr>
                </table>

        <?php
        echo '<button class="btn btn-back" onclick="cancelStatement()">' . IMAGE_BACK . '</button>';
    }

    public static function getLandingPage() {
        if (!self::allowed()) {
            return '';
        }
        $ret = '';
        if (!Yii::$app->user->isGuest) {
            $user = Yii::$app->user->getIdentity();

            if (!empty($user->groups_id) && !empty(trim(\common\helpers\Customer::check_customer_groups($user->groups_id, 'default_landing_page')))) {
                $res = \common\helpers\Customer::check_customer_groups($user->groups_id, 'default_landing_page');
                if (strpos($res, '?') !== false) {
                    $ret = substr($res, 0, strpos($res, '?'));
                    $params = [];
                    parse_str(substr($res, strpos($res, '?')+1), $params);
                    if (!empty($params['current_category_id'])) {
                        global $current_category_id;
                        $current_category_id= $params['current_category_id'];
                        unset($params['current_category_id']);
                    }
                    $_GET = array_merge($_GET, $params);
                }
            }
        }
        return $ret;

    }

    public static function groupHasProductPrice($group_id)
    {
        $row = \common\models\Groups::findOne($group_id);
        return !empty($row) && $row->per_product_price > 0;
    }

    public static function addBannerData($bannerData, $banners_id)
    {
        \common\helpers\Translation::init('extensions/user-groups');
        $platformUserGroups = [];

        $platforms = \common\models\BannersToPlatform::find()
            ->where(['banners_id' => $banners_id])->asArray()->all();

        $groupsArray = self::getGroupsArray();

        if (is_array($platforms) && isset($platforms[0]['user_groups'])) {
            foreach ($platforms as $platform) {
                if ($platform['user_groups'] === '#0#') {
                    $platformUserGroups[$platform['platform_id']][] = 0;
                    foreach ($groupsArray as $group) {
                        $platformUserGroups[$platform['platform_id']][] = (int)$group['groups_id'];
                    }
                } else {
                    $groups = explode(',', $platform['user_groups']);
                    foreach ($groups as $group) {
                        $platformUserGroups[$platform['platform_id']][] = (int)trim($group, '# ');
                    }
                }
            }
        }

        $bannerData['platformUserGroups'] = $platformUserGroups;
        $bannerData['groupsArray'] = $groupsArray;
        return $bannerData;
    }

    public static function platformTableCell($bannerData, $platformId)
    {
        return Render::widget([
            'template' => 'platform-table-cell.tpl',
            'params' => [
                'bannerData' => $bannerData,
                'platformId' => $platformId,
            ]
        ]);
    }

    public static function getModel($modelName)
    {
        if ($modelName == 'Groups') {
            return \common\models\Groups::class;
        }
    }

    public static function saveBannerUserGroup()
    {
        $bannerId = (int)Yii::$app->request->post('banners_id', 0);
        $userGroups = Yii::$app->request->post('user_group', []);


        $bannersToPlatform = \common\models\BannersToPlatform::find()->where([
            'banners_id' => $bannerId,
        ])->asArray()->all();

        $usersToPlatforms = [];
        foreach ($bannersToPlatform as $bannerToPlatform) {
            if (isset($userGroups[$bannerToPlatform['platform_id']]) &&
                is_array($userGroups[$bannerToPlatform['platform_id']]) &&
                count($userGroups[$bannerToPlatform['platform_id']])
            ) {
                $ids = [];
                foreach ($userGroups[$bannerToPlatform['platform_id']] as $userId => $flag) {
                    $ids[] = '#' . $userId . '#';
                }
                $usersToPlatforms[$bannerToPlatform['platform_id']] = implode(',', $ids);
            } else {
                $usersToPlatforms[$bannerToPlatform['platform_id']] = '';
            }
        }

        foreach ($usersToPlatforms as $platformId => $groups) {
            $bannersToPlatform = \common\models\BannersToPlatform::findOne([
                'banners_id' => $bannerId,
                'platform_id' => $platformId
            ]);
            if ($bannersToPlatform) {
                $bannersToPlatform->user_groups = $groups;
                $bannersToPlatform->save();
            }
        }
    }

    public static function useWithBanners()
    {
        static $status = null;
        if ($status === null) {
            $btp = \common\models\BannersToPlatform::find()->asArray()->one();
            if (isset($btp['user_groups']) &&
                \common\helpers\PlatformConfig::getVal('USER_GROUPS_WITH_BANNERS', 'false') == 'true'
            ) {
                $status = true;
            } else {
                $status = false;
            }
        }
        return $status;
    }

}
