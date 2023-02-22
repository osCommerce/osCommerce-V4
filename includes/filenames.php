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

// define the content used in the project
define('CONTENT_ACCOUNT', 'account');
define('CONTENT_ACCOUNT_EDIT', 'account_edit');
define('CONTENT_ACCOUNT_HISTORY', 'account_history');
define('CONTENT_ACCOUNT_HISTORY_INFO', 'account_history_info');
define('CONTENT_ACCOUNT_NEWSLETTERS', 'account_newsletters');
define('CONTENT_ACCOUNT_NOTIFICATIONS', 'account_notifications');
define('CONTENT_ACCOUNT_PASSWORD', 'account_password');
define('CONTENT_ADDRESS_BOOK', 'address_book');
define('CONTENT_ADDRESS_BOOK_PROCESS', 'address_book_process');
define('CONTENT_ADVANCED_SEARCH', 'advanced_search');
define('CONTENT_ADVANCED_SEARCH_RESULT', 'advanced_search_result');
define('CONTENT_ALSO_PURCHASED_PRODUCTS', 'also_purchased_products');
define('CONTENT_CHECKOUT_CONFIRMATION', 'checkout_confirmation');
define('CONTENT_CHECKOUT_SUCCESS', 'checkout_success');
define('CONTENT_CONTACT_US', 'contact_us');
define('CONTENT_CONDITIONS', 'conditions');
define('CONTENT_COOKIE_USAGE', 'cookie_usage');
define('CONTENT_CREATE_ACCOUNT', 'create_account');
define('CONTENT_CREATE_ACCOUNT_SUCCESS', 'create_account_success');
define('CONTENT_DOWNLOAD', 'download');
define('CONTENT_INDEX_DEFAULT', 'index_default');
define('CONTENT_INDEX_NESTED', 'index_nested');
define('CONTENT_INDEX_PRODUCTS', 'index_products');
define('CONTENT_INFO_SHOPPING_CART', 'info_shopping_cart');
define('CONTENT_LOGIN', 'login');
define('CONTENT_LOGOFF', 'logoff');
define('CONTENT_NEW_PRODUCTS', 'new_products');
define('CONTENT_PASSWORD_FORGOTTEN', 'password_forgotten');
define('CONTENT_POPUP_IMAGE', 'popup_image');
define('CONTENT_POPUP_SEARCH_HELP', 'popup_search_help');
define('CONTENT_PRIVACY', 'privacy');
define('CONTENT_PRODUCT_INFO', 'product_info');
define('CONTENT_PRODUCT_LISTING', 'product_listing');
define('CONTENT_PRODUCT_REVIEWS', 'product_reviews');
define('CONTENT_PRODUCT_REVIEWS_INFO', 'product_reviews_info');
define('CONTENT_PRODUCT_REVIEWS_WRITE', 'product_reviews_write');
define('CONTENT_REVIEWS', 'reviews');
define('CONTENT_SHIPPING', 'shipping');
define('CONTENT_SHOPPING_CART', 'shopping_cart');
define('CONTENT_SPECIALS', 'specials');
define('CONTENT_SSL_CHECK', 'ssl_check');
define('CONTENT_TELL_A_FRIEND', 'tell_a_friend');
define('CONTENT_UPCOMING_PRODUCTS', 'upcoming_products');
define('CONTENT_CHECKOUT_PROCESS', 'checkout_process');

// Lango added for GV FAQ: BOF
define('CONTENT_GV_FAQ', 'gv_faq');
define('CONTENT_GV_REDEEM', 'gv_redeem');
define('CONTENT_GV_SEND', 'gv_send');
// Lango added for GV FAQ: EOF
// Lango added for Affiliate Mod: BOF
define('CONTENT_AFFILIATE', 'affiliate_affiliate');
define('CONTENT_AFFILIATE_SIGNUP', 'affiliate_signup');
define('CONTENT_AFFILIATE_SIGNUP_OK', 'affiliate_signup_ok');

