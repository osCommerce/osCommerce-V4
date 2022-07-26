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
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Xls as PhpOfficeXls;

class XLS implements ReaderInterface
{
    protected $file_header;
    private $file_start_pointer = 0;
    private $file_header_rows = 10;
    private $file_data_start_pointer;
    private $currow=1; //excel style

    public $filename;

    protected $file_handle;
    protected $maxColumn; //A,B,C ...
    protected $maxColumnIndex; //1,2,3 ...
    protected $maxRow; //1,2,3 ....
    protected $maxColumnToCheck = 'CZ';

    protected $reader;


    protected function openFile()
    {
        $this->file_header = null;
        $this->file_start_pointer = 0;
/*        $this->file_handle = fopen($this->filename,'r');
        if ( !$this->file_handle ) {
            throw new Exception('Can\'t open file', 20);
        }
*/
        $reader = new PhpOfficeXls();
        $worksheetNames = $reader->listWorksheetNames($this->filename);
        if (is_array($worksheetNames) && count($worksheetNames)>0) {
          $sheetname = $worksheetNames[0];
        }
        $reader->setLoadSheetsOnly($sheetname);
        $reader->setReadDataOnly(true);
        if (is_object($this->filterSubset)) {
          $reader->setReadFilter($this->filterSubset);
        }
        $spreadsheet = $reader->load($this->filename);

        $this->file_handle = $spreadsheet->getActiveSheet();
        $worksheet  = $this->file_handle;
        $this->maxRow = $worksheet->getHighestRow(); // e.g. 10
        $this->maxColumn = $worksheet->getHighestColumn(); // e.g 'F'
        $this->maxColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($this->maxColumn); // e.g. 5
        $this->maxColumn++;

        $this->readColumns();
        
        $this->currow = $this->file_data_start_pointer+1;
//$tmp = $this;         unset($tmp->file_handle);        echo "#### <PRE>" .print_r($tmp, 1) ."</PRE>";        die;
          }
    
    public function currentPosition()
    {
        return $this->currow;
    }

    public function setDataPosition($position)
    {
        $this->currow = $position;
    }

    public static function cellRange($from, $to) {
      $ret = [];
      if ($from > $to) {
        $x = $from;
        $from = $to;
        $to = $x;
      }
      $to++;
      while ($from !== $to) {
        $ret[] = $from++;
      }
      return $ret;
    }

    public function readColumns()
    {
        if ( is_null($this->file_header) ) {
          if (!$this->file_handle) {
            $this->filterSubset = new XlsReadFilter($this->file_start_pointer, $this->file_header_rows, self::cellRange('A', $this->maxColumnToCheck));
            $this->openFile();
          }
          $data_start = 0;
          if ( is_null($this->file_header) ) {
            $this->file_header = false;
            if ($this->without_header) {
                $tmp = $this->file_handle->rangeToArray(
                    'A' . (1) . ':' . ($this->maxColumn) . (1),     // The worksheet range that we want to retrieve
                    '',        // Value that should be returned for empty cells
                    TRUE,        // Should formulas be calculated (the equivalent of getCalculatedValue() for each cell)
                    TRUE,        // Should values be formatted (the equivalent of getFormattedValue() for each cell)
                    false         // Should the array be indexed by cell row and cell column
                );
            } else {
  // skip table header
  // until max filled row with unique values
              $u = array();
              for ($row = 1; $row <= $this->maxRow; ++$row) {
                $tmp = $this->file_handle->rangeToArray(
                    'A' . ($row) . ':' . ($this->maxColumn) . ($row),     // The worksheet range that we want to retrieve
                    '',        // Value that should be returned for empty cells
                    TRUE,        // Should formulas be calculated (the equivalent of getCalculatedValue() for each cell)
                    TRUE,        // Should values be formatted (the equivalent of getFormattedValue() for each cell)
                    False         // Should the array be indexed by cell row and cell column
                );
                $u = array_unique($tmp[0]);
                if (ceil(0.8*$this->maxColumnIndex) <= count($u)) {
                  $this->maxColumnIndex = count($u);
                  $data_start = $row;
                  break;
                }
  //echo  'A' . ($row) . ':' . ($this->maxColumn) . ($row) . " \$u #### <PRE>" .print_r($u , 1) ."</PRE>";
              }
              $u = array_filter($u,'strlen'); /// strip empty and Null headers
              $this->file_header = $u;
            }
          }

          if ( is_null($this->file_data_start_pointer) || $this->file_data_start_pointer<$data_start ) {
              $this->file_data_start_pointer = $data_start;
          }
          
          //reopen full file (without row limits)
          if (is_object($this->filterSubset)) {
            unset($this->filterSubset);
            $this->openFile();
          }
        }        
        return array_values($this->file_header);
    }

    public function getProgress()
    {

        $percentDone = min(100,($this->currow/$this->maxRow)*100);
        return number_format(  $percentDone,1,'.','');
    }

    public function read()
    {
        if ( !$this->file_handle ) {
            $this->openFile();
        }
        $data = false;
        if ($this->currow <= $this->maxRow) {

          $data = $this->file_handle->rangeToArray(
                    'A' . ($this->currow) . ':' . ($this->maxColumn) . ($this->currow),     // The worksheet range that we want to retrieve
                    NULL,        // Value that should be returned for empty cells
                    TRUE,        // Should formulas be calculated (the equivalent of getCalculatedValue() for each cell)
                    TRUE,        // Should values be formatted (the equivalent of getFormattedValue() for each cell)
                    False         // Should the array be indexed by cell row and cell column
                );
          $data = $data[0];
          $this->currow++;

          if ( is_array($data) ) {
              if ( is_array($this->file_header) ) {
                  $named_data = array();
                  foreach( $this->file_header as $idx=>$key_name ) {
                      $named_data[$key_name] = isset($data[$idx])?$data[$idx]:null;
                  }
                  return $named_data;
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

