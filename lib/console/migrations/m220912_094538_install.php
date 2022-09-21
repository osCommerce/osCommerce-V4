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
 * Class m220912_094538_install
 */
class m220912_094538_install extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('admin/install', [
            'TEXT_NEW_LANGUAGE' => 'New language',
            'TEXT_UPDATE_LANGUAGE' => 'Update language',
            'TEXT_NEW_LANGUAGE_INTRO' => 'This language is not present in the system.<br>By continuing you allow the installation of a new language',
            'TEXT_UPDATE_LANGUAGE_SETTINGS' => 'Update language settings',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m220912_094538_install cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220912_094538_install cannot be reverted.\n";

        return false;
    }
    */
}
