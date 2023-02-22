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
 * Class m230105_185650_products_price_exc_round
 */
class m230105_185650_products_price_exc_round extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $q1 = (new yii\db\Query())->from('configuration')->where(['configuration_key' => 'PRODUCTS_PRICE_EXC_ROUND'])->count();
        if ($q1 == 0) {
            $this->insert('configuration', [
                'configuration_title' => 'Round product price excl tax',
                'configuration_key' => 'PRODUCTS_PRICE_EXC_ROUND',
                'configuration_value' => 'false',
                'configuration_description' => 'On subtotal, total and tax calculation round product price excl tax before adding tax',
                'configuration_group_id' => 'BOX_CONFIGURATION_MYSTORE',
                'set_function' => "tep_cfg_select_option(array('true', 'false'),",
                'date_added' => new \yii\db\Expression('NOW()'),
            ]);
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m230105_185650_products_price_exc_round cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230105_185650_products_price_exc_round cannot be reverted.\n";

        return false;
    }
    */
}
