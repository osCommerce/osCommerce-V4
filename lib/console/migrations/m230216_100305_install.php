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
 * Class m230216_100305_install
 */
class m230216_100305_install extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->removeTranslation('admin/easypopulate', [
            'MESSAGE_KEY_DOMAIN_ERROR'
        ]);
        
        $this->addTranslation('admin/easypopulate', [
            'MESSAGE_KEY_DOMAIN_ERROR' => 'Error: Your \'storage\' key is wrong. Please login at <a target="_blank" href="%1$s">application shop</a> with your credentials and copy it from there. You \'security store key\' for this shop is [%2$s].',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m230216_100305_install cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230216_100305_install cannot be reverted.\n";

        return false;
    }
    */
}
