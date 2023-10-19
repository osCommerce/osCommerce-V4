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
 * Class m230710_160049_remove_bonus_point_translation
 */
class m230710_160049_remove_bonus_point_translation extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->removeTranslation('main', [
            'YOUR_BONUS_POINTS_BALANCE',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->addTranslation('main', [
            'YOUR_BONUS_POINTS_BALANCE' => 'Your bonus points balance',
        ]);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230710_160049_remove_bonus_point_translation cannot be reverted.\n";

        return false;
    }
    */
}
