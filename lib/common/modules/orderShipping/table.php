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
namespace common\modules\orderShipping;

use common\classes\modules\ModuleShipping;
use common\classes\modules\ModuleStatus;
use common\classes\modules\ModuleSortOrder;

  class table extends ModuleShipping{
    var $code, $title, $description, $icon, $enabled;

    protected $defaultTranslationArray = [
        'MODULE_SHIPPING_TABLE_TEXT_TITLE' => 'Table Rate',
        'MODULE_SHIPPING_TABLE_TEXT_DESCRIPTION' => 'Table Rate',
        'MODULE_SHIPPING_TABLE_TEXT_WAY' => 'Best Way',
        'MODULE_SHIPPING_TABLE_TEXT_WEIGHT' => 'Weight',
        'MODULE_SHIPPING_TABLE_TEXT_AMOUNT' => 'Amount'
    ];

// class constructor
    function __construct() {
        parent::__construct();

        $this->code = 'table';
        $this->title = MODULE_SHIPPING_TABLE_TEXT_TITLE;
        $this->description = MODULE_SHIPPING_TABLE_TEXT_DESCRIPTION;
        if (!defined('MODULE_SHIPPING_TABLE_STATUS')) {
            $this->enabled = false;
            return false;
        }
        $this->sort_order = MODULE_SHIPPING_TABLE_SORT_ORDER;
        $this->icon = '';
        $this->tax_class = MODULE_SHIPPING_TABLE_TAX_CLASS;
        $this->enabled = ((MODULE_SHIPPING_TABLE_STATUS == 'True') ? true : false);


      if ( ($this->enabled == true) && ((int)MODULE_SHIPPING_TABLE_ZONE > 0) ) {
        $this->enabled = \common\helpers\Zones::isGeoZone(MODULE_SHIPPING_TABLE_ZONE, $this->delivery['country']['id']??null, $this->delivery['zone_id']??null);
      }
    }

// class methods
    function quote($method = '') {
      $cart = $this->manager->getCart();

      if (MODULE_SHIPPING_TABLE_MODE == 'price') {
        $order_total = $cart->show_total();
      } else {
        $order_total = $this->shipping_weight;
      }

      $table_cost = preg_split("/[:,]/" , MODULE_SHIPPING_TABLE_COST);
      $size = sizeof($table_cost);
      for ($i=0, $n=$size; $i<$n; $i+=2) {
        if ($order_total <= $table_cost[$i]) {
          $shipping = $table_cost[$i+1];
          break;
        }
      }

      if (MODULE_SHIPPING_TABLE_MODE == 'weight') {
        $shipping = $shipping * $this->shipping_num_boxes;
      }

      $this->quotes = array('id' => $this->code,
                            'module' => MODULE_SHIPPING_TABLE_TEXT_TITLE,
                            'methods' => array(array('id' => $this->code,
                                                     'title' => MODULE_SHIPPING_TABLE_TEXT_WAY,
                                                     'cost' => $shipping + MODULE_SHIPPING_TABLE_HANDLING)));

      if ($this->tax_class > 0) {
        $this->quotes['tax'] = \common\helpers\Tax::get_tax_rate($this->tax_class, $this->delivery['country']['id'], $this->delivery['zone_id']);
      }

      if (tep_not_null($this->icon)) $this->quotes['icon'] = tep_image($this->icon, $this->title);

      return $this->quotes;
    }

    public function configure_keys()
    {
      return array(
        'MODULE_SHIPPING_TABLE_STATUS' =>
          array(
            'title' => 'Enable Table Method',
            'value' => 'True',
            'description' => 'Do you want to offer table rate shipping?',
            'sort_order' => '0',
            'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
          ),
        'MODULE_SHIPPING_TABLE_COST' =>
          array(
            'title' => 'Table Method Shipping Table',
            'value' => '25:8.50,50:5.50,10000:0.00',
            'description' => 'The shipping cost is based on the total cost or weight of items. Example: 25:8.50,50:5.50,etc.. Up to 25 charge 8.50, from there to 50 charge 5.50, etc',
            'sort_order' => '0',
          ),
        'MODULE_SHIPPING_TABLE_MODE' =>
          array(
            'title' => 'Table Method',
            'value' => 'weight',
            'description' => 'The shipping cost is based on the order total or the total weight of the items ordered.',
            'sort_order' => '0',
            'set_function' => 'tep_cfg_select_option(array(\'weight\', \'price\'), ',
          ),
        'MODULE_SHIPPING_TABLE_HANDLING' =>
          array(
            'title' => 'Table Method Handling Fee',
            'value' => '0',
            'description' => 'Handling fee for this shipping method.',
            'sort_order' => '0',
          ),
        'MODULE_SHIPPING_TABLE_TAX_CLASS' =>
          array(
            'title' => 'Table Method Tax Class',
            'value' => '0',
            'description' => 'Use the following tax class on the shipping fee.',
            'sort_order' => '0',
            'use_function' => '\\common\\helpers\\Tax::get_tax_class_title',
            'set_function' => 'tep_cfg_pull_down_tax_classes(',
          ),
        'MODULE_SHIPPING_TABLE_ZONE' =>
          array(
            'title' => 'Table Method Shipping Zone',
            'value' => '0',
            'description' => 'If a zone is selected, only enable this shipping method for that zone.',
            'sort_order' => '0',
            'use_function' => '\\common\\helpers\\Zones::get_zone_class_title',
            'set_function' => 'tep_cfg_pull_down_zone_classes(',
          ),
            'MODULE_SHIPPING_TABLE_DATE_SETTING' => array (
                'title' => 'Delivery Date Management',
                'value' => 'Use default',
                'description' => 'Preferred delivery date rules',
                'sort_order' => '80',
                'set_function' => 'tep_cfg_select_option(array(\'Use default\', \'Use ownership\'), ',
            ),
            'MODULE_SHIPPING_TABLE_DISABLED_DAYS' => array (
                'title' => 'Delivery Date ownership settings',
                'value' => "Saturday, Sunday",
                'description' => 'Disabled dates',
                'sort_order' => '90',
                  'set_function' => "tep_cfg_select_multioption(array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'),",
            ),
        'MODULE_SHIPPING_TABLE_SORT_ORDER' =>
          array(
            'title' => 'Table Method Sort Order',
            'value' => '0',
            'description' => 'Sort order of display.',
            'sort_order' => '100',
          ),
      );
    }

    public function describe_status_key()
    {
      return new ModuleStatus('MODULE_SHIPPING_TABLE_STATUS','True','False');
    }

    public function describe_sort_key()
    {
      return new ModuleSortOrder('MODULE_SHIPPING_TABLE_SORT_ORDER');
    }

    public function getExtraDisabledDays()
    {
        $response = false;
        if (defined('MODULE_SHIPPING_TABLE_DATE_SETTING') && MODULE_SHIPPING_TABLE_DATE_SETTING == 'Use ownership') {
            if (defined('MODULE_SHIPPING_TABLE_DISABLED_DAYS')) {
                $response = explode(",", MODULE_SHIPPING_TABLE_DISABLED_DAYS);
                if (!is_array($response))
                    $response = array();
                $response = array_map('trim', $response);
            }
        }
        return $response;
    }

  }
