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
  public static function getWriter($filename, $toBrowser = true, $customer_id = 0, $fileType = '') {
    $writer = null;
    if ($fileType == '' && defined('EXPORT_DEFAULT_FILE_TYPE') && in_array(EXPORT_DEFAULT_FILE_TYPE, ['CSV', 'XLSX']) ) {
      $fileType = EXPORT_DEFAULT_FILE_TYPE;
    }
    switch ($fileType) {
      case 'CSV':
        $filename .= '.csv';
        $writer = new \backend\models\EP\Formatter\CSV('write', array(), $filename);
        break;
      case 'XLSX':
      default:
            $filename .= '.xlsx';
            $writer = \Box\Spout\Writer\WriterFactory::create(\Box\Spout\Common\Type::XLSX);
            $defaultStyle = (new \Box\Spout\Writer\Style\StyleBuilder())
                    ->setFontName('Arial')
                    ->setFontSize(12)
                    ->build();
            $writer->setDefaultRowStyle($defaultStyle);
            if ($toBrowser) {
              $writer->openToBrowser($filename);
            } else {
              $writer->openToFile($filename);
            }

        break;
    }
    return $writer;
  }

}
