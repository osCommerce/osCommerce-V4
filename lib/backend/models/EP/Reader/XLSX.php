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
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use yii\base\BaseObject; //extends BaseObject

class XLSX extends BaseObject implements ReaderInterface
{
    protected $file_header;
    private $file_start_pointer = 0;
    private $file_header_rows = 15; ///search headers in first file_header_rows rows (max number of filled in cells)
    private $file_data_start_pointer;
//    private $currow=1; //excel style ->key()
    protected $currow; 

    public $filename = 'ep.xls';
    public $sheetIndex = 0;
    public $sheet_name = '';
    public $without_header = false;

    protected $file_handle;
    //protected $maxColumn; //A,B,C ...
    protected $maxColumnIndex = 0; //1,2,3 ...
    protected $maxRow; //1,2,3 ....
    //protected $maxColumnToCheck = 'CZ';
    protected $reader;
    
    public function __set($name, $value)
    {
      try {
        parent::__set($name, $value);
      } catch (\Exception $ex) {
        \Yii::warning(" #### " . print_r($ex->getMessage(), true), 'TLDEBUG-EP');
      }
    }

    protected function openFile()
    {
        $this->file_header = null;
        $this->file_start_pointer = 0;
/*        $this->file_handle = fopen($this->filename,'r');
        if ( !$this->file_handle ) {
            throw new Exception('Can\'t open file', 20);
        }
*/
        $this->reader = ReaderEntityFactory::createXLSXReader();
        $this->reader->open($this->filename);

        $cnt = 1;
        foreach ($this->reader->getSheetIterator() as $sheet) {
          $this->file_handle = $sheet->getRowIterator();
          if ((empty($this->sheet_name) && empty($this->sheetIndex)) ||
              (!empty($this->sheet_name) && $this->sheet_name==$sheet->getName() ) ||
              (!empty($this->sheet_name) && $this->sheet_name== $cnt . '_' . $sheet->getName() ) ||
              (!empty($this->sheetIndex) && $this->sheetIndex==$cnt )
              ) {
            break; // no need to read more sheets
          }
          $cnt++;
        }
        $this->readColumns();
        $this->currow = $this->file_data_start_pointer;
        $this->maxRow = 10000; //dummy as as SPOUT don't read all the file Required for progress only

    }
    public function currentPosition()
    {
        return $this->file_handle->key();
    }

    public function setDataPosition($position)
    {
        $this->currow = $position;
    }

    /**
     * @return [sheetName => [
                        'columns' => $fileColumns,
                    ] ];
     */
    public function readSheets()
    {
      $ret = [];
      $sheetsCnt = 0;
      $this->reader =   ReaderEntityFactory::createXLSXReader();
      $this->reader->open($this->filename);

      foreach ($this->reader->getSheetIterator() as $sheet) {
        $this->file_header = null;
        $this->file_start_pointer = 0;
        $this->maxColumnIndex = 0;
        $this->file_handle = $sheet->getRowIterator();
        $headers = $this->readColumns();
        $sheetsCnt ++;
        $name = $sheet->getName();
        if (!empty($headers)) {
          $ret[$sheetsCnt . '_' . $name] = ['columns' => $headers];
        }
      }
      
      return $ret;
    }

    public function readColumns()
    {
      if ( is_null($this->file_header) ) {
        if (!$this->file_handle) {
            $this->openFile();
        }
        $data_start = 0;
        if ( is_null($this->file_header) ) {
          $this->file_handle->rewind();
          $this->file_header = false;
          if ($this->without_header) {
              $this->file_header = array_keys($this->read());
              $this->file_handle->rewind();
          } else {
            // skip table header
            // until max filled row with unique values
            $u = array();
            $_file_header = [];
            for ($row = 1; $row <= $this->file_header_rows; ++$row) {
              $tmp = $this->read();
              if (is_array($tmp)) {
                $u = array_unique($tmp);
                if (ceil(0.8*count($u)) > $this->maxColumnIndex) {
                  $this->maxColumnIndex = count($u);
                  $data_start = $this->currentPosition() - 1;
                  $_file_header = array_filter($u, 'strlen'); /// strip empty and Null headers
                }
              }
            }
            if(!is_array($_file_header)) {
              $this->file_header = [];
            } else {
              $this->file_header = $_file_header;
            }
          }
        }

        if ( is_null($this->file_data_start_pointer) || $this->file_data_start_pointer<$data_start ) {
            $this->file_data_start_pointer = $data_start;
            $this->file_handle->rewind();
            for ($i=0; $i<$data_start; $i++) {
              $this->file_handle->next();
            }
        }
      }

      return array_values($this->file_header);
    }


    public function getProgress()
    {
        $percentDone = min(100,($this->currentPosition()/$this->maxRow)*100);
        return number_format(  $percentDone,1,'.','');
    }


    public function read()
    {
      if ( !$this->file_handle ) {
          $this->openFile();
      }

      $data = false;
      if ($this->file_handle->valid()) {
        $data = $this->file_handle->current();
        $this->file_handle->next();
        if ( is_object($data) && $data instanceof \Box\Spout\Common\Entity\Row) {
            $data = $data->toArray();
        }
        if (is_array($data)) {
          foreach ($data as $k => $v) {
            if ( is_a($v, 'DateTime')) {
              if ($v->format('H:i')=='00:00') {
                //time not specifiede return date only
                $data[$k] = $v->format(\common\helpers\Date::DATE_FORMAT);
              } else {
                $data[$k] = $v->format(\common\helpers\Date::DATE_TIME_FORMAT);
              }
            }
          }

          if ( is_array($this->file_header) ) {
            $named_data = array();
            foreach( $this->file_header as $idx=>$key_name ) {
              $named_data[$key_name] = isset($data[$idx])?$data[$idx]:null;
            }
            $data = $named_data;
          }
        }
      }

      return $data;
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