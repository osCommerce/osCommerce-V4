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

class XMLtoArrayParser extends XMLtoSimpleParser
{
    public function read()
    {
        $xmlElement = parent::read();
        if ( is_object($xmlElement) && $xmlElement instanceof \SimpleXMLElement ) {
            return $this->simpleXmlElementToArray($xmlElement);
        }
        return $xmlElement;
    }

    protected function simpleXmlElementToArray( \SimpleXMLElement $element )
    {
        $result = [];

        foreach ($element->attributes() as $attributeName=>$attributeValue){
            $result['@'.$attributeName] = (string)$attributeValue;
        }

        foreach ($element->children() as $child){
            /**
             * @var $child \SimpleXMLElement
             */
            $nodeName = $child->getName();
            if ($child->count()>0) {
                if ( isset($result[$nodeName]) ) {
                    if (\yii\helpers\ArrayHelper::isAssociative($result[$nodeName])) {
                        $result[$nodeName] = [$result[$nodeName]];
                    }
                    $result[$nodeName][] = $this->simpleXmlElementToArray($child);
                }else {
                    $result[$nodeName] = $this->simpleXmlElementToArray($child);
                }
            }else {
                $childValue = (string)$child;
                if ( $child->attributes() ) {
                    foreach ($child->attributes() as $childAttrName=>$childAttrValue) {
                        if ( $childAttrName=='nil' ) $childValue = null;
                    }
                }
                $result[$nodeName] = $childValue;
            }
        }
        return $result;
    }

}