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
 * Class m221206_191255_add_missed_key_for_ordercontroller
 */
class m221206_191255_add_missed_key_for_ordercontroller extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('admin/main', ['NOTICE_EMAILS_SENT' => 'Mail(s) was sent successfuly']);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m221206_191255_add_missed_key_for_ordercontroller cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m221206_191255_add_missed_key_for_ordercontroller cannot be reverted.\n";

        return false;
    }
    */
}
