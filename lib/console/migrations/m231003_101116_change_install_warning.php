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
 * Class m231003_101116_change_install_warning
 */
class m231003_101116_change_install_warning extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('admin/easypopulate', [
            'MESSAGE_KEY_DOMAIN_WANING' => 'Warning: Security keys were generated for a different domain! Update required. Please change \'security store key\' to the actual value: %s.'
        ], true);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m231003_101116_change_install_warning cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m231003_101116_change_install_warning cannot be reverted.\n";

        return false;
    }
    */
}
