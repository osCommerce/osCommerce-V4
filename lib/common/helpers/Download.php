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

namespace common\helpers;

use common\models\OrdersProductsDownload;

class Download {

    /**
     * Returns a random name, 16 to 20 characters long
     * There are more than 10^28 combinations
     * The directory is "hidden", i.e. starts with '.'
     * @return string
     */
    public static function random_name() {
        $letters = 'abcdefghijklmnopqrstuvwxyz';
        $dirname = '.';
        $length = floor(tep_rand(16, 20));
        for ($i = 1; $i <= $length; $i++) {
            $q = floor(tep_rand(1, 26));
            $dirname .= $letters[$q];
        }
        return $dirname;
    }

    /**
     * Unlinks all subdirectories and files in $dir
     * Works only on one subdir level, will not recurse
     * @param type $dir
     */
    public static function unlink_temp_dir($dir) {
        $h1 = opendir($dir);
        while ($subdir = readdir($h1)) {
            // Ignore non directories
            if (!is_dir($dir . $subdir))
                continue;
            // Ignore . and .. and CVS
            if ($subdir == '.' || $subdir == '..' || $subdir == 'CVS')
                continue;
            // Loop and unlink files in subdirectory
            $h2 = opendir($dir . $subdir);
            while ($file = readdir($h2)) {
                if ($file == '.' || $file == '..')
                    continue;
                @unlink($dir . $subdir . '/' . $file);
            }
            closedir($h2);
            @rmdir($dir . $subdir);
        }
        closedir($h1);
    }

    public static function updateOrderedFile($product_id, $new_filename)
    {
        \Yii::$app->getDb()->createCommand(
            "UPDATE " . \common\models\OrdersProductsDownload::tableName() . " opd " .
            "INNER JOIN " . \common\models\OrdersProducts::tableName() . " op ON opd.orders_id=op.orders_id AND opd.orders_products_id=op.orders_products_id " .
            "SET opd.orders_products_filename=:new_filename " .
            "WHERE op.products_id=:product_id",
            [':product_id' => (int)$product_id, ':new_filename' => (string)$new_filename]
        )->execute();
    }

}
