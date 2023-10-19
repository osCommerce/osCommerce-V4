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

namespace backend\models;

use common\helpers\Translation;
use common\models\Countries;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class Configuration {
  private static $taxAddressOptions = [
    0 => TEXT_BILLING_ADDRESS,
    1 => TEXT_SHIPPING_ADDRESS,
    2 => TEXT_BILLING_SHIPPING_ADDRESS
  ];

  // Alias function for Store configuration values in the Administration Tool
  public static function tep_cfg_pull_down_country_list() {//$country_id
    $keys = func_get_args();
    eval('list($country_id, $key) = array(' . $keys[0] . ');');

    $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');

    return tep_draw_pull_down_menu($name, \common\helpers\Country::get_countries(), $country_id);
  }

  public static function tep_cfg_pull_down_zone_list() {//$zone_id
    $keys = func_get_args();
    eval('list($zone_id,) = array(' . $keys[0] . ');');

    return tep_draw_pull_down_menu('configuration_value', \common\helpers\Zones::get_country_zones(STORE_COUNTRY), $zone_id);
  }

  public static function tep_cfg_pull_down_tax_classes() {//$tax_class_id, $key = ''
    $keys = func_get_args();
    eval('list($tax_class_id, $key) = array(' . $keys[0] . ');');

    $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');

    $tax_class_array = array(array('id' => '0', 'text' => TEXT_NONE));
    $tax_class_query = tep_db_query("select tax_class_id, tax_class_title from " . TABLE_TAX_CLASS . " order by tax_class_title");
    while ($tax_class = tep_db_fetch_array($tax_class_query)) {
      $tax_class_array[] = array('id' => $tax_class['tax_class_id'],
      'text' => $tax_class['tax_class_title']);
    }

    return tep_draw_pull_down_menu($name, $tax_class_array, $tax_class_id, 'class="form-control"');
  }

  ////
  // Function to read in text area in admin
  public static function tep_cfg_textarea() {//$text
    $keys = func_get_args();
    if (is_array($keys[0])) {
      $text = $keys[0]['value'];
      $key = $keys[0]['key'];
    } else {
      eval('list($text, $key) = array(' . $keys[0] . ');');
    }
    $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');

    return tep_draw_textarea_field($name, false, 35, 5, $text);
  }

  public static function tep_cfg_get_zone_name() {//$zone_id
    $keys = func_get_args();
    eval('list($zone_id,) = array(' . $keys[0] . ');');
    $zone_query = tep_db_query("select zone_name from " . TABLE_ZONES . " where zone_id = '" . (int)$zone_id . "'");

    if (!tep_db_num_rows($zone_query)) {
      return $zone_id;
    } else {
      $zone = tep_db_fetch_array($zone_query);
      return $zone['zone_name'];
    }
  }

  public static function tep_cfg_select_multioption_order_statuses() {//$key_value, $key = ''
    global $languages_id;

    $keys = func_get_args();
    eval('list($key_value, $key) = array(' . $keys[0] . ');');

    $string = '';
    $key_values = explode( ", ", $key_value);
    $statuses_array = \common\helpers\Order::get_status('', true);

    for ($i=0; $i<sizeof($statuses_array); $i++) {
      $name = (($key) ? 'configuration[' . $key . '][]' : 'configuration_value');
      $string .= '<br><label><input type="checkbox" name="' . $name . '" value="' . $statuses_array[$i]['id'] . '"';

      if ( in_array($statuses_array[$i]['id'], $key_values) ) $string .= 'CHECKED';
      $string .= '> ' . $statuses_array[$i]['text'].'</label>';
    }
    $name = (($key) ? 'configuration[' . $key . '][]' : 'configuration_value');
    $string .= '<input type="hidden" name="' . $name . '" value="--none--">';
    return $string;
  }
////

// Alias function for Store configuration values in the Administration Tool
  public static function tep_cfg_select_option() {//$select_array, $key_value, $key=''
        global $languages_id;
        $string = '';

        $keys = func_get_args();
        $select_array=[]; $key_value=null; $key = '';
        
        eval('list($select_array, $key_value, $key) = array(' . $keys[0] . ');');

        for ($i = 0, $n = sizeof($select_array); $i < $n; $i++) {
            $name = ((tep_not_null($key)) ? 'configuration[' . $key . ']' : 'configuration_value');

            $string .= '<label class="radio-label"><input type="radio" name="' . $name . '" value="' . $select_array[$i] . '"';

            if ($key_value == $select_array[$i]) {
                $string .= ' CHECKED';
            }

            $_t = Translation::getTranslationValue(strtoupper(str_replace(" ", "_", $select_array[$i])), 'configuration', $languages_id);
            $_t = (tep_not_null($_t) ? $_t : $select_array[$i]);
            $string .= '> ' . $_t . '</label><br>';
        }

        return $string;
    }

  ////
  // Alias function for module configuration keys
  public static function tep_mod_select_option() {//$select_array, $key_name, $key_value
    global $languages_id;

    $keys = func_get_args();
    eval('list($select_array, $key_name, $key_value) = array(' . $keys[0] . ');');

    if (is_array($select_array)) foreach ($select_array as $key => $value) {
      if (is_int($key)) $key = $value;
      $string .= '<br><input type="radio" name="configuration[' . $key_name . ']" value="' . $key . '"';
      if ($key_value == $key) $string .= ' CHECKED';

      $_t = Translation::getTranslationValue(strtoupper(str_replace(" ", "_", $value)), 'configuration', $languages_id);
      $_t = (tep_not_null($_t) ? $_t : $value);

      $string .= '> ' . $value;
    }

    return $string;
  }

  public static function tep_cfg_pull_down_zone_classes() {//$zone_class_id, $key = ''
    $keys = func_get_args();
    eval('list($zone_class_id, $key) = array(' . $keys[0] . ');');

    $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');

    $zone_class_array = array(array('id' => '0', 'text' => TEXT_NONE));
    $zone_class_query = tep_db_query("select geo_zone_id, geo_zone_name from " . TABLE_GEO_ZONES . " order by geo_zone_name");
    while ($zone_class = tep_db_fetch_array($zone_class_query)) {
      $zone_class_array[] = array('id' => $zone_class['geo_zone_id'],
      'text' => $zone_class['geo_zone_name']);
    }

    return tep_draw_pull_down_menu($name, $zone_class_array, $zone_class_id);
  }

  public static function tep_cfg_pull_down_order_statuses() {//$order_status_id, $key = ''
    global $languages_id;

    $keys = func_get_args();
    eval('list($order_status_id, $key) = array(' . $keys[0] . ');');

    $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');

    $statuses_array = array(array('id' => '0', 'text' => TEXT_DEFAULT));
    /*$statuses_query = tep_db_query("select orders_status_id, orders_status_name from " . TABLE_ORDERS_STATUS . " where language_id = '" . (int)$languages_id . "' order by orders_status_name");
    while ($statuses = tep_db_fetch_array($statuses_query)) {
      $statuses_array[] = array('id' => $statuses['orders_status_id'],
      'text' => $statuses['orders_status_name']);
    }*/

    $orders_status_groups_query = tep_db_query("select orders_status_groups_id, orders_status_groups_name, orders_status_groups_color from " . TABLE_ORDERS_STATUS_GROUPS . " where language_id = '" . (int) $languages_id . "' order by orders_status_groups_id");
    while ($orders_status_groups = tep_db_fetch_array($orders_status_groups_query)) {
        $statuses_array[] = [
            'optgroup' => $orders_status_groups['orders_status_groups_id'],
            'text' => $orders_status_groups['orders_status_groups_name'],
        ];
        $orders_status_query = tep_db_query("select orders_status_id, orders_status_name from " . TABLE_ORDERS_STATUS . " where language_id = '" . (int) $languages_id . "' and orders_status_groups_id='" . (int)$orders_status_groups['orders_status_groups_id'] . "' order by orders_status_name");
        while ($orders_status = tep_db_fetch_array($orders_status_query)) {
            $statuses_array[] = [
                'id' => $orders_status['orders_status_id'],
                'text' => $orders_status['orders_status_name'],
            ];
        }
        $statuses_array[] = [
            'optgroup' => $orders_status_groups['orders_status_groups_id'],
        ];
    }

    return tep_draw_pull_down_menu($name, $statuses_array, $order_status_id);
  }

  public static function setEmailTemplate()
  {
      $keys = func_get_args();
      eval('list($selected_value, $key) = array(' . $keys[0] . ');');

      $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');

      return Html::dropDownList($name, $selected_value, \common\helpers\Mail::emailTemplatesList(), [
          'class' => 'form-control',
          'options' => [
          ],
      ]);
  }

  // setGroupedOrderStatuses(false, => w/o any status
  // setGroupedOrderStatuses('[Any order status]', => any status
  // setGroupedOrderStatuses(true, => any status
  public static function setGroupedOrderStatuses()
  {
      $keys = func_get_args();
      eval('list($any_status, $order_statuses, $key) = array(' . $keys[0] . ');');

      $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');

      //$selected_statues = \common\helpers\Order::extractStatuses($order_statuses);
      $selected_statues = preg_split('/,\s*/', $order_statuses, -1, PREG_SPLIT_NO_EMPTY);

      $orderStatusesSelect = [];
      if ( $any_status===false ){

      }else{
          $orderStatusesSelect['*'] = is_string($any_status)?$any_status:'[Any order status]';
      }
      foreach( \common\helpers\Order::getStatusesGrouped(true) as $option){
          $orderStatusesSelect[$option['id']] = html_entity_decode($option['text'],null,'UTF-8');
      }

      return Html::dropDownList($name, $selected_statues, $orderStatusesSelect, [
          'class' => 'form-control',
          'style' => 'height:auto; min-height:150px',
          'multiple' => true,
          'options' => [
          ],
      ]);
  }

  public static function useGroupedOrderStatuses($order_statuses)
  {
      $selected_statues = \common\helpers\Order::extractStatuses($order_statuses);
      return \common\helpers\Order::get_status_name(implode(',',$selected_statues));
  }

  // Alias function for array of configuration values in the Administration Tool
  public static function tep_cfg_select_multioption() {//$select_array, $key_value, $key = ''

    $keys = func_get_args();
    eval('list($select_array, $key_value, $key) = array(' . $keys[0] . ');');

    $string = '';
    for ($i=0; $i<sizeof($select_array); $i++) {
      $name = (($key) ? 'configuration[' . $key . '][]' : 'configuration_value');
      $string .= '<br><input type="checkbox" name="' . $name . '" value="' . $select_array[$i] . '"' . ' id="' . $select_array[$i] . '"';
      $key_values = explode( ", ", $key_value);
      if ( in_array($select_array[$i], $key_values) ) $string .= 'CHECKED';
      $string .= '> <label for="' . $select_array[$i] . '">' . \common\helpers\Translation::getValue($select_array[$i]) . '</label>';
    }
    $name = (($key) ? 'configuration[' . $key . '][]' : 'configuration_value');
    $string .= '<input type="hidden" name="' . $name . '" value="--none--">';
    return $string;
  }

    public static function multiOption(/*$type, $select_array, $key_value, $key = ''*/)
    {
        $keys = func_get_args();
        if ( count($keys)==1 && is_string($keys[0]) ) {
            eval('list($type, $select_array, $key_value, $key) = array(' . $keys[0] . ');');
        }else{
            list($type, $select_array, $key_value, $key) = func_get_args();
        }

        $selected_values = preg_split('/,\s?/',$key_value,-1,PREG_SPLIT_NO_EMPTY);
        $indexed = \yii\helpers\ArrayHelper::isIndexed($select_array, true);
        $variants = $select_array;
        if ( $indexed ){
            $variants = array_combine($select_array, $select_array);
        }

        $string = '';
        $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');
        if ( $type=='dropdown' ) {
            $string .= \common\helpers\Html::dropDownList($name, $key_value, $variants, ['class'=>'form-control']);
        }else{
            foreach ($variants as $value => $valueText) {
                if ($type=='radio'){
                    $string .= '<div><label>'.\common\helpers\Html::radio($name, in_array($value, $selected_values), ['value'=>$value, 'class' => 'multiOption']). ' '.$valueText.'</label></div>';
                }else{
                    $string .= '<div><label>'.\common\helpers\Html::checkbox($name.'[]', in_array($value, $selected_values), ['value'=>$value, 'class' => 'multiOption']). ' '.$valueText.'</label></div>';
                    $string .= \common\helpers\Html::hiddenInput($name.'[]', '--none--');
                }
            }
        }
        return $string;
    }

    public static function translateConfig($configuration)
    {
        $languages_id = \Yii::$app->settings->get('languages_id');

        $_t = Translation::getTranslationValue(strtoupper(str_replace(" ", "_", $configuration['configuration_value'])), 'configuration', $languages_id);
        if ( $_t===false ) {
            $_t = Translation::getTranslationValue(strtoupper(preg_replace('/[^a-z\d_]+/i', '_', $configuration['configuration_key'] . ' VALUE ' . $configuration['configuration_value'])), 'configuration', $languages_id);
        }
        $_t = (tep_not_null($_t) ? $_t : $configuration['configuration_value']);

        return $_t;
    }

  //create a select list to display list of themes available for selection
  public static function tep_cfg_pull_down_template_list() {//$template_id, $key = ''

    $keys = func_get_args();
    eval('list($template_id, $key) = array(' . $keys[0] . ');');

    $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');

    $template_query = tep_db_query("select template_id, template_name from " . TABLE_TEMPLATE . " order by template_name");
    while ($template = tep_db_fetch_array($template_query)) {
      $template_array[] = array('id' => $template['template_name'],
      'text' => $template['template_name']);
    }

    return tep_draw_pull_down_menu($name, $template_array, $template_id);
  }

  public static function tep_cfg_get_timezone_name()//$zone_id
  {
    $keys = func_get_args();
    eval('list($zone_id,) = array(' . $keys[0] . ');');

    foreach(\common\helpers\System::get_timezones() as $unused => $timezone)
    {
      if($timezone['id'] === $zone_id)
      {
        return $timezone['text'];
      }
    }
    return "";
  }

  public static function tep_cfg_pull_down_timezone_list() {//$zone_id
    $keys = func_get_args();
    eval('list($zone_id,) = array(' . $keys[0] . ');');

    return tep_draw_pull_down_menu('configuration_value', \common\helpers\System::get_timezones(), $zone_id);
  }

  public static function cfg_get_information_name() {
    $info_id = 0;

    $keys = func_get_args();
    eval('list($info_id,) = array(' . $keys[0] . ');');

    $languages_id = \Yii::$app->settings->get('languages_id');
    $i = \common\models\Information::find()
        ->where([
                  'information_id' => $info_id,
                  'languages_id' => $languages_id,
                  'affiliate_id' => 0,
                  'platform_id' => \common\classes\platform::defaultId()
                ])->asArray()->one();

    return (empty($i['info_title'])?'':$i['info_title']);
  }

  public static function cfg_get_information_list() {
    $info_id = 0;
    $keys = func_get_args();
    eval('list($info_id,) = array(' . $keys[0] . ');');
    $languages_id = \Yii::$app->settings->get('languages_id');
    $i = \common\models\Information::find()
        ->select([
          'text' => 'info_title',
          'id' => 'information_id',
          ])
        ->where([
                  'languages_id' => $languages_id,
                  'affiliate_id' => 0,
                  'platform_id' => \common\classes\platform::defaultId()
                ])->asArray()->orderBy('info_title')->all();
    $i = array_merge([['id' => 0, 'text' => TEXT_NONE]], $i);

    return tep_draw_pull_down_menu('configuration_value', $i, $info_id);
  }

  public static function tep_cfg_select_download_status() {//$value, $key

    $keys = func_get_args();
    $vals = str_getcsv($keys[0], ',', '\'');
    list($key_value, $key) = $vals;
    $name = $key ? 'configuration[' . $key . '][]' : 'configuration_value[]';

    $select_array = \common\helpers\Order::get_status('', true);
    $key_value_array = explode(',', $key_value);
    $string = '';
    for ($i=0; $i<sizeof($select_array); $i++) {
      //$string .= '<br><input type="checkbox" name="' . $select_array[$i]['text'] . '" value="' . $select_array[$i]['id'] . '"';
      $string .= '<br><input type="checkbox" name="'.$name.'" value="' . $select_array[$i]['id'] . '"';
      for ($j=0;$j<sizeof($key_value_array);$j++) {
        if ($key_value_array[$j] == $select_array[$i]['id']) $string .= ' CHECKED';
      }
      $string .= '> ' . $select_array[$i]['text'];
    }
    $string .= '<br><input type="hidden" name="flag" value="exist">';
    return $string;
  }

  public static function tep_cfg_select_user_group(){//$value, $key

    $keys = func_get_args();
    $vals = str_getcsv($keys[0], ',', '\'');
    list($key_value, $key) = $vals;
    $name = $key ? 'configuration[' . $key . ']' : 'configuration_value';

    $arr = [0 => TEXT_MAIN];
    $tmp = \common\helpers\Group::get_customer_groups_list();
    if (is_array($tmp)) {
      $arr += $tmp;
    }
    if (is_array($arr)) {
      return \common\helpers\Html::dropDownList($name, $key_value, $arr);
    }
/*
    $status_array = array();
    $status_array[] = array('id' => '0', 'text' => TEXT_NONE);
    $status_query = tep_db_query("select * from " . TABLE_GROUPS);
    while ($status = tep_db_fetch_array($status_query)){
      $status_array[] = array('id' => $status['groups_id'], 'text' => $status['groups_name']);
    }
    return tep_draw_pull_down_menu('configuration_value', $status_array, $key_value);

 */
  }

  public static function tep_cfg_select_user_edit_group(){//$key_value

    $keys = func_get_args();
    eval('list($key_value,) = array(' . $keys[0] . ');');

    $status_array = array();
    $status_array[] = array('id' => '0', 'text' => TEXT_NONE);
    $status_query = tep_db_query("select * from " . TABLE_GROUPS);
    while ($status = tep_db_fetch_array($status_query)){
      $status_array[] = array('id' => $status['groups_id'], 'text' => $status['groups_name']);
    }
    return tep_draw_pull_down_menu('groups_id', $status_array, $key_value);
  }

    public static function tep_cfg_color() {//$text
        $keys = func_get_args();
        eval('list($color,) = array(' . $keys[0] . ');');

        return '
<div class="colors-inp">
  <div id="cp3" class="input-group colorpicker-component">
    <input type="text" name="configuration_value" value="' . $color . '" class="form-control" placeholder="' . TEXT_COLOR_ . '" />
    <span class="input-group-append"><span class="input-group-text colorpicker-input-addon"><i></i></span></span>
  </div>
</div>
<script>
$(function(){
     var cp = $(\'.colorpicker-component:not(.colorpicker-element)\');
        cp.colorpicker({ sliders: {
          saturation: { maxLeft: 200, maxTop: 200 },
          hue: { maxTop: 200 },
          alpha: { maxTop: 200 }
        }}).on(\'changeColor\', changeStyle).on(\'changeColor\', function(){
          window.boxInputChanges[$(\'input\', this).attr(\'name\')] = $(\'input\', this).val()
        });
})
</script>';
    }

    public static function time_zones_select()
    {
        $keys = func_get_args();
        eval('list($key_value,) = array(' . $keys[0] . ');');

        $timeZonesVariants = [];

        $tzGroups = [
            'Europe' => \DateTimeZone::EUROPE,
            'America' => \DateTimeZone::AMERICA,
            'Africa' => \DateTimeZone::AFRICA,
            'Australia' => \DateTimeZone::AUSTRALIA,
            'Pacific' => \DateTimeZone::PACIFIC,
            'Asia' => \DateTimeZone::ASIA,
            'Antarctica' => \DateTimeZone::ANTARCTICA,
            'Arctic' => \DateTimeZone::ARCTIC,
            'Atlantic' => \DateTimeZone::ATLANTIC,
            'Indian' => \DateTimeZone::INDIAN,
            'UTC' => \DateTimeZone::UTC,
        ];

        $languages_id = \Yii::$app->settings->get('languages_id');
        $iso2country = ArrayHelper::map(Countries::find()->where(['language_id'=>$languages_id])->orderBy('countries_name')->all(),'countries_iso_code_2', 'countries_name');

        $utc = new \DateTime('now', new \DateTimeZone('UTC'));

        $optionDataAttributes = [];
        foreach( $tzGroups as $tzGroupLabel=>$tzGroupId ) {
            $offsets = [];
            $timeZonesVariants[$tzGroupLabel] = [];
            foreach(\DateTimeZone::listIdentifiers($tzGroupId) as $timeZoneIdent){
                $timeZone = (new \DateTimeZone($timeZoneIdent));
                $transition = $timeZone->getTransitions($utc->getTimestamp(), $utc->getTimestamp());
                $abbr = $transition[0]['abbr'];
                $offset = round($timeZone->getOffset($utc) / 60);
                if ($offset) {
                    $hour = floor($offset / 60);
                    $minutes = floor(abs($offset) % 60);

                    $format = sprintf('%+d', $hour);

                    if ($minutes) {
                        $format .= ':'.sprintf('%02u', $minutes);
                    }
                } else {
                    $format = '';
                }
                $offsets[] = $offset;

                $tzInfo = $timeZone->getLocation();
                if ( is_array($tzInfo) && !empty($tzInfo['country_code']) ) {
                    $optionDataAttributes[$timeZoneIdent] = ['data-country_code'=>$tzInfo['country_code']];
                    if ( isset($iso2country[$tzInfo['country_code']]) ) {
                        $optionDataAttributes[$timeZoneIdent]['data-country_name'] = $iso2country[$tzInfo['country_code']];
                    }
                }

                $timeZoneSelectLabel = $timeZoneIdent;
                $timeZonesVariants[$tzGroupLabel][$timeZoneIdent] = 'UTC'.$format.($abbr !== 'UTC' ? " ({$abbr})" : '').($timeZoneSelectLabel !== 'UTC' ? ' – '.$timeZoneSelectLabel: '');
            }
            array_multisort($offsets, array_keys($timeZonesVariants[$tzGroupLabel]), $timeZonesVariants[$tzGroupLabel]);
        }
        $js  = '<script src="plugins/moment.min.js"></script>';
        //$js .= '<script src="plugins/moment-timezone/builds/moment-timezone.min.js"></script>';
        $js .= '<script src="plugins/moment-timezone.min.js"></script>';
        $js .= '<script type="text/javascript" src="plugins/timezone-picker/timezone-picker.min.js"></script>';
        ob_start();
        ?>
<script type="text/javascript">
    $('.js-complete').select2({
        matcher: function(term, text, el) {
            if ( el && el.length>0 ) {
                var $option = $(el[0]);
                if ( $option.attr('data-country_code') ) {
                    text+=' '+$option.attr('data-country_code');
                }
                if ( $option.attr('data-country_name') ) {
                    text+=' '+$option.attr('data-country_name');
                }
            }
            return (''+text).toUpperCase().indexOf((''+term).toUpperCase()) >= 0;
        }
    });
    $('.js-btn-tz-map').on('click',function () {
        bootbox.dialog({
            title: <?php echo json_encode(defined('TEXT_HEAD_TIME_ZONE_POPUP')?TEXT_HEAD_TIME_ZONE_POPUP:'Select time zone'); ?>,
            message:
            '<div id="tzMap" style="min-height:500px; margin-bottom: 20px"></div>' +
            '<script type="text/javascript">$("#tzMap").timezonePicker({ selectedColor: \'#2F5984\', selectBox:false, quickLink:[] }); ' +
            '$("#tzMap").data("timezonePicker").setValue($("#selTimeZones").val());' +
            '</'+'scr'+'ipt>',
            onEscape: true,
            buttons:{
                confirm: {
                    label: <?php echo json_encode(defined('IMAGE_SELECT')?IMAGE_SELECT:'Select');?>,
                    className: 'btn-success',
                    callback: function() {
                        var selectedTZ = $("#tzMap").data("timezonePicker").getValue();
                        if ( selectedTZ && selectedTZ.length>0 ) {
                            for( var i=0; i<selectedTZ.length;i++ ){
                                if ( $("#selTimeZones option").filter('[value="'+selectedTZ[i].timezone+'"]').length==0 ) continue;
                                $("#selTimeZones").val(selectedTZ[i].timezone).trigger('change');
                                if ( $("#selTimeZones").val()==selectedTZ[i].timezone ) break;
                            }
                        }
                    }
                },
                cancel: { label: <?php echo json_encode(defined('TEXT_BTN_NO')?TEXT_BTN_NO:'No');?> }
            }
        });
        //map:clicked
    });
</script>
        <table cellpadding="0" cellspacing="0">
            <tr>
                <td>
                    <?php echo Html::dropDownList('configuration_value',$key_value, $timeZonesVariants, ['class'=>'select2 js-complete select2-input', 'id'=>'selTimeZones', 'options'=>$optionDataAttributes])?>
                </td>
            </tr>
            <tr>
                <td align="center">&nbsp;<br><button type="button" class="js-btn-tz-map btn btn-1"><?php echo defined('TEXT_OPEN_MAP')?TEXT_OPEN_MAP:'Open map' ?></button></td>
            </tr>
        </table>
<?php
        $js .= ob_get_clean();
        return $js/*.Html::dropDownList('configuration_value',$key_value, $timeZonesVariants, ['class'=>'select2 js-complete form-control'])*/;
        //return $js.('configuration_value', $timeZonesVariants, $key_value);
        //return $js.tep_draw_pull_down_menu('configuration_value', $timeZonesVariants, $key_value);
    }

    public static function cfg_true_get_order_status( $list='' ){
      $default = preg_split('/[, ]/', $list, -1, PREG_SPLIT_NO_EMPTY);
      if ( is_array($default) ) {
        foreach( $default as $idx=>$status_id ) {
          $status_name = \common\helpers\Order::get_order_status_name($status_id);
          $default[$idx] = empty($status_name)?$status_id.'?':$status_name;
        }
        $ret = implode(', ',$default);
      }else{
        $ret = $default;
      }

      return $ret;
    }

    public static function cfg_upload_file($cfgData){
        $cfgValues = explode(",", trim(stripslashes ($cfgData)));
        $key = null;
        $value = null;
        foreach($cfgValues as $cValue){
            $cValue = str_replace(["'",'"'], "", trim(stripslashes($cValue)));
            if (empty($cValue)) continue;
            if (preg_match("/MODULE_PAYMENT.*/", $cValue)){
                $key = $cValue;
                break;
            }
            $value = $cValue;
        }

        if ($key){
            $id = 'configuration['. $key. ']';
            $file = \yii\helpers\Html::fileInput($id, $value, ['class' => ' file-config', 'id' => $id]) . (!empty($value)? $value : '');
            $js = <<<EOD
<script>
    var files = []
    handleFileSelect = function(e){
        var _files = e.target.files;
        for (var i = 0, f; f = _files[i]; i++) {
            reader = new FileReader();
            reader.onload = (function(theFile) {
                files[e.target.name] = theFile;
            })(f);
            reader.readAsDataURL(f);
        }
    }
    document.getElementById('{$id}').addEventListener('change', handleFileSelect, false);
</script>
EOD;
            $file .= $js;
        }

        return $file;
    }

    public static function cfg_true_set_order_status( $single=true, $list='', $key = ''  ){

      if ( $single == 'true') {
        $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');
      }else{
        $name = (($key) ? 'configuration[' . $key . '][]' : 'configuration_value[]');
      }

      $values = \common\helpers\Order::get_status();
      $default = preg_split('/[, ]/', $list, -1, PREG_SPLIT_NO_EMPTY);

      $field = '<select '.($single == 'true'?'':'size="'.min(count($values),5).'" multiple="multiple" ').' name="' . \common\helpers\Output::output_string($name) . '"';
      for ($i=0, $n=sizeof($values); $i<$n; $i++) {
        $field .= '<option value="' . \common\helpers\Output::output_string($values[$i]['id']) . '"';
        if ( in_array($values[$i]['id'],$default)) {
          $field .= ' SELECTED';
        }

        $field .= '>' . \common\helpers\Output::output_string($values[$i]['text'], array('"' => '&quot;', '\'' => '&#039;', '<' => '&lt;', '>' => '&gt;')) . '</option>';
      }
      $field .= '</select>';

      return $field;
    }

    public static function cfg_supplier_price_selection_mode()
    {
        $keys = func_get_args();
        eval('list($value, $key) = array(' . $keys[0] . ');');

        $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');

        return \common\helpers\Html::dropDownList($name,
            $value,
            [
                'Manual' => TEXT_MANUAL,
                'Auto' => TEXT_AUTO,
            ]
        );
    }

    public static function cfg_supplier_price_rule_priority()
    {
        $keys = func_get_args();
        eval('list($value, $key) = array(' . $keys[0] . ');');

        $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');

        return \common\helpers\Html::dropDownList($name,
            $value,
            [
                'Category,Brand,Supplier'=>'Category, Brand, Supplier',
                'Brand,Category,Supplier'=>'Brand, Category, Supplier'
            ]
        );
    }

    public static function cfg_supplier_price_select()
    {
        $keys = func_get_args();
        eval('list($value, $key) = array(' . $keys[0] . ');');

        $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');

        return \common\helpers\Html::dropDownList($name,
            $value,
            [
                'Disabled' => 'Disabled',
                'Cheapest, In stock' => 'Cheapest, In stock',
                'Supplier order' => 'Use supplier sort order',
                'Based on priority rules' => 'Based on priority rules',
            ]
        );
    }

    public static function valueUpdated($key, $value)
    {
        switch ($key){
            case 'SUPPLIER_UPDATE_PRICE_MODE':
                \common\helpers\Suppliers::onUpdatePriceModeSwitch($value);
                break;
            default:
                ;
        }
    }

    protected static function shippingModulesWithMethods()
    {
        $modulesList = [];
        foreach (\common\helpers\Modules::shippingModules() as $module) {
            $modulesList[$module->code] = $module->title;
            $methods = $module->possibleMethods();
            if (count($methods) > 0) {
                foreach ($methods as $methodId => $methodName) {
                    $modulesList[$module->code . '_' . $methodId] = $module->title . ' : ' . $methodName;
                }
            }
        }
        return $modulesList;
    }

    public static function showSelectedShipping($value)
    {
        $modulesList = static::shippingModulesWithMethods();
        if ( is_string($value) && strpos($value,',')!==false ){
            $values = preg_split('/,\s?/',$value,-1,PREG_SPLIT_NO_EMPTY);
        }else{
            $values = [$value];
        }
        foreach ($values as $_idx=>$value){
            $values[$_idx] = isset($modulesList[$value])?$modulesList[$value]:$value;
        }

        return implode(', ',$values);
    }

    public static function selectShipping()
    {
        $keys = func_get_args();
        eval('list($multiple, $value, $key) = array(' . $keys[0] . ');');

        $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');

        $modulesList = static::shippingModulesWithMethods();

        return \common\helpers\Html::dropDownList($name,
            $value,
            $modulesList,
            ['multiple' => $multiple]
        );
    }

    public static function tep_get_country_name($country_id, $lan_id = 0) {
        return \common\helpers\Country::get_country_name($country_id, $lan_id);
    }

    public static function cfgMultiSortable() {//$possible_values, $key_value, $key = ''

      $keys = func_get_args();
      $possible_values = [];
      $string = $key_value = $key = '';
      eval('list($possible_values, $key_value, $key) = array(' . $keys[0] . ');');

      $key_values = explode( ", ", $key_value);
      $string .= '  <script>
                      $( function() {
                        $( ".sortable" ).sortable();
                        $( ".sortable" ).disableSelection();
                        $(".uniform").uniform();
                      } );
                    </script>';
      $string .= '<ul class="sortable">';

      $tmp = array_diff($possible_values, $key_values);
      $possible_values = array_merge($key_values, $tmp);


      for ($i=0, $n = count($possible_values); $i<$n; $i++) {
        $name = (($key) ? 'configuration[' . $key . '][]' : 'configuration_value');
        $string .= '<li ><span class="handle"><i class="icon-hand-paper-o"></i></span>';
        $string .= '<div class="name">';
        $string .= \common\helpers\Html::checkbox($name, in_array($possible_values[$i], $key_values), ['value' => $possible_values[$i], 'id' => 'conf_val_' . $possible_values[$i] ]);
        $string .= '<label for="conf_val_' . $possible_values[$i] .'">';
        $string .= $possible_values[$i];
        $string .= '</label></div>';
        $string .= '</li>';
      }
      $name = (($key) ? 'configuration[' . $key . '][]' : 'configuration_value');
      $string .= '</ul>';
      return $string;
    }

    protected static function variantsCheckoutRecalculateFields()
    {
        return [
            'street_address' => ENTRY_STREET_ADDRESS,
            'suburb' => ENTRY_SUBURB,
            'city' => ENTRY_CITY,
            'postcode' => ENTRY_POST_CODE,
            'state' => ENTRY_STATE,
            'country' => ENTRY_COUNTRY,
        ];
    }

    public static function setCheckoutRecalculateFields()
    {
        $keys = func_get_args();
        eval('list($value, $key) = array(' . $keys[0] . ');');
        $value = preg_split('/,\s?/',strval($value),-1, PREG_SPLIT_NO_EMPTY);

        $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');

        return '<div>'.\common\helpers\Html::checkboxList($name,
                $value,
                static::variantsCheckoutRecalculateFields(),
                ['separator'=>"<br />\n", 'class'=>'js-checkout-fields text-left']
            ).'</div>'.
            \common\helpers\Html::hiddenInput($name.'[]','country').
            '<script>$(document).ready(function(){ $(\'.js-checkout-fields input[value="country"]\').attr(\'disabled\', \'disabled\') })</script>';
    }

    public static function getCheckoutRecalculateFields($value)
    {
        $values = preg_split('/,\s?/',strval($value),-1, PREG_SPLIT_NO_EMPTY);
        $variants = static::variantsCheckoutRecalculateFields();
        foreach ($values as $idx=>$value){
            if ( isset($variants[$value]) ) $values[$idx] = $variants[$value];
        }
        return implode(', ',$values);
    }

    public static function variantsBackendProductName()
    {
        return [
            'Listing' => 'Product Listing',
            'Orders' => 'Orders Detail',
            'PackingSlip' => 'Packing Slip',
            'Invoice' => 'Invoice',
        ];
    }

    public static function getBackendProductName($value)
    {
        $values = preg_split('/,\s?/',strval($value),-1, PREG_SPLIT_NO_EMPTY);
        $variants = static::variantsBackendProductName();
        foreach ($values as $idx=>$value){
            if ( isset($variants[$value]) ) $values[$idx] = $variants[$value];
        }
        return implode(', ',$values);
    }

    public static function setBackendProductName()
    {
        $keys = func_get_args();
        eval('list($value, $key) = array(' . $keys[0] . ');');
        $value = preg_split('/,\s?/',strval($value),-1, PREG_SPLIT_NO_EMPTY);

        $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');

        return '<div>'.\common\helpers\Html::checkboxList($name,
            $value,
            static::variantsBackendProductName(),
            ['separator'=>"<br />\n", 'class'=>'text-left']
        ).'</div>';
    }

    public static function getListingSortOrder($value)
    {
        $values = preg_split('/,\s?/',strval($value),-1, PREG_SPLIT_NO_EMPTY);
        $variants = \common\helpers\Sorting::getPossibleSortOptions();
        foreach ($values as $idx=>$value) {
          if ( isset($variants[$value]) ) {
            $values[$idx] = $variants[$value];
          }
        }
        return implode(', ',$values);
    }

    public static function setListingSortOrder()
    {
        $keys = func_get_args();
        $value = $key = '';
        eval('list($value, $key) = array(' . $keys[0] . ');');
        $value = preg_split('/,\s?/',strval($value),-1, PREG_SPLIT_NO_EMPTY);

        $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');

        return '<div>'.\common\helpers\Html::radioList($name,
            $value,
            $variants = \common\helpers\Sorting::getPossibleSortOptions(),
            ['separator'=>"<br />\n", 'class'=>'text-left']
        ).'</div>';
    }

    public static function getAutoCompleteField(...$arguments)
    {
        try {
            /** @var callable $func */
            /** @var string $value */
            /** @var string $key */
            eval('[$func, $value, $key] = [' . $arguments[0] . '];');
            $name = $key ? 'configuration[' . $key . ']' : 'configuration_value';
            $params = $func([]);
            $script = <<< JS
            var params{$key} = {$params};
            $('#id_{$key}').autocomplete({
                source: function (req, res) {
                    var words = req.term.split(' ');
                    var results = $.grep(params{$key}, function(item, index) {
                        var sentence = item.toLowerCase();
                        return words.every(function(word) {
                            return sentence.indexOf(word.toLowerCase()) >= 0;
                        });
                    });
                    res(results);
                },
                appendTo: '#id{$key}Wrap',
                autoFocus: true,
                delay: 0,
                minLength: 0,
            }).focus(function () {
                $(this).autocomplete("search",$(this).val());
            });
JS;
            \Yii::$app->getView()->registerJs($script);
            return sprintf(
                '%s<div id="id%sWrap" style="position: relative;"></div>',
                \common\helpers\Html::Input('text', $name, $value, ['class' => 'form-control', 'id' => 'id_'.$key]),
                $key
            );
        } catch (\Exception $e) {
            return  $e->getMessage();
        }
    }

    public static function getDropDownField(...$arguments)
    {
        try {
            /** @var callable $func */
            /** @var string $value */
            /** @var string $key */
            eval('[$func, $value, $key] = [' . $arguments[0] . '];');
            $name = $key ? 'configuration[' . $key . ']' : 'configuration_value';
            $params = $func([]);
            return sprintf(
                '%s',
                \common\helpers\Html::dropDownList($name, $value, $params,  ['class' => 'form-control', 'id' => 'id_' . $key])
            );
        } catch (\Exception $e) {
            return  $e->getMessage();
        } catch (\Throwable $e) {
            return  $e->getMessage();
        }
    }

    public static function getDropDownDependField(...$arguments)
    {
        try {
            /** @var callable $func */
            /** @var string $keyDepend */
            /** @var string $value */
            /** @var string $key */
            eval('[$func, $keyDepend, $value, $key] = [' . $arguments[0] . '];');
            $name = $key ? 'configuration[' . $key . ']' : 'configuration_value';
            $keyDepend = 'id_' . $keyDepend;
            $params = $func([]);

            $script = <<< JS
            var params{$key} = {$params};
            $('#{$keyDepend}').on('change', function() {
              $('#id_{$key}').html('');
              /*
                WARNING reserved property
                item.id
                item.depend
                item.text
               */
              $.each(params{$key}, function (index, item) {
                  if (item.depend ===  $('#{$keyDepend}').val()){
                    var option = $("<option></option>")
                    $('#id_{$key}').append(option);
                    option.attr('value', item.id).text(item.text);
                    if (item.id === '{$value}') {
                        option.prop('selected', true);
                    }
                  }
                });
            });
            $('#{$keyDepend}').change();
JS;
            \Yii::$app->getView()->registerJs($script);
            return sprintf(
                '%s',
                \common\helpers\Html::dropDownList($name, $value, [],  ['class' => 'form-control', 'id' => 'id_' . $key])
            );
        } catch (\Exception $e) {
            return  $e->getMessage();
        }
    }

    public static function getAutoCompleteAjaxDependField(...$arguments)
    {
        try {
            /** @var callable $func */
            /** @var string $keyDepend */
            /** @var string $method */
            /** @var string $suggestProperty */
            /** @var string $value */
            /** @var string $key */
            eval('[$func, $keyDepend, $method, $suggestProperty, $value, $key] = [' . $arguments[0] . '];');
            $name = $key ? 'configuration[' . $key . ']' : 'configuration_value';
            $keyDepend = 'id_' . $keyDepend;
            $method = mb_strtolower($method);
            $url = $func([]);
            $script = <<< JS
              $('#{$keyDepend}').on('change', function() {
                if (!$('#{$keyDepend}').val()) {
                    return false;
                }
                $('#id_{$key}').html('');
                $.{$method}('{$url}', {
                    "{$suggestProperty}": $('#{$keyDepend}').val(),
                }, function (response) {
                    /*
                        WARNING reserved property
                        response.success
                        response.data = [
                            [
                                id,
                                text
                            ]
                        ]
                    */
                    if (response.success && response.data) {
                        $.each(response.data, function (index, item) {
                            var option = $("<option></option>")
                            $('#id_{$key}').append(option);
                            option.attr('value', item.id).text(item.text);
                            if (item.id === '{$value}') {
                                option.prop('selected', true);
                            }
                        });
                    }
                });
              });
              $('#{$keyDepend}').change();
JS;
            \Yii::$app->getView()->registerJs($script);
            return sprintf(
                '%s',
                \common\helpers\Html::dropDownList($name, $value, [],  ['class' => 'form-control', 'id' => 'id_' . $key])
            );
        } catch (\Exception $e) {
            return  $e->getMessage();
        }
    }

    public static function inputWithChoice(...$arguments)
    {
        try {
            /** @var callable $func */
            /** @var string $value */
            /** @var string $key */
            eval('[$func, $value, $key] = [' . $arguments[0] . '];');
            $name = $key ? 'configuration[' . $key . ']' : 'configuration_value';
            $options = $func($value);
            $script = <<< JS
                var {$key}_field = $(".{$key}inputField");
                var {$key}_result = $(".{$key}input");
                if ($('.{$key}radio:checked').attr('data-disable') === "1") {
                    {$key}_field.hide();
                }
                $(".{$key}radio").on('click', function() {
                    var current = $(this);
                    if (current.attr('data-disable') === "1") {
                        {$key}_field.hide();
                    } else {
                        {$key}_field.show();
                    }
                    if (current.attr('data-change') === "direct") {
                        {$key}_result.val(current.val());
                    }
                    if (current.attr('data-change') === "lazy") {
                        {$key}_result.val({$key}_result.val());
                    }
                });
                {$key}_field.on('keyup change', function() {
                  {$key}_result.val({$key}_field.val());
                });
JS;

            $text = '';
            foreach ($options as $optionName => $option) {
                $text .= '<div style="display: block;text-align: left;padding: 0;"><label>'.\common\helpers\Html::radio("{$name}radio", $option['value'] === $value, [ 'data-disable'=>$option['disableInput'], 'data-change'=>$option['changeInput'], 'value'=>$option['value'], 'class' => "uniform {$key}radio", 'id' => "id_{$key}_{$optionName}"]). ' '.$optionName.'</label></div>';
            }
            $text .= '<div style="display: block;text-align: left;padding: 0;">' .
                \common\helpers\Html::input('text', "{$name}inputField", $value, ['class'=>"form-control {$key}inputField"]) .
                \common\helpers\Html::input('hidden', $name, $value, ['class'=>"form-control {$key}input"]) .
                '</div>';
            return $text."<script>$script</script>";
        } catch (\Exception $e) {
            //return  $e->getMessage();
        }
    }

    public static function getTaxAddressBy($value)
    {
        $values = preg_split('/,\s?/',strval($value),-1, PREG_SPLIT_NO_EMPTY);
        $variants = self::$taxAddressOptions;
        foreach ($values as $idx=>$value) {
          if ( isset($variants[$value]) ) {
            $values[$idx] = $variants[$value];
          }
        }
        return implode(', ',$values);
    }

    public static function setTaxAddressBy()
    {
        $keys = func_get_args();
        $value = $key = '';
        $value = $keys[0]['value'];
        $key = $keys[0]['key'];
        $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');

        $value = preg_split('/,\s?/',strval($value),-1, PREG_SPLIT_NO_EMPTY);
        return '<div>'.\common\helpers\Html::radioList($name,
            $value,
            $variants = self::$taxAddressOptions,
            ['separator'=>"<br />\n", 'class'=>'text-left']
        ).'</div>';
    }

    public static function upsxml_cfg_select_multioption_indexed()
    {
        $string = '';

        $keys = func_get_args();
        eval('list($select_array, $key_value, $key) = array(' . $keys[0] . ');');

        for ($i = 0; $i < sizeof($select_array); $i++) {
            $name = (($key) ? 'configuration[' . $key . '][]' : 'configuration_value');
            $string .= '<br><input type="checkbox" name="' . $name . '" value="' . $select_array[$i] . '"';
            $key_values = explode(", ", $key_value);
            if (in_array($select_array[$i], $key_values)) {
                $string .= ' CHECKED';
            }
            $string .= '> ' . constant('UPSXML_' . trim($select_array[$i]));
        }
        $name = (($key) ? 'configuration[' . $key . '][]' : 'configuration_value');
        $string .= '<input type="hidden" name="' . $name . '" value="--none--">';
        return $string;
    }

    public static function getDropDownImageTypes()
    {
        $keys = func_get_args();
        $value = $key = '';
        eval('list($value, $key) = array(' . $keys[0] . ');');

        $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');

        $selectArray = [];
        foreach (\common\classes\Images::getImageTypes() as $type) {
            $selectArray[$type['image_types_name']] = sprintf('%s (%sx%s)', $type['image_types_name'],  $type['image_types_x'], $type['image_types_y']);
        }
        
        return Html::dropDownList($name, $value, $selectArray, [
            'class' => 'form-control',
            'options' => [
            ],
        ]);
    }
}
