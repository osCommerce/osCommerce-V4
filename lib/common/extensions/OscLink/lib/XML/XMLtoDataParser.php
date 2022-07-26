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


use function PHPSTORM_META\type;
use yii\base\InvalidConfigException;

class XMLtoDataParser extends XMLtoSimpleParser
{
    protected $configureMap = [];
    public function setConfigureMap($map)
    {
        $this->configureMap = array();
        $configData = isset($map['Data'])?$map['Data']:$map;
        foreach ($configData as $activeModel=>$configSet){
            $this->configureMap = $configSet;
            $this->configureMap['class'] = $activeModel;
            break;
        }

    }

    /**
     * @return bool|IOData
     */
    public function read()
    {
        $xmlElement = parent::read();
        if ( $xmlElement===false ) return false;
        //echo '<pre style="background-color: #0b62a9">'; var_dump($xmlElement); echo '</pre>';
        if ( is_object($xmlElement) && $xmlElement instanceof \SimpleXMLElement ) {
            $data = $this->makeIoData($xmlElement);
            //echo '<pre>$data '; var_dump($data); echo '</pre>';
            //return $this->simpleXmlElementToArray($xmlElement);
        }else{
            $data = true;
        }
        return $data;

    }

    public function makeIoData(\SimpleXMLElement $XMLElement)
    {
        return $this->simpleXmlElementToData($XMLElement, $this->configureMap );
    }

    protected function simpleXmlElementToData( \SimpleXMLElement $XMLElement, $levelConfigure )
    {
        $data = new IOData();
        $data->meta = $levelConfigure;
        $data->data = array();
        foreach ($XMLElement->attributes() as $attributeName=>$attributeValue){
            $result['@'.$attributeName] = (string)$attributeValue;
        }

        $propertiesRemap = (isset($data->meta['properties']) && is_array($data->meta['properties']))?$data->meta['properties']:array();
        $renameProperties = array();
        foreach ($propertiesRemap as $tableAttribute=>$xmlAttribute) {
            if( is_string($xmlAttribute) ) {
                $renameProperties[$xmlAttribute] = $tableAttribute;
            }elseif (is_array($xmlAttribute) && !empty($xmlAttribute['rename']) ) {
                $renameProperties[$xmlAttribute['rename']] = $tableAttribute;
            }
        }
        $collections = array();

        if ( isset($data->meta['withRelated']) && is_array($data->meta['withRelated']) ) {
            foreach ($data->meta['withRelated'] as $modelCollection=>$collectionConfig){
                list($collectionXmlTag, $collectionXmlTagItem) = explode('>',$collectionConfig['xmlCollection'],2);
                $collections[$collectionXmlTag] = array_merge(array(
                    'itemTag' => $collectionXmlTagItem,
                    'modelCollection' => $modelCollection,
                ),$collectionConfig);
            }
        }

        foreach ($XMLElement->children() as $child){
            /**
             * @var $child \SimpleXMLElement
             */
            $nodeName = $child->getName();
            if ( isset($collections[$nodeName]) ) {
                $collectionConfig = $collections[$nodeName];
                if ( !empty($collectionConfig['modelCollection']) ) $nodeName = $collectionConfig['modelCollection'];

                if (!is_object($data->data[$nodeName]??null)) {
                    $data->data[$nodeName] = new IODataRelated();
                    $data->data[$nodeName]->meta = $collectionConfig;
                }

                if ( isset($child->{$collectionConfig['itemTag']}) ) {
                    $childrenCollection = $child->{$collectionConfig['itemTag']};
                    foreach ( $childrenCollection as $childrenElement ) {
                        $data->data[$nodeName]->data[] = $this->simpleXmlElementToData($childrenElement, $collectionConfig);
                    }
                }
                //die;
            }else {
                $childValue = (string)$child;
                if ($child->attributes()->count() > 0) {
                    foreach ($child->attributes() as $childAttrName => $childAttrValue) {
                        if ($childAttrName == 'type') {
                            $childType = (string)$childAttrValue;
                            if ($childType == 'nil') {
                                $childValue = null;
                            } else {
                                /*if ( isset($data->meta['properties'][$nodeName]) && is_array($data->meta['properties'][$nodeName]) ) {

                                }else{
                                    $propMeta = [
                                        'class' => $childType,
                                        'table' => $attribute,
                                        'attribute' => $nodeName,
                                    ];
                                }
                                echo '<pre>'; var_dump($data->meta); echo '</pre>'; die;*/
                                $IoObject = null;
                                if ( isset($data->meta['properties'][$nodeName]) && is_array($data->meta['properties'][$nodeName]) && isset($data->meta['properties'][$nodeName]['class']) ) {
                                    $IoObject = IOCore::createObject($data->meta['properties'][$nodeName]);
                                }
                                $childValue = IOCore::constructObjectInstance([$childType,'restoreFrom'],[$child,$IoObject]);

                            }
                            break;
                        }
                        if ($childAttrName == 'nil') $childValue = null;
                    }
                }
                if (isset($renameProperties[$nodeName])) {
                    $nodeName = $renameProperties[$nodeName];
                }
                if ( !is_object($childValue) && isset($data->meta['properties'][$nodeName]) && is_array($data->meta['properties'][$nodeName]) && isset($data->meta['properties'][$nodeName]['class']) ){
                    $childType = $data->meta['properties'][$nodeName]['class'];
                    $IoObject = IOCore::createObject($data->meta['properties'][$nodeName]);
                    if ( is_object($IoObject) ) {
                        $childValue = IOCore::constructObjectInstance([$childType, 'restoreFrom'], [$child, $IoObject]);
                    }
                }
                $data->data[$nodeName] = $childValue;
            }
        }

        return $data;
    }

}