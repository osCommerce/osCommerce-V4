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

namespace backend\design;

use Yii;
use common\models\ThemesStyles;
use common\classes\design;

class WidgetsList
{
    public static function get($type)
    {
        $widgets = [];
        $method = $type;
        if ($type == 'invoice' || $type == 'creditnote' || $type == 'orders') {
            $method = 'orders';
        }

        if (method_exists(__CLASS__, $method)){
            $widgets = self::$method();
        }
        if ($type != 'email' && $type != 'invoice' && $type != 'packingslip' && $type != 'pdf' && $type != 'orders') {
            $widgets = array_merge(self::main(), $widgets);
        }

        $path = DIR_FS_CATALOG . 'lib'
            . DIRECTORY_SEPARATOR . 'backend'
            . DIRECTORY_SEPARATOR . 'design'
            . DIRECTORY_SEPARATOR . 'boxes'
            . DIRECTORY_SEPARATOR . 'include';
        if (file_exists($path)) {
            $dir = scandir($path);
            foreach ($dir as $file) {
                if (file_exists($path . DIRECTORY_SEPARATOR . $file) && is_file($path . DIRECTORY_SEPARATOR . $file)) {
                    require $path . DIRECTORY_SEPARATOR . $file;
                }
            }
        }

        $widgets = array_merge($widgets, \common\helpers\Acl::getExtensionWidgets($type));

        if ($type == 'productListing'){
            $productListing = [];
            foreach ($widgets as $key => $widget) {
                if ($widget['type'] == 'productListing'){
                    $productListing[] = $widget;
                }
            }
            $widgets = $productListing;
        }

        if ($type == 'backendOrder'){
            $backendOrder = [];
            foreach ($widgets as $key => $widget) {
                if ($widget['type'] == 'backendOrder'){
                    $backendOrder[] = $widget;
                }
            }
            $widgets = $backendOrder;
        }

        if ($type == 'backendOrdersList'){
            $backendOrder = [];
            foreach ($widgets as $key => $widget) {
                if ($widget['type'] == 'backendOrdersList'){
                    $backendOrder[] = $widget;
                }
            }
            $widgets = $backendOrder;
        }

        $widgets = array_merge($widgets, \backend\design\Groups::getWidgetGroups($type));

        return $widgets;
    }

    private static function product()
    {
        $widgets = [];
        $widgets[] = array('name' => 'title', 'title' => PRODUCTS_WIDGETS, 'description' => '', 'type' => 'product');
        $widgets[] = array('name' => 'product\Name', 'title' => TEXT_PRODUCTS_NAME, 'description' => '', 'type' => 'product', 'class' => 'name');
        $widgets[] = array('name' => 'product\Images', 'title' => TEXT_PRODUCTS_IMAGES, 'description' => '', 'type' => 'product', 'class' => 'images');
        $widgets[] = array('name' => 'product\ImagesAdditional', 'title' => TEXT_ADDITIONAL_IMAGES, 'description' => '', 'type' => 'product', 'class' => 'images');
        $widgets[] = array('name' => 'product\Attributes', 'title' => TEXT_PRODUCTS_ATTRIBUTES, 'description' => '', 'type' => 'product', 'class' => 'attributes');
//        $widgets[] = array('name' => 'product\Inventory', 'title' => BOX_CATALOG_INVENTORY, 'description' => '', 'type' => 'product', 'class' => 'attributes'); // not work
//        $widgets[] = array('name' => 'product\MultiInventory', 'title' => 'Multi Inventory', 'description' => '', 'type' => 'product', 'class' => 'multi-inventory-products'); // not work
        $widgets[] = array('name' => 'product\Bundle', 'title' => TEXT_PRODUCTS_BUNDLE, 'description' => '', 'type' => 'product', 'class' => 'bundle');
        $widgets[] = array('name' => 'product\InBundles', 'title' => TEXT_PRODUCTS_IN_BUNDLE, 'description' => '', 'type' => 'product', 'class' => 'in-bundles');
        $widgets[] = array('name' => 'product\Price', 'title' => TEXT_PRODUCTS_PRICE, 'description' => '', 'type' => 'product', 'class' => 'price');
        $widgets[] = array('name' => 'product\QuantityDiscounts', 'title' => QUANTITY_DISCOUNTS, 'description' => '', 'type' => 'product', 'class' => 'price');
        $widgets[] = array('name' => 'product\Quantity', 'title' => TEXT_QUANTITY_INPUT, 'description' => '', 'type' => 'product', 'class' => 'quantity');
        $widgets[] = array('name' => 'product\Stock', 'title' => TEXT_STOCK_INDICATION, 'description' => '', 'type' => 'product', 'class' => 'stock');
        $widgets[] = array('name' => 'product\Buttons', 'title' => TEXT_BUY_BUTTON, 'description' => '', 'type' => 'product', 'class' => 'buttons');
        //$widgets[] = array('name' => 'product\WishlistButton', 'title' => TEXT_WISHLIST_BUTTON, 'description' => '', 'type' => 'product', 'class' => 'buttons');
        $widgets[] = array('name' => 'product\Description', 'title' => TEXT_PRODUCTS_DESCRIPTION, 'description' => '', 'type' => 'product', 'class' => 'description');
        $widgets[] = array('name' => 'product\DescriptionShort', 'title' => TEXT_PRODUCTS_DESCRIPTION_SHORT, 'description' => '', 'type' => 'product', 'class' => 'description');
        $widgets[] = array('name' => 'product\Reviews', 'title' => TEXT_PRODUCTS_REVIEWS, 'description' => '', 'type' => 'product', 'class' => 'reviews');
        $widgets[] = array('name' => 'product\Properties', 'title' => TEXT_PRODUCTS_PROPERTIES, 'description' => '', 'type' => 'product', 'class' => 'properties');
        $widgets[] = array('name' => 'product\Model', 'title' => TABLE_HEADING_PRODUCTS_MODEL, 'description' => '', 'type' => 'product', 'class' => 'properties');
        $widgets[] = array('name' => 'product\Weight', 'title' => TEXT_WEIGHT, 'description' => '', 'type' => 'product', 'class' => 'properties');
        $widgets[] = array('name' => 'product\PropertiesIcons', 'title' => TEXT_PROPERTIES_ICONS, 'description' => '', 'type' => 'product', 'class' => 'properties');
        $widgets[] = array('name' => 'product\AlsoPurchased', 'title' => TEXT_ALSO_PURCHASED, 'description' => '', 'type' => 'product', 'class' => 'also-purchased');
        $widgets[] = array('name' => 'product\Brand', 'title' => TEXT_LABEL_BRAND, 'description' => '', 'type' => 'product', 'class' => 'brands');
        $widgets[] = array('name' => 'product\Video', 'title' => TEXT_VIDEO, 'description' => '', 'type' => 'product', 'class' => 'video');
        $widgets[] = array('name' => 'product\Documents', 'title' => TAB_DOCUMENTS, 'description' => '', 'type' => 'product', 'class' => 'description');
        $widgets[] = array('name' => 'product\Configurator', 'title' => TEXT_CONFIGURATOR, 'description' => '', 'type' => 'product', 'class' => 'configurator');
        $widgets[] = array('name' => 'product\CustomersActivity', 'title' => TEXT_ACTIVE_CUSTOMERS, 'description' => '', 'type' => 'product', 'class' => '');
        $widgets[] = array('name' => 'product\AvailableInWarehouses', 'title' => TEXT_AVAILABLE_AT_WAREHOUSES, 'description' => '', 'type' => 'product', 'class' => '');
        $widgets[] = array('name' => 'product\Dimensions', 'title' => TEXT_DIMENSION, 'description' => '', 'type' => 'product', 'class' => '');
        $widgets[] = array('name' => 'DeliveryDay', 'title' => DELIVERY_DAY, 'description' => '', 'type' => 'product', 'class' => '');
        $widgets[] = array('name' => 'cart\FreeDelivery', 'title' => TEXT_FREE_DELIVERY, 'description' => '', 'type' => 'product', 'class' => '');
        $widgets[] = array('name' => 'product\BazaarvoiceRatingSummary', 'title' => TEXT_BAZAARVOICE_RATING_SUMMARY, 'description' => '', 'type' => 'product', 'class' => '');
        $widgets[] = array('name' => 'product\BazaarvoiceReview', 'title' => TEXT_BAZAARVOICE_REVIEWS, 'description' => '', 'type' => 'product', 'class' => '');
        $widgets[] = array('name' => 'product\CompareButton', 'title' => TEXT_COMPARE_BUTTON, 'description' => '', 'type' => 'product', 'class' => '');
        $widgets[] = array('name' => 'product\PriceFrom', 'title' => TEXT_PRICE_FROM, 'description' => '', 'type' => 'product', 'class' => '');
        $widgets[] = array('name' => 'product\PayPalPayLater', 'title' => (defined("TEXT_PAYPAL_PARTNER_PAY_LATER_PLAN") ? TEXT_PAYPAL_PARTNER_PAY_LATER_PLAN : 'PAYPAL_PARTNER_PAY_LATER_PLAN'), 'description' => '', 'type' => 'product', 'class' => '');

        return $widgets;
    }

    private static function inform()
    {
        $widgets = [];

        $widgets[] = array('name' => 'title', 'title' => INFOPAGES_WIDGETS, 'description' => '', 'type' => 'inform');
        $widgets[] = array('name' => 'info\Title', 'title' => TEXT_TITLE_, 'description' => '', 'type' => 'inform', 'class' => 'title');
        $widgets[] = array('name' => 'info\Content', 'title' => TEXT_CONTENT, 'description' => '', 'type' => 'inform', 'class' => 'content');
        $widgets[] = array('name' => 'contact\ContactForm', 'title' => CONTACT_FORM, 'description' => '', 'type' => 'general', 'class' => 'contact-form');
        $widgets[] = array('name' => 'info\Image', 'title' => TEXT_IMAGE_, 'description' => '', 'type' => 'inform', 'class' => 'images');
        $widgets[] = array('name' => 'info\DescriptionShort', 'title' => TEXT_PRODUCTS_DESCRIPTION_SHORT, 'description' => '', 'type' => 'inform', 'class' => 'description');
        $widgets[] = array('name' => 'contact\Map', 'title' => TEXT_MAP, 'description' => '', 'type' => 'inform', 'class' => 'map');
        $widgets[] = array('name' => 'contact\Contacts', 'title' => TEXT_CONTACTS, 'description' => '', 'type' => 'inform', 'class' => 'contacts');
        $widgets[] = array('name' => 'contact\StreetView', 'title' => GOOGLE_STREET_VIEW, 'description' => '', 'type' => 'inform', 'class' => 'street-view');

        return $widgets;
    }

