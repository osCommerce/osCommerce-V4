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
 * Class m230227_101417_translations_feb_upd
 */
class m230227_101417_translations_feb_upd extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
		$this->addTranslation('main', [
            'TEXT_CROSS_SELL' => 'Cross Sell',
        ]);
		$this->addTranslation('main', [
            'EMAIL_GREET_MR' => 'Mr',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
       
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230227_101417_translations_feb_upd cannot be reverted.\n";

        return false;
    }
    */
}
