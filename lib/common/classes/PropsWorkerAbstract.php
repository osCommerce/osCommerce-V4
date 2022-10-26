<?php

namespace common\classes;

abstract class PropsWorkerAbstract
{
    /**
     * Convert POST params to xml
     * @param $params array $POST
     * @param $productId
     * @return array
     */
    public static function paramsToXml($params = array(), $productId = false) {
        return [];
    }

    /**
     * Return normalized urpid without props
     * @param type $uprid
     * @return type
     */
    public static function normalize_id($uprid) {
        return $uprid;
    }

    /**
     * Modify Cart contens key (product_id|uprid) particulary to props
     * @params $productId mixed normalized product id
     * @params $propsData array
     * @return mixed modified product id
     */
    public static function cartUprid($productId, array $propsData) {
        return $productId;
    }

    public static function onCartAdd($propsData) {
        return $propsData;
    }

    /*
    abstract public static function explainParams($params = array(), $tax_rate = 0);
    abstract public static function cartChanged($cart);
    */
}