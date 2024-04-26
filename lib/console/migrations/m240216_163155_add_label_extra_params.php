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
 * Class m240216_163155_add_label_extra_params
 */
class m240216_163155_add_label_extra_params extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumnIfMissing(\common\models\OrdersLabel::tableName(), 'extra_params', $this->text());
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m240216_163155_add_label_extra_params cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240216_163155_add_label_extra_params cannot be reverted.\n";

        return false;
    }
    */
}
