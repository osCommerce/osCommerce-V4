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
 * Class m230524_112916_invoice_number_format_drop_old_translation
 */
class m230524_112916_invoice_number_format_drop_old_translation extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if (method_exists($this, 'isOldExtension'))
        {
            if (!$this->isOldExtension('InvoiceNumberFormat'))
            {
                $this->removeTranslation('admin/invoice_nformat');
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->addTranslation('admin/invoice_nformat', [
            'TEXT_ORDER_NUMBER_FISCAL_YEAR' => 'Fiscal year starts on',
            'HEADING_PLATFORM_INVOICE_NUMBER_FORMAT' => 'Order Number Format',
//            'TEXT_ORDER_NUMBER_FORMAT' => 'Order Number Format',
            'TEXT_ORDER_NUMBER_FORMAT_BUTTON' => 'Change number format',
            'TEXT_ORDER_NUMBER_TYPE' => 'Type',
            'TEXT_ORDER_NUMBER_TYPE_ORDER' => 'Order',
            'TEXT_ORDER_NUMBER_TYPE_INVOICE' => 'Invoice',
            'TEXT_ORDER_NUMBER_FORMAT' => 'Format',
            'TEXT_ORDER_NUMBER_LASTUPDATED' => 'Last Updated',
            'TEXT_ORDER_NUMBER_VALIDFROM' => 'Valid From',
            'TEXT_ORDER_NUMBER_VALIDTO' => 'Valid To',
            'TEXT_ORDER_NUMBER_EDIT_FORMAT' => 'Edit Order Format',
            'TEXT_ORDER_NUMBER_ADDFORMAT' => 'Add Order Format',
            'TEXT_ORDER_NUMBER_FORMAT_DESCRIPTION' => 'Y - A full numeric representation of a year, 4 digits Examples: 2021<br/>y - A two digit representation of a year Examples: 99 or 03<br/>0Xc - invoice counter with X digits zero padding Examples: 04c => 0001; 02c => 01 or 123 (for invoice 123)<br/>Full example: GGY-04c => GG2021-0001',
            'TEXT_ORDER_NUMBER_ARE_YOU_SURE' => 'Are you sure you want to delete this format?',
            'TEXT_ORDER_NUMBER_POPUP_TITLE' => 'Delete the order number format',
            'TEXT_ORDER_NUMBER_SUCCESSFULLY_SAVED' => 'The number format was successfully saved.',
            'TEXT_ORDER_NUMBER_CANNOT_PROCESS' => 'Cannot process the request.',
            'TEXT_ORDER_NUMBER_REQUIRED_FIELDS_EMPTY' => 'Required fields are empty.',
            'TEXT_ORDER_NUMBER_DATE_RANGE_NOTVALID' => 'Date range is not valid.',
            'TEXT_ORDER_NUMBER_COUNTER_STARTS_FROM' => 'Order number counter starts from',
            'TEXT_ORDER_NUMBER_INVOICE_STARTS_FROM' => 'Invoice number counter starts from',
            'TEXT_ORDER_NUMBER_BOTH' => 'Both',

        ]);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230524_112916_invoice_number_format_drop_old_translation cannot be reverted.\n";

        return false;
    }
    */
}
