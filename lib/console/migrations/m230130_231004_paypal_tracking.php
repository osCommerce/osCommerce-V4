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
 * Class m230130_231004_paypal_tracking
 */
class m230130_231004_paypal_tracking extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('payment', [
          'TEXT_ERROR_TRANSACTIONID_REQUIRED' => 'Transaction id is required',
          'TEXT_ERROR_TRACKING_NOT_ADDED' => 'Tracking not passed to payment gateway',
          'TEXT_TRACKING_ADDED' => 'Tracking passed to payment gateway',
          'TEXT_TRACKING_ADD_TO_PAYMENT_GATEWAY' => 'Send Tracking info to payment gateway',
          'TEXT_TRACKING_TRANSACTION' => '%s - %s',
          'TEXT_TRACKING_ADD_DETAILS' => 'Tracking sent to %s on %s',
        ]);

        //if (!$this->isTableExists('tracking_numbers_export')) {}

        try {
            $this->createTableIfNotExists('tracking_numbers_export', [
              'id' =>  $this->primaryKey(11)->append('AUTO_INCREMENT'),
              'tracking_numbers_id' => $this->integer(11)->notNull()->defaultValue(0),
              'orders_payment_id' => $this->integer(11)->notNull()->defaultValue(0),
              'orders_id' => $this->integer(11)->notNull()->defaultValue(0),
              'classname' => $this->string(96)->notNull()->defaultValue(''),
              'external_id' => $this->string(96)->notNull()->defaultValue(''),
              'status' => $this->integer(1)->notNull()->defaultValue(0),
              'message' => $this->text()->notNull()->defaultValue(''),
              'date_added' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
              ],
              null,
              [
                'idx_classname_ext_id' => ['classname', 'external_id'],
                'unique:unq_tracking_numbers_id' => ['tracking_numbers_id', 'orders_payment_id'],
                'idx_orders_id' => ['orders_id'],
                'idx_orders_payment_id' => ['classname', 'orders_payment_id'],
              ]
            );
        } catch (\Exception $e) {
            echo $e->getMessage();
            return false;
        }
        

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
//        echo "m230130_231004_paypal_tracking cannot be reverted.\n";

//        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230130_231004_paypal_tracking cannot be reverted.\n";

        return false;
    }
    */
}
