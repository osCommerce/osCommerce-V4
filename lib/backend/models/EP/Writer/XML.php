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

namespace backend\models\EP\Writer;


use backend\models\EP\ArrayTransform;
use backend\models\EP\Exception;
use DOMDocument;
use DOMElement;
use DOMText;
use SimpleXMLElement;
use yii\base\Arrayable;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;

class XML implements WriterInterface
{
    public $filename;

    protected $file_handle;
    protected $_first_write = true;

    protected $useTraversableAsArray = true;

    public $Header = [];
    public $header = [];
    public $rootTag = 'data';
    public $rowsTag = 'records';
    public $rowTag = 'record';

    protected $columns = [];
    protected $columnsMulti = [];

    public function setColumns(array $columns)
    {
        $this->columns = $columns;
    }

    protected function cutSelectedColumns($data)
    {
        $newData = [];
        $flatData = ArrayTransform::convertMultiDimensionalToFlat($data);
        foreach (array_keys($this->columns) as $needPath ) {
            if ( strpos($needPath,'*')!==false ) {
                $regExp = str_replace('.','\.',$needPath);
                $regExp = str_replace('*','[^\.]+',$regExp);
                foreach (preg_grep('#'.$regExp.'#',array_keys($flatData)) as $needKey){
                    $newData[$needKey] = $flatData[$needKey];
                }
            }else{
                if ( array_key_exists($needPath, $flatData) ) {
                    $newData[$needPath] = $flatData[$needPath];
                }
            }
        }
        $newData = ArrayTransform::convertFlatToMultiDimensional($newData);
        return $newData;
    }

    public function write(array $writeData)
    {
        if (substr(strval(key($writeData)),0,1)==':') {
            if (isset($writeData[':xmlConfig'])) {
                if ( is_array($writeData[':xmlConfig']) ) {
                    foreach ($writeData[':xmlConfig'] as $confKey=>$confValue){
                        if ( isset($this->{$confKey}) ) {
                            $this->{$confKey} = $confValue;
                        }
                    }
                }
            }
            if (isset($writeData[':feed_data'])) {
                $writeData = $writeData[':feed_data'];
            }else{
                return;
            }
        }

        if ( $this->_first_write ) {
            $this->_first_write = false;

            if ( strpos($this->filename,'php://')===false ) {
                if ( !is_dir(dirname($this->filename)) ) {
                    try{
                        \yii\helpers\FileHelper::createDirectory(dirname($this->filename), 0777, true);
                    }catch(\yii\base\Exception $ex){

                    }
                }
            }
            $this->file_handle = @fopen($this->filename,'w');
            if ( !$this->file_handle ) {
                throw new Exception('Can\'t open file', 21);
            }
            fwrite($this->file_handle,'<?xml version="1.0" encoding="UTF-8"?>'."\n");
            fwrite($this->file_handle,'<'.$this->rootTag.'>'."\n");
            if ( !empty($this->header) ) {
                fwrite($this->file_handle, $this->array2xml($this->header,'header') . "\n");
            }else
            if ( !empty($this->Header) ) {
                fwrite($this->file_handle, $this->array2xml($this->Header,'Header') . "\n");
            }
            fwrite($this->file_handle,'<'.$this->rowsTag.'>'."\n");
        }

        if (substr(strval(key($writeData)),0,1)==':') {
            if (isset($writeData[':feed_data'])) {
                $writeData = $writeData[':feed_data'];
            }else{
                return;
            }
        }
        if ( count($writeData)==1 && is_object($writeData[0]) && $writeData[0] instanceof DOMDocument ) {
            fwrite($this->file_handle, preg_replace('#<\?xml.*\?>#', '',$writeData[0]->saveXML()) . "\n");
        }elseif ( count($writeData)==1 && is_object($writeData[0]) && $writeData[0] instanceof SimpleXMLElement ) {
            $xml = $writeData[0]->saveXML();
            $headPos = strpos($xml, "?>\n");
            if ($headPos !== false) {
                $xml = substr($xml, $headPos + 3);
            } else {
                $headPos = strpos($xml, "?>");
                if ($headPos !== false) {
                    $xml = substr($xml, $headPos + 2);
                }
            }
            fwrite($this->file_handle, $xml /*. "\n"*/);
        }else {
            $writeData = $this->cutSelectedColumns($writeData);
            fwrite($this->file_handle, $this->array2xml($writeData) . "\n");
        }
        fflush($this->file_handle);
    }

    public function close()
    {
        if ( $this->file_handle ) {
            fwrite($this->file_handle, '</' . $this->rowsTag . '>' . "\n");
            fwrite($this->file_handle, '</' . $this->rootTag . '>' . "\n");
            if (strpos($this->filename, 'php://') === false) {
                fclose($this->file_handle);
                $this->file_handle = null;
            }
        }
    }

    protected function array2xml($array, $tag='')
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $root = new DOMElement(empty($tag)?$this->rowTag:$tag);
        $dom->appendChild($root);
        $this->buildXml($root, $array);
        return $dom->saveXML($root);

    }

    /**
     * @param DOMElement $element
     * @param mixed $data
     */
    protected function buildXml($element, $data, $numericKeyFormat='item%s')
    {
        if (is_array($data) ||
            ($data instanceof \Traversable && $this->useTraversableAsArray && !$data instanceof Arrayable)
        ) {
            foreach ($data as $name => $value) {
                $itemTag = $name;
                if ( is_int($name) ) {
                    $itemTag = sprintf($numericKeyFormat,$name);
                }
                $itemTag = preg_replace('/[^\w|\d|:|_|\.|-]/i','_', $itemTag);

                if (is_int($name) && is_object($value)) {
                    $this->buildXml($element, $value);
                } elseif (is_array($value) || is_object($value)) {
                    if ( empty($itemTag) ) continue;
                    $child = new DOMElement($itemTag);
                    $element->appendChild($child);
                    if ( substr($name,-1)=='s' && strlen($name)>2 ) {
                        $this->buildXml($child, $value, substr($name,0,-1));
                    }else {
                        $this->buildXml($child, $value);
                    }
                } else {
                    if ( empty($itemTag) ) continue;
                    $child = new DOMElement($itemTag);
                    $element->appendChild($child);
                    $child->appendChild(new DOMText((string) $value));
                }
            }
        } elseif (is_object($data)) {
            $child = new DOMElement(StringHelper::basename(get_class($data)));
            $element->appendChild($child);
            if ($data instanceof Arrayable) {
                $this->buildXml($child, $data->toArray());
            } else {
                $array = [];
                foreach ($data as $name => $value) {
                    $array[$name] = $value;
                }
                $this->buildXml($child, $array);
            }
        } else {
            $element->appendChild(new DOMText((string) $data));
        }
    }

}