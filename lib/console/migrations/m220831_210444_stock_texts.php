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
 * Class m220831_210444_stock_texts
 */
class m220831_210444_stock_texts extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('admin/categories', [
          'TEXT_STOCK_OVERALL' => 'Overall stock',
          'TEXT_STOCK_SPLIT_PLATFORMS' => 'Split stock between platforms',
          'TEXT_STOCK_PLATFORMS_TO_WAREHOUSE' => 'Assign platform to warehouse',
          'WARNING_STOCK_ASSIGNED' => 'Some stock is assigned to platform you\'ve disabled. See Main details tab to update',
        ]);

        $this->getDb()->createCommand("update products_stock_indication set allow_out_of_stock_add_to_cart=0 where stock_code='out-stock'")->execute();

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
//        echo "m220831_210444_stock_texts cannot be reverted.\n";

  //      return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220831_210444_stock_texts cannot be reverted.\n";

        return false;
    }
    */
}
