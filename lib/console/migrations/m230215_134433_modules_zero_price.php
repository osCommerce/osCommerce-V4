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
 * Class m230215_134433_modules_zero_price
 */
class m230215_134433_modules_zero_price extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $code = "ModulesZeroPrice";
        if (method_exists($this, 'isOldExtension'))
        {
            if (!$this->isOldExtension($code))
            {
                if (!((class_exists("\\common\\extensions\\$code\\$code"))
                    && !method_exists("\\common\\extensions\\$code\\$code", 'optionPriceFree')))
                {
                    $this->removeConfigurationKeys('MODULE_PRICE_FREE');
                }
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m230215_134433_modules_zero_price cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230215_134433_modules_zero_price cannot be reverted.\n";

        return false;
    }
    */
}
