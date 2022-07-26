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

namespace backend\models\EP\Reader;

use backend\models\EP\Exception;

class XML implements ReaderInterface
{
    protected $file_header;
    private $file_start_pointer = 0;
    private $file_data_start_pointer;

    public $filename;

    protected $file_handle;

    public $rootTag = 'data';
    public $rowsTag = 'records';
    public $rowTag = 'record';

    public $importData = 'array';

    var $parser;
    protected $currentTagStack = [];
    protected $detectedIndexed = [];
    protected $lastClosedTag = '';
    protected $cdataCollect = false;

    protected $collectedPathData = false;
    protected $cutCountFromCollectPath = 0;

    protected $readOutQueue = [];

    // {{ SimpleXML
    /**
     * @var \SimpleXMLElement
     */
    protected $root;

    /**
     * @var \SimpleXMLElement
     */
    protected $currentNode;
    protected $isNodesCollect = true;
    protected $collectPath = []; // collect only selected "xpath"
    protected $currentXpathArray = [];
    // }} SimpleXML

    protected function openFile()
    {
        $this->file_header = null;
        $this->file_start_pointer = 0;
        $this->file_handle = @fopen($this->filename,'r');
        if ( !$this->file_handle ) {
            throw new Exception('Can\'t open file', 20);
        }

        $this->currentTagStack = [];
        $this->parser = xml_parser_create('utf-8');
        xml_set_object($this->parser, $this);
        xml_parser_set_option($this->parser,XML_OPTION_CASE_FOLDING,0);
        xml_parser_set_option($this->parser,XML_OPTION_SKIP_WHITE,1);

        if ( $this->importData=='SimpleXml' ) {
            xml_set_element_handler($this->parser, "sx_tag_open", "sx_tag_close");
            xml_set_character_data_handler($this->parser, "sx_cdata");

            if ( empty($this->rootTag) ) {
                $collectPath = '/'.$this->rowsTag.'/'.$this->rowTag;
            }else {
                $collectPath = '/'.$this->rootTag.'/'.$this->rowsTag.'/'.$this->rowTag;
            }
            $this->collectPath[$collectPath] = $collectPath;
        }else {
            xml_set_element_handler($this->parser, "tag_open", "tag_close");
            xml_set_character_data_handler($this->parser, "cdata");

            if ( empty($this->rootTag) ) {
                $this->collectPath[$this->rowsTag.'.'.$this->rowTag] = true;
            }else {
                $this->collectPath[$this->rootTag . '.' . $this->rowsTag . '.' . $this->rowTag] = true;
            }
        }

        $this->readColumns();

    }

    public function readColumns()
    {
        return [];
    }

    public function read()
    {
        if (!$this->file_handle) {
            $this->openFile();
        }

        if ( $this->importData=='SimpleXml' ) {
            if (count($this->readOutQueue) > 0) {
                while ($item = array_shift($this->readOutQueue)) {
                    if (count($this->collectPath) > 0) {
                        if (isset($this->collectPath[$item['xpath']])) {
                            return $item['node'];
                        }
                    } else {
                        return $item['node'];
                    }
                }
            }
        }else {
            if (count($this->readOutQueue) > 0) {
                $data = array_shift($this->readOutQueue);
                if (count($this->readOutQueue) == 0) {
                    unset($this->readOutQueue);
                    $this->readOutQueue = [];
                }
                return $data;
            } else {
                unset($this->readOutQueue);
                $this->readOutQueue = [];
            }
        }

        while ( $data = fread($this->file_handle,4096*1) ){
            if ( !xml_parse($this->parser, $data, feof($this->file_handle)) ){
                $fmt = new \yii\i18n\Formatter();
                $memusage = $fmt->asShortSize(memory_get_usage(true),3);
                $mempeakusage = $fmt->asShortSize(memory_get_peak_usage(true),3);
                throw new Exception("XML Error: memusage {$memusage} mempeakusage {$mempeakusage} ".xml_error_string(xml_get_error_code($this->parser))." at line ".xml_get_current_line_number($this->parser)."");
            }
            if ( $this->importData=='SimpleXml' ) {
                if (count($this->readOutQueue) > 0) {
                    while ($item = array_shift($this->readOutQueue)) {
                        if (count($this->collectPath) > 0) {
                            if (isset($this->collectPath[$item['xpath']])) {
                                return $item['node'];
                            }
                        } else {
                            return $item['node'];
                        }
                    }
                }
            }else {
                if (count($this->readOutQueue) > 0) {
                    $data = array_shift($this->readOutQueue);
                    if (count($this->readOutQueue) == 0) {
                        unset($this->readOutQueue);
                        $this->readOutQueue = [];
                    }
                    return $data;
                }
            }
        }
        return false;
    }

    public function currentPosition()
    {
        if ($this->file_handle) {
            return ftell($this->file_handle);
        }
        return 0;
    }

    public function setDataPosition($position)
    {
        // TODO: Implement setDataPosition() method.
    }

    public function getProgress()
    {
        $filePosition = $this->currentPosition();
        if ( $this->file_handle ) {
            $fstat = fstat($this->file_handle);
            $percentDone = min(100,($filePosition/max(1,$fstat['size']))*100);
        }else{
            $percentDone = min(100,($filePosition/filesize($this->filename))*100);
        }
        return number_format(  $percentDone,1,'.','');
    }

