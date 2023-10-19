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
 * Class m230804_085614_address
 */
class m230804_085614_address extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('main', [
            'TEXT_DROP_SHIP_ADDRESS' => 'Drop Ship Address',
            'TEXT_DROP_SHIP_NOTE1' => 'Drop Ship Address is a one time address, so it will be stored in your address book temporarily',
            'TEXT_DROP_SHIP_NOTE2' => 'This is a One time shipment address so please note this address won\'t be saved in your account',
            'TEXT_DROP_SHIP_NOTE3' => 'If yo want to save a new Ship To address then please got to My Account -> Address Book and add a new address -> Save',
        ]);
        $this->addTranslation('admin/customers/customeredit', [
            'TEXT_USE_DROP_SHIP' => 'Customer can use drop shipping addresses',
            'TEXT_DROP_SHIP' => 'Drop Ship',
        ]);
        if ($this->isTableExists('customers')) {
            if (!$this->isFieldExists('can_use_drop_ship', 'customers')) {
                $this->addColumn('customers', 'can_use_drop_ship', $this->integer(1)->notNull()->defaultValue(0));
            }
        }
        if ($this->isTableExists('address_book')) {
            if (!$this->isFieldExists('drop_ship', 'address_book')) {
                $this->addColumn('address_book', 'drop_ship', $this->integer(1)->notNull()->defaultValue(0));
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m230804_085614_address cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230804_085614_address cannot be reverted.\n";

        return false;
    }
    */
}
