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

require('includes/application_top.php');

if (defined('DEVELOPMENT_MODE')) {
    if (DEVELOPMENT_MODE == 'True') {
        defined('YII_DEBUG') or define('YII_DEBUG', true);
        defined('YII_ENV') or define('YII_ENV', 'dev');
    } elseif (DEVELOPMENT_MODE == 'False') {
        defined('YII_DEBUG') or define('YII_DEBUG', false);
        defined('YII_ENV') or define('YII_ENV', 'prod');
    }
}

require(__DIR__ . '/../lib/backend/web/index.php');

require_once ('includes/application_bottom.php');