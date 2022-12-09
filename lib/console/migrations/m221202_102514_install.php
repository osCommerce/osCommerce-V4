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
 * Class m221202_102514_install
 */
class m221202_102514_install extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->update('translation',
            ['translation_value' => 'force update exclude mark as ignore'],
            ['translation_key' => 'TEXT_FORCE_UPDATE', 'translation_entity' => 'admin/install']
        );
        $this->update('translation',
            ['translation_value' => 'In this case all local changes not marked as ignore will be lost'],
            ['translation_key' => 'TEXT_FORCE_UPDATE_INTRO', 'translation_entity' => 'admin/install']
        );
        if (!$this->isTableExists('install_ignore_list')) {
            $this->createTable('install_ignore_list', [
                'id' => $this->primaryKey(),
                'path' => $this->string(255)->defaultValue(''),
            ], 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB');
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m221202_102514_install cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m221202_102514_install cannot be reverted.\n";

        return false;
    }
    */
}