    private static function subscribe()
    {
        $widgets = [];
         /** @var \common\extensions\Subscribers\Subscribers $subscr  */
        if ($subscr = \common\helpers\Acl::checkExtensionAllowed('Subscribers', 'allowed')) {
            $widgets[] = array('name' => 'subscribers\SubscribeForm', 'title' => TEXT_SUBSCRIBE_FORM, 'description' => '', 'type' => 'subscribe', 'class' => '');
            $widgets[] = array('name' => 'subscribers\UnsubscribeForm', 'title' => TEXT_UNSUBSCRIBE_FORM, 'description' => '', 'type' => 'subscribe', 'class' => '');
        }

        return $widgets;
    }

    private static function catalog()
    {
        $widgets = [];

        //$widgets[] = array('name' => 'product\WeddingRegistryButton', 'title' => TEXT_WEDDING_REGISTRY, 'description' => '', 'type' => 'catalog', 'class' => 'buttons');

        $widgets[] = array('name' => 'title', 'title' => CATALOGS_WIDGETS, 'description' => '', 'type' => 'catalog');
        $widgets[] = array('name' => 'catalog\Title', 'title' => TEXT_TITLE_, 'description' => '', 'type' => 'catalog', 'class' => 'title');
        $widgets[] = array('name' => 'catalog\Description', 'title' => TEXT_CATEGORY_DESCRIPTION, 'description' => '', 'type' => 'catalog', 'class' => 'description');
        $widgets[] = array('name' => 'catalog\Image', 'title' => TEXT_CATEGORY_IMAGE, 'description' => '', 'type' => 'catalog', 'class' => 'image');
        //$widgets[] = array('name' => 'PagingBar', 'title' => TEXT_PAGING_BAR, 'description' => '', 'type' => 'catalog', 'class' => 'paging-bar');
        $widgets[] = array('name' => 'catalog\Paging', 'title' => TEXT_PAGING, 'description' => '', 'type' => 'catalog', 'class' => 'paging-bar');
        $widgets[] = array('name' => 'catalog\CountsItems', 'title' => COUNTS_ITEMS_ON_PAGE, 'description' => '', 'type' => 'catalog', 'class' => 'paging-bar');
        $widgets[] = array('name' => 'Listing', 'title' => TEXT_PRODUCT_LISTING, 'description' => '', 'type' => 'catalog', 'class' => 'listing');
        //$widgets[] = array('name' => 'ListingFunctionality', 'title' => TEXT_LISTING_FUNCTIONALITY_BAR, 'description' => '', 'type' => 'catalog', 'class' => 'listing-functionality');
        $widgets[] = array('name' => 'catalog\ListingLook', 'title' => TEXT_LISTING_LOOK, 'description' => '', 'type' => 'catalog', 'class' => 'listing-functionality');
        $widgets[] = array('name' => 'catalog\CompareButton', 'title' => TEXT_COMPARE_BUTTON, 'description' => '', 'type' => 'catalog', 'class' => 'listing-functionality');
        $widgets[] = array('name' => 'catalog\Sorting', 'title' => TEXT_SORTING, 'description' => '', 'type' => 'catalog', 'class' => 'listing-functionality');
        $widgets[] = array('name' => 'catalog\ItemsOnPage', 'title' => TEXT_ITEMS_ON_PAGE, 'description' => '', 'type' => 'catalog', 'class' => 'listing-functionality');
        $widgets[] = array('name' => 'Categories', 'title' => TEXT_CATEGORIES, 'description' => '', 'type' => 'catalog', 'class' => 'categories');
        $widgets[] = array('name' => 'Filters', 'title' => TEXT_FILTERS, 'description' => '', 'type' => 'catalog', 'class' => 'filters');
        $widgets[] = array('name' => 'catalog\B2bAddButton', 'title' => B2B_ADD_BUTTON, 'description' => '', 'type' => 'catalog', 'class' => '');
        $widgets[] = array('name' => 'catalog\AdditionalImages', 'title' => TEXT_ADDITIONAL_IMAGES, 'description' => '', 'type' => 'catalog', 'class' => '');

        return $widgets;
    }

    private static function cart()
    {
        $widgets = [];

        $widgets[] = array('name' => 'title', 'title' => SHOPPING_CART_WIDGETS, 'description' => '', 'type' => 'cart');
        $widgets[] = array('name' => 'cart\ContinueBtn', 'title' => CONTINUE_BUTTON, 'description' => '', 'type' => 'cart', 'class' => 'continue-button');
        $widgets[] = array('name' => 'cart\CheckoutBtn', 'title' => CHECKOUT_BUTTON, 'description' => '', 'type' => 'cart', 'class' => 'checkout-button');
        $widgets[] = array('name' => 'cart\Products', 'title' => TABLE_HEADING_PRODUCTS, 'description' => '', 'type' => 'cart', 'class' => 'products');
        $widgets[] = array('name' => 'cart\SubTotal', 'title' => SUB_TOTAL_AND_GIFT_WRAP_PRICE, 'description' => '', 'type' => 'cart', 'class' => 'price');
        $widgets[] = array('name' => 'cart\GiftCertificate', 'title' => GIFT_CERTIFICATE, 'description' => '', 'type' => 'cart', 'class' => 'gift-certificate');
        $widgets[] = array('name' => 'cart\DiscountCoupon', 'title' => DISCOUNT_COUPON, 'description' => '', 'type' => 'cart', 'class' => 'discount-coupon');
        $widgets[] = array('name' => 'cart\OrderReference', 'title' => TEXT_ORDER_REFERENCE, 'description' => '', 'type' => 'cart', 'class' => 'order-reference');
        $widgets[] = array('name' => 'cart\GiveAway', 'title' => BOX_CATALOG_GIVE_AWAY, 'description' => '', 'type' => 'cart', 'class' => 'give-away');
        $widgets[] = array('name' => 'cart\ShippingEstimator', 'title' => SHOW_SHIPPING_ESTIMATOR_TITLE, 'description' => '', 'type' => 'cart', 'class' => 'shipping-estimator');
        $widgets[] = array('name' => 'cart\OrderTotal', 'title' => ORDER_PRICE_TOTAL, 'description' => '', 'type' => 'cart', 'class' => 'order-total');
        $widgets[] = array('name' => 'cart\CartTabs', 'title' => TEXT_CART_TABS, 'description' => '', 'type' => 'cart', 'class' => '');
        $widgets[] = array('name' => 'cart\CreditAmount', 'title' => CREDIT_AMOUNT, 'description' => '', 'type' => 'cart', 'class' => '');
        $widgets[] = array('name' => 'cart\FreeDelivery', 'title' => TEXT_FREE_DELIVERY, 'description' => '', 'type' => 'cart', 'class' => '');
        $widgets[] = array('name' => 'DeliveryDay', 'title' => DELIVERY_DAY, 'description' => '', 'type' => 'cart', 'class' => '');
        $widgets[] = array('name' => 'cart\DependedProducts', 'title' => TEXT_DEPENDED_PRODUCTS, 'description' => '', 'type' => 'cart', 'class' => '');
        $widgets[] = array('name' => 'cart\PayPalPayLater', 'title' => TEXT_PAYPAL_PARTNER_PAY_LATER_PLAN, 'description' => '', 'type' => 'cart', 'class' => '');

        return $widgets;
    }

    private static function quote()
    {
        $widgets = [];

        $widgets[] = array('name' => 'quote\Products', 'title' => TABLE_HEADING_PRODUCTS, 'description' => 'Quote', 'type' => 'quote', 'class' => 'products');
        $widgets[] = array('name' => 'cart\CartTabs', 'title' => TEXT_CART_TABS, 'description' => '', 'type' => 'quote', 'class' => '');
        $widgets[] = array('name' => 'quote\CheckoutBtn', 'title' => CHECKOUT_BUTTON, 'description' => 'Quote', 'type' => 'quote', 'class' => 'checkout-button');

        return $widgets;
    }

    private static function sample()
    {
        $widgets = [];

        $widgets[] = array('name' => 'cart\CartTabs', 'title' => TEXT_CART_TABS, 'description' => '', 'type' => 'sample', 'class' => '');

        return $widgets;
    }

    private static function wishlist()
    {
        $widgets = [];

        $widgets[] = array('name' => 'title', 'title' => TEXT_WISHLIST, 'description' => '', 'type' => 'wishlist');
        $widgets[] = array('name' => 'cart\CartTabs', 'title' => TEXT_CART_TABS, 'description' => '', 'type' => 'wishlist', 'class' => '');
        $widgets[] = array('name' => 'account\Wishlist', 'title' => TEXT_WISHLIST, 'description' => '', 'type' => 'wishlist', 'class' => '');

        return $widgets;
    }

    private static function checkout()
    {
        $widgets = [];

        $widgets[] = array('name' => 'title', 'title' => TEXT_CHECKOUT, 'description' => '', 'type' => 'checkout');
        $widgets[] = array('name' => 'checkout\ContinueBtn', 'title' => CONTINUE_BUTTON, 'description' => '', 'type' => 'checkout', 'class' => '');
        $widgets[] = array('name' => 'checkout\Shipping', 'title' => TEXT_CHOOSE_SHIPPING_METHOD, 'description' => '', 'type' => 'checkout', 'class' => '');
        $widgets[] = array('name' => 'checkout\ShippingAddress', 'title' => ENTRY_SHIPPING_ADDRESS, 'description' => '', 'type' => 'checkout', 'class' => '');
        $widgets[] = array('name' => 'checkout\BillingAddress', 'title' => TEXT_BILLING_ADDRESS, 'description' => '', 'type' => 'checkout', 'class' => '');
        $widgets[] = array('name' => 'checkout\PaymentMethod', 'title' => TEXT_SELECT_PAYMENT_METHOD, 'description' => '', 'type' => 'checkout', 'class' => '');
        $widgets[] = array('name' => 'checkout\CreditAmount', 'title' => CREDIT_AMOUNT, 'description' => '', 'type' => 'checkout', 'class' => '');
        $widgets[] = array('name' => 'checkout\ContactInformation', 'title' => CATEGORY_CONTACT, 'description' => '', 'type' => 'checkout', 'class' => '');
        $widgets[] = array('name' => 'checkout\Comments', 'title' => TABLE_HEADING_COMMENTS, 'description' => '', 'type' => 'checkout', 'class' => '');
        $widgets[] = array('name' => 'checkout\Totals', 'title' => TEXT_TOTALS, 'description' => '', 'type' => 'checkout', 'class' => '');
        $widgets[] = array('name' => 'cart\Products', 'title' => TABLE_HEADING_PRODUCTS, 'description' => '', 'type' => 'checkout', 'class' => '');
        $widgets[] = array('name' => 'quote\Products', 'title' => TEXT_QUOTE_PRODUCTS, 'description' => '', 'type' => 'checkout', 'class' => 'products');
        $widgets[] = array('name' => 'checkout\CreateAccount', 'title' => TEXT_CREATE_ACCOUNT, 'description' => '', 'type' => 'checkout', 'class' => '');
        $widgets[] = array('name' => 'checkout\ShippingChoice', 'title' => TEXT_SHIPPING_CHOICE, 'description' => '', 'type' => 'checkout', 'class' => '');
        $widgets[] = array('name' => 'checkout\Terms', 'title' => TEXT_TERMS_CONDITIONS, 'description' => '', 'type' => 'checkout', 'class' => '');
        $widgets[] = array('name' => 'DeliveryDay', 'title' => DELIVERY_DAY, 'description' => '', 'type' => 'checkout', 'class' => '');
        $widgets[] = array('name' => 'cart\FreeDelivery', 'title' => TEXT_FREE_DELIVERY, 'description' => '', 'type' => 'checkout', 'class' => '');
		$widgets[] = array('name' => 'checkout\LoginOnForm', 'title' => 'Login On Form', 'description' => '', 'type' => 'checkout', 'class' => '');
        $widgets[] = array('name' => 'checkout\PayPalPayLater', 'title' => TEXT_PAYPAL_PARTNER_PAY_LATER_PLAN, 'description' => '', 'type' => 'cart', 'class' => '');

        return $widgets;
    }

