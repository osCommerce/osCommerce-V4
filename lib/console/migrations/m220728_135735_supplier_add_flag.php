<?php
/**
 * This file is part of True Loaded.
 *
 * @link http://www.holbi.co.uk
 * @copyright Copyright (c) 2005 Holbi Group LTD
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use common\classes\Migration;

/**
 * Class m220728_135735_supplier_add_flag
 */
class m220728_135735_supplier_add_flag extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if (!$this->isFieldExists('invoice_needed_to_complete_po', 'suppliers')) {
            $this->addColumn('suppliers', 'invoice_needed_to_complete_po', $this->tinyInteger(1)->notNull()->defaultValue(1));
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m220728_135735_supplier_add_flag cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220728_135735_supplier_add_flag cannot be reverted.\n";

        return false;
    }
    */
}
