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
 * Class m220830_213528_stock_control_text
 */
class m220830_213528_stock_control_text extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->removeTranslation('configuration', ['PURCHASE_OFF_STOCK_DESC']);
        $this->addTranslation('configuration', ['PURCHASE_OFF_STOCK_DESC' => 'Purchase out of stock products. False Affected to "Stock availability by q-ty in stock" only']);

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        //echo "m220830_213528_stock_control_text cannot be reverted.\n";

        //return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220830_213528_stock_control_text cannot be reverted.\n";

        return false;
    }
    */
}
