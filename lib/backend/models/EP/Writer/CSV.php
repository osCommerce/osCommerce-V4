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

use backend\models\EP\Exception;
use yii\base\BaseObject;

class CSV extends BaseObject implements WriterInterface
{
    public $column_separator = "\t";
    public $line_separator = "\r\n";
    public $output_encoding = 'UTF-16LE';
    public $utf_bom = true;
    public $quote_all = false;
    public $header_line = true;

    public $filename;

    protected $file_handle;
    protected $_first_write = true;
    
    protected $columns = [];

    public function __set($name, $value)
    {
        try {
            parent::__set($name, $value);
        }catch (\Exception $ex){}
    }

    protected function openOutputFile()
    {
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

        // write BOM
        $encoding_bom = $this->getOutputEncodingBOM();
        if ( $encoding_bom ) {
            fwrite($this->file_handle, $encoding_bom);
        }
    }

    protected function writeHeader()
    {
        $header = array_values($this->columns);
        $data = array_map(array($this,'quoteText'), $header);
        $line = implode($this->column_separator,$data).$this->line_separator;
        if ( $this->output_encoding=='UTF-8' ) {
            fwrite($this->file_handle, $line);
        }else {
            fwrite($this->file_handle, mb_convert_encoding($line, $this->output_encoding, 'UTF-8'));
        }
        $this->_first_write = false;
    }

    public function setColumns(array $columns)
    {
        $this->columns = $columns;

        if ( $this->_first_write ) {
            $this->openOutputFile();

            if ( $this->header_line!==false ) {
                $this->writeHeader();
            }
        }
    }
    
    public function write(array $writeData)
    {
        if (substr(strval(key($writeData)),0,1)==':') {
            if (isset($writeData[':feed_data'])) {
                $writeData = $writeData[':feed_data'];
            }else{
                return;
            }
        }
        if ( $this->_first_write ) {
            $this->openOutputFile();

            if ( $this->header_line!==false ) {
                $this->writeHeader();
            }
        }
        
        $data = array();

        foreach (array_keys($this->columns) as $columnName) {
            if ( isset($writeData[$columnName]) ) {
                $data[$columnName] = $this->quoteText($writeData[$columnName]);
            }else{
                $data[$columnName] = '';
            }
        }
        
        $line = implode($this->column_separator,$data).$this->line_separator;
        if ( $this->output_encoding=='UTF-8' ) {
            fwrite($this->file_handle, $line);
        }else {
            fwrite($this->file_handle, mb_convert_encoding($line, $this->output_encoding, 'UTF-8'));
        }

        fflush($this->file_handle);
        $this->_first_write = false;
    }

    public function close()
    {
        if ( $this->file_handle && strpos($this->filename,'php://')===false) {
            fclose($this->file_handle);
        }
        $this->file_handle = null;
    }

    protected function quoteText($string)
    {
        if ( (empty($string) || is_numeric($string)) && !$this->quote_all ) return $string;

        if ( $this->quote_all || strpos($string,$this->column_separator)!==false || strpos($string,'"')!==false || strpos($string,"\n")!==false || strpos($string,"\r")!==false ) {
            $string = '"'.str_replace('"','""',$string).'"';
        }
        $string = str_replace( "\t", '\t', $string );

        return $string;
    }

    private function getUtfBomMap()
    {
        $UTF_BOM = array(
            'UTF-32BE' => chr(0x00) . chr(0x00) . chr(0xFE) . chr(0xFF),
            'UTF-32LE' => chr(0xFF) . chr(0xFE) . chr(0x00) . chr(0x00),
            'UTF-16BE' => chr(0xFE) . chr(0xFF),
            'UTF-16LE' => chr(0xFF) . chr(0xFE),
            'UTF-8' => chr(0xEF) . chr(0xBB) . chr(0xBF),
        );
        return $UTF_BOM;
    }

    private function getOutputEncodingBOM()
    {
        if ( !$this->utf_bom ) return '';
        $utfMap = $this->getUtfBomMap();
        if ( isset($utfMap[$this->output_encoding]) ) {
            return $utfMap[$this->output_encoding];
        }
        return '';
    }

}