define('CONTENT_AFFILIATE_BANNERS', 'affiliate_banners');
define('CONTENT_AFFILIATE_SUMMARY', 'affiliate_summary');
define('CONTENT_AFFILIATE_DETAILS', 'affiliate_details');
define('CONTENT_AFFILIATE_DETAILS_OK', 'affiliate_details_ok');
define('CONTENT_AFFILIATE_CLICKS', 'affiliate_clicks');
define('CONTENT_AFFILIATE_CONTACT', 'affiliate_contact');
define('CONTENT_AFFILIATE_PAYMENT', 'affiliate_payment');
define('CONTENT_AFFILIATE_FAQ', 'affiliate_faq');
define('CONTENT_AFFILIATE_INFO', 'affiliate_info');
define('CONTENT_AFFILIATE_LOGOUT', 'affiliate_logout');
define('CONTENT_AFFILIATE_TERMS', 'affiliate_terms');
define('CONTENT_AFFILIATE_PASSWORD_FORGOTTEN', 'affiliate_password_forgotten');
// Lango added for Affiliate Mod: EOF
// Lango Added for Down for Maintainance Mod: BOF
define('CONTENT_DOWN_FOR_MAINTAINANCE', 'down_for_maintenance');
// Lango Added for Down for Maintainance Mod: EOF
// Lango added forALL_PRODS: BOF
define('CONTENT_ALL_PRODS', 'allprods');
// Lango added forALL_PRODS: EOF
// Lango added for osC-PrintOrder v1.0: BOF
define('CONTENT_ORDERS_PRINTABLE', 'printorder');
// Lango added for osC-PrintOrder v1.0: EOF
// Lango added for Featured products: BOF
define('CONTENT_FEATURED', 'featured');
// Lango added for Featured products: EOF
// Lango Added for Links Manager Mod: BOF
define('CONTENT_LINKS', 'links');
define('CONTENT_LINKS_SUBMIT', 'links_submit');
define('CONTENT_LINKS_SUBMIT_SUCCESS', 'links_submit_success');
// Lango Added for Links Manager Mod:
// Lango Added for shop by price Mod: BOF
define('CONTENT_SHOP_BY_PRICE', 'shop_by_price');
// Lango Added for shop by price Mod: EOF
//DWD Modify: Information Page Unlimited 1.1f - PT
define('CONTENT_INFORMATION', 'information');
//DWD Modify End
// define the filenames used in the project
define('FILENAME_ACCOUNT', 'account');
define('FILENAME_ACCOUNT_EDIT', 'account/edit');
define('FILENAME_ACCOUNT_HISTORY', 'account/history');
define('FILENAME_ACCOUNT_HISTORY_INFO', 'account/history-info');
define('FILENAME_ACCOUNT_NEWSLETTERS', 'account/newsletters');
define('FILENAME_ACCOUNT_NOTIFICATIONS', CONTENT_ACCOUNT_NOTIFICATIONS . '.php');
define('FILENAME_ACCOUNT_PASSWORD', 'account/password');
define('FILENAME_ADDRESS_BOOK', 'account/address-book');
define('FILENAME_ADDRESS_BOOK_PROCESS', 'account/address-book-process');
define('FILENAME_ADVANCED_SEARCH', 'catalog/advanced-search');
define('FILENAME_ADVANCED_SEARCH_RESULT', 'catalog/all-products');
define('FILENAME_ALSO_PURCHASED_PRODUCTS', CONTENT_ALSO_PURCHASED_PRODUCTS . '.php');
define('FILENAME_CHECKOUT_CONFIRMATION', 'checkout/confirmation');

