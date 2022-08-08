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

// Superadmin Configuration
  define('WL_ENABLED', false);
  define('SUPERADMIN_ENABLED', false);

  define('WL_COMPANY_STYLE', 'superadmin.css');
  define('WL_COMPANY_LOGO', 'logo-superadmin.png');
  define('WL_COMPANY_NAME', '');
  define('WL_COMPANY_PHONE', '08000112569');
  
  define('WL_SERVICES_URL', true);
  define('WL_SERVICES_TEXT', 'Ecommerce Development');
  define('WL_SERVICES_WWW', 'http://www.holbi.co.uk/ecommerce-development');
  
  define('WL_SUPPORT_URL', true);
  define('WL_SUPPORT_TEXT', 'Support');
  define('WL_SUPPORT_WWW', 'http://www.holbi.co.uk/ecommerce-support');
  
  define('WL_CONTACT_URL', true);
  define('WL_CONTACT_WWW', 'http://www.holbi.co.uk/contact-us');
  define('WL_CONTACT_TEXT', 'Contact Us');
  
  define('WL_PRODUCT_NAME', 'Superadmin');

  define('WL_COMPANY_EMAIL', 'info@holbi.co.uk');
  define('WL_COMPANY_WWW', 'http://www.holbi.co.uk/');

  define('WL_TEXT_COPYRIGHT', 'Copyright &copy; %s %s. All rights reserved.');

  define('WL_DEFAULT_STORE_LOGO', 'themes/theme-1/img/mystore.png');
  define('WL_DEFAULT_STORE_LOGO_URL', 'http://www.holbi.co.uk/');
  
// to remove
defined('SUPERADMIN_HTTP_URL') or define('SUPERADMIN_HTTP_URL', 'tllab.co.uk');
defined('SUPERADMIN_HTTP_IMAGES') or define('SUPERADMIN_HTTP_IMAGES', '');
defined('DEPARTMENTS_ID') or define('DEPARTMENTS_ID', 0);
define('TABLE_DEPARTMENTS', 'departments');
define('TABLE_DEPARTMENTS_CATEGORIES', 'departments_categories');
define('TABLE_DEPARTMENTS_PRODUCTS', 'departments_products');
define('TABLE_DEPARTMENTS_EXTERNAL_PLATFORMS', 'departments_external_platforms');
define('TABLE_EP_HOLBI_SOAP_SERVER_KV_STORAGE','ep_holbi_soap_server_kv_storage');