    private static function confirmation()
    {
        $widgets = [];

        $widgets[] = array('name' => 'title', 'title' => TEXT_CONFIRMATION, 'description' => '', 'type' => 'confirmation');
        $widgets[] = array('name' => 'checkout\ConfirmBtn', 'title' => TEXT_CONFIRMATION_BUTTON, 'description' => '', 'type' => 'confirmation', 'class' => '');
        $widgets[] = array('name' => 'checkout\ShippingConfirm', 'title' => TEXT_CHOOSE_SHIPPING_METHOD, 'description' => '', 'type' => 'confirmation', 'class' => '');
        $widgets[] = array('name' => 'checkout\ShippingAddressConfirm', 'title' => ENTRY_SHIPPING_ADDRESS, 'description' => '', 'type' => 'confirmation', 'class' => '');
        $widgets[] = array('name' => 'checkout\BillingAddressConfirm', 'title' => TEXT_BILLING_ADDRESS, 'description' => '', 'type' => 'confirmation', 'class' => '');
        $widgets[] = array('name' => 'checkout\PaymentMethodConfirm', 'title' => TEXT_SELECT_PAYMENT_METHOD, 'description' => '', 'type' => 'confirmation', 'class' => '');
        $widgets[] = array('name' => 'checkout\CommentsConfirm', 'title' => TABLE_HEADING_COMMENTS, 'description' => '', 'type' => 'confirmation', 'class' => '');
        $widgets[] = array('name' => 'checkout\Totals', 'title' => TEXT_TOTALS, 'description' => '', 'type' => 'confirmation', 'class' => '');
        $widgets[] = array('name' => 'cart\Products', 'title' => TABLE_HEADING_PRODUCTS, 'description' => '', 'type' => 'confirmation', 'class' => '');
        $widgets[] = array('name' => 'checkout\EditBtn', 'title' => TEXT_EDIT_LINK, 'description' => '', 'type' => 'confirmation', 'class' => '');
        $widgets[] = array('name' => 'quote\Products', 'title' => TEXT_QUOTE_PRODUCTS, 'description' => '', 'type' => 'confirmation', 'class' => 'products');
        $widgets[] = array('name' => 'checkout\ContactConfirm', 'title' => TEXT_CONTACT_INFO, 'description' => '', 'type' => 'confirmation', 'class' => '');

        return $widgets;
    }

    private static function success()
    {
        $widgets = [];

        $widgets[] = array('name' => 'title', 'title' => CHECKOUT_SUCCESS_WIDGETS, 'description' => '', 'type' => 'success');
        $widgets[] = array('name' => 'success\ContinueBtn', 'title' => CONTINUE_BUTTON, 'description' => '', 'type' => 'success', 'class' => 'continue-button');
        $widgets[] = array('name' => 'success\PrintBtn', 'title' => PRINT_BUTTON, 'description' => '', 'type' => 'success', 'class' => 'print-button');
        $widgets[] = array('name' => 'success\Download', 'title' => IMAGE_DOWNLOAD, 'description' => '', 'type' => 'success', 'class' => 'download-button');
        $widgets[] = array('name' => 'order\Products', 'title' => 'Products', 'description' => '', 'type' => 'success', 'class' => '');
        $widgets[] = array('name' => 'order\BillingAddress', 'title' => 'BillingAddress', 'description' => '', 'type' => 'success', 'class' => '');
        $widgets[] = array('name' => 'order\DeliveryAddress', 'title' => 'DeliveryAddress', 'description' => '', 'type' => 'success', 'class' => '');
        $widgets[] = array('name' => 'order\Email', 'title' => 'Customer Email', 'description' => '', 'type' => 'success', 'class' => '');
        $widgets[] = array('name' => 'order\Name', 'title' => 'Customer Name', 'description' => '', 'type' => 'success', 'class' => '');
        $widgets[] = array('name' => 'order\Telephone', 'title' => 'Customer Phone', 'description' => '', 'type' => 'success', 'class' => '');
        $widgets[] = array('name' => 'order\OrderDate', 'title' => 'OrderDate', 'description' => '', 'type' => 'success', 'class' => '');
        $widgets[] = array('name' => 'order\OrderDateTime', 'title' => 'OrderDateTime', 'description' => '', 'type' => 'success', 'class' => '');
        $widgets[] = array('name' => 'order\PaymentMethod', 'title' => 'PaymentMethod', 'description' => '', 'type' => 'success', 'class' => '');
        $widgets[] = array('name' => 'order\ShippingMethod', 'title' => 'ShippingMethod', 'description' => '', 'type' => 'success', 'class' => '');
        $widgets[] = array('name' => 'order\Totals', 'title' => 'Totals', 'description' => '', 'type' => 'success', 'class' => '');
        $widgets[] = array('name' => 'order\OrderNumber', 'title' => 'Order Number', 'description' => '', 'type' => 'success', 'class' => '');

        return $widgets;
    }

    private static function email()
    {
        $widgets = [];

        $widgets[] = array('name' => 'title', 'title' => TABLE_HEADING_EMAIL_TEMPLATES, 'description' => '', 'type' => 'email');
        $widgets[] = array('name' => 'email\Title', 'title' => TEXT_TITLE_, 'description' => '', 'type' => 'email', 'class' => 'title');
        $widgets[] = array('name' => 'email\Date', 'title' => TEXT_CURRENT_DATE, 'description' => '', 'type' => 'email', 'class' => 'date');
        $widgets[] = array('name' => 'email\Content', 'title' => TEXT_CONTENT, 'description' => '', 'type' => 'email', 'class' => 'content');
        $widgets[] = array('name' => 'email\BlockBox', 'title' => TEXT_BLOCK, 'description' => '', 'type' => 'email', 'class' => 'block-box');
        $widgets[] = array('name' => 'Banner', 'title' => TEXT_BANNER, 'description' => '', 'type' => 'email', 'class' => 'banner');
        $widgets[] = array('name' => 'email\Logo', 'title' => TEXT_LOGO, 'description' => '', 'type' => 'email', 'class' => 'logo');
        $widgets[] = array('name' => 'email\Image', 'title' => TEXT_IMAGE_, 'description' => '', 'type' => 'email', 'class' => 'image');
        $widgets[] = array('name' => 'Text', 'title' => TEXT_TEXT, 'description' => '', 'type' => 'email', 'class' => 'text');
        $widgets[] = array('name' => 'Import', 'title' => IMPORT_BLOCK, 'description' => '', 'type' => 'email', 'class' => 'import');
        $widgets[] = array('name' => 'Copyright', 'title' => COPYRIGHT, 'description' => '', 'type' => 'email', 'class' => 'copyright');

        return $widgets;
    }

