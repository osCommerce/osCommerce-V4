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
 * Class m230303_183322_fix_translation_en5
 */
class m230303_183322_fix_translation_en5 extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        // remove one empty translation
        $this->db->createCommand("DELETE FROM translation WHERE  translation_entity = '' AND hash LIKE '336d5ebc5436534e61d16e63ddfca327'")->execute();
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m230303_183322_fix_translation_en5 cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230303_183322_fix_translation_en5 cannot be reverted.\n";

        return false;
    }
    */
}
