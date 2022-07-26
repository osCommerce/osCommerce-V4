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

namespace backend\models\EP;


class ArrayTransform
{


    public static function convertMultiDimensionalToFlat($array, $separator='.')
    {
        //return self::__multi_to_flat_set('', $array, $separator);
        $out = [];
        self::__multi_to_flat_set_ref($out, '', $array, $separator);
        return $out;
    }

    public static function convertFlatToMultiDimensional($array, $separator='.')
    {
        $multi = [];
        foreach( $array as $multiKey=>$value ){
            $keys = explode($separator, $multiKey);
            $key = array_shift($keys);
            if ( count($keys)==0 ) {
                $multi[$key] = $value;
            }else{
                if ( !isset($multi[$key]) || !is_array($multi[$key]) ) $multi[$key] = [];
                self::__flat_to_multi_set($multi[$key], $keys, $value );
            }
        }
        return $multi;
    }

    /**
     *
     * ArrayTransform::transformMulti([
     *  'key1' => 'group.k1',
     *  'key2' => 'group.k2',
     *  'key3' => 'any'
     * ],$array1)
     *
     * @param $mapping
     * @param $array
     * @return array
     */
    public static function transformMulti($mapping, $array)
    {
        $tmpFlat = self::convertMultiDimensionalToFlat($array);
        $newFlat = [];
        if ( is_callable($mapping) ) {
            foreach( array_keys($tmpFlat) as $fromPath ) {
                $toPath = $mapping($fromPath);
                if ( $toPath!==false ) {
                    $newFlat[$toPath] = $tmpFlat[$fromPath];
                }
            }
        }else {
            foreach ($mapping as $fromPath=>$toPath) {
                if (isset($tmpFlat[$fromPath])) {
                    $newFlat[$toPath] = $tmpFlat[$fromPath];
                }
            }
        }
        return self::convertFlatToMultiDimensional($newFlat);
    }


    private static function __multi_to_flat_set_ref(&$flat, $currentKey, $array, $separator){
        $keyPrepend = '';
        if ( !empty($currentKey) ) $keyPrepend = $currentKey.$separator;
        foreach($array as $key=>$val) {
            $itemKey = $keyPrepend.$key;
            if ( is_array($val) ) {
                self::__multi_to_flat_set_ref($flat, $itemKey, $val, $separator);
            }else {
                $flat[$itemKey] = $val;
            }
        }
    }

    private static function __multi_to_flat_set($currentKey, $array, $separator){
        $flat = [];
        $keyPrepend = '';
        if ( !empty($currentKey) ) $keyPrepend = $currentKey.$separator;
        foreach($array as $key=>$val) {
            $itemKey = $keyPrepend.$key;
            if ( is_array($val) ) {
                $flat = array_merge($flat, self::__multi_to_flat_set($itemKey, $val, $separator));
            }else {
                $flat[$itemKey] = $val;
            }
        }
        return $flat;
    }

    private static function __flat_to_multi_set(&$array, $keys, $value)
    {
        $key = array_shift($keys);
        if ( count($keys)==0 ) {
            $array[$key] = $value;
        }else{
            if ( !isset($array[$key]) || !is_array($array[$key]) ) $array[$key] = [];
            self::__flat_to_multi_set($array[$key], $keys, $value );
        }
    }

}