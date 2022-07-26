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
use yii\base\BaseObject;

class CSV extends BaseObject implements ReaderInterface
{
    const MAX_LINE_LENGTH = 1000000;

    public $column_separator = "auto";
    public $column_enclosure = '"';
    public $data_escape = "\0";
    public $line_separator = "\r\n";
    public $input_encoding = 'auto';
    public $without_header = false;

    public $filename;

    protected $file_handle;

    protected $file_header;
    protected $use_config = array(
        'column_separator' => "auto",
        'column_enclosure' => '"',
        'data_escape' => "\0",
        'line_separator' => "auto",
        'input_encoding' => 'auto',
    );
    private $file_start_pointer = 0;
    private $file_data_start_pointer;
    protected $file_size = 0;

    public function __set($name, $value)
    {
        try {
            parent::__set($name, $value);
        }catch (\Exception $ex){}
    }

    protected function openFile()
    {
        $this->file_header = null;
        $this->file_start_pointer = 0;

        ini_set("auto_detect_line_endings", true);
        if ( preg_match('/\.gz$/',$this->filename) && function_exists('gzopen') ) {
            // {{ get ungzipped length
            $fh = fopen($this->filename, 'rb');
            fseek($fh, -4, SEEK_END);
            $arrLength = unpack("C*",fread($fh, 4));
            fclose($fh);
            $this->file_size = ($arrLength[4] << 24) | ($arrLength[3] << 16) + ($arrLength[1] << 8) + $arrLength[0];
            // }}
            $this->file_handle = @gzopen($this->filename, 'rb');
        }else{
            $this->file_size = @filesize($this->filename);
            $this->file_handle = @fopen($this->filename, 'r');
        }
        if ( !$this->file_handle ) {
            throw new Exception('Can\'t open file', 20);
        }
        foreach ($this->use_config as $param=>$value) {
            if ( isset($this->$param) ) {
                $this->use_config[$param] = $this->$param;
            }
        }

        $this->detectEncoding();
        if ( $this->use_config['input_encoding']!='auto' && $this->use_config['input_encoding']!='UTF-8' ) {
            stream_filter_append($this->file_handle, 'convert.iconv.' . $this->use_config['input_encoding'] . '/UTF-8');
        }
        $this->detectSeparator();

        $this->readColumns();

        if ( !is_null($this->file_data_start_pointer) ) {
            //fseek($this->file_handle, $this->file_data_start_pointer, SEEK_SET);
        }
    }

    public function currentPosition()
    {
        if ($this->file_handle) {
            return ftell($this->file_handle);
        }
        return 0;
    }

    public function getProgress()
    {
        $filePosition = $this->currentPosition();
        if ( stripos($this->use_config['input_encoding'],'UTF-16')===0 ) {
            $filePosition *= 2;
        }elseif ( stripos($this->use_config['input_encoding'],'UTF-32')===0 ) {
            $filePosition *= 4;
        }
        $percentDone = min(100,($filePosition/max(1,$this->file_size))*100);
        return number_format(  $percentDone,1,'.','');
    }

    public function setDataPosition($position)
    {
        $this->file_data_start_pointer = $position;
        if ($this->file_handle) {
            fseek($this->file_handle, $position, SEEK_SET);
        }
    }

    public function readColumns()
    {
        if ( is_null($this->file_header) ) {
            if (!$this->file_handle) {
                $this->openFile();
            }
            fseek($this->file_handle, $this->file_start_pointer, SEEK_SET);

            $this->file_header = false;
            if ($this->without_header) {
                $this->file_header = array_keys($this->read());
                fseek($this->file_handle, $this->file_start_pointer, SEEK_SET);
            }else{
                $this->file_header = $this->read();
            }
            $data_start = $this->currentPosition();

            if ( is_null($this->file_data_start_pointer) || $this->file_data_start_pointer<$data_start ) {
                $this->file_data_start_pointer = $data_start;
            }
        }

        return $this->file_header;
    }

