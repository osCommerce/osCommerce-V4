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
 * Class m220505_105639_supplier_extra_fields
 */
class m220505_105639_supplier_extra_fields extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if (!$this->isFieldExists('contact_name', 'suppliers')) {
            $this->addColumn('suppliers', 'contact_name', $this->string(128)->notNull()->defaultValue(''));
        }
        if (!$this->isFieldExists('contact_phone', 'suppliers')) {
            $this->addColumn('suppliers', 'contact_phone', $this->string(128)->notNull()->defaultValue(''));
        }
        if (!$this->isFieldExists('awrs_no', 'suppliers')) {
            $this->addColumn('suppliers', 'awrs_no', $this->string(128)->notNull()->defaultValue(''));
        }
        if (!$this->isFieldExists('sage_code', 'suppliers')) {
            $this->addColumn('suppliers', 'sage_code', $this->string(128)->notNull()->defaultValue(''));
        }
        if (!$this->isFieldExists('payment_delay', 'suppliers')) {
            $this->addColumn('suppliers', 'payment_delay', $this->integer(11)->notNull()->defaultValue(0));
        }
        if (!$this->isFieldExists('supply_delay', 'suppliers')) {
            $this->addColumn('suppliers', 'supply_delay', $this->integer(11)->notNull()->defaultValue(0));
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m220505_105639_supplier_extra_fields cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220505_105639_supplier_extra_fields cannot be reverted.\n";

        return false;
    }
    */
}
