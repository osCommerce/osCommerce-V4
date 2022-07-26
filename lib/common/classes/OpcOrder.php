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

namespace common\classes;

class OpcOrder extends \common\classes\extended\OrderAbstract {

    var $_store;

    function __construct() {
        $this->info = array();
        $this->totals = array();
        $this->products = array();
        $this->customer = array();
        $this->delivery = array();
        $this->tax_address = array();

        $this->cart();
        // store values
        $this->_store = array();
        if (DISPLAY_PRICE_WITH_TAX == 'true') {
            $this->_store['total'] = $this->info['subtotal'];
        } else {
            $this->_store['total'] = $this->info['subtotal'] + $this->info['tax'];
        }
        $this->_store['tax_groups'] = $this->info['tax_groups'];
        $this->_store['tax'] = $this->info['tax'];

        $this->info['total'] = $this->_store['total'];
    }

// recalc stubs
    function _billing_address() {
        global $opc_billto;
        if (is_array($opc_billto)) {
            foreach ($opc_billto as $key => $value) {
                if (in_array($key, array_keys($this->customer)))
                    $this->customer[$key] = $value;
                if (in_array($key, array_keys($this->billing)))
                    $this->billing[$key] = $value;
            }
            $this->tax_address = array('entry_country_id' => $this->billing['country_id'], 'entry_zone_id' => $this->billing['zone_id']);
        }
        return false;
    }

    function _shipping_address() {
        global $opc_sendto;
        if (is_array($opc_sendto)) {
            foreach ($opc_sendto as $key => $value) {
                if (in_array($key, array_keys($this->delivery)))
                    $this->delivery[$key] = $value;
            }
            $this->tax_address = array('entry_country_id' => $this->delivery['country_id'], 'entry_zone_id' => $this->delivery['zone_id']);
        }
        return false;
    }

    function change_shipping($new_shipping) {
        $this->info['total'] = $this->_store['total'];
        $this->info['tax_groups'] = $this->_store['tax_groups'];
        $this->info['tax'] = $this->_store['tax'];
        if (!is_array($new_shipping)) {
            $this->info['shipping_class'] = '';
            $this->info['shipping_method'] = '';
            $this->info['shipping_cost'] = '0';
            $this->info['shipping_cost_inc_tax'] = 0;
            $this->info['shipping_cost_exc_tax'] = 0;
        } else {
            $this->info['total'] += $new_shipping['cost'];
            $this->info['total_inc_tax'] = $this->info['total_inc_tax'] - $this->info['shipping_cost_exc_tax'] + $new_shipping['cost'];
            $this->info['total_exc_tax'] = $this->info['total_exc_tax'] - $this->info['shipping_cost_exc_tax'] + $new_shipping['cost'];
            $this->info['shipping_class'] = $new_shipping['id'];
            $this->info['shipping_method'] = $new_shipping['title'];
            $this->info['shipping_cost'] = $new_shipping['cost'];
            $this->info['shipping_cost_inc_tax'] = $new_shipping['cost_inc_tax'];
            $this->info['shipping_cost_exc_tax'] = $new_shipping['cost'];
        }
        return false;
    }

//\ recalc stubs    
}
