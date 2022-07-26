<?php
/*
  MultiSafepay Payment Module for osCommerce 
  http://www.multisafepay.com

  Copyright (C) 2008 MultiSafepay.com
 */

chdir("../");
require_once("includes/application_top.php");
require_once("includes/modules/payment/multisafepay_fastcheckout.php");
if (!empty($GLOBALS['_SESSION']['language'])){
    require_once('includes/languages/'. $GLOBALS['_SESSION']['language'] .'/modules/payment/multisafepay_fastcheckout.php');
}
require_once(DIR_WS_CLASSES . 'order.php');
require_once(DIR_WS_CLASSES . 'shipping.php');
require_once("mspcheckout/include/MultiSafepay.combined.php");

if($cart->count_contents() == 0){
    tep_redirect(tep_href_link(FILENAME_SHOPPING_CART));
    exit();
}

//require_once(DIR_WS_CLASSES . 'order_total.php');


$msp = new multisafepay_fastcheckout();

// Create Classes
$order = new \common\classes\Order();

$total_weight = $cart->show_weight();
$total_count = $cart->count_contents();

// from shipping.php:
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


$tax_class = array ();
$shipping_arr = array ();
$tax_class_unique = array ();

if(DOWNLOAD_ENABLED != 'true' || $cart->get_content_type() != 'virtual') {
}

/*
 * Load shipping modules
 */
$module_directory = dirname(dirname(__FILE__)) . '/' . DIR_WS_MODULES . 'shipping/';
if(!file_exists($module_directory)) {
  echo 'Error: ' . $module_directory;
}

// find module files
$file_extension = substr($PHP_SELF, strrpos($PHP_SELF, '.'));
$directory_array = array();
if ($dir = @ dir($module_directory)) {
  while ($file = $dir->read()) {
    if (!is_dir($module_directory . $file)) {
      if (substr($file, strrpos($file, '.')) == $file_extension) {
        $directory_array[] = $file;
      }
    }
  }
  sort($directory_array);
  $dir->close();
}

