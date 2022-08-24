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
 * Class m220819_090148_install
 */
class m220819_090148_install extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->removeTranslation('admin/install', [
            'TEXT_NO_UPDATES',
        ]);
        $this->addTranslation('admin/install', [
            'TEXT_NO_UPDATES' => 'You have latest version of osCommerce',
            'TEXT_SHOW_UPDATES' => 'show updates log',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m220819_090148_install cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220819_090148_install cannot be reverted.\n";

        return false;
    }
    */
}
