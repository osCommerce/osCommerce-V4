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
 * Class m230126_171904_personal_catalog
 */
class m230126_171904_personal_catalog extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if(method_exists($this, 'isOldExtension')) {
            if (!$this->isOldExtension('PersonalCatalog')) {
                if (!\common\helpers\Extensions::isInstalled('PersonalCatalog')) {
                    $this->dropTables(['personal_catalog', 'personal_catalog_list']);
                }
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->createTableIfNotExists('personal_catalog', [
            'uprid' =>  $this->string(255)->notNull()->defaultValue('')->comment('Uprid Product'),
            'products_id' => $this->integer()->notNull()->defaultValue(0)->comment('ID Product'),
            'created_at' => $this->integer()->notNull()->defaultValue(0)->comment('Date Create'),
            'qty' => $this->integer()->notNull()->defaultValue(1),
            'list_id' => $this->integer()->notNull()->defaultValue(0),
        ], 'uprid, list_id', 'uprid, products_id, created_at');

        $this->createTableIfNotExists('personal_catalog_list', [
            'list_id' => $this->primaryKey()->append('AUTO_INCREMENT'),
            'customers_id' => $this->integer()->notNull()->defaultValue(0),
            'add_flag' => $this->integer()->notNull()->defaultValue(0),
            'list_name' => $this->string(128)->notNull()->defaultValue(''),
            'is_default' => $this->integer()->notNull()->defaultValue(0),
        ], null, ['idx_cid' => 'customers_id']);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230126_171904_personal_catalog cannot be reverted.\n";

        return false;
    }
    */
}
