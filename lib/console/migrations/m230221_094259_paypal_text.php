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
 * Class m230221_094259_paypal_text
 */
class m230221_094259_paypal_text extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        
        $this->addTranslation('payment', [
          'PAYPAL_PARTNER_SELLER_SETUP' => 'New account is being set up. Please NOTE: old one are yet active until your final confirmation.'
        ]);

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        //echo "m230221_094259_paypal_text cannot be reverted.\n";

        //return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230221_094259_paypal_text cannot be reverted.\n";

        return false;
    }
    */
}
