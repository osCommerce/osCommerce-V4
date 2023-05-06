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

//Admin begin
  define('FILENAME_ADMIN_ACCOUNT', 'adminaccount');
  define('FILENAME_ADMIN_MEMBERS', 'admin_members');  
  Define('FILENAME_FORBIDEN', 'forbiden');
  define('FILENAME_LOGIN', 'login');
  define('FILENAME_LOGOFF', 'logout');
  define('FILENAME_PASSWORD_FORGOTTEN', 'password_forgotten');
//Admin end

// MaxiDVD Added Line For WYSIWYG HTML Area: BOF
  define('FILENAME_DEFINE_MAINPAGE', 'define_mainpage');
// MaxiDVD Added Line For WYSIWYG HTML Area: EOF

// Lango Added Line For Infobox configuration: BOF
  define('FILENAME_TEMPLATE_CONFIGURATION', 'template_configuration');
  define('FILENAME_INFOBOX_ADMIN', 'infobox_admin');
// Lango Added Line For Infobox configuration: EOF

// Lango Added Line For Salemaker Mod: BOF
  define('FILENAME_SALEMAKER', 'salemaker');
  define('FILENAME_SALEMAKER_INFO', 'salemaker_info');
// Lango Added Line For Salemaker Mod: EOF

// BOF: Lango Added for Featured product MOD
  define('FILENAME_FEATURED', 'featured');
// EOF: Lango Added for Featured product MOD

// BOF: Lango Added for Order_edit MOD
  define('FILENAME_CREATE_ACCOUNT', 'create_account');
  define('FILENAME_CREATE_ACCOUNT_PROCESS', 'create_account_process');
  define('FILENAME_CREATE_ACCOUNT_SUCCESS', 'create_account_success');
  define('FILENAME_CREATE_ORDER_PROCESS', 'create_order_process');
  define('FILENAME_CREATE_ORDER', 'create_order');
  define('FILENAME_EDIT_ORDERS', 'edit_orders');
// EOF: Lango Added for Order_edit MOD

// BOF: Lango Added for Sales Stats MOD
define('FILENAME_STATS_MONTHLY_SALES', 'stats_monthly_sales');
// EOF: Lango Added for Sales Stats MOD

// define the filenames used in the project
  define('FILENAME_BACKUP', 'backup');
  define('FILENAME_BANNER_MANAGER', 'banner_manager');
  define('FILENAME_BANNER_STATISTICS', 'banner_statistics');
  define('FILENAME_CACHE', 'cache');
  define('FILENAME_CATALOG_ACCOUNT_HISTORY_INFO', 'account/history-info');
  define('FILENAME_CATEGORIES', 'categories');
  define('FILENAME_IMAGE_RESIZE', 'image_resize');
  define('FILENAME_CONFIGURATION', 'configuration');
  define('FILENAME_COUNTRIES', 'countries');
  define('FILENAME_CURRENCIES', 'currencies');
  define('FILENAME_CUSTOMERS', 'customers');
  define('FILENAME_DEFAULT', 'index');
  define('FILENAME_DEFINE_LANGUAGE', 'define_language');
  define('FILENAME_FILE_MANAGER', 'file_manager');
  define('FILENAME_GEO_ZONES', 'geo_zones');
  define('FILENAME_LANGUAGES', 'languages');
  define('FILENAME_MAIL', 'mail');
  define('FILENAME_MANUFACTURERS', 'manufacturers');
  define('FILENAME_MODULES', 'modules');
  define('FILENAME_NEWSLETTERS', 'newsletters');
  define('FILENAME_ORDERS', 'orders');
  define('FILENAME_ORDERS_INVOICE', 'invoice');
  define('FILENAME_ORDERS_PACKINGSLIP', 'packingslip');
  define('FILENAME_ORDERS_STATUS', 'orders_status');
  define('FILENAME_POPUP_IMAGE', 'popup_image');
  define('FILENAME_PRODUCTS_ATTRIBUTES', 'products_attributes');
  define('FILENAME_PRODUCTS_EXPECTED', 'products_expected');
  define('FILENAME_REVIEWS', 'reviews');
  define('FILENAME_SERVER_INFO', 'server_info');
  define('FILENAME_CLEANER', 'cleaner');
  define('FILENAME_SHIPPING_MODULES', 'shipping_modules');
  define('FILENAME_SPECIALS', 'specials');
  define('FILENAME_STATS_CUSTOMERS', 'stats_customers');
  define('FILENAME_STATS_PRODUCTS_PURCHASED', 'stats_products_purchased');
  define('FILENAME_STATS_PRODUCTS_VIEWED', 'stats_products_viewed');
  define('FILENAME_TAX_CLASSES', 'tax_classes');
  define('FILENAME_TAX_RATES', 'tax_rates');
  define('FILENAME_WHOS_ONLINE', 'whos_online');
  define('FILENAME_ZONES', 'zones');
  define('FILENAME_PAYPALIPN_TRANSACTIONS', 'paypalipn_txn'); // PAYPALIPN
  define('FILENAME_PAYPALIPN_TESTS', 'paypalipn_tests'); // PAYPALIPN
  define('FILENAME_XSELL_PRODUCTS', 'xsell_products'); // X-Sell
  define('FILENAME_EASYPOPULATE', 'easypopulate');

