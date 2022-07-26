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

namespace common\helpers;

class Html extends \yii\helpers\Html
{
    public static function textInputNullable($name, $value = null, $options = [])
    {
        $button = [
            'content' => '<i class="icon-pencil"></i>',
            'options' => []
        ];
        if (isset($options['button'])){
            $button = array_merge($button, $options['button']);
        }
        if ( !is_array($button['options']) ) $button['options'] = [];
        if ( !isset($button['options']['class']) ) {
            $button['options']['class'] = 'input-group-addon js-input-nullable-btn';
        }elseif(strpos($button['options']['class'],'input-group-addon')===false){
            $button['options']['class'] = 'input-group-addon js-input-nullable-btn '.$button['options']['class'];
        }
        $inputButton = static::tag('div', $button['content'], $button['options']);

        //'<div class="input-group-addon js-price-formula" data-formula-rel="#txtSupplierPriceFormula" data-formula-allow-params=""></div>';
        if ( is_null($value) ){
            $options['readonly'] = 'readonly';
            $options['disabled'] = 'disabled';
        }

        return
            '<div class="input-group js-main-text-input-nullable">'.
              static::textInput($name,$value,$options).
              $inputButton.
            '</div>';
    }

/**
 * adds css default class (form-control), unique class (<last-class>|<$type>-<start[name]>) id by name (_ to lo-camel-ed),
 * @param type $type
 * @param type $name
 * @param type $value
 * @param type $options
 * @return type
 */
    public static function input($type, $name = null, $value = null, $options = []){

      self::commonClass($type, $name, $options);
      self::commonId($name, $options);

      if (!isset($options['class']) || strpos($options['class'], 'form-control')===false) {
        if (in_array($type, ['checkbox', 'radio'])) {
          $c = 'form-control-bool ';
        } else {
          $c = 'form-control ';
        }
        $options['class'] = $c . (isset($options['class'])?$options['class']:'');
      }

      return parent::input($type, $name, $value, $options);
    }

    public static function checkbox($name, $checked = false, $options = array()) {
      if (!isset($options['class']) || (strpos($options['class'], 'uniform')===false && strpos($options['class'], '_on_off')===false )) { // check|switch| etc _on_off O_O
        $options['class'] = 'uniform ' . (isset($options['class'])?$options['class']:'');
      }
      return parent::checkbox($name, $checked, $options);
    }

    public static function commonId ($name, &$options) {
      if (!isset($options['id']) && !empty($name) && !strpos($name, '[]')) {
        $p = explode('_', $name);
        array_walk($p, function(&$val, $key) { if ($key>0) {$val = ucfirst($val);} } );
        $options['id'] =
          str_replace(['[',']'], '_', implode('', $p));
      }
    }

    public static function commonClass ($type, $name, &$options) {
      if (!empty($name)) {
        $m = [];
        preg_match('/^[\da-z]+/i', $name, $m);
        if (count($m)) {
          $sf = '-' . $m[0];
        } else {
          $sf = '-' . $name;
        }

        if (isset($options['class'])) {
          $m = [];
          preg_match('/[\S]+$/i', trim($options['class']), $m);
          if (count($m)) {
            $options['class'] .= ' ' . $m[0] . $sf;
          } else {
            $options['class'] .= ' '. $type . $sf;
          }
        } else {
          $options['class'] = ' '. $type . $sf;
        }
      }
    }

/**
 * {@inheritdocs}
 */
    public static function dropDownList($name, $selection = null, $items = array(), $options = array()) {
      self::commonClass('select', $name, $options);
      self::commonId($name, $options);

      if (!isset($options['class']) || strpos($options['class'], 'form-control')===false) {
        $options['class'] = 'form-control' . (isset($options['class'])?$options['class']:'');
      }
      return parent::dropDownList($name, $selection, $items, $options);
    }
    
    public static function activeFileInput($model, $attribute, $options = [])
    {
        $hiddenOptions = ['id' => null];
        if (isset($options['name'])) {
            $hiddenOptions['name'] = $options['name'];
        }
        // make sure disabled input is not sending any value
        if (!empty($options['disabled'])) {
            $hiddenOptions['disabled'] = $options['disabled'];
        }
        $hiddenOptions = \yii\helpers\ArrayHelper::merge($hiddenOptions, \yii\helpers\ArrayHelper::remove($options, 'hiddenOptions', []));
     
        return static::activeHiddenInput($model, $attribute, $hiddenOptions)
            . static::activeInput('file', $model, $attribute, $options);
    }

    public static function fixHtmlTags($html)
    {
        if (!class_exists('\DOMDocument')) {
            return $html;
        }
        $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
        if (empty($html)) {
            return $html;
        }
        $dom = new \DOMDocument();
        @$dom->loadHTML($html);
        $nodes = $dom->getElementsByTagName('body')->item(0)->childNodes;

        $html = '';
        $len = $nodes->length;
        for ($i = 0; $i < $len; $i++) {
            $html .= $dom->saveHTML($nodes->item($i));
        }
        $html = preg_replace('/<p[^>]{0,}>/', '', $html);
        $html = str_replace('</p>', '', $html);

        return $html;
    }

}