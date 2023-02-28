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
 * Class m220805_074648_install
 */
class m220805_074648_install extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->removeTranslation('admin/main', [
            'BOX_HEADING_INSTALL'
        ]);
        $this->addTranslation('admin/main', [
            'BOX_HEADING_INSTALL' => 'App Shop',
            'TEXT_INSTALL_CACHE' => 'App Shop Cache',
            'MESSAGE_KEY_EMPTY' => 'Your shop is not connected with <a href="%1$s">App Shop</a>. Why and how do I need to connect to App Shop? Please see the article <a target="_blank" href="%2$s">Connecting to App shop</a> for more details.',
            'MESSAGE_SYSTEM_UPDATES' => 'The updates are available in <a href="%1$s">App Shop</a>. Please check it for more details.',
        ]);
        
        $this->removeTranslation('admin/easypopulate', [
            'MESSAGE_KEY_DOMAIN_OK',
            'MESSAGE_KEY_DOMAIN_ERROR',
            'MESSAGE_KEY_DOMAIN_INFO'
        ]);
        $this->addTranslation('admin/easypopulate', [
            'MESSAGE_KEY_DOMAIN_OK' => 'Your store successfully connected to our <a target="_blank" href="%1$s">application shop</a>. You \'security store key\' for this shop is [%2$s].',
            'MESSAGE_KEY_DOMAIN_ERROR' => 'Error: Your \'storage\' key is wrong. Please login at <a target="_blank" href="%1$s">application shop</a> with your credentials and copy it from there.',
            'MESSAGE_KEY_DOMAIN_INFO' => 'It looks like your store is not connected with our <a target="_blank" href="%1$s">application shop</a>.<br>If you are already registered with us please insert \'storage\' key value. If you do not remember your \'storage\' key - please login at <a target="_blank" href="%1$s">application shop</a> with your credentials and copy it from there.<br>If you are not registered with us yet, please visit <a target="_blank" href="%1$s">application shop</a>, register your account and put your \'security store key\' there.<br>Your \'secutiry store key\' for this shop is [%2$s].<br>After registration insert the received \'storage\' key (<a href="javascript:void(0);" onclick="$(\'.create_item_popup\').click();">use button on this page</a>) value.',
        ]);
        
        $this->addTranslation('admin/install', [
            'TEXT_SYSTEM_UPDATES' => 'System updates',
            'TEXT_CHANGELOG_INTRO' => 'for more information see changelog',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m220805_074648_install cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220805_074648_install cannot be reverted.\n";

        return false;
    }
    */
}