// VJ Links Manager v1.00 begin
  define('FILENAME_LINKS', 'links');
  define('FILENAME_LINK_CATEGORIES', 'link_categories');
  define('FILENAME_LINKS_CONTACT', 'links_contact');
// VJ Links Manager v1.00 end

//DWD Modify: Information Page Unlimited 1.1f - PT
  define('FILENAME_INFORMATION_MANAGER', 'information_manager');
//DWD Modify End
// sales statistics start
  define('FILENAME_SALES_STATISTICS', 'sales_statistics');
  define('TABLE_SALES_FILTERS', 'sales_filters');
// sales statistics end
  
  define('FILENAME_XSELL_CATS_PRODUCTS', 'xsell_cats_products');
  define('FILENAME_UPSELL_PRODUCTS', 'upsell_products');
  define('FILENAME_UPSELL_CATEGORIES', 'upsell_categories');
  define('FILENAME_UPSELL_CATS_PRODUCTS', 'upsell_cats_products');

  define('FILENAME_INVENTORY', 'inventory');
  define('FILENAME_STOCK_INDICATION_INDICATION', 'stock-indication');
  define('FILENAME_STOCK_INDICATION_DELIVERY_TERMS', 'stock-delivery-terms');
  
  define('FILENAME_PROPERTIES', 'properties');
  define('FILENAME_DOWNLOAD', 'download');
  define('FILENAME_DEFINE_AFFILIATE_INFO', 'define_affiliate_info');
  define('FILENAME_DEFINE_AFFILIATE_TERMS', 'define_affiliate_terms');
  define('FILENAME_DEFINE_AGB', 'define_agb');

  define('FILENAME_GROUPS', 'groups');
  define('FILENAME_GROUPS_CUSTOMERS', 'groups_customers');
  define('FILENAME_VENDOR_SUMMARY', 'vendor_summary');
  define('FILENAME_VENDOR', 'vendor_vendors');
  define('FILENAME_DEFINE_VENDOR_INFO', 'define_vendor_info');
  define('FILENAME_DEFINE_VENDOR_TERMS', 'define_vendor_terms');
  define('FILENAME_VENDOR_CONTACT', 'vendor_contact');
  define('FILENAME_VENDOR_PAYMENT', 'vendor_payment');
  define('FILENAME_VENDOR_SALES', 'vendor_sales');
  define('FILENAME_VENDOR_STATISTICS', 'vendor_statistics');
  define('FILENAME_VENDOR_INVOICE', 'vendor_invoice');
  define('FILENAME_POPUP_VENDOR_HELP', 'popup_vendor_help');
  define('FILENAME_SEARCH_STATISTICS', 'se_statistics');
  define('FILENAME_EXPORT','export');

  //xml related files
  define('FILENAME_BACKUP_XML_DATA', 'xml_backup');
  define('FILENAME_RESTORE_XML_DATA', 'xml_restore');
  
  define('FILENAME_POPUP_INFOBOX_HELP', 'popup_infobox_help');
  define('FILENAME_META_TAGS','meta_tags');

  define('FILENAME_BRAND_MANAGER', 'brand_manager');

  define('FILENAME_RECOVER_CART_SALES', 'recover_cart_sales');
  define('FILENAME_STATS_RECOVER_CART_SALES', 'stats_recover_cart_sales');
  define('FILENAME_CATALOG_PRODUCT_INFO', 'catalog/product');
  define('FILENAME_CATALOG_LOGIN', 'login');

  define('FILENAME_GIVE_AWAY', 'give_away');
  
  define('FILENAME_TRUSTPILOT', 'trustpilot');
  
  define('FILENAME_SEO_MAKER', 'seo_maker');
  define('FILENAME_ORDERS_STATUS_GROUPS', 'orders_status_groups');
  define('FILENAME_SHOPPING_CART', 'shopping-cart');
  define('FILENAME_ACCOUNT_HISTORY_INFO', 'account/history-info');

  define('FILENAME_TESTIMONIALS', 'testimonials');
  
  define('FILENAME_PRODUCTS_GROUPS', 'products-groups');

  define('FILENAME_COLLECTIONS', 'collections');
    define('FILENAME_GOOGLECATEGORIES', 'google-categories');