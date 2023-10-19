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

use common\classes\warehouse;
use common\helpers\Acl;
use common\models\Warehouses;
use Yii;
use \yii\helpers\Html;

class WarehousesController extends Sceleton {

    public $acl = ['BOX_CATALOG_WAREHOUSES'];
    protected $selected_platform_id;

    public function __construct($id, $module=null) {
        parent::__construct($id, $module);

        $this->selected_platform_id = \common\classes\platform::firstId();
        $try_set_platform = Yii::$app->request->get('platform_id', 0);
        if (Yii::$app->request->isPost) {
            $try_set_platform = Yii::$app->request->post('platform_id', $try_set_platform);
        }
        if ($try_set_platform > 0) {
            foreach (\common\classes\platform::getList(false) as $_platform) {
                if ((int) $try_set_platform == (int) $_platform['id']) {
                    $this->selected_platform_id = (int) $try_set_platform;
                }
            }
            //Yii::$app->get('platform')->config($this->selected_platform_id)->constant_up();
        }
        \common\helpers\Translation::init('admin/warehouses');
    }

    /**
     * Index action is the default action in a controller.
     */
    public function actionIndex() {

        $this->selectedMenu = array('catalog', 'warehouses');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('warehouses/index'), 'title' => HEADING_TITLE);
        $this->topButtons[] = '<a href="' . Yii::$app->urlManager->createUrl('warehouses/edit') . '" class="btn btn-primary addprbtn"><i class="icon-tag"></i>' . TEXT_CREATE_NEW_WAREHOUSE . '</a>';
        $this->topButtons[] = '<a href="' . Yii::$app->urlManager->createUrl('warehouses/location-blocks') . '" class="btn btn-primary">' . TEXT_MANAGE_LOCATION_BLOCKS . '</a>';

        $this->view->headingTitle = HEADING_TITLE;
        $this->view->groupsTable = array(
            array(
                'title' => TABLE_HEADING_WAREHOUSE_NAME,
                'not_important' => 1
            ),
            array(
                'title' => TABLE_HEADING_STATUS,
                'not_important' => 1
            ),
        );

        $this->view->filters = new \stdClass();
        $this->view->filters->row = (int)Yii::$app->request->get('row', 0);

