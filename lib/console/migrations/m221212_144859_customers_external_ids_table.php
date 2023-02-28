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
 * Class m221212_144859_customers_external_ids_table
 */
class m221212_144859_customers_external_ids_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {

        if (!$this->isTableExists('customers_external_ids')) {
            $this->createTable('customers_external_ids', [
                'id' => $this->primaryKey(),
                'customers_id' => $this->integer(11)->notNull()->defaultValue(0),
                'system_name' => $this->string(127)->notNull()->defaultValue(''),
                'external_id' => $this->string(256)->notNull()->defaultValue(''),
                'date_added' => $this->dateTime()->notNull() . ' DEFAULT NOW()',
            ]);
            $this->createIndex('unq_cid_system', 'customers_external_ids', ['customers_id', 'system_name'], true);
        }

        if ($this->isTableExists('customers_stripe_tokens')) {
            $this->db->createCommand("insert ignore into customers_external_ids (customers_id, system_name, external_id, date_added) select customers_id, 'stripe_ds', stripe_token, date_added from customers_stripe_tokens")->execute();
        }

        try {
            if (function_exists('exec')) {
                exec('cd ' . DIR_FS_CATALOG . ' && mysqldump --opt -h' . DB_SERVER . ' -u' . DB_SERVER_USERNAME . ' -p' . DB_SERVER_PASSWORD . ' ' . DB_DATABASE . ' customers_stripe_tokens > sql/bkcustomers_stripe_tokens.sql');
            }
            $this->dropTableIfExists('customers_stripe_tokens');
        } catch (\Exception $ex) {
            echo "Note Can't backup customers_stripe_tokens - not dropped \nBackup and drop Manually, data is moved to customers_external_ids \n";
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
//        echo "m221212_144859_customers_external_ids_table cannot be reverted.\n";

//        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m221212_144859_customers_external_ids_table cannot be reverted.\n";

        return false;
    }
    */
}
