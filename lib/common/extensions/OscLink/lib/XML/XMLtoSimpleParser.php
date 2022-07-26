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

class XMLtoSimpleParser {

    protected $filename = '';
    protected $file_handle;

    protected $parser;

    /**
     * @var \SimpleXMLElement
     */
    protected $root;

    /**
     * @var \SimpleXMLElement
     */
    protected $currentNode;
    protected $cdataCollect = false;

    protected $isNodesCollect = true;

    protected $collectPath = []; // collect only selected "xpath"
    protected $readOutQueue = []; // collect elements here

    protected $currentXpathArray = [];

    protected function initParser()
    {
        $this->parser = xml_parser_create('utf-8');
        xml_set_object($this->parser, $this);
        xml_parser_set_option($this->parser,XML_OPTION_CASE_FOLDING,0);
        xml_parser_set_option($this->parser,XML_OPTION_SKIP_WHITE,1);

        xml_set_element_handler($this->parser, "tag_open", "tag_close");
        xml_set_character_data_handler($this->parser, "cdata");

        $this->root = null;
        $this->currentNode = null;
        $this->cdataCollect = false;
    }

    public function setCollectPath($collectPath)
    {
        $this->collectPath[$collectPath] = $collectPath;
    }

    public function parseFile($filename)
    {
        $this->initParser();

        $this->filename = $filename;

        $this->file_handle = @fopen($this->filename,'r');
        if ( !$this->file_handle ) {
            throw new \Exception('Can\'t open file', 20);
        }

    }

    public function parseString($string)
    {
        $this->initParser();

        if (!xml_parse($this->parser, $string, true))
        {
            throw new \Exception("XML Error: ".xml_error_string(xml_get_error_code($this->parser))." at line ".xml_get_current_line_number($this->parser)."");
        }
    }

    /**
     * @return bool|\SimpleXMLElement
     * @throws \Exception
     */
    public function read()
    {
        // fill out from file
        if ( $this->file_handle && count($this->readOutQueue)==0 ) {
            while ($data = fread($this->file_handle, 4096 * 1))
            {
                if (!xml_parse($this->parser, $data, feof($this->file_handle)))
                {
                    throw new \Exception("XML Error: ".xml_error_string(xml_get_error_code($this->parser))." at line ".xml_get_current_line_number($this->parser)."");
                }
            }
            if (feof($this->file_handle)){
                fclose($this->file_handle);
                $this->file_handle = false;
            }
        }

        if ( count($this->readOutQueue)>0 ) {
            while($item = array_shift($this->readOutQueue)) {
                if ( count($this->collectPath)>0 ) {
                    if (isset($this->collectPath[$item['xpath']])){
                        return $item['node'];
                    }
                }else {
                    return $item['node'];
                }
            }
        }

        return false;
    }

    protected function tag_open($parser, $tag, $attributes)
    {
        $this->currentXpathArray[] = $tag;

        if ( !$this->isNodesCollect ) return;

        if ( is_null($this->root) ) {
            $element = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><'.$tag.'></'.$tag.'>');
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

    protected function cdata($parser, $cdata)
    {
        if ( !$this->isNodesCollect ) return;
        if ( /*(!empty($cdata) && trim($cdata)!=='') &&*/ $this->cdataCollect!==false ) {
            $this->cdataCollect .= $cdata;
        }
    }

    protected function tag_close($parser, $tag){
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
