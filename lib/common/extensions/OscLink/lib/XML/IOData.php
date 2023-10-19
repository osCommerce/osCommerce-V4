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

namespace OscLink\XML;


#[\AllowDynamicProperties]
class IOData
{
    public $exportPK = [];
    public $meta = [];
    public $data = [];

    static public function fromArray($dataArray)
    {
        $obj = new self();
        $obj->data = $dataArray;
        return $obj;
    }

    public function getAttachmentList()
    {
        $attachmentObjects = array();
        foreach ( $this->data as $dataValue ) {
            if ( !is_object($dataValue) ) continue;
            if ( $dataValue instanceof IOAttachment ){
                $attachmentObjects[] = $dataValue;
            }elseif ( $dataValue instanceof IOData ){
                $attachmentObjects = array_merge($attachmentObjects, $dataValue->getAttachmentList());
            }
        }
        return $attachmentObjects;
    }

    public function isImportable($processModel)
    {
        $isValid = true;
        /*foreach ( $this->data as $name=>$value ) {
            if ( is_object($value) && $value instanceof IOMap ) {
                $value->isMapValid();
                echo '<pre>'; var_dump($value); echo '</pre>'; die;
            }
            //IOMap
        }*/
        return $isValid;
    }

    static public function serializeToSimpleXml(IOData $data, $recordTag, $rootElement=null){
        if ( is_object($recordTag) && $recordTag instanceof \SimpleXMLElement) {
            $element = $recordTag;
        }else {
            $element = new \SimpleXMLElement("<?xml version=\"1.0\" encoding=\"UTF-8\"?><{$recordTag} />");
        }
        foreach ($data->data as $name=>$val){
            if ( false && in_array($name, $data->exportPK) ) {
                if ( is_object($val) && $val instanceof Complex ) {
                    $element->addAttribute('type',Helper::getClassShortName(get_class($val)));
                    $val->serializeTo($element);
                    //$element->addAttribute($name, $val);
                }else {
                    $element->addAttribute($name, $val);
                }
                continue;
            }
            if ( is_array($val) ) {
                //echo '<pre>'; var_dump($name, $val); echo '</pre>'; die;
            }elseif ( is_object($val) && $val instanceof IOData ) {
                $rootTag = '';
                if ( strpos($val->meta['xmlCollection'],'>')!==false ) {
                    list($rootTag, $recordTag) = explode('>',$val->meta['xmlCollection'],2);
                }else{
                    $recordTag = $val->meta['xmlCollection'];
                }
                $childElement = $element->addChild($rootTag);
                foreach ( $val->data as $nestedData ) {
                    $childCollectionElement = $childElement->addChild($recordTag);
                    static::serializeToSimpleXml($nestedData, $childCollectionElement, $childElement);
                }
            }elseif ( is_object($val) ) {
                if ( $val instanceof Complex ) {
                    $complexObject = $element->addChild($name);
                    $complexObject->addAttribute('type',Helper::getClassShortName(get_class($val)));
                    $val->serializeTo($complexObject);
                }
            }else{
                if ( is_null($val) ) {
                    $element->addChild($name)->addAttribute('type','nil');
                }else {
                    //$element->{$name} = $val;
                    $valStripedInvalid = preg_replace('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', '', $val);
                    $element->{$name} = $valStripedInvalid;
                }
            }
        }
        if ( $rootElement && is_object($rootElement) ) {
            return null;
        }
        return $element;
    }

}