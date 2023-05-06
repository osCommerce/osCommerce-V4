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
 * Class m230323_074536_install
 */
class m230323_074536_install extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('admin/easypopulate', [
            'TEXT_APPLICATION' => 'Application',
            'TEXT_REQUIREMENTS' => 'Requirements',
            'TEXT_STATE' => 'State',
            'MESSAGE_KEY_DOMAIN_INFO2' => 'This shop is already registered to %3$s and is not shared key to all administrators. You need to connect it using your own credentials.<br>
If your already registered with us and %3$s, approved your storage key, please insert \'storage\' key value. If you do not remember your \'storage\' key - please login at <a target="_blank" href="%1$s">application shop</a> with your credentials and copy it from there.<br>
If you not registered with us, please visit <a target="_blank" href="%1$s">application shop</a>, register your account and put there your \'security store key\'.<br>
You \'secutiry store key\' for this shop is [%2$s].<br>
After registration, wait for confirmation by %3$s. If this approuve take a lot, you may e-mail him directly. After confirmation insert the received \'storage\' key (<a href="javascript:void(0);" onclick="$(\'.create_item_popup\').click();">use button on this page</a>) value.',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
      //  echo "m230323_074536_install cannot be reverted.\n";

       // return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230323_074536_install cannot be reverted.\n";

        return false;
    }
    */
}
