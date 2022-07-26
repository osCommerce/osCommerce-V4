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

class XMLWriter
{
    protected $firstOutput = true;

    protected $fileName = '';
    protected $fileHandle;
    protected $rootTag = '';

    protected $xslt;
    /**
     * IOProject constructor.
     * @param string $fileName
     */
    public function __construct($fileName)
    {
        $this->fileName = $fileName;
    }

    public function applyXSLT($filename)
    {
        $this->xslt = new \XSLTProcessor();
        $this->xslt->importStylesheet(simplexml_load_file($filename));
    }

    public function exportBegin($header)
    {
        $this->fileHandle = fopen($this->fileName,'w+');
        fwrite($this->fileHandle,'<?xml version="1.0" encoding="UTF-8"?'.'>'."\n");
        fwrite($this->fileHandle,"<data>"."\n");
        if (is_string($header) && !empty($header)) {
            $header = ['type'=>$header,'projectCode'=>IOCore::get()->getProjectCode()];
        }
        if ( is_array($header) && count($header)>0 ) {
            $header['projectCode'] = IOCore::get()->getProjectCode();
            $xmlHeader = $this->serializeToXML(IOData::fromArray($header),'Header');
            fwrite($this->fileHandle, $xmlHeader);
        }
    }

    public function exportData($data)
    {
        $recordTag = '';
        if ( isset($data->meta['xmlCollection']) ) {
            $rootTag = '';
            if ( strpos($data->meta['xmlCollection'],'>')!==false ) {
                list($rootTag, $recordTag) = explode('>',$data->meta['xmlCollection'],2);
            }else{
                $recordTag = $data->meta['xmlCollection'];
            }
            if ( $this->firstOutput ) {
                $this->rootTag = $rootTag;
                fwrite($this->fileHandle,"<{$this->rootTag}>"."\n");
            }
        }
        if ( $recordTag ) {
            $xml = $this->serializeToXML($data, $recordTag);
            fwrite($this->fileHandle,$xml);
        }
        $this->firstOutput = false;
    }

    protected function serializeToXML(IOData $data, $recordTag, $rootElement=null)
    {
        $element = IOData::serializeToSimpleXml($data, $recordTag, $rootElement);
        $xml = '';
        if ( is_object($element) ) {
            if ( $this->xslt ) {
                if ( true ) {
                    $xml = $this->xslt->transformToXML($element);
                }else {
                    $doc = $this->xslt->transformToDoc($element);
                    //echo '<pre>'; var_dump(json_encode(simplexml_import_dom($doc))); echo '</pre>';
                    $xml = $doc->saveXML($doc);
                }
            }else{
                $xml = $element->asXML();
            }
            $headPos = strpos($xml, "?>\n");
            if ($headPos !== false) {
                $xml = substr($xml, $headPos + 3);
            } else {
                $headPos = strpos($xml, "?>");
                if ($headPos !== false) {
                    $xml = substr($xml, $headPos + 2);
                }
            }
        }
        return $xml;
    }

    public function exportEnd()
    {
        if ( !empty($this->rootTag) ) {
            fwrite($this->fileHandle, "</{$this->rootTag}>" . "\n");
        }
        fwrite($this->fileHandle,"</data>"."\n");
        fclose($this->fileHandle);
        if ( is_file($this->fileName) ) {
            @chmod($this->fileName,0666);
        }
    }
}