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

spl_autoload_register('osclink_autoload');

function osclink_autoload($class)
{
    if (preg_match('/^OscLink\\\\(.+)$/', $class, $match)) {
        $filename = __DIR__ .'/'. str_replace('\\', '/', $match[1]) . '.php';
        if (file_exists($filename)) {
            require_once ($filename);
            return true;
        } else {
            \Yii::warning("Class $class not found: $filename");
        }
    }
}

 