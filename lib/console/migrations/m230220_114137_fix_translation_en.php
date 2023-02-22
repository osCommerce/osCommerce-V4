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

use common\classes\Migration;

/**
 * Class m230220_114137_fix_translation_en
 */
class m230220_114137_fix_translation_en extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('admin/main', ['BOX_CONFIGURATION_RECHNUNGEN' => 'Invoices'], true);
        $this->addTranslation('admin/coupon_admin', ['COUPON_CUSTOMERS_IMAGE_CONFIRM' => 'Confirm'], true);
        $this->addTranslation('admin/design', ['TEXT_POINTS_EARNT' => 'Points Earned'], true);
        $this->addTranslation('account/subscription-renewal', ['TEXT_REGULAR_OFFERS_CONFIRM_SUCC' => 'Updated successfully'], true);
        $this->addTranslation('account/update', ['TEXT_RDPR_CONFIRM_SUCC' => 'Updated successfully'], true);
        $this->addTranslation('admin/coupon_admin', ['COUPON_CUSTOMERS_SUCCESSFULLY_DELETED' => 'Items have been successfully deleted'], true);
        $this->addTranslation('admin/design',
            [
                'AVAILABLE_AT_WAREHOUSES_SHOW_QTY' => 'Show quantity',
                'AVAILABLE_AT_WAREHOUSES_SHOW_QTY_AS_LEVELS' => 'Show quantity levels',
                'TEXT_HEADER_STOCK' => 'Stock Switcher',
                'TEXT_MORE_THEN_ONE_VISIT' => 'More than one visit',
                'TEXT_SOW_GO_TO_BUTTON' => 'Show go to button',
            ], true);
        $this->addTranslation('admin/main',
            [
                'COUPON_CUSTOMERS_COUPONS_CSV_HELP' => 'Please attach CSV file with the two columns. The fist one is "Only for customer" (contains one email), the second one is "Coupon Code". The columns must be separated by "," and enclosed by double quotes',
                'TEXT_ADMIN_ASSIGN_PLATFORMS' => 'Admin members with restricted access should be assigned to frontends. <a href="%s">Assign now</a>',
                'TEXT_CONFIRM_ANONIMAZE' => 'Confirm you wish to anonymize',
                'TEXT_CONFIRM_SORT_GROUPPED' => 'Do you really want to sort grouped products and place them together',
                'TEXT_EXTERNAL_NEWSLETTER_ADD' => 'Add customer to Mailchimp list',
                'TEXT_PROMPT_DELETE' => 'Are you sure you want to delete this transaction (localy)?',
                'TEXT_UPDATE_EXISTING' => 'Update existing records',
            ], true);


        $this->addTranslation('admin/modules', ['HEADING_TITLE_MODULES_LABEL' => 'Shipping label modules'], true);

        $this->addTranslation('admin/orders', 
            [
                'CONFIRM_CLOSE_QUESTION' => 'Do you really want to close order?',
                'TEXT_INNER_COMMENTS' => 'Inner Comments',
                'TEXT_INVOICE_COMMENTS' => 'Invoice Comments',
                'TEXT_LOG_REFUND_ERROR' => '%s transaction refund error',
            ], true);
        
        $this->addTranslation('admin/printers',
            [
                'ERROR_INVALID_DOCUMENT_ASSIGNMENT' => 'Invalid document assignment',
                'TEXT_ACCEPTED_ALREADY' => 'Already añcepted',
                'TEXT_ACCEPTED_SUCCESSFULY' => 'Accepted suñcessfully',
            ], true);

        $this->addTranslation('admin/stock-indication', ['TEXT_STOCK_INTICATION_PRICE_ZERO' => 'Hide price when zero (only)'], true);

        $this->addTranslation('extensions/osclink',
            [
                'EXTENSION_OSCLINK_TEXT_DESCRIPTION_CATEGORIES' => 'Download and import Categories structure from OSCommerce',
                'EXTENSION_OSCLINK_TEXT_DESCRIPTION_CUSTOMERS' => 'Download and import Customers structure from OSCommerce',
                'EXTENSION_OSCLINK_TEXT_DESCRIPTION_ORDERS' => 'Download and import Orders structure from OSCommerce',
                'EXTENSION_OSCLINK_TEXT_DESCRIPTION_PRODUCTS' => 'Download and import Products structure from OSCommerce',
                'EXTENSION_OSCLINK_TEXT_DESCRIPTION_PRODUCTS_OPTIONS' => 'Download and import Products Options structure from OSCommerce',
                'EXTENSION_OSCLINK_TEXT_DESCRIPTION_TAXES' => 'Download and import Taxes structure from OSCommerce',
                'EXTENSION_OSCLINK_TEXT_DESCRIPTION_TAX_ZONES' => 'Download and import Tax Zones structure from OSCommerce',
                'EXTENSION_OSCLINK_TEXT_DESCRIPTION_BRANDS' => 'Download and import Brands structure from OSCommerce',
                'EXTENSION_OSCLINK_TEXT_DESCRIPTION_GROUPS' => 'Download and import Groups structure from OSCommerce',
                'EXTENSION_OSCLINK_TEXT_DESCRIPTION_REVIEWS' => 'Download and import Reviews structure from OSCommerce',
            ], true);

        $this->addTranslation('main',
            [
                'TEXT_SHOW_ABSENT_PRODUCTS'      => 'Show absent products',
                'TEXT_SUPERDISCOUNT_INTRO'       => 'Regular Customer can receive a Super Discount buying over',
                'TEXT_WITHOUT_SHIPPING_ADDRESS'  => 'Without shipping address',
                'THERE_ARE_NO_PTOMOTIONS'        => 'There are no promotions today, please come back tomorrow to get special promotions for you',
                'TEXT_PAYMENT_ERROR_TRY_OTHER_METHOD'  => "The attempt to pay for your order was unsuccessful.<br>\nPlease re-select the payment method you want and try again",
            ], true);

        $this->addTranslation('ordertotal', ['ERROR_INVALID_COUNTRY_COUPON' => 'Invalid usage for determined country'], true);

        $this->addTranslation('payment',
            [
                'PAYPAL_PARTNER_RESTART' => 'Error during communication with PayPal (incorrect total). Please login to PayPal once again',
                'PAYPAL_PARTNER_RESTART_AUTHORIZE' => 'Error during communication with PayPal (payment not authorized). Please login to PayPal once again',
                'PAYPAL_PARTNER_RESTART_CAPTURE' => 'Error during communication with PayPal (payment not captured). Please login to PayPal once again',
            ], true);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m230220_114137_fix_translation_en cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230220_114137_fix_translation_en cannot be reverted.\n";

        return false;
    }
    */
}