$check_query = tep_db_fetch_array(tep_db_query("select countries_iso_code_2
                             from " . TABLE_COUNTRIES . "
                             where countries_id =
                             '" . STORE_COUNTRY . "'"));
$shipping_origin_iso_code_2 = $check_query['countries_iso_code_2'];

// load modules
$module_info = array();
$module_info_enabled = array();
$shipping_modules = array();
for ($i = 0, $n = sizeof($directory_array); $i < $n; $i++) {
  $file = $directory_array[$i];

  include_once (DIR_FS_CATALOG .DIR_WS_LANGUAGES . $language . '/modules/shipping/' . $file);
  include_once ($module_directory . $file);

  $class = substr($file, 0, strrpos($file, '.'));
  $module = new $class;
  $curr_ship = strtoupper($module->code);
  
  switch($curr_ship){
    case 'FEDEXGROUND':
      $curr_ship = 'FEDEX_GROUND';
      break;
    case 'FEDEXEXPRESS':
      $curr_ship = 'FEDEX_EXPRESS';
      break;
    case 'UPSXML':
      $curr_ship = 'UPSXML_RATES';
      break;
    case 'DHLAIRBORNE':
      $curr_ship = 'AIRBORNE';
      break;
    default:
      break;
  }
  if (@constant('MODULE_SHIPPING_' . $curr_ship . '_STATUS') == 'True') {
    $module_info_enabled[$module->code] = array('enabled' => true);
  }
  if ($module->check() == true) {
    $module_info[$module->code] = array(
      'code' => $module->code,
      'title' => $module->title,
      'description' => $module->description,
      'status' => $module->check());
  }
  
  if (!empty($module_info_enabled[$module->code]['enabled'])){
    $shipping_modules[$module->code] = $module;
  }
}

/*
 * Get shipping prices
 */
$shipping_methods = array();
foreach ($module_info as $key => $value) {
  // check if active
  $module_name = $module_info[$key]['code'];
  if (!$module_info_enabled[$module_name]){
    continue;
  }
  
  $curr_ship = strtoupper($module_name);
  
  // calculate price
  $module = $shipping_modules[$module_name];
  $quote = $module->quote($method);
  $price = $quote['methods'][0]['cost'];
  $shipping_price = $currencies->get_value(DEFAULT_CURRENCY) * ($price>=0?$price:0);

  // need this?
  $common_string = "MODULE_SHIPPING_" . $curr_ship . "_";
  @$zone =  constant($common_string . "ZONE");
  @$enable =  constant($common_string . "STATUS");
  @$curr_tax_class =  constant($common_string . "TAX_CLASS");
  @$price =  constant($common_string . "COST");
  @$handling =  constant($common_string . "HANDLING");
  @$table_mode =  constant($common_string . "MODE");
  
  // allowed countries - zones
  if ($zone != '') {
    $zone_result = tep_db_query("SELECT countries_name, coalesce(zone_code, 'All Areas') zone_code, countries_iso_code_2
                                  FROM " . TABLE_TAX_ZONES . " AS gz
                                  inner join ". TABLE_ZONES_TO_TAX_ZONES ." AS ztgz on gz.geo_zone_id = ztgz.geo_zone_id
                                  inner join ". TABLE_COUNTRIES ." AS c on ztgz.zone_country_id = c.countries_id
                                  left join ". TABLE_ZONES ." AS z on ztgz.zone_id = z.zone_id
                                  WHERE gz.geo_zone_id = '". $zone ."'");

    $allowed_restriction_state = $allowed_restriction_country = array();
    // Get all the allowed shipping zones.
    while($zone_answer = tep_db_fetch_array($zone_result)) {
      $allowed_restriction_state[] = $zone_answer['zone_code'];
      $allowed_restriction_country[] = $zone_answer['countries_iso_code_2'];
    }
  }
  
  if ($curr_tax_class != 0 && $curr_tax_class != '') {
    $tax_class[] = $curr_tax_class;

    if (!in_array($curr_tax_class, $tax_class_unique))
      $tax_class_unique[] = $curr_tax_class;
  }
  
  
  if (empty($quote['error']) && $quote['id'] != 'zones'){
    foreach($quote['methods'] as $method){
        $shipping_methods[] = array(
            'id' => $quote['id'],
            'module' => $quote['module'],
            'title' => $quote['methods'][0]['title'],
            'price' => $shipping_price,
            'allowed' => $allowed_restriction_country,
            'tax_class' => $curr_tax_class,
            'zone' => $zone,
        );
    }
  }elseif ($quote['id'] == 'zones'){
    for ($cur_zone=1; $cur_zone<=$module->num_zones; $cur_zone++) {
      $countries_table = constant('MODULE_SHIPPING_ZONES_COUNTRIES_' . $cur_zone);
	  $country_zones = explode(",", $countries_table);

      if (count($country_zones) > 1 || !empty($country_zones[0])){
        $shipping = -1;
        $zones_cost = constant('MODULE_SHIPPING_ZONES_COST_' . $cur_zone);

		$zones_table = preg_split("/[:,]/" , $zones_cost);
        $size = sizeof($zones_table);
        for ($i=0; $i<$size; $i+=2) {
          if ($shipping_weight <= $zones_table[$i]) {
            $shipping = $zones_table[$i+1];
            $shipping_method = $shipping_weight . ' ' . MODULE_SHIPPING_ZONES_TEXT_UNITS;
            break;
          }
        }

        if ($shipping == -1) {
          $shipping_cost = 0;
          $shipping_method = MODULE_SHIPPING_ZONES_UNDEFINED_RATE;
        } else {
          $shipping_cost = ($shipping * $shipping_num_boxes) + constant('MODULE_SHIPPING_ZONES_HANDLING_' . $cur_zone);
          
          $shipping_methods[] = array(
              'id' => $quote['id'],
              'module' => $quote['module'],
              'title' => $shipping_method,
              'price' => $shipping_cost,
              'allowed' => $country_zones,
          );
        }
      }
    }
  }
}

/*
 * Tax stuff
 */
 
$taxes = array();
$taxes['alternate'] = array();
$taxes['default'] = array();

// alternate rules (for the products)
foreach ($GLOBALS['order']->products as $product){
  $tax_name = $product['tax_description'];
  $tax_rate = $product['tax'];
  
  if ($tax_name != 'Unknown tax rate'){
      if (empty($tax_name)){
          $tax_name = 'rate' . $tax_rate;
      }

      $taxes['alternate'][$tax_name] = ($tax_rate/100);
  }
}

// we can't handle different tax classes for different shipping methods
if(sizeof($tax_class_unique) > 1)  {
   echo 'There are multiple shipping methods actives with different tax classes. Please make sure all the methods use the same tax class';
   exit();
}

// default tax (for shipping)
if(sizeof($tax_class_unique) == 1)  {
  $tax_rates_result = tep_db_query("select *
                                 from " . TABLE_TAX_RATES . " as tr 
                                 where tr.tax_class_id= '" .  $tax_class_unique[0] ."'");
                                 
  $num_rows = tep_db_num_rows($tax_rates_result);
  
  if ($num_rows > 1){
    echo 'There are multiple zones active for the same tax rate, this is not supported';
    exit();
  }
  
  $tax_result = tep_db_fetch_array($tax_rates_result);
  $rate = ((double) ($tax_result['tax_rate'])) / 100.0;
  
  $taxes['default'] = $rate;
}

// set shipping methods
$msp->shipping_methods = $shipping_methods;

// set taxes
$msp->taxes = $taxes;

// Save Order
$msp->_save_order();

// Start Transaction Request
$url = $msp->_start_fastcheckout();

// Redirect Transaction
header('Location: ' . $url);

?>