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

  class freeshipper extends ModuleShipping{
    var $code, $title, $description, $icon, $enabled;

    protected $defaultTranslationArray = [
        'MODULE_SHIPPING_FREESHIPPER_TEXT_TITLE' => 'FREE SHIPPING!',
        'MODULE_SHIPPING_FREESHIPPER_TEXT_DESCRIPTION' => 'FREE SHIPPING',
        'MODULE_SHIPPING_FREESHIPPER_TEXT_WAY' => 'Free Shipping Only'
    ];

// BOF: WebMakers.com Added: Free Payments and Shipping
// class constructor
    function __construct() {
        parent::__construct();

        $this->code = 'freeshipper';
        $this->title = MODULE_SHIPPING_FREESHIPPER_TEXT_TITLE;
        $this->description = MODULE_SHIPPING_FREESHIPPER_TEXT_DESCRIPTION;
        if (!defined('MODULE_SHIPPING_FREESHIPPER_STATUS')) {
            $this->enabled = false;
            return false;
        }
        $this->sort_order = MODULE_SHIPPING_FREESHIPPER_SORT_ORDER;
        $this->icon = DIR_WS_ICONS . 'shipping_free_shipper.jpg';
        $this->tax_class = MODULE_SHIPPING_FREESHIPPER_TAX_CLASS;
        $this->enabled = (defined('MODULE_SHIPPING_FREESHIPPER_STATUS') && (MODULE_SHIPPING_FREESHIPPER_STATUS == 'True'));

// Only show if weight is 0
//      if ( (!strstr($PHP_SELF,'modules.php')) || $cart->show_weight()==0) {

        if ( ($this->enabled == true) && ((int)MODULE_SHIPPING_FREESHIPPER_ZONE > 0) ) {
            $this->enabled = \common\helpers\Zones::isGeoZone(MODULE_SHIPPING_FREESHIPPER_ZONE, $this->delivery['country']['id']??null, $this->delivery['zone_id']??null);
        }
//      }
// EOF: WebMakers.com Added: Free Payments and Shipping
    }

// class methods
    function quote($method = '') {

      $this->quotes = array('id' => $this->code,
                            'module' => MODULE_SHIPPING_FREESHIPPER_TEXT_TITLE,
                            'methods' => array(array('id' => $this->code,
                                                     'title' => (MODULE_SHIPPING_FREESHIPPER_TEXT_WAY=='&nbsp;'?'':'<FONT class="freeshipper-way" COLOR=FF0000><B>' . MODULE_SHIPPING_FREESHIPPER_TEXT_WAY . '</B></FONT>'),
                                                     'cost' => MODULE_SHIPPING_FREESHIPPER_COST)));// SHIPPING_HANDLING

      if ($this->tax_class > 0) {
        $this->quotes['tax'] = \common\helpers\Tax::get_tax_rate($this->tax_class, $this->delivery['country']['id'], $this->delivery['zone_id']);
      }
      if (tep_not_null($this->icon)) $this->quotes['icon'] = tep_image($this->icon, $this->title);

      return $this->quotes;
    }

    public function configure_keys()
    {
      return array(
        'MODULE_SHIPPING_FREESHIPPER_STATUS' => array(
          'title' => 'Enable Free Shipping',
          'value' => 'True',
          'description' => 'Do you want to offer Free shipping?',
          'sort_order' => '0',
          'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        ),
        'MODULE_SHIPPING_FREESHIPPER_COST' => array(
          'title' => 'Free Shipping Cost',
          'value' => '0.00',
          'description' => 'What is the Shipping cost? The Handling fee will also be added.',
          'sort_order' => '6',
        ),
        'MODULE_SHIPPING_FREESHIPPER_TAX_CLASS' => array(
          'title' => 'FREESHIPPER Tax Class',
          'value' => '0',
          'description' => 'Use the following tax class on the shipping fee.',
          'sort_order' => '0',
          'use_function' => '\\common\\helpers\\Tax::get_tax_class_title',
          'set_function' => 'tep_cfg_pull_down_tax_classes(',
        ),
        'MODULE_SHIPPING_FREESHIPPER_ZONE' => array(
          'title' => 'FREESHIPPER Shipping Zone',
          'value' => '0',
          'description' => 'If a zone is selected, only enable this shipping method for that zone.',
          'sort_order' => '0',
          'use_function' => '\\common\\helpers\\Zones::get_zone_class_title',
          'set_function' => 'tep_cfg_pull_down_zone_classes(',
        ),
        'MODULE_SHIPPING_FREESHIPPER_DATE_SETTING' => array (
            'title' => 'Delivery Date Management',
            'value' => 'Use default',
            'description' => 'Preferred delivery date rules',
            'sort_order' => '80',
            'set_function' => 'tep_cfg_select_option(array(\'Use default\', \'Use ownership\'), ',
        ),
        'MODULE_SHIPPING_FREESHIPPER_DISABLED_DAYS' => array (
            'title' => 'Delivery Date ownership settings',
            'value' => "Saturday, Sunday",
            'description' => 'Disabled dates',
            'sort_order' => '90',
              'set_function' => "tep_cfg_select_multioption(array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'),",
        ),
        'MODULE_SHIPPING_FREESHIPPER_SORT_ORDER' => array(
          'title' => 'FREESHIPPER Sort Order',
          'value' => '0',
          'description' => 'Sort order of display.',
          'sort_order' => '100',
        ),
      );
    }
    public function describe_status_key()
    {
      return new ModuleStatus('MODULE_SHIPPING_FREESHIPPER_STATUS','True','False');
    }

    public function describe_sort_key()
    {
      return new ModuleSortOrder('MODULE_SHIPPING_FREESHIPPER_SORT_ORDER');
    }
    
    public function getExtraDisabledDays()
    {
        $response = false;
        if (defined('MODULE_SHIPPING_FREESHIPPER_DATE_SETTING') && MODULE_SHIPPING_FREESHIPPER_DATE_SETTING == 'Use ownership') {
            if (defined('MODULE_SHIPPING_FREESHIPPER_DISABLED_DAYS')) {
                $response = explode(",", MODULE_SHIPPING_FREESHIPPER_DISABLED_DAYS);
                if (!is_array($response))
                    $response = array();
                $response = array_map('trim', $response);
            }
        }
        return $response;
    }

  }
