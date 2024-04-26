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
 * Class m240103_140120_add_translation
 */
class m240103_140120_add_translation extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('admin/install', [
            'TEXT_MENU_STRUCTURE' => 'Admin menu items were added'
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
       $this->removeTranslation('admin/install', ['TEXT_MENU_STRUCTURE']);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240103_140120_add_translation cannot be reverted.\n";

        return false;
    }
    */
}
