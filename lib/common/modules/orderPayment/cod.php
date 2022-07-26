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

  namespace common\modules\orderPayment;

  use common\classes\modules\ModulePayment;
  use common\classes\modules\ModuleStatus;
  use common\classes\modules\ModuleSortOrder;


class cod extends ModulePayment{
    var $code, $title, $description, $enabled;

    protected $defaultTranslationArray = [
        'MODULE_PAYMENT_COD_TEXT_TITLE' => 'Cash on Delivery',
        'MODULE_PAYMENT_COD_TEXT_DESCRIPTION' => 'Cash on Delivery'
    ];

// class constructor
    function __construct() {
        parent::__construct();

        $this->code = 'cod';
        $this->title = MODULE_PAYMENT_COD_TEXT_TITLE;
        $this->description = MODULE_PAYMENT_COD_TEXT_DESCRIPTION;
        if (!defined('MODULE_PAYMENT_COD_STATUS')) {
            $this->enabled = false;
            return false;
        }
        $this->sort_order = MODULE_PAYMENT_COD_SORT_ORDER;
        $this->enabled = ((MODULE_PAYMENT_COD_STATUS == 'True') ? true : false);
        $this->online = false;

        if ((int)MODULE_PAYMENT_COD_ORDER_STATUS_ID > 0) {
          $this->order_status = MODULE_PAYMENT_COD_ORDER_STATUS_ID;
        }

        $this->update_status();
    }

// class methods
    function update_status() {

      if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_COD_ZONE > 0) ) {
        $check_flag = false;
        $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_COD_ZONE . "' and zone_country_id = '" . $this->delivery['country']['id'] . "' order by zone_id");
        while ($check = tep_db_fetch_array($check_query)) {
          if ($check['zone_id'] < 1) {
            $check_flag = true;
            break;
          } elseif ($check['zone_id'] == $this->delivery['zone_id']) {
            $check_flag = true;
            break;
          }
        }

        if ($check_flag == false) {
          $this->enabled = false;
        }
      }

// disable the module if the order only contains virtual products
      if ($this->enabled == true) {
        if ($this->manager ){
            $this->enabled = $this->manager->isShippingNeeded() ? true : false;
        }
      }
    }

    function selection() {
      return array('id' => $this->code,
                   'module' => $this->title);
    }

    public function configure_keys(){
      return array(
        'MODULE_PAYMENT_COD_STATUS' => array (
          'title' => 'COD Enable Cash On Delivery Module',
          'value' => 'True',
          'description' => 'Do you want to accept Cash On Delevery payments?',
          'sort_order' => '1',
          'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        ),
        'MODULE_PAYMENT_COD_ZONE' => array(
          'title' => 'COD Payment Zone',
          'value' => '0',
          'description' => 'If a zone is selected, only enable this payment method for that zone.',
          'sort_order' => '2',
          'use_function' => '\\common\\helpers\\Zones::get_zone_class_title',
          'set_function' => 'tep_cfg_pull_down_zone_classes(',
        ),
        'MODULE_PAYMENT_COD_ORDER_STATUS_ID' => array (
          'title' => 'COD Set Order Status',
          'value' => '0',
          'description' => 'Set the status of orders made with this payment module to this value',
          'sort_order' => '0',
          'set_function' => 'tep_cfg_pull_down_order_statuses(',
          'use_function' => '\\common\\helpers\\Order::get_order_status_name',
        ),
        'MODULE_PAYMENT_COD_SORT_ORDER' => array (
          'title' => 'COD Sort order of  display.',
          'value' => '0',
          'description' => 'Sort order of COD display. Lowest is displayed first.',
          'sort_order' => '0',
        ),
      );
  }

  public function describe_status_key()
  {
    return new ModuleStatus('MODULE_PAYMENT_COD_STATUS', 'True', 'False');
  }

  public function describe_sort_key()
  {
    return new ModuleSortOrder('MODULE_PAYMENT_COD_SORT_ORDER');
  }

}