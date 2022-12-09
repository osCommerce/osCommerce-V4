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


use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use yii\base\BaseObject;

class XLSX extends BaseObject implements WriterInterface
{
    public $filename;
    public $writer_type = "Xlsx";

    protected $_first_write = true;

    public $header_line = true;

    protected $spreadsheet;
    protected $columns = [];
    protected $descriptions = [];
    protected $row_counter = 0;

    protected function openOutputFile()
    {
        $this->spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

//        $sheet = $this->spreadsheet->getActiveSheet();
//        $headers = array_values($headers_map);
//        for ($i = 0, $l = sizeof($headers); $i < $l; $i++) {
//            $sheet->setCellValueByColumnAndRow($i + 1, 1, $headers[$i]);
//            $sheet->getColumnDimension(chr(ord('A')+$i))->setAutoSize(true);
//            if ($headers[$i]=='Discount Amount') {
//                $sheet->getStyle(chr(ord('A') + $i))
//                    ->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_00);
//            }else{
//                $sheet->getStyle(chr(ord('A') + $i))
//                    ->getNumberFormat()
//                    ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
//            }
//        }

    }


    public function setDescription(array $descriptions) {
      if (!empty($descriptions)) {
        $this->descriptions = $descriptions;
      }
    }

    protected function writeHeader()
    {
        $headers = array_values($this->columns);
        $sheet = $this->spreadsheet->getActiveSheet();

        $row = 1;
        if (!empty($this->descriptions['top'])) {
          $sheet->mergeCells('A1:' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(sizeof($headers)) . '1');
          $sheet->setCellValueByColumnAndRow(1, $row, $this->descriptions['top']);
          $linesCnt = count(explode("\n", $this->descriptions['top']));
          if ($linesCnt>1) {
            $sheet->getStyle('A1:A1')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
            $sheet->getRowDimension('1')->setRowHeight(min(($linesCnt+1)*12.75, 409));
          }
          $row++;
          $this->row_counter++;
          $row++;
          $this->row_counter++;
        }
        for ($i = 0, $l = sizeof($headers); $i < $l; $i++) {
            $sheet->setCellValueByColumnAndRow($i + 1, $row, $headers[$i]);
            $sheet->getColumnDimension(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i+1))->setAutoSize(true);
        }
        $styleArray = [
                'font' => [
                    'bold' => true,
                ],
 /*               'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                ],
                'borders' => [
                    'top' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_GRADIENT_LINEAR,
                    'rotation' => 90,
                    'startColor' => [
                        'argb' => 'FFA0A0A0',
                    ],
                    'endColor' => [
                        'argb' => 'FFFFFFFF',
                    ],
                ],*/
            ];
        $sheet->getStyle('A' . $row . ':' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(sizeof($headers)) . $row)->applyFromArray($styleArray);
        $sheet->getStyle('A:' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(sizeof($headers)))
            ->getNumberFormat()
            ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
        $this->row_counter++;
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

        $sheet = $this->spreadsheet->getActiveSheet();
        /**
         * @var $sheet Worksheet
         */
        foreach (array_keys($this->columns) as $idx=>$columnName) {
            if ( isset($writeData[$columnName]) ) {
                if ( true ) {
                    $sheet->getCellByColumnAndRow($idx + 1, ($this->row_counter + 1))
                        ->setValueExplicit($writeData[$columnName], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                }else {
                    $sheet->setCellValueByColumnAndRow($idx + 1, ($this->row_counter + 1), $writeData[$columnName]);
                }
            }else{
                $sheet->setCellValueByColumnAndRow($idx + 1, ($this->row_counter + 1), '');
            }
        }
        $this->row_counter++;

        $this->_first_write = false;
    }

    public function close()
    {
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($this->spreadsheet, $this->writer_type);
        $writer->save($this->filename);
    }

}