    private static function orders()
    {
        $widgets = [];

        $widgets[] = array('name' => 'title', 'title' => INVOICE_TEMPLATE, 'description' => '', 'type' => 'invoice');
        $widgets[] = array('name' => 'title', 'title' => TABLE_HEADING_ORDER, 'description' => '', 'type' => 'invoice');
        $widgets[] = array('name' => 'BlockBox', 'title' => TEXT_BLOCK, 'description' => '', 'type' => 'invoice', 'class' => 'block-box');
        $widgets[] = array('name' => 'Logo', 'title' => TEXT_LOGO, 'description' => '', 'type' => 'invoice', 'class' => 'logo');
        $widgets[] = array('name' => 'email\Image', 'title' => TEXT_IMAGE_, 'description' => '', 'type' => 'invoice', 'class' => 'image');
        $widgets[] = array('name' => 'Text', 'title' => TEXT_TEXT, 'description' => '', 'type' => 'invoice', 'class' => 'text');
        $widgets[] = array('name' => 'invoice\Products', 'title' => TABLE_HEADING_PRODUCTS, 'description' => '', 'type' => 'invoice', 'class' => 'products');
        $widgets[] = array('name' => 'invoice\StoreAddress', 'title' => TEXT_STORE_ADDRESS, 'description' => '', 'type' => 'invoice', 'class' => 'store-address');
        $widgets[] = array('name' => 'invoice\CompanyTaxDetails', 'title' => CATEGORY_COMPANY, 'description' => '', 'type' => 'invoice', 'class' => '');
        $widgets[] = array('name' => 'invoice\StorePhone', 'title' => TEXT_STORE_PHONE, 'description' => '', 'type' => 'invoice', 'class' => 'store-phone');
        $widgets[] = array('name' => 'invoice\StoreEmail', 'title' => TEXT_STORE_EMAIL, 'description' => '', 'type' => 'invoice', 'class' => 'store-email');
        $widgets[] = array('name' => 'invoice\StoreSite', 'title' => TEXT_STORE_SITE, 'description' => '', 'type' => 'invoice', 'class' => 'store-site');
        $widgets[] = array('name' => 'invoice\ShippingAddress', 'title' => ENTRY_SHIPPING_ADDRESS, 'description' => '', 'type' => 'invoice', 'class' => 'shipping-address');
        $widgets[] = array('name' => 'invoice\BillingAddress', 'title' => TEXT_BILLING_ADDRESS, 'description' => '', 'type' => 'invoice', 'class' => 'shipping-address');
        $widgets[] = array('name' => 'invoice\ShippingMethod', 'title' => TEXT_CHOOSE_SHIPPING_METHOD, 'description' => '', 'type' => 'invoice', 'class' => 'shipping-method');
        $widgets[] = array('name' => 'invoice\AddressQrcode', 'title' => ADDRESS_QRCODE, 'description' => '', 'type' => 'invoice', 'class' => 'address-qrcode');
        $widgets[] = array('name' => 'invoice\OrderBarcode', 'title' => ORDER_BARCODE, 'description' => '', 'type' => 'invoice', 'class' => 'order-barcode');
        $widgets[] = array('name' => 'invoice\CustomerName', 'title' => TEXT_CUSTOMER_NAME, 'description' => '', 'type' => 'invoice', 'class' => 'customer-name');
        $widgets[] = array('name' => 'invoice\CustomerEmail', 'title' => TEXT_CUSTOMER_EMAIL, 'description' => '', 'type' => 'invoice', 'class' => 'customer-email');
        $widgets[] = array('name' => 'invoice\CustomerPhone', 'title' => TEXT_CUSTOMER_PHONE, 'description' => '', 'type' => 'invoice', 'class' => 'customer-phone');
        $widgets[] = array('name' => 'invoice\Totals', 'title' => TRXT_TOTALS, 'description' => '', 'type' => 'invoice', 'class' => 'totals');
        $widgets[] = array('name' => 'invoice\OrderId', 'title' => TEXT_ORDER_ID, 'description' => '', 'type' => 'invoice', 'class' => 'order-id');
        $widgets[] = array('name' => 'invoice\InvoiceId', 'title' => TEXT_INVOICE_PREFIX."_".TEXT_CREDIT_NOTE_PREFIX, 'description' => '', 'type' => 'invoice', 'class' => 'invoice-id');
        $widgets[] = array('name' => 'invoice\PaymentDate', 'title' => TEXT_PAYMENT_DATE, 'description' => '', 'type' => 'invoice', 'class' => 'payment-date');
        $widgets[] = array('name' => 'invoice\PaymentMethod', 'title' => TEXT_SELECT_PAYMENT_METHOD, 'description' => '', 'type' => 'invoice', 'class' => 'payment-method');
        $widgets[] = array('name' => 'invoice\PaidMark', 'title' => TEXT_PAID_MARK, 'description' => '', 'type' => 'invoice', 'class' => 'paid-mark');
        $widgets[] = array('name' => 'invoice\UnpaidMark', 'title' => TEXT_UNPAID_MARK, 'description' => '', 'type' => 'invoice', 'class' => 'unpaid-mark');
        $widgets[] = array('name' => 'invoice\Container', 'title' => TEXT_CONTAINER, 'description' => '', 'type' => 'invoice', 'class' => 'container');
        $widgets[] = array('name' => 'Import', 'title' => IMPORT_BLOCK, 'description' => '', 'type' => 'invoice', 'class' => 'import');
        $widgets[] = array('name' => 'Copyright', 'title' => COPYRIGHT, 'description' => '', 'type' => 'invoice', 'class' => 'copyright');
        $widgets[] = array('name' => 'invoice\IpAddress', 'title' => TEXT_IP_ADDRESS, 'description' => '', 'type' => 'invoice', 'class' => '');
        $widgets[] = array('name' => 'invoice\TotalProductsQty', 'title' => TEXT_TOTAL_PRODUCTS_QTY, 'description' => '', 'type' => 'invoice', 'class' => '');
        $widgets[] = array('name' => 'invoice\TotalProductsDelivered', 'title' => 'Total Products Delivered', 'description' => '', 'type' => 'invoice', 'class' => '');
        $widgets[] = array('name' => 'invoice\TotalProductsCanceled', 'title' => 'Total Products Canceled', 'description' => '', 'type' => 'invoice', 'class' => '');
        $widgets[] = array('name' => 'invoice\OrderType', 'title' => TEXT_ORDER_TYPE, 'description' => '', 'type' => 'invoice', 'class' => '');
        $widgets[] = array('name' => 'invoice\Transactions', 'title' => TEXT_TRANSACTIONS, 'description' => '', 'type' => 'invoice', 'class' => '');
        $widgets[] = array('name' => 'invoice\PurchaseOrderNo', 'title' => 'Purchase Order Number', 'description' => '', 'type' => 'invoice', 'class' => '');
        $widgets[] = array('name' => 'invoice\Comments', 'title' => 'Comments', 'description' => '', 'type' => 'invoice', 'class' => '');
        $widgets[] = array('name' => 'invoice\Currency', 'title' => 'Currency', 'description' => '', 'type' => 'invoice', 'class' => '');
        $widgets[] = array('name' => 'order\OrderDate', 'title' => 'Order Date', 'description' => '', 'type' => 'invoice', 'class' => '');
        $widgets[] = array('name' => 'order\OrderDateTime', 'title' => 'Order Date and Time', 'description' => '', 'type' => 'invoice', 'class' => '');
        $widgets[] = array('name' => 'order\PageNumber', 'title' => 'Page Number', 'description' => '', 'type' => 'invoice', 'class' => '');

        return $widgets;
    }

    private static function packingslip()
    {
        $widgets = [];

        $widgets[] = array('name' => 'title', 'title' => TEXT_PACKINGSLIP, 'description' => '', 'type' => 'packingslip');
        $widgets[] = array('name' => 'order\OrderDate', 'title' => 'Order Date', 'description' => '', 'type' => 'packingslip', 'class' => '');
        $widgets[] = array('name' => 'BlockBox', 'title' => TEXT_BLOCK, 'description' => '', 'type' => 'packingslip', 'class' => 'block-box');
        $widgets[] = array('name' => 'email\Image', 'title' => TEXT_IMAGE_, 'description' => '', 'type' => 'packingslip', 'class' => 'image');
        $widgets[] = array('name' => 'Text', 'title' => TEXT_TEXT, 'description' => '', 'type' => 'packingslip', 'class' => 'text');
        $widgets[] = array('name' => 'packingslip\Products', 'title' => TABLE_HEADING_PRODUCTS, 'description' => '', 'type' => 'packingslip', 'class' => 'products');
        $widgets[] = array('name' => 'invoice\StoreAddress', 'title' => TEXT_STORE_ADDRESS, 'description' => '', 'type' => 'packingslip', 'class' => 'store-address');
        $widgets[] = array('name' => 'invoice\CompanyTaxDetails', 'title' => CATEGORY_COMPANY, 'description' => '', 'type' => 'invoice', 'class' => '');
        $widgets[] = array('name' => 'invoice\ShippingMethod', 'title' => TEXT_CHOOSE_SHIPPING_METHOD, 'description' => '', 'type' => 'packingslip', 'class' => 'shipping-method');
        $widgets[] = array('name' => 'invoice\StorePhone', 'title' => TEXT_STORE_PHONE, 'description' => '', 'type' => 'packingslip', 'class' => 'store-phone');
        $widgets[] = array('name' => 'invoice\StoreEmail', 'title' => TEXT_STORE_EMAIL, 'description' => '', 'type' => 'packingslip', 'class' => 'store-email');
        $widgets[] = array('name' => 'invoice\StoreSite', 'title' => TEXT_STORE_SITE, 'description' => '', 'type' => 'packingslip', 'class' => 'store-site');
        $widgets[] = array('name' => 'invoice\ShippingAddress', 'title' => ENTRY_SHIPPING_ADDRESS, 'description' => '', 'type' => 'packingslip', 'class' => 'shipping-address');
        $widgets[] = array('name' => 'invoice\BillingAddress', 'title' => TEXT_BILLING_ADDRESS, 'description' => '', 'type' => 'packingslip', 'class' => 'shipping-address');
        $widgets[] = array('name' => 'invoice\AddressQrcode', 'title' => ADDRESS_QRCODE, 'description' => '', 'type' => 'packingslip', 'class' => 'address-qrcode');
        $widgets[] = array('name' => 'invoice\OrderBarcode', 'title' => ORDER_BARCODE, 'description' => '', 'type' => 'packingslip', 'class' => 'order-barcode');
        $widgets[] = array('name' => 'invoice\CustomerName', 'title' => TEXT_CUSTOMER_NAME, 'description' => '', 'type' => 'packingslip', 'class' => 'customer-name');
        $widgets[] = array('name' => 'invoice\CustomerEmail', 'title' => TEXT_CUSTOMER_EMAIL, 'description' => '', 'type' => 'packingslip', 'class' => 'customer-email');
        $widgets[] = array('name' => 'invoice\CustomerPhone', 'title' => TEXT_CUSTOMER_PHONE, 'description' => '', 'type' => 'packingslip', 'class' => 'customer-phone');
        $widgets[] = array('name' => 'invoice\OrderId', 'title' => TEXT_ORDER_ID, 'description' => '', 'type' => 'packingslip', 'class' => 'order-id');
        $widgets[] = array('name' => 'invoice\PaymentMethod', 'title' => TEXT_SELECT_PAYMENT_METHOD, 'description' => '', 'type' => 'packingslip', 'class' => 'payment-method');
        $widgets[] = array('name' => 'invoice\Container', 'title' => TEXT_CONTAINER, 'description' => '', 'type' => 'packingslip', 'class' => 'container');
        $widgets[] = array('name' => 'Import', 'title' => IMPORT_BLOCK, 'description' => '', 'type' => 'packingslip', 'class' => 'import');
        $widgets[] = array('name' => 'Copyright', 'title' => COPYRIGHT, 'description' => '', 'type' => 'packingslip', 'class' => 'copyright');
        $widgets[] = array('name' => 'invoice\IpAddress', 'title' => TEXT_IP_ADDRESS, 'description' => '', 'type' => 'packingslip', 'class' => '');
        $widgets[] = array('name' => 'invoice\TotalProductsQty', 'title' => TEXT_TOTAL_PRODUCTS_QTY, 'description' => '', 'type' => 'packingslip', 'class' => '');
        $widgets[] = array('name' => 'invoice\TotalProductsDelivered', 'title' => 'Total Products Delivered', 'description' => '', 'type' => 'packingslip', 'class' => '');
        $widgets[] = array('name' => 'invoice\TotalProductsCanceled', 'title' => 'Total Products Canceled', 'description' => '', 'type' => 'packingslip', 'class' => '');
        $widgets[] = array('name' => 'invoice\OrderType', 'title' => TEXT_ORDER_TYPE, 'description' => '', 'type' => 'packingslip', 'class' => '');
        $widgets[] = array('name' => 'invoice\Transactions', 'title' => TEXT_TRANSACTIONS, 'description' => '', 'type' => 'packingslip', 'class' => '');
        $widgets[] = array('name' => 'invoice\PurchaseOrderNo', 'title' => 'Purchase Order Number', 'description' => '', 'type' => 'packingslip', 'class' => '');
        $widgets[] = array('name' => 'invoice\Comments', 'title' => 'Comments', 'description' => '', 'type' => 'packingslip', 'class' => '');
        $widgets[] = array('name' => 'invoice\Currency', 'title' => 'Currency', 'description' => '', 'type' => 'packingslip', 'class' => '');

        return $widgets;
    }

