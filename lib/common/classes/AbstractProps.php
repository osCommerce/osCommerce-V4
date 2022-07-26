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

abstract class AbstractProps {

    /**
     * Convert paramt to xml
     */
    abstract public static function paramsToXml($params = array(), $productId = false);

    /**
     * Retrieve params to working state
     */
    abstract public static function explainParams($params = array(), $tax_rate = 0);
    
    /**
     * Describe Uprid without any transforms
     */
    abstract public static function normalize_id($uprid);

    /**
     * Cart accessible unique uprid
     */
    abstract public static function cartUprid($products_id, $props);
    
    /**
     * event in add_cart method
     */
    abstract public static function onCartAdd($props);
    
    /**
     * 
     */
    abstract public static function cartChanged($cart);
    
    /**
     * describe product properties for cartDecorator
     * @param type $cartProduct
     */
    public static function describeProduct(&$cartProduct){
        if ( isset($cartProduct['explain_info']) && is_array($cartProduct['explain_info']) ) {
            if (!is_array($cartProduct['attr'])) $cartProduct['attr'] = [];
            foreach ( $cartProduct['explain_info'] as $_props_info ) {
                if ( $_props_info['extra_view'] ) $_props_info['products_options_values_name'] .= $_props_info['extra_view'];
                $cartProduct['attr'][] = $_props_info;
            }
        }
    }
    
    /**
     * Return extra properties information to product
     */
    abstract public static function adminOrderProductView($orderProduct);

    /**
     * Append properties as continious of attributes list
     * @param type $orderProduct
     */
    public static function describeOrderProduct(&$orderProduct){
        if ($orderProduct['propsData']){
            $explain_info = static::explainParams($orderProduct['propsData'], $orderProduct['tax']);
            if ( is_array($explain_info) ) {
                !is_array($orderProduct['attributes']) && $orderProduct['attributes'] = [];
                $subindex = (int)count($orderProduct['attributes']);
                foreach( $explain_info as $attributes ) {
                    $orderProduct['attributes'][$subindex] = array(
                        'option' => $attributes['products_options_name'],
                        'value' => $attributes['products_options_values_name'],
                        'prefix' => false,
                        'price' => false);
                    $subindex++;
                }
            }
        }
    }

    public static function toXML($data, $root = null) {
        $xml = new \SimpleXMLElement($root ? '<' . $root . '/>' : '<root/>');
        self::array_to_xml($data, $xml);
        return $xml->asXML();
    }

    public static function XmlToParams($xmlstring = '') {
        $params = [];
        $xml = @simplexml_load_string($xmlstring);
        if ($xml) {
            $params = self::parseSimpleXML($xml);
        }
        return $params;
    }

    public static function parseSimpleXML($xmldata) {
        $childNames = array();
        $children = array();

        if (count($xmldata) !== 0) {
            foreach ($xmldata->children() AS $child) {
                $name = $child->getName();
                if (!isset($childNames[$name])) {
                    $childNames[$name] = 0;
                }
                $childNames[$name] ++;
                $children[$name][] = self::parseSimpleXML($child);
            }
        }
        $returndata = array();
        if (count($childNames) > 0) {
            foreach ($childNames AS $name => $count) {
                if ($count === 1) {
                    $returndata[$name] = $children[$name][0];
                } else {
                    $returndata[$name] = array();
                    $counter = 0;
                    foreach ($children[$name] AS $data) {
                        $returndata[$name][$counter] = $data;
                        $counter++;
                    }
                }
            }
        } else {
            if (defined('CHARSET')) {
                $xmldata = iconv("utf-8", CHARSET, $xmldata);
            }
            $returndata = (string) $xmldata;
            $returndata = stripslashes($returndata);
        }
        return $returndata;
    }

    public static function array_to_xml($data, &$xml_data) {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if (is_numeric($key)) {
                    $key = 'item' . $key; //dealing with <0/>..<n/> issues
                }
                $subnode = $xml_data->addChild($key);
                self::array_to_xml($value, $subnode);
            } else {
                $xml_data->addChild("$key", htmlspecialchars("$value"));
            }
        }
    }

}
