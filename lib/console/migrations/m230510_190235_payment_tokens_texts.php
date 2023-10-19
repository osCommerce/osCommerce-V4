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
 * Class m230510_190235_payment_tokens_texts
 */
class m230510_190235_payment_tokens_texts extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {

        $this->addTranslation('payment', [
          'TEXT_SAVED_CARDS' => 'Previously saved cards',
          'TEXT_SAVED_CARDS_TS' => 'See terms & conditions',
          'TEXT_SAVED_CARDS_LINK' => 'terms-conditions',
          'TEXT_CARD_HOLDER_NAME' => 'Name on Card',
          'TEXT_CARD_NUMBER' => 'Card Number',
          'TEXT_EXPIRY_DATE' => 'Expiry',
          'TEXT_CARD_CVV' => 'CVV',
        ]);



    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
//        echo "m230510_190235_payment_tokens_texts cannot be reverted.\n";

//        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230510_190235_payment_tokens_texts cannot be reverted.\n";

        return false;
    }
    */
}
