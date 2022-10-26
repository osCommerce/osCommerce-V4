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
 * Class m221007_401315_gift_message
 */
class m221007_401315_gift_message extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn("products_options_values", "custom_input_type", $this->tinyInteger(2)->defaultValue(null));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m221007_401315_gift_message cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m221007_401315_gift_message cannot be reverted.\n";

        return false;
    }
    */
}
