<?php

namespace common\classes;

class PropsWorkerAttrText extends PropsWorkerAbstract
{

    /**
     * Convert POST params to xml
     * @param $params array $POST
     * @param $productId
     * @return array
     */
    public static function paramsToXml($params = array(), $productId = false)
    {
        $data = [];
        if (is_array($params['attr_text'] ?? null)) {
            $data['AttrText'] = [];
            foreach ($params['attr_text'] as $attrId => $textValue) {
                $textValue = tep_db_prepare_input($textValue);
                $data['AttrText']['a'.$attrId] = [
                    'value' => $textValue,
                    'crc' => crc32((string)$textValue),
                ];

            }
        }
        return $data;
    }

    /**
     * Return normalized urpid without props
     * @param type $uprid
     * @return type
     */
    public static function normalize_id($uprid)
    {
        $uprid = preg_replace('#\{a\d+\}[^{]*#','',$uprid);
        return $uprid;
    }

    /**
     * Modify Cart contens key (product_id|uprid) particulary to props
     * @params $productId mixed normalized product id
     * @params $propsData array
     * @return mixed modified product id
     */
    public static function cartUprid($products_id, $propsData)
    {
        if (is_array($propsData['AttrText'] ?? null)) {
            foreach ($propsData['AttrText'] as $id=>$val) {
                if (preg_match('#^a(\d)+$#', $id, $match)) {
                    $products_id .= sprintf('{a%s}%s', $match[1], $val['crc']);
                }
            }
        }
        return $products_id;
    }

    /**
     * retrieve attrText array from props. attrText = [attr_id1 => Text1, attr_id2 => Text2]
     * @param $props string xml props
     * @return array|null
     */
    public static function getAttrText($props)
    {
        if (!empty($props)) {
            $propData = \Yii::$app->get('PropsHelper')::XmlToParams($props);
            if (is_array($propData['AttrText'] ?? null)) {
                $res = [];
                foreach($propData['AttrText'] as $id=>$val) {
                    if (preg_match('#^a(\d+)$#', $id, $match)) {
                        $res[$match[1]] = $val['value'];
                    }
                }
                return $res;
            }
        }
    }

    /**
     * retrieve attrText array from cart
     * @param $cart \common\classes\shopping_cart
     * @param $uprid
     * @return array|null
     */
    public static function getAttrTextCart($cart, $uprid)
    {
        if ($cart instanceof \common\classes\shopping_cart) {
            $product = $cart->get_products($uprid);
            return self::getAttrText($product[0]['props'] ?? null);
        }
    }
}