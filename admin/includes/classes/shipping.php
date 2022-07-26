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

  class shipping {
    var $modules;

// class constructor
    function __construct($module = '') {
// BOF: WebMakers.com Added: Downloads Controller
      global $language, $PHP_SELF, $cart;
// EOF: WebMakers.com Added: Downloads Controller
      \common\helpers\Translation::init('shipping');
      if (defined('MODULE_SHIPPING_INSTALLED') && tep_not_null(MODULE_SHIPPING_INSTALLED)) {
        $this->modules = explode(';', MODULE_SHIPPING_INSTALLED);

        $include_modules = array();

        if ( (tep_not_null($module)) && (in_array(substr($module['id'], 0, strpos($module['id'], '_')) . '.' . substr($PHP_SELF, (strrpos($PHP_SELF, '.')+1)), $this->modules)) ) {
          $include_modules[] = array('class' => substr($module['id'], 0, strpos($module['id'], '_')), 'file' => substr($module['id'], 0, strpos($module['id'], '_')) . '.' . substr($PHP_SELF, (strrpos($PHP_SELF, '.')+1)));
        } else {
// BOF: WebMakers.com Added: Downloads Controller - Free Shipping and Payments
// Show either normal shipping modules or free shipping module when Free Shipping Module is On
          // Free Shipping Only
          if ( false && defined('MODULE_SHIPPING_FREESHIPPER_STATUS') && (MODULE_SHIPPING_FREESHIPPER_STATUS=='1' || MODULE_SHIPPING_FREESHIPPER_STATUS=='True') and $cart->show_weight()==0 ) {
            $include_modules[] = array('class'=> 'freeshipper', 'file' => 'freeshipper.php');
          } else {
          // All Other Shipping Modules
            if (is_array($this->modules)) foreach ($this->modules as $value) {
              $class = substr($value, 0, strrpos($value, '.'));
              // Don't show Free Shipping Module
              if (true || $class !='freeshipper') {
                $include_modules[] = array('class' => $class, 'file' => $value);
              }
            }
          }
// EOF: WebMakers.com Added: Downloads Controller - Free Shipping and Payments
        }

        for ($i=0, $n=sizeof($include_modules); $i<$n; $i++) {
          include_once(DIR_FS_CATALOG . DIR_WS_MODULES . 'shipping/' . $include_modules[$i]['file']);

          if (!is_object($include_modules[$i]['class'])){
            $GLOBALS[$include_modules[$i]['class']] = new $include_modules[$i]['class'];
          }
        }
      }
    }

    function quote($method = '', $module = '') {
      global $total_weight, $shipping_weight, $shipping_quoted, $shipping_num_boxes;

      $quotes_array = array();

      if (is_array($this->modules)) {
        $shipping_quoted = '';
        $shipping_num_boxes = 1;
        $shipping_weight = $total_weight;

        if (SHIPPING_BOX_WEIGHT >= $shipping_weight*SHIPPING_BOX_PADDING/100) {
          $shipping_weight = $shipping_weight+SHIPPING_BOX_WEIGHT;
        } else {
          $shipping_weight = $shipping_weight + ($shipping_weight*SHIPPING_BOX_PADDING/100);
        }

        if ($shipping_weight > SHIPPING_MAX_WEIGHT) { // Split into many boxes
          $shipping_num_boxes = ceil($shipping_weight/SHIPPING_MAX_WEIGHT);
          $shipping_weight = $shipping_weight/$shipping_num_boxes;
        }

        $include_quotes = array();

        if (is_array($this->modules)) foreach ($this->modules as $value) {
          $class = substr($value, 0, strrpos($value, '.'));
          if (tep_not_null($module)) {
            if ( ($module == $class) && ($GLOBALS[$class]->enabled) ) {
              $include_quotes[] = $class;
            }
          } elseif ($GLOBALS[$class]->enabled) {
            $include_quotes[] = $class;
          }
        }

        $size = sizeof($include_quotes);
        for ($i=0; $i<$size; $i++) {
          $quotes = $GLOBALS[$include_quotes[$i]]->quote($method);
          if (is_array($quotes)) $quotes_array[] = $quotes;
        }
      }

      return $quotes_array;
    }

    function cheapest() {
      if (is_array($this->modules)) {
        $rates = array();
        foreach ($this->modules as $value) {
          $class = substr($value, 0, strrpos($value, '.'));
          if ($GLOBALS[$class]->enabled) {
            $quotes = $GLOBALS[$class]->quotes;
            for ($i=0, $n=sizeof($quotes['methods']); $i<$n; $i++) {
              if (isset($quotes['methods'][$i]['cost']) && tep_not_null($quotes['methods'][$i]['cost'])) {
                $rates[] = array('id' => $quotes['id'] . '_' . $quotes['methods'][$i]['id'],
                                 'title' => $quotes['module'] . ' (' . $quotes['methods'][$i]['title'] . ')',
                                 'cost' => $quotes['methods'][$i]['cost']);
              }
            }
          }
        }

        $cheapest = false;
        for ($i=0, $n=sizeof($rates); $i<$n; $i++) {
          if (is_array($cheapest)) {
            if ($rates[$i]['cost'] < $cheapest['cost']) {
              $cheapest = $rates[$i];
            }
          } else {
            $cheapest = $rates[$i];
          }
        }

        return $cheapest;
      }
    }
  }