define('ONE_PAGE_CHECKOUT', 'True');
if (ONE_PAGE_CHECKOUT == 'True') {
    define('CONTENT_CHECKOUT_PAYMENT', 'checkout');
    define('CONTENT_CHECKOUT_SHIPPING', 'checkout');
    define('CONTENT_CHECKOUT_PAYMENT_ADDRESS', 'checkout');
    define('CONTENT_CHECKOUT_SHIPPING_ADDRESS', 'checkout');
    
    define('FILENAME_CHECKOUT_SHIPPING', 'checkout/');
    define('FILENAME_CHECKOUT_PAYMENT_ADDRESS', 'checkout/');
    define('FILENAME_CHECKOUT_SHIPPING_ADDRESS', 'checkout/');
} else {
    define('CONTENT_CHECKOUT_PAYMENT', 'checkout_payment');
    define('CONTENT_CHECKOUT_PAYMENT_ADDRESS', 'checkout_payment_address');
    define('CONTENT_CHECKOUT_SHIPPING_ADDRESS', 'checkout_shipping_address');
    define('CONTENT_CHECKOUT_SHIPPING', 'checkout_shipping');
    define('FILENAME_CHECKOUT_PAYMENT', CONTENT_CHECKOUT_PAYMENT . '.php');
    define('FILENAME_CHECKOUT_PAYMENT_ADDRESS', CONTENT_CHECKOUT_PAYMENT_ADDRESS . '.php');
    define('FILENAME_CHECKOUT_SHIPPING', CONTENT_CHECKOUT_SHIPPING . '.php');
    define('FILENAME_CHECKOUT_SHIPPING_ADDRESS', CONTENT_CHECKOUT_SHIPPING_ADDRESS . '.php');
}
/* }} */
if (isset($_SERVER['REQUEST_URI'])) $script_pos = $_SERVER['REQUEST_URI'];
if (!isset($script_pos)) $script_pos = preg_replace ("/.php/i", "", $_SERVER['PHP_SELF']);
//if (basename($script_pos) == 'order-process') {
if (isset($_SERVER['REQUEST_URI']) && (strpos($_SERVER['REQUEST_URI'], 'order-process') !== false) || ( (isset($_POST['M_paid']) && $_POST['M_paid'] == 'partlypaid') || (isset($_POST['M_paid']) && $_POST['M_paid'] == 'partlypaid') )){
define('FILENAME_CHECKOUT_PROCESS', 'payer/order-process');
define('FILENAME_CHECKOUT_PAYMENT', 'payer/order-pay');
} else {
define('FILENAME_CHECKOUT_PROCESS', 'checkout/process');
define('FILENAME_CHECKOUT_PAYMENT', 'checkout/');
}
define('FILENAME_CHECKOUT_SUCCESS', 'checkout/success');
define('FILENAME_CONTACT_US', 'contact');
define('FILENAME_CONDITIONS', CONTENT_CONDITIONS . '.php');
define('FILENAME_COOKIE_USAGE', 'index');
define('FILENAME_CREATE_ACCOUNT', 'account/create');
define('FILENAME_CREATE_ACCOUNT_SUCCESS', 'account/create-success');
define('FILENAME_DEFAULT', 'index');
define('FILENAME_DOWNLOAD', 'account/download');
define('FILENAME_INFO_SHOPPING_CART', CONTENT_INFO_SHOPPING_CART . '.php');
define('FILENAME_LOGIN', 'account/login');
define('FILENAME_LOGOFF', 'account/logoff');
define('FILENAME_NEW_PRODUCTS', CONTENT_NEW_PRODUCTS . '.php');
define('FILENAME_PASSWORD_FORGOTTEN', 'account/password-forgotten');
define('FILENAME_POPUP_IMAGE', CONTENT_POPUP_IMAGE . '.php');
define('FILENAME_POPUP_IMAGE1', 'popup_image1.php');
define('FILENAME_POPUP_IMAGE2', 'popup_image2.php');
define('FILENAME_POPUP_IMAGE3', 'popup_image3.php');
define('FILENAME_POPUP_IMAGE4', 'popup_image4.php');
define('FILENAME_POPUP_IMAGE5', 'popup_image5.php');
define('FILENAME_POPUP_IMAGE6', 'popup_image6.php');
define('FILENAME_POPUP_SEARCH_HELP', CONTENT_POPUP_SEARCH_HELP . '.php');
define('FILENAME_PRIVACY', CONTENT_PRIVACY . '.php');
define('FILENAME_PRODUCT_INFO', 'catalog/product');
define('FILENAME_PRODUCT_LISTING', CONTENT_PRODUCT_LISTING . '.php');
define('FILENAME_PRODUCT_REVIEWS', CONTENT_PRODUCT_REVIEWS . '.php');
define('FILENAME_PRODUCT_REVIEWS_INFO', CONTENT_PRODUCT_REVIEWS_INFO . '.php');
define('FILENAME_PRODUCT_REVIEWS_WRITE', CONTENT_PRODUCT_REVIEWS_WRITE . '.php');
define('FILENAME_PRODUCTS_NEW', 'catalog/products-new');
define('FILENAME_ALL_PRODUCTS', 'catalog/all-products');
define('FILENAME_PERSONAL_CATALOG', 'personal-catalog');
define('FILENAME_REDIRECT', 'redirect.php');
define('FILENAME_REVIEWS', CONTENT_REVIEWS . '.php');
define('FILENAME_SHIPPING', CONTENT_SHIPPING . '.php');
define('FILENAME_SHOPPING_CART', 'shopping-cart');
define('FILENAME_SPECIALS', 'catalog/sales');
define('FILENAME_SSL_CHECK', CONTENT_SSL_CHECK . '.php');
define('FILENAME_TELL_A_FRIEND', CONTENT_TELL_A_FRIEND . '.php');
define('FILENAME_UPCOMING_PRODUCTS', CONTENT_UPCOMING_PRODUCTS . '.php');
define('FILENAME_CHECKOUT_PAYPALIPN', 'checkout_paypalipn.php'); // PAYPALIPN
//BEGIN allprods modification
define('FILENAME_ALLPRODS', 'allprods.php');
//END allprods modification
define('FILENAME_DYNAMIC_MOPICS', 'dynamic_mopics.php');

