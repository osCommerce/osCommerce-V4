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

$path_info = !empty($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : (!empty($_SERVER['ORIG_PATH_INFO']) ? $_SERVER['ORIG_PATH_INFO'] : '');
if ($path_info != "" && strpos($path_info, '/pathinfotest') !== false) {
    echo filter_var($path_info, FILTER_SANITIZE_STRING);
    die();
}

$rootPath = './../';

ini_set("display_errors", 0);

defined('VERSION_EXT') or define('VERSION_EXT', 'v.4.14 build 63493');
defined('VERSION_PHP_RQ') or define('VERSION_PHP_RQ', '7.4.0');
defined('VERSION_PHP_REC') or define('VERSION_PHP_REC', '8.2.18');
defined('REQ_PHP_MEMORY') or define('REQ_PHP_MEMORY', '256M');
defined('REQ_PHP_MEMORY_REC') or define('REQ_PHP_MEMORY_REC', '512M');

if (version_compare(PHP_VERSION, VERSION_PHP_RQ, '<')) {
    echo "<pre>";
    printf("Incorrect PHP version: %s (%d)\n\n", PHP_VERSION, defined('PHP_VERSION_ID')? PHP_VERSION_ID : '');
    echo "Current osCommerce version requires at least PHP " . VERSION_PHP_RQ . "\n\n";

    echo "If you've already selected minimal PHP version for your site and still see this message:\n";
    echo "Probably you host sets PHP version into root .htaccess file\n";
    echo "After the first step of this installation .htaccess has been overwritten and PHP version has been reset to default.\n";
    echo "The solution is to change PHP version again or (for advanced users) manually add PHP selector to root .htaccess. Usually it's something like:\n\n";
    echo htmlspecialchars("<IfModule mime_module>\n");
    echo "AddHandler application/x-httpd-ea-php80 .php .php8 .phtml\n";
    echo htmlspecialchars("</IfModule>\n");
    echo "</pre>";
}

@set_time_limit(0);
@ignore_user_abort(true);

if (defined('PHP_VERSION_ID') && PHP_VERSION_ID >= 80100) {
   //mysqli_report(MYSQLI_REPORT_ERROR); // MYSQLI_REPORT_OFF
   mysqli_report(MYSQLI_REPORT_ALL ^ MYSQLI_REPORT_STRICT);
}

function _symlink( $target, $link ) {
  if (isset($_SERVER['WINDIR']) || isset($_SERVER['windir'])) {
    @exec('junction "' . $link . '" "' . $target . '"');
  } else {
    @symlink($target,$link);
  }
}

defined('ROOT_PATH') or define('ROOT_PATH', dirname(dirname(__FILE__)));
if (!is_link(ROOT_PATH . DIRECTORY_SEPARATOR . 'watch')) {
    _symlink(ROOT_PATH, ROOT_PATH . DIRECTORY_SEPARATOR . 'watch');
}
if (!is_link(ROOT_PATH . DIRECTORY_SEPARATOR . 'furniture')) {
    _symlink(ROOT_PATH, ROOT_PATH . DIRECTORY_SEPARATOR . 'furniture');
}
if (!is_link(ROOT_PATH . DIRECTORY_SEPARATOR . 'b2b-supermarket')) {
    _symlink(ROOT_PATH, ROOT_PATH . DIRECTORY_SEPARATOR . 'b2b-supermarket');
}
if (!is_link(ROOT_PATH . DIRECTORY_SEPARATOR . 'printshop')) {
    _symlink(ROOT_PATH, ROOT_PATH . DIRECTORY_SEPARATOR . 'printshop');
}

if (file_exists($rootPath . 'includes/local/configure.php'))
    include_once $rootPath . 'includes/local/configure.php';

include_once($rootPath . 'install/install.class.php');
Log::write('Install started', 'info');
$install = new install();
$install->root_path = $rootPath;
$install->init();
