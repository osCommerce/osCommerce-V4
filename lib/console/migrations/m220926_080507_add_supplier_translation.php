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
 * Class m220926_080507_add_supplier_translation
 */
class m220926_080507_add_supplier_translation extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->removeTranslation('admin/categories', ['TEXT_SUPPLIERS_OUR_PRICE']);

        $this->addTranslation('admin/categories', [
            'TEXT_SUPPLIER_SORT_HEADER' => 'Default sort:',
            'TEXT_SUPPLIER_SET_PRICE_ERR_SAME' => 'The price is exactly the same already. Nothing changed',
            'TEXT_SUPPLIER_PRICE_UNDO' => 'Undo',
            'TEXT_SUPPLIERS_OUR_PRICE' => 'Our (landed) price',
            'TEXT_SUPPLIERS_EDIT_LANDED_PRICE' => 'Edit Landed Price',
            'TEXT_SUPPLIER_LANDED_PRICE_CALC' => 'Calculated:',
            'TEXT_SUPPLIER_LANDED_PRICE_MANUAL' => 'Manually:',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m220926_080507_add_supplier_translation cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220926_080507_add_supplier_translation cannot be reverted.\n";

        return false;
    }
    */
}