    private static function gift()
    {
        $widgets = [];

        $widgets[] = array('name' => 'title', 'title' => TEXT_GIFT_CARD, 'description' => '', 'type' => 'gift');
        $widgets[] = array('name' => 'gift\Form', 'title' => TEXT_FORM, 'description' => '', 'type' => 'gift', 'class' => 'contact-form');
        $widgets[] = array('name' => 'gift\AmountView', 'title' => AMOUNT_VIEW, 'description' => '', 'type' => 'gift', 'class' => 'contact-form');
        $widgets[] = array('name' => 'gift\MessageView', 'title' => MESSAGE_VIEW, 'description' => '', 'type' => 'gift', 'class' => 'contact-form');
        $widgets[] = array('name' => 'gift\CodeView', 'title' => CODE_VIEW, 'description' => '', 'type' => 'gift', 'class' => 'contact-form');
        $widgets[] = array('name' => 'invoice\StoreAddress', 'title' => TEXT_STORE_ADDRESS, 'description' => '', 'type' => 'gift', 'class' => 'store-address');
        $widgets[] = array('name' => 'invoice\CompanyTaxDetails', 'title' => CATEGORY_COMPANY, 'description' => '', 'type' => 'invoice', 'class' => '');
        $widgets[] = array('name' => 'invoice\StorePhone', 'title' => TEXT_STORE_PHONE, 'description' => '', 'type' => 'gift', 'class' => 'store-phone');
        $widgets[] = array('name' => 'invoice\StoreEmail', 'title' => TEXT_STORE_EMAIL, 'description' => '', 'type' => 'gift', 'class' => 'store-email');
        $widgets[] = array('name' => 'invoice\StoreSite', 'title' => TEXT_STORE_SITE, 'description' => '', 'type' => 'gift', 'class' => 'store-site');
        $widgets[] = array('name' => 'info\Title', 'title' => TEXT_TITLE_, 'description' => '', 'type' => 'gift', 'class' => 'title');
        $widgets[] = array('name' => 'gift\Card', 'title' => TEXT_GIFT_CARD, 'description' => '', 'type' => 'gift', 'class' => 'title');

        return $widgets;
    }

    private static function gift_card()
    {
        $widgets = [];

        $widgets[] = array('name' => 'title', 'title' => TEXT_GIFT_CARD, 'description' => '', 'type' => 'gift');
        $widgets[] = array('name' => 'gift\Form', 'title' => TEXT_FORM, 'description' => '', 'type' => 'gift', 'class' => 'contact-form');
        $widgets[] = array('name' => 'gift\AmountView', 'title' => AMOUNT_VIEW, 'description' => '', 'type' => 'gift', 'class' => 'contact-form');
        $widgets[] = array('name' => 'gift\MessageView', 'title' => MESSAGE_VIEW, 'description' => '', 'type' => 'gift', 'class' => 'contact-form');
        $widgets[] = array('name' => 'gift\CodeView', 'title' => CODE_VIEW, 'description' => '', 'type' => 'gift', 'class' => 'contact-form');
        $widgets[] = array('name' => 'invoice\StoreAddress', 'title' => TEXT_STORE_ADDRESS, 'description' => '', 'type' => 'gift', 'class' => 'store-address');
        $widgets[] = array('name' => 'invoice\CompanyTaxDetails', 'title' => CATEGORY_COMPANY, 'description' => '', 'type' => 'invoice', 'class' => '');
        $widgets[] = array('name' => 'invoice\StorePhone', 'title' => TEXT_STORE_PHONE, 'description' => '', 'type' => 'gift', 'class' => 'store-phone');
        $widgets[] = array('name' => 'invoice\StoreEmail', 'title' => TEXT_STORE_EMAIL, 'description' => '', 'type' => 'gift', 'class' => 'store-email');
        $widgets[] = array('name' => 'invoice\StoreSite', 'title' => TEXT_STORE_SITE, 'description' => '', 'type' => 'gift', 'class' => 'store-site');
        $widgets[] = array('name' => 'info\Title', 'title' => TEXT_TITLE_, 'description' => '', 'type' => 'gift', 'class' => 'title');

        $widgets[] = array('name' => 'gift\AmountViewPdf', 'title' => AMOUNT_VIEW . ' pdf', 'description' => '', 'type' => 'gift', 'class' => 'contact-form');
        $widgets[] = array('name' => 'gift\MessageViewPdf', 'title' => MESSAGE_VIEW . ' pdf', 'description' => '', 'type' => 'gift', 'class' => 'contact-form');
        $widgets[] = array('name' => 'gift\CodeViewPdf', 'title' => CODE_VIEW . ' pdf', 'description' => '', 'type' => 'gift', 'class' => 'contact-form');

        return $widgets;
    }

    private static function main()
    {
        $widgets = [];

        $widgets[] = array('name' => 'title', 'title' => GENERAL_WIDGETS, 'description' => '', 'type' => 'general');
        $widgets[] = array('name' => 'BlockBox', 'title' => TEXT_BLOCK, 'description' => '', 'type' => 'general', 'class' => 'block-box');
        $widgets[] = array('name' => 'Tabs', 'title' => TEXT_TABS, 'description' => '', 'type' => 'general', 'class' => 'tabs');
        $widgets[] = array('name' => 'Brands', 'title' => TEXT_BRANDS, 'description' => '', 'type' => 'general', 'class' => 'brands');
        $widgets[] = array('name' => 'Bestsellers', 'title' => TEXT_BESTSELLERS, 'description' => '', 'type' => 'general', 'class' => 'bestsellers');
        $widgets[] = array('name' => 'Banner', 'title' => TEXT_BANNER, 'description' => '', 'type' => 'general', 'class' => 'banner');
        $widgets[] = array('name' => 'SpecialsProducts', 'title' => TEXT_SPECIALS_PRODUCTS, 'description' => '', 'type' => 'general', 'class' => 'specials-products');
        $widgets[] = array('name' => 'FeaturedProducts', 'title' => BOX_CATALOG_FEATURED, 'description' => '', 'type' => 'general', 'class' => 'featured-products');
        $widgets[] = array('name' => 'NewProducts', 'title' => TEXT_NEW_PRODUCTS, 'description' => '', 'type' => 'general', 'class' => 'new-products');
        $widgets[] = array('name' => 'NewProductsWithParams', 'title' => TEXT_NEW_PRODUCTS_PARAMS, 'description' => '', 'type' => 'general', 'class' => 'new-products-params');
        $widgets[] = array('name' => 'ViewedProducts', 'title' => VIEWED_PRODUCTS, 'description' => '', 'type' => 'general', 'class' => 'viewed-products');
        $widgets[] = array('name' => 'Logo', 'title' => TEXT_LOGO, 'description' => '', 'type' => 'general', 'class' => 'logo');
        $widgets[] = array('name' => 'Image', 'title' => TEXT_IMAGE_, 'description' => '', 'type' => 'general', 'class' => 'image');
        $widgets[] = array('name' => 'Video', 'title' => TEXT_VIDEO, 'description' => '', 'type' => 'general', 'class' => 'video');
        $widgets[] = array('name' => 'Text', 'title' => TEXT_TEXT, 'description' => '', 'type' => 'general', 'class' => 'text');
        $widgets[] = array('name' => 'Heading', 'title' => TEXT_SEO_HEADING, 'description' => '', 'type' => 'general', 'class' => '');
        $widgets[] = array('name' => 'InfoPage', 'title' => INFORMATION_PAGES, 'description' => '', 'type' => 'general', 'class' => 'text');
        $widgets[] = array('name' => 'Reviews', 'title' => TEXT_REVIEWS, 'description' => '', 'type' => 'general', 'class' => 'reviews');
        $widgets[] = array('name' => 'Menu', 'title' => TEXT_MENU, 'description' => '', 'type' => 'general', 'class' => 'menu');
        $widgets[] = array('name' => 'Languages', 'title' => TEXT_LANGUAGES_, 'description' => '', 'type' => 'general', 'class' => 'languages');
        $widgets[] = array('name' => 'Currencies', 'title' => TEXT_CURRENCIES, 'description' => '', 'type' => 'general', 'class' => 'currencies');
        $widgets[] = array('name' => 'Search', 'title' => TEXT_SEARCH, 'description' => '', 'type' => 'general', 'class' => 'search');
        $widgets[] = array('name' => 'Cart', 'title' => TEXT_CART, 'description' => '', 'type' => 'general', 'class' => 'cart');
        $widgets[] = array('name' => 'Breadcrumb', 'title' => TEXT_BREADCRUMB, 'description' => '', 'type' => 'general', 'class' => 'breadcrumb');
        $widgets[] = array('name' => 'Compare', 'title' => TEXT_COMPARE, 'description' => '', 'type' => 'general', 'class' => 'compare');
        //$widgets[] = array('name' => 'Address', 'title' => 'Store Address', 'description' => '', 'type' => 'general', 'class' => 'contacts');
        //$widgets[] = array('name' => 'invoice\StoreAddress', 'title' => TEXT_STORE_ADDRESS, 'description' => '', 'type' => 'general', 'class' => 'store-address');
        $widgets[] = array('name' => 'Copyright', 'title' => COPYRIGHT, 'description' => '', 'type' => 'general', 'class' => 'copyright');
        $widgets[] = array('name' => 'Account', 'title' => TEXT_ACCOUNT, 'description' => '', 'type' => 'general', 'class' => 'account');
        $widgets[] = array('name' => 'Import', 'title' => IMPORT_BLOCK, 'description' => '', 'type' => 'general', 'class' => 'import');

        if (\frontend\design\Info::hasBlog()){
            $widgets[] = array('name' => 'BlogSidebar', 'title' => TEXT_BLOG_SIDEBAR, 'description' => '', 'type' => 'general', 'class' => 'menu');
            $widgets[] = array('name' => 'BlogContent', 'title' => TEXT_BLOG_CONTENT, 'description' => '', 'type' => 'general', 'class' => 'content');
        }
        $widgets[] = array('name' => 'Quote', 'title' => TEXT_QUOTE_CART, 'description' => '', 'type' => 'general', 'class' => 'quote');
        $widgets[] = array('name' => 'StoreName', 'title' => TEXT_STORE_NAME, 'description' => '', 'type' => 'general', 'class' => '');
        $widgets[] = array('name' => 'WidgetsAria', 'title' => 'Widgets Aria', 'description' => '', 'type' => 'general', 'class' => '');
        $widgets[] = array('name' => 'GoogleReviews', 'title' => TEXT_GOOGLE_REVIEWS, 'description' => '', 'type' => 'general', 'class' => 'contact-form');
        $widgets[] = array('name' => 'CustomerData', 'title' => TEXT_CUSTOMER_DATA, 'description' => '', 'type' => 'general', 'class' => '');
        //$widgets[] = array('name' => 'ProductElement', 'title' => TEXT_PRODUCT_ELEMENT, 'description' => '', 'type' => 'general', 'class' => ''); //this widget has to parent product component

        if (\common\helpers\Acl::checkExtensionAllowed('Trustpilot', 'allowed')) {
            $client = new \common\extensions\Trustpilot\Trustpilot();
            if ($client->anyAPIKeyExists()){
                $widgets[] = array('name' => 'TrustPilotReviews', 'title' => EXT_TRUSTPILOT_TRUSTPILOT_REVIEWS, 'description' => '', 'type' => 'general', 'class' => 'content');
            }
        }
        $widgets[] = array('name' => 'SocialLinks', 'title' => TEXT_SOCIAL_LINKS, 'description' => '', 'type' => 'general', 'class' => '');
        $widgets[] = array('name' => 'FiltersSimple', 'title' => FILTERS_SIMPLE, 'description' => '', 'type' => 'general', 'class' => '');
        //$widgets[] = array('name' => 'CartPopUp', 'title' => 'CartPopUp', 'description' => '', 'type' => 'general', 'class' => '');

        $widgets[] = array('name' => 'BatchProducts', 'title' => TEXT_WIDGET_BATCH_PRODUCTS, 'description' => '', 'type' => 'general', 'class' => '');
        $widgets[] = array('name' => 'BatchSelectedProducts', 'title' => TEXT_WIDGET_BATCH_SELECTED_PRODUCTS, 'description' => '', 'type' => 'general', 'class' => '');
        $widgets[] = array('name' => 'TopText', 'title' => TEXT_TOP_TEXT, 'description' => '', 'type' => 'general', 'class' => 'toptext');
        //$widgets[] = array('name' => 'UnsupportedBrowser', 'title' => TEXT_UNSUPPORTED_BROWSER, 'description' => '', 'type' => 'general', 'class' => '');

        $widgets[] = array('name' => 'VisitorCountry', 'title' => TEXT_VISITOR_COUNTRY, 'description' => '', 'type' => 'general', 'class' => 'account');

        //$widgets[] = array('name' => 'Wristband', 'title' => 'wristband', 'description' => '', 'type' => 'general');

        //Committed because not stylized
        $widgets[] = array('name' => 'CatalogPages\CategoryPagesList', 'title' => TEXT_WIDGET_CATEGORY_PAGE, 'description' => TEXT_WIDGET_CATEGORY_PAGE, 'type' => 'general', 'class' => 'delivery-location-products');
        $widgets[] = array('name' => 'CatalogPages\CategoryPagesLastList', 'title' => TEXT_WIDGET_CATEGORY_PAGE_LAST_LIST, 'description' => TEXT_WIDGET_CATEGORY_PAGE_LAST_LIST, 'type' => 'general', 'class' => 'delivery-location-products');
        $widgets[] = array('name' => 'CatalogPages\CategoryPagesLastListByCatalog', 'title' => TEXT_WIDGET_CATEGORY_PAGE_LAST_LIST_BY_CATALOG, 'description' => TEXT_WIDGET_CATEGORY_PAGE_LAST_LIST_BY_CATALOG, 'type' => 'general', 'class' => 'delivery-location-products');
        $widgets[] = array('name' => 'CatalogPages\CategoryPagesLastListBlock', 'title' => TEXT_WIDGET_CATEGORY_PAGE_LAST_LIST_BLOCK, 'description' => TEXT_WIDGET_CATEGORY_PAGE_LAST_LIST_BLOCK, 'type' => 'general', 'class' => 'delivery-location-products');
        $widgets[] = array('name' => 'CatalogPages\CategoryPagesLastListByCatalogBlock', 'title' => TEXT_WIDGET_CATEGORY_PAGE_LAST_LIST_BY_CATALOG_BLOCK, 'description' => TEXT_WIDGET_CATEGORY_PAGE_LAST_LIST_BY_CATALOG_BLOCK, 'type' => 'general', 'class' => 'delivery-location-products');

        return $widgets;
    }

