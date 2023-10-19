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
 * Class m230717_212415_module_tokens
 */
class m230717_212415_module_tokens extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if (!$this->isTableExists('module_tokens')) {
            $this->createTable('module_tokens', [
              'id' => $this->primaryKey(11)->append('AUTO_INCREMENT'),
              'class' => $this->string(64)->notNull()->defaultValue(''),
              'token' => $this->text(),
              'valid_until' => $this->dateTime()->notNull(),
              'platform_id' => $this->integer(11)->notNull()->defaultValue(0),
              'admin_id' => $this->integer(11)->notNull()->defaultValue(0),
            ]);
            $this->createIndex('idx_class_ids_until', 'module_tokens', ['class', 'platform_id', 'admin_id', 'valid_until']);
        }

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        //echo "m230717_212415_module_tokens cannot be reverted.\n";

        //return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230717_212415_module_tokens cannot be reverted.\n";

        return false;
    }
    */
}
