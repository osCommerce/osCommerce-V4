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
 * Class m221109_161613_checkout_recalculate_fields
 */
class m221109_161613_checkout_recalculate_fields extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->insert('configuration',[
            'configuration_title' => 'Trigger recalculate shipping',
            'configuration_key' => 'TRIGGER_RECALCULATE_FIELDS',
            'configuration_value' => 'postcode, state, city, country',
            'configuration_description' => '',
            'configuration_group_id' => 'BOX_SHIPPING_CUSTOMER_DETAILS',
            'sort_order' => 3245,
            'date_added' => new \yii\db\Expression('NOW()'),
            'use_function' => 'getCheckoutRecalculateFields',
            'set_function' => 'setCheckoutRecalculateFields('
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m221109_161613_checkout_recalculate_fields cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m221109_161613_checkout_recalculate_fields cannot be reverted.\n";

        return false;
    }
    */
}