    private static function pdf()
    {
        $widgets = [];

        $widgets[] = array('name' => 'BlockBox', 'title' => TEXT_BLOCK, 'description' => '', 'type' => 'pdf', 'class' => 'block-box');
        $widgets[] = array('name' => 'Logo', 'title' => TEXT_LOGO, 'description' => '', 'type' => 'pdf', 'class' => 'logo');
        $widgets[] = array('name' => 'Image', 'title' => TEXT_IMAGE_, 'description' => '', 'type' => 'pdf', 'class' => 'image');
        $widgets[] = array('name' => 'Text', 'title' => TEXT_TEXT, 'description' => '', 'type' => 'pdf', 'class' => 'text');
        $widgets[] = array('name' => 'invoice\StoreAddress', 'title' => TEXT_STORE_ADDRESS, 'description' => '', 'type' => 'pdf', 'class' => 'store-address');
        $widgets[] = array('name' => 'invoice\CompanyTaxDetails', 'title' => CATEGORY_COMPANY, 'description' => '', 'type' => 'invoice', 'class' => '');
        //$widgets[] = array('name' => 'Copyright', 'title' => COPYRIGHT, 'description' => '', 'type' => 'pdf', 'class' => 'copyright');
        $widgets[] = array('name' => 'invoice\StorePhone', 'title' => TEXT_STORE_PHONE, 'description' => '', 'type' => 'pdf', 'class' => 'store-phone');
        $widgets[] = array('name' => 'invoice\StoreEmail', 'title' => TEXT_STORE_EMAIL, 'description' => '', 'type' => 'pdf', 'class' => 'store-email');
        $widgets[] = array('name' => 'invoice\StoreSite', 'title' => TEXT_STORE_SITE, 'description' => '', 'type' => 'pdf', 'class' => 'store-site');
        $widgets[] = array('name' => 'invoice\Container', 'title' => TEXT_CONTAINER, 'description' => '', 'type' => 'pdf', 'class' => 'container');
        $widgets[] = array('name' => 'pdf\ProductElement', 'title' => TEXT_PRODUCT_ELEMENT, 'description' => '', 'type' => 'pdf', 'class' => '');
        $widgets[] = array('name' => 'pdf\CategoryName', 'title' => CATEGORY_NAME, 'description' => '', 'type' => 'pdf', 'class' => '');
        $widgets[] = array('name' => 'pdf\CategoryImage', 'title' => TEXT_CATEGORY_IMAGE, 'description' => '', 'type' => 'pdf', 'class' => '');
        $widgets[] = array('name' => 'pdf\CategoryDescription', 'title' => TEXT_CATEGORY_DESCRIPTION, 'description' => '', 'type' => 'pdf', 'class' => '');
        $widgets[] = array('name' => 'pdf\PageNumber', 'title' => TEXT_PAGE_NUMBER, 'description' => '', 'type' => 'pdf', 'class' => '');
        $widgets[] = array('name' => 'Import', 'title' => IMPORT_BLOCK, 'description' => '', 'type' => 'pdf', 'class' => 'import');

        return $widgets;
    }

    private static function account()
    {
        $widgets = [];

        $widgets[] = array('name' => 'title', 'title' => TEXT_ACCOUNT, 'description' => '', 'type' => 'account');
        $widgets[] = array('name' => 'account\AccountLink', 'title' => ACCOUNT_LINK, 'description' => '', 'type' => 'account', 'class' => '');
        $widgets[] = array('name' => 'account\LastOrder', 'title' => DATE_LAST_ORDERED, 'description' => '', 'type' => 'account', 'class' => '');
        $widgets[] = array('name' => 'account\OrderCount', 'title' => ORDER_COUNT, 'description' => '', 'type' => 'account', 'class' => '');
        $widgets[] = array('name' => 'account\TotalOrdered', 'title' => TOTAL_ORDERED, 'description' => '', 'type' => 'account', 'class' => '');
        $widgets[] = array('name' => 'account\CreditAmount', 'title' => CREDIT_AMOUNT, 'description' => '', 'type' => 'account', 'class' => '');
        $widgets[] = array('name' => 'account\CreditAmountHistory', 'title' => CREDIT_AMOUNT_HISTORY, 'description' => '', 'type' => 'account', 'class' => '');
        $widgets[] = array('name' => 'account\ApplyCertificate', 'title' => APPLY_CERTIFICATE_FORM, 'description' => '', 'type' => 'account', 'class' => '');
        $widgets[] = array('name' => 'account\CustomerData', 'title' => TEXT_CUSTOMER_DATA, 'description' => '', 'type' => 'account', 'class' => '');
        $widgets[] = array('name' => 'account\AccountEdit', 'title' => EDIT_MAIN_DETAILS, 'description' => '', 'type' => 'account', 'class' => '');
        $widgets[] = array('name' => 'account\ChangePassword', 'title' => TEXT_CHANGE_PASSWORD, 'description' => '', 'type' => 'account', 'class' => '');
        $widgets[] = array('name' => 'account\PrimaryAddress', 'title' => TEXT_PRIMARY_ADDRESS, 'description' => '', 'type' => 'account', 'class' => '');
        $widgets[] = array('name' => 'account\AddressBook', 'title' => TEXT_ADDRESS_BOOK, 'description' => '', 'type' => 'account', 'class' => '');
        $widgets[] = array('name' => 'account\EditAddress', 'title' => TEXT_EDIT_ADDRESS, 'description' => '', 'type' => 'account', 'class' => '');
        $widgets[] = array('name' => 'account\Tokens', 'title' => TEXT_TOKENS, 'description' => '', 'type' => 'account', 'class' => '');
        $widgets[] = array('name' => 'account\OrdersHistory', 'title' => TEXT_ORDERS_HISTORY, 'description' => '', 'type' => 'account', 'class' => '');
        $widgets[] = array('name' => 'account\OrderData', 'title' => TEXT_ORDER_DATA, 'description' => '', 'type' => 'account', 'class' => '');
        $widgets[] = array('name' => 'account\OrderProducts', 'title' => ORDER_PRODUCTS, 'description' => '', 'type' => 'account', 'class' => '');
        $widgets[] = array('name' => 'account\OrderSubTotals', 'title' => ORDER_SUBTOTALS, 'description' => '', 'type' => 'account', 'class' => '');
        $widgets[] = array('name' => 'account\OrderHistory', 'title' => ORDER_HISTORY, 'description' => '', 'type' => 'account', 'class' => '');
        $widgets[] = array('name' => 'account\OrderInvoiceButton', 'title' => ORDER_INVOICE_BUTTON, 'description' => '', 'type' => 'account', 'class' => '');
        $widgets[] = array('name' => 'account\OrderNotPaid', 'title' => ORDER_NOT_PAID, 'description' => '', 'type' => 'account', 'class' => '');
        $widgets[] = array('name' => 'account\OrderPayButton', 'title' => ORDER_PAY_BUTTON, 'description' => '', 'type' => 'account', 'class' => '');
        $widgets[] = array('name' => 'account\OrderReorderButton', 'title' => REORDER_BUTTON, 'description' => '', 'type' => 'account', 'class' => '');
        $widgets[] = array('name' => 'account\OrderDownload', 'title' => IMAGE_DOWNLOAD, 'description' => '', 'type' => 'account', 'class' => '');
        $widgets[] = array('name' => 'account\OrderCancelAndReorder', 'title' => CANCEL_AND_REORDER_BUTTON, 'description' => '', 'type' => 'account', 'class' => '');
        //$widgets[] = array('name' => 'account\Wishlist', 'title' => TEXT_WISHLIST, 'description' => '', 'type' => 'account', 'class' => '');
        $widgets[] = array('name' => 'account\Reviews', 'title' => BOX_CATALOG_REVIEWS, 'description' => '', 'type' => 'account', 'class' => '');
        $widgets[] = array('name' => 'account\OrderHeading', 'title' => TEXT_ORDER_HEADING, 'description' => '', 'type' => 'account', 'class' => '');
        $widgets[] = array('name' => 'account\OrderTracking', 'title' => TEXT_ORDER_TRACKING, 'description' => '', 'type' => 'account', 'class' => '');
        $widgets[] = array('name' => 'account\Subscription', 'title' => 'Subscription', 'description' => '', 'type' => 'account', 'class' => '');
        $widgets[] = array('name' => 'account\GiftCards', 'title' => 'Gift cards', 'description' => '', 'type' => 'account', 'class' => '');
        $widgets[] = array('name' => 'account\SpendBalance', 'title' => TEXT_SPEND_BALANCE, 'description' => '', 'type' => 'account', 'class' => '');

        return $widgets;
    }

