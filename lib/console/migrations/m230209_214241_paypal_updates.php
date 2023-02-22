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
 * Class m230209_214241_paypal_updates
 */
class m230209_214241_paypal_updates extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('payment', [
          'TEXT_CANCELLED_BY_CUSTOMER' => 'Cancelled by customer',
          'SESSION_EXPIRED_LOGIN_OR_CHECK_EMAIL' => 'Your session expired. Please check your email for order updates or login to your account',
          'TEXT_UNEXPECTED_PAYMENT_ERROR' => 'Unexpected error has occurred. Please reload page, select different payment or contact us if the error persists',
        ]);

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
//        echo "m230209_214241_paypal_updates cannot be reverted.\n";

  //      return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230209_214241_paypal_updates cannot be reverted.\n";

        return false;
    }
    */
}
