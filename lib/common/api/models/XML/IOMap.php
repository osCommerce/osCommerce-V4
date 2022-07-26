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

namespace common\api\models\XML;


class IOMap extends Complex
{
    public $internalId;
    public $externalId;

    public function serializeTo(\SimpleXMLElement $parent)
    {
        if ( IOCore::get()->isLocalProject() ) {
            $parent->addAttribute('internalId', $this->value);
            $externalId = IOCore::get()->getAttributeMapper()->externalId($this);
            if (is_numeric($externalId)) {
                $parent->addAttribute('externalId', $externalId);
            }
        }else{
            $parent->addAttribute('externalId', $this->value);
            $externalId = IOCore::get()->getAttributeMapper()->externalId($this);
            if (is_numeric($externalId)) {
                $parent->addAttribute('internalId', $externalId);
            }
        }
    }

    static public function restoreFrom(\SimpleXMLElement $node, $obj)
    {
        if ( !is_object($obj) || !($obj instanceof Complex) ) {
            $obj = new self();
        }
        $objProperties = \Yii::getObjectVars($obj);
        foreach ($node->attributes() as $attrName=>$attrValue){
            if ( array_key_exists($attrName,$objProperties) ) {
                $obj->{$attrName} = strval($attrValue);
            }
        }

        if ( empty($obj->internalId) && empty($obj->externalId) && trim($node)!='' ) {
            // imported XML without type : <tag>value</tag>
            $obj->internalId = trim($node);
        }

        if ( $obj->internalId ){
            $obj->internalId = intval($obj->internalId);
        }
        if ( $obj->externalId ){
            $obj->externalId = intval($obj->externalId);
        }

        if ( !IOCore::get()->isLocalProject() ) {
            $externalId = $obj->externalId;
            $obj->externalId = $obj->internalId;
            $obj->internalId = $externalId;
        }
        //if ( is_numeric($map->internalId) ) {
        $obj->value = $obj->internalId;
        //}else{

        //}
        return $obj;
    }

    public function isMapValid()
    {

    }

    public function toImportModel()
    {
        if ( !IOCore::get()->isLocalProject() && empty($this->internalId) && !empty($this->externalId) ) {
            $mappedId = IOCore::get()->getAttributeMapper()->internalId($this);
            if ( $mappedId ) {
                $this->internalId = $mappedId;
                $this->value = $mappedId;
            }
        }
        return $this->value;
    }

    public function afterImportModel($value)
    {
        if ( !empty($this->externalId) && !IOCore::get()->isLocalProject() ) {
            IOCore::get()->getAttributeMapper()->mapIds($this, $value, $this->externalId);
        }
        //echo '<pre>afterImportModel '; var_dump($this->table, $this->attribute, $value); echo '</pre>';
    }
}