    function tag_open($parser, $tag, $attributes)
    {
        // {{ indexed arrays
        if ( $this->lastClosedTag==$tag ) {
            $indexedPath = substr(implode('.', $this->currentTagStack).'.'.$tag, $this->cutCountFromCollectPath);
            if ( !isset($this->detectedIndexed[$indexedPath]) ) {
                $this->detectedIndexed[$indexedPath] = 0;
                foreach (preg_grep('/^'.preg_quote($indexedPath).'/',array_keys((array)$this->collectedPathData)) as $renameKey){
                    $this->collectedPathData[str_replace($indexedPath,$indexedPath.'.'.$this->detectedIndexed[$indexedPath],$renameKey)] = $this->collectedPathData[$renameKey];
                    unset($this->collectedPathData[$renameKey]);
                }
            }
            $this->detectedIndexed[$indexedPath]++;
        }
        // }} indexed arrays
        $this->currentTagStack[] = $tag;
        $startedPath = implode('.', $this->currentTagStack);
        if (isset($this->collectPath[$startedPath])){
            unset($this->collectedPathData);
            $this->collectedPathData = [];
            $this->cutCountFromCollectPath = strlen($startedPath)+1;
        }
        $this->cdataCollect = '';
        if ( count($attributes)>0 && is_array($this->collectedPathData) ) {
            foreach( $attributes as $attributeName=>$attributeValue ) {
                $this->collectedPathData[substr($startedPath.'.@'.$attributeName,$this->cutCountFromCollectPath)] = $attributeValue;
            }
        }
    }
    function cdata($parser, $cdata)
    {
        if ( !empty($cdata) && $this->cdataCollect!==false ) {
            $this->cdataCollect .= $cdata;
        }
    }
    function tag_close($parser, $tag)
    {
        $this->lastClosedTag = $tag;
        $closePath = implode('.', $this->currentTagStack);
        if ( $this->cdataCollect!==false ) {
            if ( is_array($this->collectedPathData) ) {
                $dataKey = substr($closePath,$this->cutCountFromCollectPath);
                foreach( $this->detectedIndexed as $indexedKey=>$indexCounter ){
                    if ( strpos($dataKey,$indexedKey)!==0 ) continue;
                    $dataKey = $indexedKey.'.'.$indexCounter.substr($dataKey,strlen($indexedKey));
                }
                $this->collectedPathData[$dataKey] = $this->cdataCollect;
            }
        }
        if ( isset($this->collectPath[$closePath]) ) {
            $this->lastClosedTag = '';
            $this->detectedIndexed = [];
            $this->readOutQueue[] = \backend\models\EP\ArrayTransform::convertFlatToMultiDimensional($this->collectedPathData);
            unset($this->collectedPathData);
            $this->collectedPathData = false;
        }
        unset($closePath);
        array_pop($this->currentTagStack);
        $this->cdataCollect = false;
    }

    protected function sx_tag_open($parser, $tag, $attributes)
    {
        $this->currentXpathArray[] = $tag;

        if ( !$this->isNodesCollect ) return;

        if ( is_null($this->root) ) {
            $element = new \SimpleXMLElement('<'.$tag.'></'.$tag.'>');
            $element->registerXPathNamespace('xsi','http://www.w3.org/2001/XMLSchema-instance');
            $this->root = $element;
            $this->currentNode = $element;
        }else{
            $this->currentNode = $this->currentNode->addChild($tag);
            if ( is_array($attributes) && count($attributes)>0 ) {
                foreach ($attributes as $attrName=>$attrValue) {
                    $ns = null;
                    //if ( strpos($attrName,':')!==false ) {
                    //list($ns, /*$attrName*/) = explode(':',$attrName,2);
                    //}
                    //echo '<pre>'; var_dump($attrName, $attrValue, $ns); echo '</pre>';
                    $this->currentNode->addAttribute($attrName, $attrValue, $ns);
                }
            }
        }
        $this->cdataCollect = '';
    }

    protected function sx_cdata($parser, $cdata)
    {
        if ( !$this->isNodesCollect ) return;
        if ( /*(!empty($cdata) && trim($cdata)!=='') &&*/ $this->cdataCollect!==false ) {
            $this->cdataCollect .= $cdata;
        }
    }

    protected function sx_tag_close($parser, $tag){
        $closeTagPath = $this->currentXpathArray;
        $closeTagXPath = '/'.implode('/',$closeTagPath);

        // put collected CDATA
        if ( $this->cdataCollect!==false ) {
            //$this->currentNode[0] = $this->cdataCollect; //??
            $this->currentNode[0] = trim($this->cdataCollect);
            $this->cdataCollect = false;
        }

        $closedNode = $this->currentNode;
        // shift current to parent
        $parentNodeArray = $this->currentNode->xpath('..');
        if ( count($parentNodeArray)==1 ) {
            $this->currentNode = $parentNodeArray[0];
        }

        // fill readOutQueue
        if ( !empty($this->collectPath) && isset($this->collectPath[$closeTagXPath]) ) {
            $this->readOutQueue[] = [
                'xpath' => '/'.implode('/',$this->currentXpathArray),
                'node' => $closedNode,
            ];
            $closedDomNode = dom_import_simplexml($closedNode);
            $closedDomNode->parentNode->removeChild($closedDomNode);
        }else
            if ( empty($this->collectPath) && count($closeTagPath)==2 ) {
                // collect every 1st level node from root
                $this->readOutQueue[] = [
                    'xpath' => '/'.implode('/',$this->currentXpathArray),
                    'node' => $closedNode,
                ];
                $closedDomNode = dom_import_simplexml($closedNode);
                $closedDomNode->parentNode->removeChild($closedDomNode);
            }

        array_pop($this->currentXpathArray);
    }
}