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

namespace backend\models\EP\Formatter;

use backend\models\EP;

class CSV implements FormatterInterface {
  protected  $config = array(
    'column_separator' => "\t",
    'line_separator' => "\r\n",
    'save_file' => false,
  );
  private $_first_write = true;
  private $filename = '';
  private $file_handle;
  protected $remap_read = [];
  protected $_read_header;

  function __construct($mode, $config, $filename)
  {
    if ( is_array($config) ) {
      $this->config = array_merge($this->config, $config);
    }
    $this->filename = $filename;
  }

  public function addRow($data_array)
  {
    if (is_array($data_array)) {
      foreach ($data_array as $k => $v) {
      // value  is array with formatting - use only 0 element - value
        if (is_array($v)) {
          $data_array[$k] = isset($v[0])?$v[0]:'';
        }
      }
    }
    $this->write_array($data_array);
  }

  public function write_array($data_array)
  {
    if ( $this->_first_write ) {
      if ( empty($this->config['save_file']) ) {
        $mime_type = 'application/vnd.ms-excel';

        header('Content-Type: ' . $mime_type);
        header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Content-Disposition: attachment; filename="' . $this->filename . '"');

        if (preg_match('@MSIE ([0-9].[0-9]{1,2})@', $_SERVER['HTTP_USER_AGENT'], $log_version)) {
          header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
          header('Pragma: public');
        } else {
          header('Pragma: no-cache');
        }
        $this->file_handle = fopen('php://output','w');
      } else {
        $this->file_handle = fopen($this->filename, 'w');
      }
      // write BOM
      fwrite($this->file_handle,chr(0xff) . chr(0xfe));
    }

    $data_array = array_map(array($this,'quoteText'),array_values($data_array));
    $line = implode($this->config['column_separator'],$data_array).$this->config['line_separator'];
    fwrite($this->file_handle,mb_convert_encoding($line, 'UTF-16LE', 'UTF-8'));

    fflush($this->file_handle);
    $this->_first_write = false;
  }

  public function close(){
    if ( $this->file_handle ) {
      fclose($this->file_handle);
      $this->file_handle = false;
    }
  }

  public function setReadRemapArray($data_array){
    $this->remap_read = $data_array;
  }

  protected function detect_separator(){
    $file_position = ftell($this->file_handle);
    $test_line = fgets($this->file_handle);
    $delimiters = array(",", "\t", ";", "|", ":");

    $results = array();
    foreach ($delimiters as $delimiter) {
      $fields = explode($delimiter, $test_line);
      if (count($fields) > 1) {
        $results[$delimiter] = count($fields);
      }
    }

    fseek($this->file_handle, $file_position);

    if ( count($results)>0 ) {
      $results = array_keys($results, max($results));
      return $results[0];
    }else{
      return $delimiters[0];
    }
  }

  public function read_array(){
    if( !$this->file_handle ) {
      $this->file_handle = fopen($this->filename,'r');
      $this->config['column_separator'] = $this->detect_separator();

      $bom = fread($this->file_handle, 2);
      rewind($this->file_handle);
      if ($bom === chr(0xff).chr(0xfe) || $bom === chr(0xfe).chr(0xff)) {
        // UTF16 Byte Order Mark present
        $encoding = 'UTF-16';
      } else {
        $file_sample = fread($this->file_handle, filesize($this->filename));
        rewind($this->file_handle);
        $encoding = mb_detect_encoding($file_sample, 'auto', true);
      }
      if ($encoding) {
        stream_filter_append($this->file_handle, 'convert.iconv.'.$encoding.'/UTF-8');
      } else {
        $encoding = 'CP850';
        stream_filter_append($this->file_handle, 'convert.iconv.'.$encoding.'/UTF-8');
      }
      $this->_read_header = fgetcsv($this->file_handle,262144, $this->config['column_separator']);

      $this->_read_header = array_flip($this->_read_header);
    }
    $file_data = fgetcsv($this->file_handle,262144, $this->config['column_separator']);

    if ( !is_array($file_data) ) {
      return false;
    }

    $data = array();
    foreach( $this->remap_read as $db_key=>$file_key ) {
      if ( !isset($this->_read_header[$file_key]) ) continue;
      $data_idx = $this->_read_header[$file_key];
      $data[$db_key] = $this->restoreText($file_data[$data_idx]);
    }

    return $data;
  }

  public function getHeaders(){
    $this->read_array();
    return array_flip($this->_read_header);
  }

  private function restoreText($string){
    $string = str_replace( '\t', "\t", $string );
    return $string;
  }
  private function quoteText($string){
    if ( empty($string) || is_numeric($string) ) return $string;

    if ( strpos($string,$this->config['column_separator'])!==false || strpos($string,'"')!==false || strpos($string,"\n")!==false || strpos($string,"\r")!==false ) $string = '"'.str_replace('"','""',$string).'"';
    $string = str_replace( "\t", '\t', $string );

    return $string;
  }

}