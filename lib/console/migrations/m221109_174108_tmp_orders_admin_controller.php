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
 * Class m221109_174108_tmp_orders_admin_controller
 */
class m221109_174108_tmp_orders_admin_controller extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('admin/main', [
          'BOX_CUSTOMERS_TMP_ORDERS' => 'Temporary orders',
          'HEADING_TMP_ORDERS' => 'Temporary orders',
          'TEXT_UNEXPECTED_ERROR' => 'Unexpected Error has occurred',
        ]);
        $this->addTranslation('admin/orders', [
          'TEXT_PROCESS_TMP_ORDER' => 'Processing Temporary Order #',
          'IMAGE_CONVERT' => 'Convert to order',
          'TEXT_CONVERTED_ORDER' => 'Converted to order #',
          'TEXT_DELETE_AND_TO_LIST' => 'Delete and go to list',
          'TEXT_DELETE_AND_TO_NEXT' => 'Delete and go to next order',
          'TEXT_CANT_DELETE_HAS_CHILD' => 'Temporary Order can NOT be deleted as it has child order ',
          'TEXT_INFO_HEADING_CONVERT_TMP_ORDER' => 'Create Order from Temporary Order',
          'TEXT_INFO_CONVERT_INTRO' => 'Are you sure you want to create new order from this Temporary Order?',

          'TEXT_DELETE_SELECTED_ORDERS_TITLE' => 'Delete Selected Temporary Orders',
          'TEXT_DELETE_SELECTED_ORDERS' => 'Are you sure you want to delete selected Temporary Orders?',
          'TEXT_ERROR_INCORRECT_TMP_ORDER' => 'Temporary order is incorrect ',
          'TEXT_ERROR_TMP_ORDER_PROCESSED' => 'Temporary order is incorrect or processed',
          'TEXT_CURRENT_DATE_TIME' => 'Use current date/time in new order',
          'TEXT_TMP_ORDER_DATE_TIME' => 'Use Temporary order date/time in new order',
        ]);
        $this->appendAcl(['BOX_HEADING_CUSTOMERS', 'BOX_CUSTOMERS_TMP_ORDERS']);

        $this->addAdminMenuAfter([
                'path' => 'tmp-orders',
                'title' => 'BOX_CUSTOMERS_TMP_ORDERS',
            ], 'BOX_CUSTOMERS_CUSTOMERS');

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        //echo "m221109_174108_tmp_orders_admin_controller cannot be reverted.\n";

        //return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m221109_174108_tmp_orders_admin_controller cannot be reverted.\n";

        return false;
    }
    */
}
