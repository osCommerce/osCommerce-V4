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
 * Class m220727_095640_supplier_address_fields
 */
class m220727_095640_supplier_address_fields extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if (!$this->isFieldExists('street_address', 'suppliers')) {
            $this->addColumn('suppliers', 'street_address', $this->string(128)->notNull()->defaultValue(''));
        }
        if (!$this->isFieldExists('suburb', 'suppliers')) {
            $this->addColumn('suppliers', 'suburb', $this->string(128)->notNull()->defaultValue(''));
        }
        if (!$this->isFieldExists('city', 'suppliers')) {
            $this->addColumn('suppliers', 'city', $this->string(128)->notNull()->defaultValue(''));
        }
        if (!$this->isFieldExists('postcode', 'suppliers')) {
            $this->addColumn('suppliers', 'postcode', $this->string(128)->notNull()->defaultValue(''));
        }
        if (!$this->isFieldExists('state', 'suppliers')) {
            $this->addColumn('suppliers', 'state', $this->string(128)->notNull()->defaultValue(''));
        }
        if (!$this->isFieldExists('country', 'suppliers')) {
            $this->addColumn('suppliers', 'country', $this->string(128)->notNull()->defaultValue('GB'));
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m220727_095640_supplier_address_fields cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220727_095640_supplier_address_fields cannot be reverted.\n";

        return false;
    }
    */
}
