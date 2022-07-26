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

if ( isset($_SERVER['SCRIPT_FILENAME']) ) $_SERVER['SCRIPT_FILENAME'] = str_replace('/images/index.php','/index.php', $_SERVER['SCRIPT_FILENAME']);
if ( isset($_SERVER['PHP_SELF']) ) $_SERVER['PHP_SELF'] = str_replace('/images/index.php','/index.php', $_SERVER['PHP_SELF']);
if ( isset($_SERVER['SCRIPT_NAME']) ) $_SERVER['SCRIPT_NAME'] = str_replace('/images/index.php','/index.php', $_SERVER['SCRIPT_NAME']);

define('IS_NESTED_IMAGE_HANDLER',1);
chdir('..');
include(__DIR__.'/../index.php');