    public function read()
    {
        if ( !$this->file_handle ) {
            $this->openFile();
        }

        $data = fgetcsv(
            $this->file_handle,
            self::MAX_LINE_LENGTH,
            $this->use_config['column_separator'],
            $this->use_config['column_enclosure'],
            $this->use_config['data_escape']
        );

        if ( is_array($data) ) {
            if ($this->use_config['input_encoding'] == 'auto') {
                $data = $this->deEncode($data);
            }

            $data = array_map(array($this, 'filterText'), $data);

            if ( is_array($this->file_header) ) {
                $named_data = array();
                foreach( $this->file_header as $idx=>$key_name ) {
                    $named_data[$key_name] = isset($data[$idx])?$data[$idx]:null;
                }
                return $named_data;
            }
        }

        return $data;
    }

    protected function filterText($string)
    {
        if ( strpos($string,'\t')!==false ) {
            $string = str_replace('\t', "\t", $string);
        }
        if ( strpos($string,'\r')!==false ) {
            $string = str_replace('\r', "\r", $string);
        }
        if ( strpos($string,'\n')!==false ) {
            $string = str_replace('\n', "\n", $string);
        }
        return $string;
    }

    protected function detectEncoding()
    {
        // check UTF encoding
        rewind($this->file_handle);
        $utfMap = $this->getUtfBomMap();
        if ( isset($utfMap[ $this->use_config['input_encoding'] ]) ) {
            $this->file_start_pointer = strlen($utfMap[ $this->use_config['input_encoding'] ]);
        }

        if ( $this->use_config['input_encoding']=='auto' ) {
            $readLength = array_reduce($utfMap, function($initial, $signature){
                return max($initial, strlen($signature));
            },0);
            $checkSignature = fread($this->file_handle,$readLength);

            rewind($this->file_handle);
            foreach( $utfMap as $utfEncoding=>$utfSignature ) {
                if ( substr($checkSignature,0, strlen($utfSignature))==$utfSignature ) {
                    $this->use_config['input_encoding'] = $utfEncoding;
                    $this->file_start_pointer = strlen($utfSignature);
                    break;
                }
            }
        }

        fseek($this->file_handle, $this->file_start_pointer, SEEK_SET);
    }

    protected function detectSeparator()
    {
        if ( $this->use_config['column_separator']=='auto' ) {
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

            if (count($results) > 0) {
                $results = array_keys($results, max($results));
                $this->use_config['column_separator'] = $results[0];
            } else {
                $this->use_config['column_separator'] = $delimiters[0];
            }
        }
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

    private function deEncode($dataArray)
    {
        static $preferredEncodingOrder = false;
        if ( !is_array($preferredEncodingOrder) ) {
            $encoding_list = mb_list_encodings();
            $encoding_list = preg_grep('/(-Mobile|auto)/i', $encoding_list, PREG_GREP_INVERT);
            $prefer3_order = 'UTF,ISO,WIN,CP8';
            usort($encoding_list, function ($a, $b) use ($prefer3_order) {
                $cmp_res = 0;
                $a_idx = strpos($prefer3_order, strtoupper(substr($a, 0, 3)));
                $b_idx = strpos($prefer3_order, strtoupper(substr($b, 0, 3)));
                if ($a_idx !== false && $b_idx !== false) {
                    $cmp_res = $a_idx - $b_idx;
                } elseif ($a_idx !== false) {
                    $cmp_res = -1;
                } elseif ($b_idx !== false) {
                    $cmp_res = 1;
                }
                return $cmp_res;
            });
            $preferredEncodingOrder = $encoding_list;
        }

        foreach ( $dataArray as $key=>$file_data ) {
            if (!empty($file_data) && !is_numeric($file_data)) {
                $cellEncoding = mb_detect_encoding($file_data, $preferredEncodingOrder, true);
                if ( $cellEncoding!='UTF-8' ) {
                    $dataArray[$key] = mb_convert_encoding($file_data, 'UTF-8', $cellEncoding);
                }
            }
        }
        return $dataArray;
    }

}