    private static function trade_form()
    {
        $widgets = [];

        $widgets[] = array('name' => 'account\CustomerAdditionalField', 'title' => 'CustomerAdditionalField', 'description' => '', 'type' => 'trade_form', 'class' => '');
        $widgets[] = array('name' => 'account\BackButton', 'title' => 'BackButton', 'description' => '', 'type' => 'trade_form', 'class' => '');
        $widgets[] = array('name' => 'account\SaveButton', 'title' => 'SaveButton', 'description' => '', 'type' => 'trade_form', 'class' => '');
        $widgets[] = array('name' => 'account\PdfButton', 'title' => 'PdfButton', 'description' => '', 'type' => 'trade_form', 'class' => '');
        $widgets[] = array('name' => 'account\AddressesList', 'title' => 'AddressesList', 'description' => '', 'type' => 'trade_form', 'class' => '');

        return $widgets;
    }

    private static function trade_form_pdf()
    {
        $widgets = [];

        $widgets[] = array('name' => 'account\CustomerAdditionalField', 'title' => 'CustomerAdditionalField', 'description' => '', 'type' => 'trade_form', 'class' => '');
        $widgets[] = array('name' => 'account\BackButton', 'title' => 'BackButton', 'description' => '', 'type' => 'trade_form', 'class' => '');
        $widgets[] = array('name' => 'account\SaveButton', 'title' => 'SaveButton', 'description' => '', 'type' => 'trade_form', 'class' => '');
        $widgets[] = array('name' => 'account\PdfButton', 'title' => 'PdfButton', 'description' => '', 'type' => 'trade_form', 'class' => '');
        $widgets[] = array('name' => 'account\AddressesList', 'title' => 'AddressesList', 'description' => '', 'type' => 'trade_form', 'class' => '');
        $widgets[] = array('name' => 'account\CombinedField', 'title' => 'CombinedField', 'description' => '', 'type' => 'trade_form', 'class' => '');

        return $widgets;
    }

    private static function login()
    {
        $widgets = [];

        $widgets[] = array('name' => 'title', 'title' => 'Login', 'description' => '', 'type' => 'login');
        $widgets[] = array('name' => 'login\Returning', 'title' => 'Returning customer', 'description' => '', 'type' => 'login', 'class' => '');
        $widgets[] = array('name' => 'login\Register', 'title' => 'Register', 'description' => '', 'type' => 'login', 'class' => '');
        $widgets[] = array('name' => 'login\Socials', 'title' => 'Socials login', 'description' => '', 'type' => 'login', 'class' => '');
        //$widgets[] = array('name' => 'login\Guest', 'title' => 'Guest login', 'description' => '', 'type' => 'login', 'class' => '');
        $widgets[] = array('name' => 'quote\FastOrder', 'title' => 'Fast Order', 'description' => '', 'type' => 'login', 'class' => '');
        $widgets[] = array('name' => 'checkout\GuestBtn', 'title' => 'Guest Button', 'description' => '', 'type' => 'login', 'class' => '');
        $widgets[] = array('name' => 'checkout\CreateBtn', 'title' => 'Create Button', 'description' => '', 'type' => 'login', 'class' => '');

        return $widgets;
    }

    private static function password_forgotten()
    {
        $widgets = [];

        $widgets[] = array('name' => 'login\PasswordForgotten', 'title' => 'Password Forgotten', 'description' => '', 'type' => 'login', 'class' => '');

        return $widgets;
    }

    private static function index()
    {
        $widgets = [];

        $widgets[] = array('name' => 'title', 'title' => HOME_PAGE_WIDGETS, 'description' => '', 'type' => 'index');
        $widgets[] = array('name' => 'TopCategories', 'title' => TEXT_CATEGORIES, 'description' => '', 'type' => 'index', 'class' => 'categories');
        $widgets[] = array('name' => 'login\Returning', 'title' => 'Returning customer', 'description' => '', 'type' => 'index', 'class' => 'categories');
        $widgets[] = array('name' => 'login\Register', 'title' => 'Register', 'description' => '', 'type' => 'index', 'class' => 'categories');
        //$widgets[] = array('name' => 'login\Enquire', 'title' => 'Enquire', 'description' => '', 'type' => 'index', 'class' => 'categories');

        return $widgets;
    }

    private static function sitemap()
    {
        $widgets = [];

        $widgets[] = array('name' => 'sitemap\Categories', 'title' => 'Categories', 'description' => '', 'type' => 'sitemap', 'class' => '');
        $widgets[] = array('name' => 'sitemap\InfoPages', 'title' => 'Info Pages', 'description' => '', 'type' => 'sitemap', 'class' => '');

        return $widgets;
    }

    private static function reviews()
    {
        $widgets = [];

        $widgets[] = array('name' => 'reviews\Heading', 'title' => 'Heading', 'description' => '', 'type' => 'reviews', 'class' => '');
        $widgets[] = array('name' => 'reviews\Content', 'title' => 'Content', 'description' => '', 'type' => 'reviews', 'class' => '');

        return $widgets;
    }

    private static function compare()
    {
        $widgets = [];

        $widgets[] = array('name' => 'catalog\Compare', 'title' => TEXT_COMPARE, 'description' => '', 'type' => 'compare', 'class' => '');

        return $widgets;
    }

    private static function productListing()
    {
        $widgets = [];

        $widgets[] = array('name' => 'title', 'title' => TEXT_LISTING_ITEM, 'description' => '', 'type' => 'productListing');
        $widgets[] = array('name' => 'productListing\name', 'title' => TEXT_PRODUCT_NAME, 'description' => '', 'type' => 'productListing', 'class' => '');
        $widgets[] = array('name' => 'productListing\image', 'title' => TEXT_IMAGE, 'description' => '', 'type' => 'productListing', 'class' => '');
        $widgets[] = array('name' => 'productListing\stock', 'title' => BOX_SETTINGS_BOX_STOCK_INDICATION, 'description' => '', 'type' => 'productListing', 'class' => '');
        $widgets[] = array('name' => 'productListing\description', 'title' => TEXT_PRODUCTS_DESCRIPTION_SHORT, 'description' => '', 'type' => 'productListing', 'class' => '');
        $widgets[] = array('name' => 'productListing\model', 'title' => TEXT_MODEL, 'description' => '', 'type' => 'productListing', 'class' => '');
        $widgets[] = array('name' => 'productListing\properties', 'title' => TEXT_PRODUCTS_PROPERTIES, 'description' => '', 'type' => 'productListing', 'class' => '');
        $widgets[] = array('name' => 'productListing\rating', 'title' => TEXT_RATING, 'description' => '', 'type' => 'productListing', 'class' => '');
        $widgets[] = array('name' => 'productListing\ratingCounts', 'title' => TEXT_RATING_COUNTS, 'description' => '', 'type' => 'productListing', 'class' => '');
        $widgets[] = array('name' => 'productListing\price', 'title' => TABLE_HEADING_PRODUCTS_PRICE, 'description' => '', 'type' => 'productListing', 'class' => '');
        $widgets[] = array('name' => 'productListing\priceFrom', 'title' => 'Price From', 'description' => '', 'type' => 'productListing', 'class' => '');
        $widgets[] = array('name' => 'productListing\buyButton', 'title' => TEXT_BUY_BUTTON, 'description' => '', 'type' => 'productListing', 'class' => '');
        $widgets[] = array('name' => 'productListing\quoteButton', 'title' => REQUEST_FOR_QUOTE_BUTTON, 'description' => '', 'type' => 'productListing', 'class' => '');
        $widgets[] = array('name' => 'productListing\qtyInput', 'title' => TEXT_QUANTITY_INPUT, 'description' => '', 'type' => 'productListing', 'class' => '');
        $widgets[] = array('name' => 'productListing\viewButton', 'title' => TEXT_VIEW_BUTTON, 'description' => '', 'type' => 'productListing', 'class' => '');
        //$widgets[] = array('name' => 'productListing\wishlistButton', 'title' => TEXT_WISHLIST_BUTTON, 'description' => '', 'type' => 'productListing', 'class' => '');
        $widgets[] = array('name' => 'productListing\compare', 'title' => TEXT_COMPARE, 'description' => '', 'type' => 'productListing', 'class' => '');
        $widgets[] = array('name' => 'productListing\attributes', 'title' => TEXT_ATTRIBUTES, 'description' => '', 'type' => 'productListing', 'class' => '');
        $widgets[] = array('name' => 'productListing\paypalButton', 'title' => TEXT_PAYPAL_BUTTON, 'description' => '', 'type' => 'productListing', 'class' => '');
        $widgets[] = array('name' => 'Import', 'title' => IMPORT_BLOCK, 'description' => '', 'type' => 'productListing', 'class' => 'import');
        $widgets[] = array('name' => 'BlockBox', 'title' => TEXT_BLOCK, 'description' => '', 'type' => 'productListing', 'class' => 'block-box');
        $widgets[] = array('name' => 'batchSelect', 'title' => 'batchSelect', 'description' => '', 'type' => 'productListing', 'class' => '');
        $widgets[] = array('name' => 'batchRemove', 'title' => 'batchRemove', 'description' => '', 'type' => 'productListing', 'class' => '');
        $widgets[] = array('name' => 'productListing\productGroup', 'title' => 'Product Group', 'description' => '', 'type' => 'productListing', 'class' => '');
        $widgets[] = array('name' => 'productListing\BazaarvoiceRatingInline', 'title' => 'Bazaarvoice Rating Inline', 'description' => '', 'type' => 'productListing', 'class' => '');
        //$widgets[] = array('name' => 'productListing\amazonButton', 'title' => 'amazon button', 'description' => '', 'type' => 'productListing', 'class' => '');
        $widgets[] = array('name' => 'productListing\brand', 'title' => 'Brand', 'description' => '', 'type' => 'productListing', 'class' => '');
        $widgets[] = array('name' => 'productListing\internalName', 'title' => 'Internal product name', 'description' => '', 'type' => 'productListing', 'class' => '');

        return $widgets;
    }

