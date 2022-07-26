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

namespace backend\controllers;

use Yii;

/**
 *
 */
class DownloadController extends Sceleton {

    public function actionIndex() {
        $filename = Yii::$app->request->get('filename');
        $filename = \common\helpers\Output::mb_basename($filename);
        if (file_exists(DIR_FS_DOWNLOAD . $filename)) {
            header("Expires: Mon, 26 Nov 1962 00:00:00 GMT");
            header("Last-Modified: " . gmdate("D,d M Y H:i:s") . " GMT");
            header("Cache-Control: no-cache, must-revalidate");
            header("Pragma: no-cache");
            header("Content-Type: Application/octet-stream");
            header("Content-disposition: attachment; filename=" . $filename);
            
            readfile(DIR_FS_DOWNLOAD . $filename);
            exit();
        }
    }

}
