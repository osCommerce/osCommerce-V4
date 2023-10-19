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
 * Class m230605_111342_fix_furniture_checkout_412
 */
class m230605_111342_fix_furniture_checkout_412 extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $dbtRec = \common\models\DesignBoxesTmp::findOne(['theme_name' => 'furniture', 'block_name' => 'checkout']);

        if (empty($dbtRec)) {
            $this->addWidget('checkout', 'lib/console/migrations/themes/furniture/desktop/furniture_checkout_412.zip', '', 'furniture');
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m230605_111342_fix_furniture_checkout_412 cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230605_111342_fix_furniture_checkout_412 cannot be reverted.\n";

        return false;
    }
    */
}