    private static function backendOrder()
    {
        $widgets = [];

        $widgets[] = array('name' => 'title', 'title' => 'Backend Order', 'description' => '', 'type' => 'backendOrder');
        $widgets[] = array('name' => 'BlockBox', 'title' => TEXT_BLOCK, 'description' => '', 'type' => 'backendOrder', 'class' => 'block-box');
        $widgets[] = array('name' => 'AddressDetailsHolder', 'title' => 'AddressDetails', 'description' => '', 'type' => 'backendOrder', 'class' => '');
        $widgets[] = array('name' => 'Asset', 'title' => 'Asset', 'description' => '', 'type' => 'backendOrder', 'class' => '');
        $widgets[] = array('name' => 'AssignTransactions', 'title' => 'AssignTransactions', 'description' => '', 'type' => 'backendOrder', 'class' => '');
        $widgets[] = array('name' => 'Attributes', 'title' => 'Attributes', 'description' => '', 'type' => 'backendOrder', 'class' => '');
        $widgets[] = array('name' => 'Buttons', 'title' => 'Buttons', 'description' => '', 'type' => 'backendOrder', 'class' => '');
        $widgets[] = array('name' => 'CommentTemplate', 'title' => 'CommentTemplate', 'description' => '', 'type' => 'backendOrder', 'class' => '');
        $widgets[] = array('name' => 'CreditNotes', 'title' => 'CreditNotes', 'description' => '', 'type' => 'backendOrder', 'class' => '');
        $widgets[] = array('name' => 'Customer', 'title' => 'Customer', 'description' => '', 'type' => 'backendOrder', 'class' => '');
        $widgets[] = array('name' => 'DeleteOrder', 'title' => 'DeleteOrder', 'description' => '', 'type' => 'backendOrder', 'class' => '');
        $widgets[] = array('name' => 'Downloads', 'title' => 'Downloads', 'description' => '', 'type' => 'backendOrder', 'class' => '');
        $widgets[] = array('name' => 'ExternalOrders', 'title' => 'ExternalOrders', 'description' => '', 'type' => 'backendOrder', 'class' => '');
        $widgets[] = array('name' => 'ExtraCustomData', 'title' => 'ExtraCustomData', 'description' => '', 'type' => 'backendOrder', 'class' => '');
        $widgets[] = array('name' => 'FoundTransactionsList', 'title' => 'FoundTransactionsList', 'description' => '', 'type' => 'backendOrder', 'class' => '');
        $widgets[] = array('name' => 'InvoiceComments', 'title' => 'InvoiceComments', 'description' => '', 'type' => 'backendOrder', 'class' => '');
        $widgets[] = array('name' => 'MapHolder', 'title' => 'Map', 'description' => '', 'type' => 'backendOrder', 'class' => '');
        $widgets[] = array('name' => 'MapJS', 'title' => 'MapJS', 'description' => '', 'type' => 'backendOrder', 'class' => '');
        $widgets[] = array('name' => 'Notification', 'title' => 'Notification', 'description' => '', 'type' => 'backendOrder', 'class' => '');
        $widgets[] = array('name' => 'NSHelper', 'title' => 'NSHelper', 'description' => '', 'type' => 'backendOrder', 'class' => '');
        $widgets[] = array('name' => 'OrderComments', 'title' => 'OrderComments', 'description' => '', 'type' => 'backendOrder', 'class' => '');
        $widgets[] = array('name' => 'OrderTotals', 'title' => 'OrderTotals', 'description' => '', 'type' => 'backendOrder', 'class' => '');
        $widgets[] = array('name' => 'Paying', 'title' => 'Paying', 'description' => '', 'type' => 'backendOrder', 'class' => '');
        $widgets[] = array('name' => 'PaymentActions', 'title' => 'PaymentActions', 'description' => '', 'type' => 'backendOrder', 'class' => '');
        $widgets[] = array('name' => 'PaymentExtraInfo', 'title' => 'PaymentExtraInfo', 'description' => '', 'type' => 'backendOrder', 'class' => '');
        $widgets[] = array('name' => 'PrintLabel', 'title' => 'PrintLabel', 'description' => '', 'type' => 'backendOrder', 'class' => '');
        $widgets[] = array('name' => 'Product', 'title' => 'Product', 'description' => '', 'type' => 'backendOrder', 'class' => '');
        $widgets[] = array('name' => 'ProductAssets', 'title' => 'ProductAssets', 'description' => '', 'type' => 'backendOrder', 'class' => '');
        $widgets[] = array('name' => 'ProductsHolder', 'title' => 'Products', 'description' => '', 'type' => 'backendOrder', 'class' => '');
        $widgets[] = array('name' => 'RequestHolder', 'title' => 'Request', 'description' => '', 'type' => 'backendOrder', 'class' => '');
        $widgets[] = array('name' => 'ShippingExtraInfo', 'title' => 'ShippingExtraInfo', 'description' => '', 'type' => 'backendOrder', 'class' => '');
        $widgets[] = array('name' => 'SMS', 'title' => 'SMS', 'description' => '', 'type' => 'backendOrder', 'class' => '');
        $widgets[] = array('name' => 'StatusComments', 'title' => 'StatusComments', 'description' => '', 'type' => 'backendOrder', 'class' => '');
        $widgets[] = array('name' => 'StatusList', 'title' => 'StatusList', 'description' => '', 'type' => 'backendOrder', 'class' => '');
        $widgets[] = array('name' => 'StatusTable', 'title' => 'StatusTable', 'description' => '', 'type' => 'backendOrder', 'class' => '');
        $widgets[] = array('name' => 'Toolbar', 'title' => 'Toolbar', 'description' => '', 'type' => 'backendOrder', 'class' => '');
        $widgets[] = array('name' => 'TotalsItem', 'title' => 'TotalsItem', 'description' => '', 'type' => 'backendOrder', 'class' => '');
        $widgets[] = array('name' => 'Transactions', 'title' => 'Transactions', 'description' => '', 'type' => 'backendOrder', 'class' => '');
        $widgets[] = array('name' => 'Trustpilot', 'title' => 'Trustpilot', 'description' => '', 'type' => 'backendOrder', 'class' => '');
        $widgets[] = array('name' => 'Unprocessed', 'title' => 'Unprocessed', 'description' => '', 'type' => 'backendOrder', 'class' => '');
        $widgets[] = array('name' => 'ClosableBox', 'title' => 'ClosableBox', 'description' => '', 'type' => 'backendOrder', 'class' => '');


        return $widgets;
    }

    private static function backendOrdersList()
    {
        $widgets = [];

        $widgets[] = array('name' => 'title', 'title' => ORDERS_LIST_ITEMS, 'description' => '', 'type' => 'backendOrdersList');
        $widgets[] = array('name' => 'BlockBox', 'title' => TEXT_BLOCK, 'description' => '', 'type' => 'backendOrdersList', 'class' => 'block-box');
        $widgets[] = array('name' => 'backendOrdersList\BatchCheckbox', 'title' => BATCH_CHECKBOX_CELL, 'description' => '', 'type' => 'backendOrdersList', 'class' => '');
        $widgets[] = array('name' => 'backendOrdersList\OrderMarkersCell', 'title' => ORDER_MARKERS_CELL, 'description' => '', 'type' => 'backendOrdersList', 'class' => '');
        $widgets[] = array('name' => 'backendOrdersList\CustomerColumnCell', 'title' => CUSTOMER_COLUMN_CELL, 'description' => '', 'type' => 'backendOrdersList', 'class' => '');
        $widgets[] = array('name' => 'backendOrdersList\OrderTotalsCell', 'title' => ORDER_TOTALS_CELL, 'description' => '', 'type' => 'backendOrdersList', 'class' => '');
        $widgets[] = array('name' => 'backendOrdersList\OrderDescriptionCell', 'title' => ORDER_DESCRIPTION_CELL, 'description' => '', 'type' => 'backendOrdersList', 'class' => '');
        $widgets[] = array('name' => 'backendOrdersList\OrderPurchaseCell', 'title' => ORDER_PURCHASE_CELL, 'description' => '', 'type' => 'backendOrdersList', 'class' => '');
        $widgets[] = array('name' => 'backendOrdersList\OrderStatusCell', 'title' => ORDER_STATUS_CELL, 'description' => '', 'type' => 'backendOrdersList', 'class' => '');
        $widgets[] = array('name' => 'backendOrdersList\NeighbourCell', 'title' => NEIGHBOUR_CELL, 'description' => '', 'type' => 'backendOrdersList', 'class' => '');
        $widgets[] = array('name' => 'backendOrdersList\CustomerGender', 'title' => CUSTOMER_GENDER, 'description' => '', 'type' => 'backendOrdersList', 'class' => '');
        $widgets[] = array('name' => 'backendOrdersList\CustomerName', 'title' => TEXT_CUSTOMER_NAME, 'description' => '', 'type' => 'backendOrdersList', 'class' => '');
        $widgets[] = array('name' => 'backendOrdersList\CustomerEmail', 'title' => TEXT_CUSTOMER_EMAIL, 'description' => '', 'type' => 'backendOrdersList', 'class' => '');
        $widgets[] = array('name' => 'backendOrdersList\OrderLocation', 'title' => ORDER_LOCATION, 'description' => '', 'type' => 'backendOrdersList', 'class' => '');
        $widgets[] = array('name' => 'backendOrdersList\WalkinOrder', 'title' => WALKIN_ORDER, 'description' => '', 'type' => 'backendOrdersList', 'class' => '');
        $widgets[] = array('name' => 'Html_box', 'title' => 'html', 'description' => '', 'type' => 'backendOrdersList', 'class' => 'html');
        $widgets[] = array('name' => 'backendOrdersList\OrderId', 'title' => TEXT_ORDER_ID, 'description' => '', 'type' => 'backendOrdersList', 'class' => '');
        $widgets[] = array('name' => 'backendOrdersList\OrderProducts', 'title' => ORDER_PRODUCTS, 'description' => '', 'type' => 'backendOrdersList', 'class' => '');
        $widgets[] = array('name' => 'backendOrdersList\Platform', 'title' => TABLE_HEADING_PLATFORM, 'description' => '', 'type' => 'backendOrdersList', 'class' => '');
        $widgets[] = array('name' => 'backendOrdersList\PaymentMethod', 'title' => TEXT_INFO_PAYMENT_METHOD, 'description' => '', 'type' => 'backendOrdersList', 'class' => '');
        $widgets[] = array('name' => 'backendOrdersList\ShippingMethod', 'title' => TEXT_CHOOSE_SHIPPING_METHOD, 'description' => '', 'type' => 'backendOrdersList', 'class' => '');
        $widgets[] = array('name' => 'backendOrdersList\OrderPurchase', 'title' => TABLE_HEADING_DATE_PURCHASED, 'description' => '', 'type' => 'backendOrdersList', 'class' => '');


        return $widgets;
    }
}