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
 * Class m230322_143242_update_translation
 */
class m230322_143242_update_translation extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
	$this->execute("UPDATE translation SET translation_value = REPLACE(translation_value, 'TrueLoaded','Powerful commerce') WHERE translation_key='TEXT_FOOTER_BOTTOM'");
        $this->execute("UPDATE translation SET translation_value = REPLACE(translation_value, 'Wolq','Powerful commerce') WHERE translation_key='TEXT_FOOTER_BOTTOM'");
        $this->execute("UPDATE translation SET translation_value = REPLACE(translation_value, 'Trueloaded','Powerful commerce') WHERE translation_key='TEXT_FOOTER_COPYRIGHT'");
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m230322_143242_update_translation cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230322_143242_update_translation cannot be reverted.\n";

        return false;
    }
    */
}
