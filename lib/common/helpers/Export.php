<?php

/*
 * This file is part of osCommerce ecommerce platform.
 * osCommerce the ecommerce
 * 
 * @link https://www.oscommerce.com
 * @copyright Copyright (c) 2005 Holbi Group Ltd
 * 
 * Released under the GNU General Public License
 * For the full copyright and license information, please view the LICENSE.TXT file that was distributed with this source code.
 */

namespace common\helpers;

class Export {

    private static $xlsLib = 'spout';
    private static $toFile;
    private static $toBrowser;
    private static $sheet;
  
  /**
   * create and init default export writer object
   * 2do config default file type per front/back, frontend, preferable by customer/admin
   * $writer= getWriter('filename without extension'); $writer->addRow($header); .... $writer->close();
   * @param string $filename
   * @param bool $toBrowser
   * @param int $customer_id
   * @param string $fileType
   * @return object|null
   */
    public static function getWriter($filename, $toBrowser = true, $customer_id = 0, $fileType = '')
    {
        $writer = null;
        if ($fileType == '' && defined('EXPORT_DEFAULT_FILE_TYPE') && in_array(EXPORT_DEFAULT_FILE_TYPE, ['CSV', 'XLSX'])) {
            $fileType = EXPORT_DEFAULT_FILE_TYPE;
        }
        self::$toFile = $filename;
        self::$toFile .= strpos(self::$toFile, '.xlsx') === false ? '.xlsx' : '';
        self::$toBrowser = $toBrowser;

        switch ($fileType) {
            case 'CSV':
                $filename .= '.csv';
                $writer = new \backend\models\EP\Formatter\CSV('write', array(), $filename);
                break;
            case 'XLSX':
            default:
                switch (self::$xlsLib) {
                    case 'spout':
                        $filename .= '.xlsx';
                        $writer = \Box\Spout\Writer\Common\Creator\WriterFactory::createFromType(\Box\Spout\Common\Type::XLSX);
                        $defaultStyle = (new \Box\Spout\Writer\Common\Creator\Style\StyleBuilder())
                            ->setFontName('Arial')
                            ->setFontSize(12)
                            ->build();
                        $writer->setDefaultRowStyle($defaultStyle);
                        if ($toBrowser) {
                            $writer->openToBrowser($filename);
                        } else {
                            $writer->openToFile($filename);
                        }
                    case 'phpoffice':
                    {
                        self::$sheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
                        $writer = self::$sheet->getActiveSheet();
                        break;
                    }
                }
                break;
        }
        return $writer;
    }

    public static function addRowToWriter($writer, $rowArray)
    {
        static $line = 1;
        switch (self::getWriterType($writer))
        {
            case 'csv':
                $writer->addRow($rowArray);
                break;
            case 'spout':
                $writer->addRow( \Box\Spout\Writer\Common\Creator\WriterEntityFactory::createRowFromArray($rowArray) );
                break;
            case 'phpoffice':
                $writer->fromArray([$rowArray], NULL, 'A' . $line++);
                break;
        }
    }

    public static function finishWriter($writer)
    {
        switch (self::getWriterType($writer))
        {
            case 'csv':
                $writer->close();
                break;
            case 'spout':
                $writer->close();
                break;
            case 'phpoffice':
                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx(self::$sheet);
                if (self::$toBrowser) {
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="' . self::$toFile . '"');
                    header('Cache-Control: max-age=0');
                    $writer->save('php://output');
                } else {
                    $writer->save(self::$toFile);
                }
                break;
        }
    }

    public static function usePhpOffice()
    {
        self::$xlsLib = 'phpoffice';
    }

    private static function getWriterType($writer)
    {
        if ($writer instanceof \backend\models\EP\Formatter\CSV) {
            return 'csv';
        } else {
            return self::$xlsLib;
        }
    }



}