// Lango Added for GV_FAQ: BOF
// Lango Added for GV_FAQ: BOF
// MaxiDVD Added Line For WYSIWYG HTML Area: BOF
// MaxiDVD Added Line For WYSIWYG HTML Area: EOF
// Lango added for Affiliate Mod: BOF
/*  define('FILENAME_AFFILIATE', CONTENT_AFFILIATE . '.php');
  define('FILENAME_AFFILIATE_SIGNUP', CONTENT_AFFILIATE_SIGNUP . '.php');
  define('FILENAME_AFFILIATE_BANNERS', CONTENT_AFFILIATE_BANNERS . '.php');
  define('FILENAME_AFFILIATE_SUMMARY', CONTENT_AFFILIATE_SUMMARY . '.php');
  define('FILENAME_AFFILIATE_DETAILS', CONTENT_AFFILIATE_DETAILS . '.php');
  define('FILENAME_AFFILIATE_CLICKS', CONTENT_AFFILIATE_CLICKS . '.php');
  define('FILENAME_AFFILIATE_CONTACT', CONTENT_AFFILIATE_CONTACT . '.php');
  define('FILENAME_AFFILIATE_PAYMENT', CONTENT_AFFILIATE_PAYMENT . '.php');
  define('FILENAME_AFFILIATE_FAQ', CONTENT_AFFILIATE_FAQ . '.php');
  define('FILENAME_AFFILIATE_INFO', CONTENT_AFFILIATE_INFO . '.php');
  define('FILENAME_AFFILIATE_LOGOUT', CONTENT_AFFILIATE_LOGOUT . '.php');
  define('FILENAME_AFFILIATE_TERMS', CONTENT_AFFILIATE_TERMS . '.php');
  define('FILENAME_AFFILIATE_PASSWORD_FORGOTTEN', CONTENT_AFFILIATE_PASSWORD_FORGOTTEN . '.php'); */
