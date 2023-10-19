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

// Define the webserver and path parameters
// * DIR_FS_* = Filesystem directories (local/physical)
// * DIR_WS_* = Webserver directories (virtual/URL)
defined('HTTP_SERVER') or define('HTTP_SERVER', 'http://localhost'); // eg, http://localhost - should not be empty for productive servers
defined('HTTPS_SERVER') or define('HTTPS_SERVER', 'http://localhost'); // eg, http://localhost - should not be empty for productive servers
defined('HTTP_CATALOG_SERVER') or define('HTTP_CATALOG_SERVER', 'http://localhost');
defined('HTTPS_CATALOG_SERVER') or define('HTTPS_CATALOG_SERVER', 'http://localhost');
defined('ENABLE_SSL_CATALOG') or define('ENABLE_SSL_CATALOG', false); // secure webserver for catalog module
defined('ENABLE_SSL') or define('ENABLE_SSL', false);
defined('DIR_FS_DOCUMENT_ROOT') or define('DIR_FS_DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']); // where the pages are located on the server
defined('DIR_WS_ADMIN') or define('DIR_WS_ADMIN', '/trueloaded/admin/'); // absolute path required
defined('DIR_FS_ADMIN') or define('DIR_FS_ADMIN', DIR_FS_DOCUMENT_ROOT . DIR_WS_ADMIN); // absolute pate required
defined('DIR_WS_CATALOG') or define('DIR_WS_CATALOG', '/trueloaded/'); // absolute path required
defined('DIR_FS_CATALOG') or define('DIR_FS_CATALOG', DIR_FS_DOCUMENT_ROOT . DIR_WS_CATALOG); // absolute path required
defined('DIR_WS_HTTP_ADMIN_CATALOG') or define('DIR_WS_HTTP_ADMIN_CATALOG', ltrim(DIR_WS_ADMIN, '/'));
defined('DIR_WS_IMAGES') or define('DIR_WS_IMAGES', 'images/');
defined('DIR_WS_ICONS') or define('DIR_WS_ICONS', DIR_WS_IMAGES . 'icons/');
defined('DIR_WS_CATALOG_IMAGES') or define('DIR_WS_CATALOG_IMAGES', DIR_WS_CATALOG . 'images/');
defined('DIR_WS_INCLUDES') or define('DIR_WS_INCLUDES', 'includes/');
defined('DIR_WS_BOXES') or define('DIR_WS_BOXES', DIR_WS_INCLUDES . 'boxes/');
defined('DIR_WS_FUNCTIONS') or define('DIR_WS_FUNCTIONS', DIR_WS_INCLUDES . 'functions/');
defined('DIR_WS_CLASSES') or define('DIR_WS_CLASSES', DIR_WS_INCLUDES . 'classes/');
defined('DIR_WS_MODULES') or define('DIR_WS_MODULES', DIR_WS_INCLUDES . 'modules/');
defined('DIR_WS_AFFILATES') or define('DIR_WS_AFFILATES', DIR_WS_CATALOG . 'affiliates/');
defined('DIR_FS_AFFILATES') or define('DIR_FS_AFFILATES', DIR_FS_CATALOG . 'affiliates/');
defined('DIR_FS_CATALOG_IMAGES') or define('DIR_FS_CATALOG_IMAGES', DIR_FS_CATALOG . 'images/');
defined('DIR_FS_CATALOG_MODULES') or define('DIR_FS_CATALOG_MODULES', DIR_FS_CATALOG . 'includes/modules/');
defined('DIR_FS_BACKUP') or define('DIR_FS_BACKUP', DIR_FS_ADMIN . 'backups/');
defined('DIR_FS_CATALOG_XML') or define('DIR_FS_CATALOG_XML', DIR_FS_CATALOG . 'admin/xml/');
defined('DIR_FS_DOWNLOAD') or define('DIR_FS_DOWNLOAD', DIR_FS_CATALOG . 'download/');
defined('DIR_WS_DOWNLOAD') or define('DIR_WS_DOWNLOAD', DIR_WS_CATALOG . 'download/');

// Added for Templating
defined('DIR_FS_CATALOG_MAINPAGE_MODULES') or define('DIR_FS_CATALOG_MAINPAGE_MODULES', DIR_FS_CATALOG_MODULES . 'mainpage_modules/');
defined('DIR_WS_TEMPLATES') or define('DIR_WS_TEMPLATES', DIR_WS_CATALOG . 'templates/');
defined('DIR_FS_TEMPLATES') or define('DIR_FS_TEMPLATES', DIR_FS_CATALOG . 'templates/');

// define our database connection
defined('DB_SERVER') or define('DB_SERVER', 'localhost'); // eg, localhost - should not be empty for productive servers
defined('DB_SERVER_USERNAME') or define('DB_SERVER_USERNAME', 'root');
defined('DB_SERVER_PASSWORD') or define('DB_SERVER_PASSWORD', '');
defined('DB_DATABASE') or define('DB_DATABASE', 'trueloaded');
defined('USE_PCONNECT') or define('USE_PCONNECT', 'false'); // use persisstent connections?
defined('STORE_SESSIONS') or define('STORE_SESSIONS', ''); // leave empty '' for default handler or set to 'mysql'

defined('HTTP_COOKIE_DOMAIN') or define('HTTP_COOKIE_DOMAIN', 'localhost');
defined('HTTPS_COOKIE_DOMAIN') or define('HTTPS_COOKIE_DOMAIN', 'localhost');
defined('HTTP_COOKIE_PATH') or define('HTTP_COOKIE_PATH', DIR_WS_CATALOG);
defined('HTTPS_COOKIE_PATH') or define('HTTPS_COOKIE_PATH', DIR_WS_CATALOG);

// define superadmin parameters
defined('SUPERADMIN_HTTP_URL') or define('SUPERADMIN_HTTP_URL', '');
defined('SUPERADMIN_HTTP_IMAGES') or define('SUPERADMIN_HTTP_IMAGES', '');
defined('DEPARTMENTS_ID') or define('DEPARTMENTS_ID', 0);
