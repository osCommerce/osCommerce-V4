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
 * Class m220823_132646_remove_supplier_priority_remains
 */
class m220823_132646_remove_supplier_priority_remains extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if (!\common\helpers\Acl::checkExtensionAllowed('SupplierPriority')) {
            $this->dropTable('suppliers_selection_priority');
            $this->removeTranslation('admin/suppliers-priority');
            $this->removeTranslation('admin/main', 'BOX_SUPPLIER_PRIORITY');
        }
        $this->getDb()->createCommand("update `products_stock_indication` set is_default=0;")->execute();
        $this->getDb()->createCommand("update `products_stock_indication` set is_default=1 order by stock_code='out-stock' desc, stock_indication_id limit 1;")->execute();
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m220823_132646_remove_supplier_priority_remains cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220823_132646_remove_supplier_priority_remains cannot be reverted.\n";

        return false;
    }
    */
}