// Lango added for Affiliate Mod: BOF
// Lango Added for ALL_PODS Mod: BOF
define('FILENAME_ALL_PRODS', CONTENT_ALL_PRODS . '.php');
// Lango Added for ALL_PRODS Mod: EOF
// Lango Added for ALL_PODS Mod: BOF
define('FILENAME_ORDERS_PRINTABLE', 'account/invoice');
// Lango Added for ALL_PRODS Mod: EOF
// Lango Added for Featured Products: BOF
define('FILENAME_FEATURED', CONTENT_FEATURED . '.php');
define('FILENAME_FEATURED_PRODUCTS', 'catalog/featured-products');
// Lango Added for Featured Product: EOF
// Lango Added for ALL_PODS Mod: BOF
define('FILENAME_POPUP_AFFILIATE_HELP', 'popup_affiliate_help.php');
// Lango Added for ALL_PRODS Mod: EOF
// Lango Added for Links Manager Mod: BOF
define('FILENAME_LINKS', CONTENT_LINKS . '.php');
define('FILENAME_LINKS_SUBMIT', CONTENT_LINKS_SUBMIT . '.php');
define('FILENAME_LINKS_SUBMIT_SUCCESS', CONTENT_LINKS_SUBMIT_SUCCESS . '.php');
define('FILENAME_POPUP_LINKS_HELP', 'popup_links_help.php');
// Lango Added for Links Manager Mod: EOF
// Lango Added for shop by price Mod: BOF
define('FILENAME_SHOP_BY_PRICE', CONTENT_SHOP_BY_PRICE . '.php');
// Lango Added for shop by price Mod: EOF
// define the templatenames used in the project
define('TEMPLATENAME_BOX', 'box.tpl.php');
define('TEMPLATENAME_MAIN_PAGE', 'main_page.tpl.php');
define('TEMPLATENAME_POPUP', 'popup.tpl.php');
define('TEMPLATENAME_STATIC', 'static.tpl.php');

//DWD Modify: Information Page Unlimited 1.1f - PT
define('FILENAME_INFORMATION', 'info');
//DWD Modify End
define('FILENAME_CONDITIONS_DOWNLOAD', 'agbs.php');

// Added for Xsell Products Mod
define('FILENAME_XSELL_PRODUCTS', 'xsell_products.php');

define('CONTENT_VENDOR_INFO', 'vendor_info');
define('FILENAME_VENDOR_INFO', CONTENT_VENDOR_INFO . '.php');
define('CONTENT_VENDOR', 'vendor_vendor');
define('FILENAME_VENDOR', CONTENT_VENDOR . '.php');
define('CONTENT_VENDOR_TERMS', 'vendor_terms');
define('FILENAME_VENDOR_TERMS', CONTENT_VENDOR_TERMS . '.php');

define('CONTENT_VENDOR_SIGNUP', 'vendor_signup');
define('FILENAME_VENDOR_SIGNUP', CONTENT_VENDOR_SIGNUP . '.php');
define('CONTENT_VENDOR_SIGNUP_OK', 'vendor_signup_ok');
define('FILENAME_VENDOR_SIGNUP_OK', CONTENT_VENDOR_SIGNUP_OK . '.php');
define('FILENAME_POPUP_VENDOR_HELP', 'popup_vendor_help.php');
define('CONTENT_SUBSCRIBERS', 'subscribers');
define('FILENAME_SUBSCRIBERS', CONTENT_SUBSCRIBERS . '.php');
// cms:
define('CONTENT_CMS', 'cms');
define('FILENAME_CMS', CONTENT_CMS . '.php');
define('CONTENT_COMPARE', 'compare');
define('FILENAME_COMPARE', CONTENT_COMPARE . '.php');
define('FILENAME_INFO_FAQ', 'info_faq.php');

define('CONTENT_VIRTUAL_GIFT_CARD', 'virtual-gift-card');
define('FILENAME_VIRTUAL_GIFT_CARD', CONTENT_VIRTUAL_GIFT_CARD . '.php');
define('CONTENT_VIRT_TEMPLATE', 'virtual-gift-card-template');
define('FILENAME_VIRT_TEMPLATE', CONTENT_VIRT_TEMPLATE . '.php');
//
define('FILENAME_FILTERS', 'filters.php');

define('FILENAME_DELIVERY_LOCATION', 'delivery-location/');