        $_platforms = \common\classes\platform::getList(false);
        foreach( $_platforms as $_idx => $_platform ) {
            $_platforms[$_idx]['link'] = Yii::$app->urlManager->createUrl(['warehouses/index', 'platform_id' => $_platform['id']]);
        }
        return $this->render('index', [
              'platforms' => $_platforms,
              'isMultiPlatforms' => \common\classes\platform::isMulti(false),
              'selected_platform_id' => $this->selected_platform_id,
            ]);
    }

    public function actionList() {
        $draw = Yii::$app->request->get('draw', 1);
        $start = Yii::$app->request->get('start', 0);
        $length = Yii::$app->request->get('length', 10);

        $formFilter = Yii::$app->request->get('filter');
        parse_str($formFilter, $output);

        if (isset($output['platform_id'])) {
            $this->selected_platform_id = (int)$output['platform_id'];
        }

        $responseList = array();
        if ($length == -1)
            $length = 10000;
        $query_numrows = 0;

        //TODO search
        if (isset($_GET['search']['value']) && tep_not_null($_GET['search']['value'])) {
            $keywords = tep_db_input(tep_db_prepare_input($_GET['search']['value']));
            $search_condition = " and w.warehouse_name like '%" . $keywords . "%' ";
        } else {
            $search_condition = "";
        }

        if (isset($_GET['order'][0]['column']) && $_GET['order'][0]['dir']) {
            switch ($_GET['order'][0]['column']) {
                case 0:
                    $orderBy = "w.warehouse_name " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir']));
                    break;
                case 1:
                    $orderBy = "ifnull(w2p.sort_order, w.sort_order) " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir'])) . ", w.warehouse_id ";
                    break;
                default:
                    $orderBy = "ifnull(w2p.sort_order, w.sort_order), w.warehouse_name";
                    break;
            }
        } else {
            $orderBy = "ifnull(w2p.sort_order, w.sort_order), w.warehouse_name";
        }

        $query_show = 0;
        //$warehouses_query_raw = "select * from " . TABLE_WAREHOUSES . $search_condition . " order by " . $orderBy;
        $warehouses_query_raw = "select w.warehouse_id, w.warehouse_name, w.is_default, ifnull(w2p.status, w.status) as status from " . TABLE_WAREHOUSES . " w left join " . TABLE_WAREHOUSES_TO_PLATFORMS . " w2p on w.warehouse_id = w2p.warehouse_id and w2p.platform_id = '" . (int)$this->selected_platform_id . "' where 1 " . $search_condition . " order by " . $orderBy;
        $current_page_number = ( $start / $length ) + 1;
        $_split = new \splitPageResults($current_page_number, $length, $warehouses_query_raw, $query_numrows, 'w.warehouse_id');
        $warehouses_query = tep_db_query($warehouses_query_raw);
        while ($warehouses = tep_db_fetch_array($warehouses_query)) {

            $status = '<input type="checkbox" value="' . $warehouses['warehouse_id'] . '" name="status" class="check_on_off" ' . ((int) $warehouses['status'] > 0 ? 'checked="checked"' : '') . ((int) $warehouses['is_default'] > 0 ? 'readonly="readonly"' : '') . '>';

            $responseList[] = array(
                '<div class="handle_cat_list"><span class="handle"><i class="icon-hand-paper-o"></i></span><div class="cat_name cat_name_attr cat_no_folder">' .
                $warehouses['warehouse_name'] .
                '<input class="cell_identify" type="hidden" value="' . $warehouses['warehouse_id'] . '">' .
                '<input class="cell_type" type="hidden" value="top">' .
                '</div></div>',
                $status,
            );
            $query_show++;
        }

        $response = array(
            'draw' => $draw,
            'recordsTotal' => $query_numrows,
            'recordsFiltered' => $query_show,
            'data' => $responseList
        );
        echo json_encode($response);
    }

    public function actionSwitchStatus() {
        $id = Yii::$app->request->post('id');
        $status = Yii::$app->request->post('status');
        if ($this->selected_platform_id == \common\classes\platform::defaultId()) {
            tep_db_query("UPDATE " . TABLE_WAREHOUSES . " SET status = '" . ($status == 'true' ? 1 : 0) . "' WHERE warehouse_id = '" . (int) $id . "'");
        }
        $check = tep_db_fetch_array(tep_db_query("SELECT warehouse_id FROM " . TABLE_WAREHOUSES_TO_PLATFORMS . " WHERE warehouse_id = '" . (int) $id . "' AND platform_id = '" . (int) $this->selected_platform_id . "'"));
        if ($check['warehouse_id'] == $id) {
            tep_db_query("UPDATE " . TABLE_WAREHOUSES_TO_PLATFORMS . " SET status = '" . ($status == 'true' ? 1 : 0) . "' WHERE warehouse_id = '" . (int) $id . "' AND platform_id = '" . (int) $this->selected_platform_id . "'");
        } else {
            tep_db_query("INSERT INTO " . TABLE_WAREHOUSES_TO_PLATFORMS . " SET warehouse_id = '" . (int) $id . "', platform_id = '" . (int) $this->selected_platform_id . "', status = '" . ($status == 'true' ? 1 : 0) . "'");
        }
        \common\helpers\Warehouses::warehouse_status_change();

    }

    public function actionItemPreedit() {
        $this->layout = false;

        \common\helpers\Translation::init('admin/warehouses');

        $item_id = (int) Yii::$app->request->post('item_id');

        $warehouses_query = tep_db_query("select * from " . TABLE_WAREHOUSES . " where warehouse_id = '" . (int) $item_id . "'");
        $warehouses = tep_db_fetch_array($warehouses_query);

        if (!is_array($warehouses)) {
            die("");
        }

        $mInfo = new \objectInfo($warehouses);

        echo '<div class="or_box_head">' . $mInfo->warehouse_name . '</div>';
        echo '<div class="row_or"><div>' . TEXT_DATE_ADDED . '</div><div>' . \common\helpers\Date::date_short($mInfo->date_added) . '</div></div>';
        if (tep_not_null($mInfo->last_modified)) {
            echo '<div class="row_or"><div>' . TEXT_LAST_MODIFIED . '</div><div>' . \common\helpers\Date::date_short($mInfo->last_modified) . '</div></div>';
        }
        echo '<div class="btn-toolbar btn-toolbar-order">
            <a href="' . Yii::$app->urlManager->createUrl(['warehouses/edit', 'id' => $item_id]) . '" class="btn btn-edit btn-primary btn-process-order ">' . IMAGE_EDIT . '</a>
            ' . ($warehouses['is_default'] ? '' : ('<button onclick="return deleteItemConfirm(' . $item_id . ')" class="btn btn-delete btn-no-margin btn-process-order ">' . IMAGE_DELETE . '</button>')) . '
            <a href="' . Yii::$app->urlManager->createUrl(['warehouses/locations', 'id' => $item_id]) . '" class="btn btn-edit btn-primary btn-process-order ">' . TEXT_WAREHOUSE_LOCATIONS . '</a>';
        if (\common\helpers\Acl::checkExtensionAllowed('ProductsRelocate')) {
            echo  '<a href="' . Yii::$app->urlManager->createUrl(['products-relocate/create', 'warehouse_id' => $item_id]) . '" class="btn btn-primary btn-process-order ">' . BOX_PRODUCTS_RELOCATE . '</a>';
        }
        echo '</div>';
    }

    public function actionEdit() {
        $languages_id = \Yii::$app->settings->get('languages_id');
        \common\helpers\Translation::init('admin/warehouses');
        \common\helpers\Translation::init('admin/platforms');
        \common\helpers\Translation::init('admin/platforms/edit');

        $days = [
            0 => TEXT_EVERYDAY,
            1 => TEXT_MONDAY,
            2 => TEXT_TUESDAY,
            3 => TEXT_WEDNESDAY,
            4 => TEXT_THURSDAY,
            5 => TEXT_FRIDAY,
            6 => TEXT_SATURDAY,
            7 => TEXT_SUNDAY,
        ];

        if (Yii::$app->request->isPost) {
            $item_id = (int) Yii::$app->request->post('id');
        } else {
            $item_id = (int) Yii::$app->request->get('id');
        }

        $this->topButtons[] = '<span class="btn btn-confirm" onclick="$(\'#save_item_form\').trigger(\'submit\')">' . IMAGE_SAVE . '</span>';

        if ($item_id > 0) {
            $warehouses_query = tep_db_query("select * from " . TABLE_WAREHOUSES . " where warehouse_id = '" . (int) $item_id . "'");
            $warehouses = tep_db_fetch_array($warehouses_query);
            $pInfo = new \objectInfo($warehouses);
        } else {
            $pInfo = new \objectInfo([]);
        }

        $address_query = tep_db_query("select ab.*, if (LENGTH(ab.entry_state), ab.entry_state, z.zone_name) as entry_state, c.countries_name  from " . TABLE_WAREHOUSES_ADDRESS_BOOK . " ab left join " . TABLE_COUNTRIES . " c on ab.entry_country_id=c.countries_id  and c.language_id = '" . (int) $languages_id . "' left join " . TABLE_ZONES . " z on z.zone_country_id=c.countries_id and ab.entry_zone_id=z.zone_id where warehouse_id = '" . (int) $item_id . "' ");
        $d = tep_db_fetch_array($address_query);
        if (!isset($d['entry_country_id'])) {
            $d['entry_country_id'] = STORE_COUNTRY;
        }
        $addresses = new \objectInfo($d);

        $open_hours = [];
        $open_hours_query = tep_db_query("select * from " . TABLE_WAREHOUSES_OPEN_HOURS . " where warehouse_id = '" . (int) $item_id . "' ");
        while ($d = tep_db_fetch_array($open_hours_query)) {
            if (isset($d['open_days'])) {
                $d['open_days'] = explode(",", $d['open_days']);
            }
            $open_hours[] = new \objectInfo($d);
        }
        if (count($open_hours) == 0) {
            $open_hours[] = new \objectInfo([]);
        }

        $text_new_or_edit = ($item_id == 0) ? TEXT_INFO_HEADING_NEW_WAREHOUSE : TEXT_INFO_HEADING_EDIT_WAREHOUSE;
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('warehouses/'), 'title' => $text_new_or_edit . ' ' . ($pInfo->warehouses_name ?? null));
        $this->selectedMenu = array('catalog', 'warehouses');

        if (Yii::$app->request->isPost) {
            $this->layout = false;
        }
        $this->view->showState = (ACCOUNT_STATE == 'required' || ACCOUNT_STATE == 'visible');

        $have_one_or_more_warehouse = true;
        if (tep_db_num_rows(tep_db_query("select warehouse_id from " . TABLE_WAREHOUSES . "")) < 1) {
            $have_one_or_more_warehouse = false;
            if (!$pInfo->is_default)
                $pInfo->is_default = 1;
        }
        $checkbox_default_warehouse_attr = array();
        if ($pInfo->is_default ?? null) {
            // disable off for default - only on available
            $checkbox_default_warehouse_attr['readonly'] = 'readonly';
        }

        $_platforms = \common\classes\platform::getList(true, true);
        foreach ($_platforms as $_idx => $_platform) {
            $check = tep_db_fetch_array(tep_db_query("select ifnull(w2p.status, w.status) as status from " . TABLE_WAREHOUSES . " w left join " . TABLE_WAREHOUSES_TO_PLATFORMS . " w2p on w.warehouse_id = w2p.warehouse_id and w2p.platform_id = '" . (int) $_platform['id'] . "' where w.warehouse_id = '" . (int) $item_id . "'"));
            $_platforms[$_idx]['status'] = $check['status'] ?? null;
        }

        return $this->render('edit.tpl', [
                    'pInfo' => $pInfo,
                    'addresses' => $addresses,
                    'open_hours' => $open_hours, 'count_open_hours' => count($open_hours),
                    'days' => $days,
                    'checkbox_default_warehouse_attr' => $checkbox_default_warehouse_attr,
                    'have_one_or_more_warehouse' => $have_one_or_more_warehouse,
                    'platforms' => $_platforms,
                    'isMultiPlatforms' => \common\classes\platform::isMulti(false),
        ]);
    }

    public function actionSubmit() {
        \common\helpers\Translation::init('admin/warehouses');

        if (Yii::$app->request->isPost) {
            $item_id = (int) Yii::$app->request->post('id');
        } else {
            $item_id = (int) Yii::$app->request->get('id');
        }
        $warehouse_owner = '';
        if ($es = \common\helpers\Extensions::isAllowed('EventSystem')) {
            $exhibitor = Yii::$app->request->post('exhibitors_id', false);
            if ($exhibitor) {
                $model = $es::venue()->exec('getExhibtor', [(int)$exhibitor]);
                if (!empty($model))
                {
                    $warehouse_owner = tep_db_prepare_input($model->exhibitors_name);
                }
            }
        } else {
            $warehouse_owner = tep_db_prepare_input(Yii::$app->request->post('warehouse_owner'));
        }

        $warehouse_name = tep_db_prepare_input(Yii::$app->request->post('warehouse_name'));

        $warehouse_email_address = tep_db_prepare_input(Yii::$app->request->post('warehouse_email_address'));
        $warehouse_telephone = tep_db_prepare_input(Yii::$app->request->post('warehouse_telephone'));
        $warehouse_landline = tep_db_prepare_input(Yii::$app->request->post('warehouse_landline'));

        $is_default = false;
        if (Yii::$app->request->post('present_is_default')) {
            $is_default = Yii::$app->request->post('is_default', 0);
        }
        $platform_status = Yii::$app->request->post('platform_status', []);
        $status = (int) Yii::$app->request->post('status', $platform_status[\common\classes\platform::defaultId()] ?? null);

        $is_store = (int) Yii::$app->request->post('is_store');

        $this->layout = false;
        $error = false;
        $message = '';
        $script = '';
        $delete_btn = '';

        $messageType = 'success';

        $entry_company = tep_db_prepare_input(Yii::$app->request->post('entry_company'));
        $entry_company_vat = tep_db_prepare_input(Yii::$app->request->post('entry_company_vat'));
        $entry_company_reg_number = tep_db_prepare_input(Yii::$app->request->post('entry_company_reg_number'));
        $entry_postcode = tep_db_prepare_input(Yii::$app->request->post('entry_postcode'));
        $entry_street_address = tep_db_prepare_input(Yii::$app->request->post('entry_street_address'));
        $entry_suburb = tep_db_prepare_input(Yii::$app->request->post('entry_suburb'));
        $entry_city = tep_db_prepare_input(Yii::$app->request->post('entry_city'));
        $entry_state = tep_db_prepare_input(Yii::$app->request->post('entry_state'));
        $entry_country_id = tep_db_prepare_input(Yii::$app->request->post('entry_country_id'));
        $address_book_ids = tep_db_prepare_input(Yii::$app->request->post('warehouses_address_book_id'));
        $entry_zone_id = [];

        $entry_post_code_error = false;
        $entry_street_address_error = false;
        $entry_city_error = false;
        $entry_country_error = false;
        $entry_state_error = false;

        foreach ($address_book_ids as $address_book_key => $address_book_id) {

            $skipAddress = false;

            /* if (strlen($entry_postcode[$address_book_key]) < ENTRY_POSTCODE_MIN_LENGTH) {
              if ($address_book_id > 0) {
              $error = true;
              $entry_post_code_error = true;
              }
              $skipAddress = true;
              }

              if (strlen($entry_street_address[$address_book_key]) < ENTRY_STREET_ADDRESS_MIN_LENGTH) {
              if ($address_book_id > 0) {
              $error = true;
              $entry_street_address_error = true;
              }
              $skipAddress = true;
              }

              if (strlen($entry_city[$address_book_key]) < ENTRY_CITY_MIN_LENGTH) {
              if ($address_book_id > 0) {
              $error = true;
              $entry_city_error = true;
              }
              $skipAddress = true;
              }

              if ((int)$entry_country_id[$address_book_key] == 0) {
              if ($address_book_id > 0) {
              $error = true;
              $entry_country_error = true;
              }
              $skipAddress = true;
              } */

            if ($address_book_id == 0 && $skipAddress) {
                unset($address_book_ids[$address_book_key]);
                continue;
            }

            if (ACCOUNT_STATE == 'required' || ACCOUNT_STATE == 'visible') {
                if ($entry_country_error == true) {
                    //$entry_state_error = true;
                } else {
                    $entry_zone_id[$address_book_key] = 0;
                    //$entry_state_error = false;
                    $check_query = tep_db_query("select count(*) as total from " . TABLE_ZONES . " where zone_country_id = '" . (int) $entry_country_id[$address_book_key] . "'");
                    $check_value = tep_db_fetch_array($check_query);
                    $entry_state_has_zones = ($check_value['total'] > 0);
                    if ($entry_state_has_zones == true) {
                        $zone_query = tep_db_query("select zone_id from " . TABLE_ZONES . " where zone_country_id = '" . (int) $entry_country_id[$address_book_key] . "' and (zone_name like '" . tep_db_input($entry_state[$address_book_key]) . "' or zone_code like '" . tep_db_input($entry_state[$address_book_key]) . "')");
                        if (tep_db_num_rows($zone_query) == 1) {
                            $zone_values = tep_db_fetch_array($zone_query);
                            $entry_zone_id[$address_book_key] = $zone_values['zone_id'];
                        } /* else {
                          $error = true;
                          $entry_state_error = true;
                          } */
                    } else {

                        /* if ($entry_state[$address_book_key] == false) {
                          $error = true;
                          $entry_state_error = true;
                          } */
                    }
                }
            }
        }

        $warehouses_open_hours_ids = Yii::$app->request->post('warehouses_open_hours_id');
        $warehouses_open_hours_keys = Yii::$app->request->post('warehouses_open_hours_key');
        $open_time_from = Yii::$app->request->post('open_time_from');
        $open_time_to = Yii::$app->request->post('open_time_to');

        $shipping_additional_charge = Yii::$app->request->post('shipping_additional_charge');


        if ($is_store == 0) {
            $warehouses_open_hours_ids = [];
        }

        if ($error === FALSE) {
            $sql_data_array = [
                'warehouse_owner' => $warehouse_owner,
                'warehouse_name' => $warehouse_name,
                'warehouse_email_address' => $warehouse_email_address,
                'warehouse_telephone' => $warehouse_telephone,
                'warehouse_landline' => $warehouse_landline,
                'is_store' => $is_store,
                'status' => (int)$status,
                'shipping_additional_charge' => (float)$shipping_additional_charge,
            ];

            if ($is_default !== false) {
                $sql_data_array['is_default'] = $is_default;
            }
            $message = "Item updated";
            $warehouse_updated = true;
            $warehouseModel = \common\models\Warehouses::findOne($item_id);
            if (!$warehouseModel){
                $message = "Item inserted";
                $warehouse_updated = false;
                $warehouseModel = new \common\models\Warehouses([
                    'date_added' => new \yii\db\Expression('NOW()'),
                ]);
                $warehouseModel->loadDefaultValues();
            }
            $sql_data_array['last_modified'] = new \yii\db\Expression('NOW()');
            $warehouseModel->setAttributes($sql_data_array,false);
            $status_changed = false;
            if (!$warehouseModel->isNewRecord && $warehouseModel->isAttributeChanged('status')) {
                $status_changed = true;
            }
            $warehouseModel->save(false);
            $item_id = $warehouseModel->warehouse_id;

            if ($status_changed){
                \common\helpers\Warehouses::warehouse_status_change();
            }

            if ($is_default) {
                tep_db_query(
                        "UPDATE " . TABLE_WAREHOUSES . " SET is_default=0 " .
                        "WHERE warehouse_id!='" . (int) $item_id . "'"
                );
            }

            $google = new \common\components\GoogleTools();
            $activeaddress_book_ids = [];
            foreach ($address_book_ids as $address_book_key => $address_book_id) {
                $entry_zone_id[$address_book_key] = $entry_zone_id[$address_book_key] ?? null;
                if ($entry_zone_id[$address_book_key] > 0)
                    $entry_state[$address_book_key] = '';

                $sql_data_array = [
                    'entry_street_address' => $entry_street_address[$address_book_key],
                    'entry_postcode' => $entry_postcode[$address_book_key],
                    'entry_city' => $entry_city[$address_book_key],
                    'entry_country_id' => $entry_country_id[$address_book_key],
                    'entry_company_reg_number' => $entry_company_reg_number[$address_book_key],
                    'is_default' => 1
                ];

                $sql_data_array['entry_company'] = $entry_company[$address_book_key];
                $sql_data_array['entry_suburb'] = $entry_suburb[$address_book_key];
                $sql_data_array['entry_company_vat'] = $entry_company_vat[$address_book_key];

                if ($entry_zone_id[$address_book_key] > 0) {
                    $sql_data_array['entry_zone_id'] = $entry_zone_id[$address_book_key];
                    $sql_data_array['entry_state'] = '';
                } else {
                    $sql_data_array['entry_zone_id'] = '0';
                    $sql_data_array['entry_state'] = $entry_state[$address_book_key];
                }

                $address = $entry_postcode[$address_book_key] . " " . $entry_street_address[$address_book_key] . " " . $entry_city[$address_book_key] . " " . \common\helpers\Country::get_country_name($entry_country_id[$address_book_key]);
                $location = $google->getGeocodingLocation($address);
                if (is_array($location)) {
                    $sql_data_array['lat'] = $location['lat'];
                    $sql_data_array['lng'] = $location['lng'];
                }

                if ((int) $address_book_id > 0) {
                    tep_db_perform(TABLE_WAREHOUSES_ADDRESS_BOOK, $sql_data_array, 'update', "warehouse_id = '" . (int) $item_id . "' and warehouses_address_book_id = '" . (int) $address_book_id . "'");
                    $activeaddress_book_ids[] = $address_book_id;
                } else {
                    tep_db_perform(TABLE_WAREHOUSES_ADDRESS_BOOK, array_merge($sql_data_array, array('warehouse_id' => $item_id)));
                    $new_customers_address_id = tep_db_insert_id();
                    $activeaddress_book_ids[] = $new_customers_address_id;
                }
            }
            if (count($activeaddress_book_ids) > 0) {
                tep_db_query("delete from " . TABLE_WAREHOUSES_ADDRESS_BOOK . " where warehouse_id = '" . (int) $item_id . "' and warehouses_address_book_id NOT IN (" . implode(", ", $activeaddress_book_ids) . ")");
            }

            $active_open_hours_ids = [];
            foreach ($warehouses_open_hours_ids as $warehouses_open_hours_key => $warehouses_open_hours_id) {

                $open_days = Yii::$app->request->post('open_days_' . $warehouses_open_hours_keys[$warehouses_open_hours_key], []);

                $sql_data_array = [
                    'open_days' => implode(",", $open_days),
                    'open_time_from' => $open_time_from[$warehouses_open_hours_key],
                    'open_time_to' => $open_time_to[$warehouses_open_hours_key],
                ];
                if ((int) $warehouses_open_hours_id > 0) {
                    tep_db_perform(TABLE_WAREHOUSES_OPEN_HOURS, $sql_data_array, 'update', "warehouse_id = '" . (int) $item_id . "' and warehouses_open_hours_id = '" . (int) $warehouses_open_hours_id . "'");
                    $active_open_hours_ids[] = $warehouses_open_hours_id;
                } else {
                    tep_db_perform(TABLE_WAREHOUSES_OPEN_HOURS, array_merge($sql_data_array, array('warehouse_id' => $item_id)));
                    $new_open_hours_id = tep_db_insert_id();
                    $active_open_hours_ids[] = $new_open_hours_id;
                }
            }
            if (count($active_open_hours_ids) > 0) {
                tep_db_query("delete from " . TABLE_WAREHOUSES_OPEN_HOURS . " where warehouse_id = '" . (int) $item_id . "' and warehouses_open_hours_id NOT IN (" . implode(", ", $active_open_hours_ids) . ")");
            }
        }

        if (\common\classes\platform::isMulti(false)) {
            $_platforms = \common\classes\platform::getList(false);
            foreach ($_platforms as $_idx => $_platform) {
                $check = tep_db_fetch_array(tep_db_query("SELECT warehouse_id FROM " . TABLE_WAREHOUSES_TO_PLATFORMS . " WHERE warehouse_id = '" . (int) $item_id . "' AND platform_id = '" . (int) $_platform['id'] . "'"));
                if (($check['warehouse_id']??null) == $item_id) {
                    tep_db_query("UPDATE " . TABLE_WAREHOUSES_TO_PLATFORMS . " SET status = '" . (int) $platform_status[$_platform['id']] . "' WHERE warehouse_id = '" . (int) $item_id . "' AND platform_id = '" . (int) $_platform['id'] . "'");
                } else {
                    tep_db_query("INSERT INTO " . TABLE_WAREHOUSES_TO_PLATFORMS . " SET warehouse_id = '" . (int) $item_id . "', platform_id = '" . (int) $_platform['id'] . "', status = '" . (int) ($platform_status[$_platform['id']]??null) . "'");
                }
            }
        } else {
            tep_db_query("UPDATE " . TABLE_WAREHOUSES_TO_PLATFORMS . " SET status = '" . (int) $status . "' WHERE warehouse_id = '" . (int) $item_id . "' AND platform_id = '" . (int) \common\classes\platform::defaultId() . "'");
        }
        
        if ($es = \common\helpers\Extensions::isAllowed('EventSystem')) {
            $es::venue()->exec('saveVenueAdditionalFields', [$item_id, Yii::$app->request->post()]);
        }

        if ($error === TRUE) {
            $messageType = 'warning';

            if ($message == '')
                $message = WARN_UNKNOWN_ERROR;
        }
        ?>
        <div class="popup-box-wrap pop-mess">
            <div class="around-pop-up"></div>
            <div class="popup-box">
                <div class="pop-up-close pop-up-close-alert"></div>
                <div class="pop-up-content">
                    <div class="popup-heading"><?php echo TEXT_NOTIFIC; ?></div>
                    <div class="popup-content pop-mess-cont pop-mess-cont-<?php echo $messageType; ?>">
        <?php echo $message; ?>
                    </div>   
                </div>  
                <div class="noti-btn">
                    <div></div>
                    <div><span class="btn btn-primary"><?php echo TEXT_BTN_OK; ?></span></div>
                </div>
            </div> 
            <script>
                $('body').scrollTop(0);
                $('.pop-mess .pop-up-close-alert, .noti-btn .btn').click(function () {
                    $(this).parents('.pop-mess').remove();
                });
            </script>
        </div>


        <?php
        echo '<script>location.replace("' . Yii::$app->urlManager->createUrl(['warehouses/edit', 'id' => $item_id]) . '");</script>';
        die();
        return $this->actionEdit();
    }

    public function actionConfirmitemdelete() {
        \common\helpers\Translation::init('admin/warehouses');

        $this->layout = false;

        $item_id = (int) Yii::$app->request->post('item_id');

        $message = $name = $title = '';
        $parent_id = 0;

        $warehouses_query = tep_db_query("select * from " . TABLE_WAREHOUSES . " where warehouse_id = '" . (int) $item_id . "'");
        $warehouses = tep_db_fetch_array($warehouses_query);
        $pInfo = new \objectInfo($warehouses);

        echo tep_draw_form('item_delete', 'warehouses', \common\helpers\Output::get_all_get_params(array('action')) . 'action=update', 'post', 'id="item_delete" onSubmit="return deleteItem();"');
        echo '<div class="or_box_head">' . TEXT_INFO_HEADING_DELETE_WAREHOUSE . '</div>';
        echo '<div class="col_desc">' . TEXT_INFO_DELETE_WAREHOUSE_INTRO . '</div>';
        echo '<div class="col_desc">' . $pInfo->warehouse_name . '</div>';
        ?>
        <p class="btn-toolbar">
        <?php
        echo '<input type="submit" class="btn btn-primary" value="' . IMAGE_DELETE . '" >';
        echo '<input type="button" class="btn btn-cancel" value="' . IMAGE_CANCEL . '" onClick="return resetStatement()">';

        echo tep_draw_hidden_field('item_id', $item_id);
        ?>
        </p>
        </form>
        <?php
    }

    public function actionItemdelete() {
        \common\helpers\Translation::init('admin/warehouses');

        $this->layout = false;

        $item_id = (int) Yii::$app->request->post('item_id');

        $messageType = 'success';
        $message = TEXT_INFO_DELETED;

        $check_is_default = tep_db_fetch_array(tep_db_query(
                        "SELECT COUNT(*) AS c FROM " . TABLE_WAREHOUSES . " WHERE is_default = 1 AND warehouse_id = '" . (int) $item_id . "'"
        ));
        if ($check_is_default['c']) {

        } else {
            \common\models\Warehouses::deleteAll( ['warehouse_id' => (int) $item_id] );
            \common\models\WarehousesAddressBook::deleteAll( ['warehouse_id' => (int) $item_id] );
            \common\models\WarehousesOpenHours::deleteAll( ['warehouse_id' => (int) $item_id] );
            \common\models\WarehousesProducts::deleteAll( ['warehouse_id' => (int) $item_id] );
            \common\models\WarehousesPlatforms::deleteAll( ['warehouse_id' => (int) $item_id] );
        }
        ?>
        <div class="popup-box-wrap pop-mess">
            <div class="around-pop-up"></div>
            <div class="popup-box">
                <div class="pop-up-close pop-up-close-alert"></div>
                <div class="pop-up-content">
                    <div class="popup-heading"><?php echo TEXT_NOTIFIC; ?></div>
                    <div class="popup-content pop-mess-cont pop-mess-cont-<?php echo $messageType; ?>">
        <?php echo $message; ?>
                    </div>  
                </div>  
                <div class="noti-btn">
                    <div></div>
                    <div><span class="btn btn-primary"><?php echo TEXT_BTN_OK; ?></span></div>
                </div>
            </div>   
            <script>
                $('body').scrollTop(0);
                $('.pop-mess .pop-up-close-alert, .noti-btn .btn').click(function () {
                    $(this).parents('.pop-mess').remove();
                });
            </script>
        </div>

        <p class="btn-toolbar">
        <?php
        echo '<input type="button" class="btn btn-primary" value="' . IMAGE_CANCEL . '" onClick="return resetStatement()">';
        ?>
        </p>
        <?php
    }

    public function actionSortOrder() {
        $moved_id = (int) $_POST['sort_top'];
        $ref_array = (isset($_POST['top']) && is_array($_POST['top'])) ? array_map('intval', $_POST['top']) : array();
        if ($moved_id && in_array($moved_id, $ref_array)) {
            // {{ normalize
            $order_counter = 0;
            $order_list_r = tep_db_query(
                    "SELECT w.warehouse_id, ifnull(w2p.status, w.status) as status, ifnull(w2p.sort_order, w.sort_order) as sort_order " .
                    "FROM " . TABLE_WAREHOUSES . " w " .
                    "LEFT JOIN " . TABLE_WAREHOUSES_TO_PLATFORMS . " w2p ON w.warehouse_id = w2p.warehouse_id AND w2p.platform_id = '" . (int)$this->selected_platform_id . "'" .
                    "WHERE 1 " .
                    "ORDER BY ifnull(w2p.sort_order, w.sort_order), w.warehouse_name"
            );
            while ($order_list = tep_db_fetch_array($order_list_r)) {
                $order_counter++;
                if ($this->selected_platform_id == \common\classes\platform::defaultId()) {
                    tep_db_query("UPDATE " . TABLE_WAREHOUSES . " SET sort_order = '{$order_counter}' WHERE warehouse_id = '{$order_list['warehouse_id']}'");
                }
                $check = tep_db_fetch_array(tep_db_query("SELECT warehouse_id FROM " . TABLE_WAREHOUSES_TO_PLATFORMS . " WHERE warehouse_id = '{$order_list['warehouse_id']}' AND platform_id = '" . (int)$this->selected_platform_id . "'"));
                if ($check['warehouse_id'] == $order_list['warehouse_id']) {
                    tep_db_query("UPDATE " . TABLE_WAREHOUSES_TO_PLATFORMS . " SET sort_order = '{$order_counter}' WHERE warehouse_id = '{$order_list['warehouse_id']}' AND platform_id = '" . (int)$this->selected_platform_id . "'");
                } else {
                    tep_db_query("INSERT INTO " . TABLE_WAREHOUSES_TO_PLATFORMS . " SET warehouse_id = '{$order_list['warehouse_id']}', platform_id = '" . (int)$this->selected_platform_id . "', status = '{$order_list['status']}', sort_order = '{$order_counter}'");
                }
            }
            // }} normalize
            $get_current_order_r = tep_db_query(
                    "SELECT w.warehouse_id, ifnull(w2p.sort_order, w.sort_order) as sort_order " .
                    "FROM " . TABLE_WAREHOUSES . " w " .
                    "LEFT JOIN " . TABLE_WAREHOUSES_TO_PLATFORMS . " w2p ON w.warehouse_id = w2p.warehouse_id AND w2p.platform_id = '" . (int)$this->selected_platform_id . "'" .
                    "WHERE w.warehouse_id IN ('" . implode("','", $ref_array) . "') " .
                    "ORDER BY ifnull(w2p.sort_order, w.sort_order)"
            );
            $ref_ids = array();
            $ref_so = array();
            while ($_current_order = tep_db_fetch_array($get_current_order_r)) {
                $ref_ids[] = (int) $_current_order['warehouse_id'];
                $ref_so[] = (int) $_current_order['sort_order'];
            }

            foreach ($ref_array as $_idx => $id) {
                if ($this->selected_platform_id == \common\classes\platform::defaultId()) {
                    tep_db_query("UPDATE " . TABLE_WAREHOUSES . " SET sort_order = '{$ref_so[$_idx]}' WHERE warehouse_id = '{$id}'");
                }
                tep_db_query("UPDATE " . TABLE_WAREHOUSES_TO_PLATFORMS . " SET sort_order = '{$ref_so[$_idx]}' WHERE warehouse_id = '{$id}' AND platform_id = '" . (int)$this->selected_platform_id . "'");
            }
        }
    }

    private function locationTree($locations, $parrent_id, $blocksList) {
        $tree = [];
        if (is_array($locations)) {
            foreach ($locations as $value) {
                if ($value['parrent_id'] == $parrent_id) {
                    if (isset($blocksList[$value['block_id']])) {
                        $value['block_name'] = $blocksList[$value['block_id']];
                    } else {
                        $value['block_name'] = '';
                    }
                    if ($value['is_final'] == 0) {
                        $value['children'] = $this->locationTree($locations, $value['location_id'], $blocksList);
                    }
                    $tree[] = $value;
                }
            }
        }
        return $tree;
    }
            
    public function actionLocations() {
        $item_id = (int) Yii::$app->request->get('id');
        \common\helpers\Translation::init('admin/warehouses');
        
        $warehouse = \common\models\Warehouses::findOne($item_id);
        if (!is_object($warehouse)) {
            return $this->redirect(Yii::$app->urlManager->createUrl('warehouses/'));
        }
        
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('warehouses/locations'), 'title' => IMAGE_EDIT . ' ' . TEXT_WAREHOUSE_LOCATIONS . ': ' . $warehouse->warehouse_name);
        $this->view->headingTitle = IMAGE_EDIT . ' ' . TEXT_WAREHOUSE_LOCATIONS . ': ' . $warehouse->warehouse_name;
        
        $blocks =\common\models\LocationBlocks::find()->asArray()->all();
        $blocksList = [];
        foreach ($blocks as $value) {
            $blocksList[$value['block_id']] = $value['block_name'];
        }
        
        $locations = \common\models\Locations::find()->where(['warehouse_id' => $item_id])->orderBy('sort_order')->asArray()->all();
        $locationsTree = $this->locationTree($locations, 0, $blocksList);
        
        return $this->render('locations', [
            'id' => $item_id,
            'blocks' => $blocks,
            'locationsTree' => $locationsTree,
            'warehouse' => $warehouse,
        ]);
    }
    
    private $usedLocationIds = [];
    private function updateLocation($data, $parrent_id, $warehouse_id, $sort_order = 0) {
        if (!is_array($data)) {
            return false;
        }
        if (count($data) != 4) {
            return false;
        }
        
        $id = $data[0];
        $block_id = $data[1];
        $name = $data[2];
        $children = $data[3];
        
        $obj = false;
        if ($id > 0) {
            $obj = \common\models\Locations::findOne($id);
        }
        if (!is_object($obj)) {
            $obj = new \common\models\Locations();
        }
        $obj->block_id = $block_id;
        $obj->location_name = $name;
        $obj->parrent_id = $parrent_id;
        $obj->warehouse_id = $warehouse_id;
        $obj->sort_order = $sort_order;
        
        if (is_array($children) && count($children) > 0) {
            $obj->is_final = 0;
            $obj->save();
            foreach ($children as $sort => $child) {
                $this->updateLocation($child, $obj->location_id, $warehouse_id, $sort);
            }
        } else {
            $obj->is_final = 1;
            $obj->save();
        }
        $this->usedLocationIds[$obj->location_id] = $obj->location_id;
    }
    
    public function actionSaveLocation() {
        $item_id = (int) Yii::$app->request->get('id');
        $post_data = Yii::$app->request->post('post_data');
        $locations = json_decode($post_data);
        if (is_array($locations[0])) {
            $this->usedLocationIds = [];
            foreach ($locations[0] as $sort => $location) {
                $this->updateLocation($location, 0, $item_id, $sort);
            }
            \common\models\Locations::deleteAll( ['AND', ['warehouse_id' => $item_id], ['NOT IN', 'location_id', $this->usedLocationIds]] );
        }
        echo json_encode(['status'  =>  'ok']);
    }
    
    public function actionLocationBlocks() {
        \common\helpers\Translation::init('admin/warehouses');
        
        $this->view->filters = new \stdClass();
        $this->view->filters->row = (int)Yii::$app->request->get('row', 0);
        
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('warehouses/location-blocks'), 'title' => TEXT_MANAGE_LOCATION_BLOCKS);
        $this->view->headingTitle = TEXT_MANAGE_LOCATION_BLOCKS;
        $this->topButtons[] = '<a href="#" class="create_item" onclick="return editItem(0)">'.TEXT_INFO_HEADING_NEW_LOCATION_BLOCK.'</a>';
        
        $this->view->groupsTable = array(
            array(
                'title' => TABLE_TEXT_NAME,
                'not_important' => 1
            ),
        );
        
        return $this->render('location-blocks');
    }
    
    public function actionLocationBlocksList() {
        $this->layout = false;
        $draw = Yii::$app->request->get('draw');
        $start = Yii::$app->request->get('start');
        $length = Yii::$app->request->get('length');

        if ($length == -1)
            $length = 10000;
        
        $query_raw =\common\models\LocationBlocks::find();
        
        if (isset($_GET['search']['value']) && tep_not_null($_GET['search']['value'])) {
            $keywords = tep_db_input(tep_db_prepare_input($_GET['search']['value']));
            $query_raw->where(['like', 'block_name', $keywords]);
        }
        
        $query_numrows = $query_raw->count();
        $query_raw->limit($length)->offset($start);

        $blocks = $query_raw->asArray()->all();

        $responseList = [];
        if (is_array($blocks)) {
            foreach ($blocks as $block) {
                $responseList[] = array(
                    $block['block_name'] .
                    '<input class="cell_identify" type="hidden" value="' . $block['block_id'] . '">' .
                    '<input class="cell_type" type="hidden" value="top">'
                );

            }
        }
        
        
        $response = array(
            'draw' => $draw,
            'recordsTotal' => $query_numrows,
            'recordsFiltered' => $query_numrows,
            'data' => $responseList
        );
        echo json_encode($response);
    }
    
    public function actionLocationBlocksPreview() {
        \common\helpers\Translation::init('admin/warehouses');

        $this->layout = false;

        $item_id = (int) Yii::$app->request->post('item_id');
        
        $block = \common\models\LocationBlocks::findOne($item_id);
        
        if (is_object($block)) {
            $loationsQty = \common\models\Locations::find()->where(['block_id' => $item_id])->count();
            echo '<div class="or_box_head">' . $block->block_name . '</div>';
            echo '<div class="btn-toolbar btn-toolbar-order">
                <button onclick="return editItem(' . $item_id . ')" class="btn btn-edit btn-primary btn-process-order ">' . IMAGE_EDIT . '</button>
                ' . ($loationsQty > 0 ? '' : ('<button onclick="return deleteItemConfirm(' . $item_id . ')" class="btn btn-delete btn-no-margin btn-process-order ">' . IMAGE_DELETE . '</button>')) . '
            </div>';
        }
    }
    
    public function actionLocationBlocksConfirmDelete() {
        \common\helpers\Translation::init('admin/warehouses');

        $this->layout = false;

        $item_id = (int) Yii::$app->request->post('item_id');

        $block = \common\models\LocationBlocks::findOne($item_id);

        if (is_object($block)) {
            echo tep_draw_form('item_delete', 'blocks', 'location-blocks-delete', 'post', 'id="item_delete" onSubmit="return deleteItem();"');
            echo '<div class="or_box_head">' . TEXT_INFO_HEADING_DELETE_BLOCK . '</div>';
            echo '<div class="col_desc">' . TEXT_INFO_DELETE_BLOCK_INTRO . '</div>';
            echo '<div class="col_desc">' . $block->block_name . '</div>';
            echo '<p class="btn-toolbar">';
            echo '<input type="submit" class="btn btn-primary" value="' . IMAGE_DELETE . '" >';
            echo '<input type="button" class="btn btn-cancel" value="' . IMAGE_CANCEL . '" onClick="return resetStatement()">';

            echo tep_draw_hidden_field('item_id', $item_id);
            echo '</p></form>';
        }
    }
    
    public function actionLocationBlocksDelete() {
        $item_id = (int) Yii::$app->request->post('item_id');
        \common\models\LocationBlocks::deleteAll(['block_id' => $item_id]);
        
    }
    
    public function actionLocationBlocksEdit() {
        \common\helpers\Translation::init('admin/warehouses');

        $this->layout = false;
        
        $item_id = (int) Yii::$app->request->get('item_id');
        
        $block = \common\models\LocationBlocks::findOne($item_id);
        
        
        echo tep_draw_form('item_edit', 'blocks', 'location-blocks-edit');
        if ($item_id > 0) {
            echo '<div class="or_box_head">' . TEXT_INFO_HEADING_EDIT_BLOCK . '</div>';
        } else {
            echo '<div class="or_box_head">' . TEXT_INFO_HEADING_NEW_BLOCK . '</div>';
        }
        
        echo '<div class="main_row"><div class="main_title">' . TEXT_INFO_BLOCK_NAME . '</div><div class="main_value">' . tep_draw_input_field('block_name', $block->block_name ?? null) . '</div></div>';
        
        echo '<div class="btn-toolbar btn-toolbar-order">';
        echo '<input type="button" value="' . IMAGE_UPDATE . '" class="btn btn-no-margin" onclick="saveItem(' . $item_id . ')"><input type="button" value="' . IMAGE_CANCEL . '" class="btn btn-cancel" onclick="resetStatement()">';
        echo '</div>';
        echo '</form>';
    }
    
    public function actionLocationBlocksSave() {
        $item_id = (int) Yii::$app->request->get('item_id');
        
        if ($item_id > 0) {
            $block = \common\models\LocationBlocks::findOne($item_id);
        } else {
            $block = new \common\models\LocationBlocks();
        }
        
        $block->block_name = Yii::$app->request->post('block_name');
        $block->save();
    }
}
