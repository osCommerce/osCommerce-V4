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
 * Class m230614_131212_report_products_notify_remove_old_translation
 */
class m230614_131212_report_products_notify_remove_old_translation extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->removeTranslation('admin/report-products-notify');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->addTranslation('admin/report-products-notify', [
            'HEADING_TITLE_PRODUCT' => 'Product',
            'TABLE_HEADING_CUSTOMERS_EMAIL' => 'Customer Email',
            'TABLE_HEADING_CUSTOMERS_NAME' => 'Customer Name',
            'TABLE_HEADING_IS_CUSTOMER' => 'Is a Customer?',
            'TABLE_HEADING_PRODUCTS_ATTRIBUTES' => 'Product Attributes',
            'TABLE_HEADING_PRODUCTS_MODEL' => 'Product Model',
            'TABLE_HEADING_PRODUCTS_NAME' => 'Product Name',
            'TEXT_CUSTOMER_STATUS' => 'Customer Status',
            'TEXT_NOTIFIED' => 'Notified',
            'TEXT_NOT_NOTIFIED' => 'Not notified',
        ]);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230614_131212_report_products_notify_remove_old_translation cannot be reverted.\n";

        return false;
    }
    */
}
