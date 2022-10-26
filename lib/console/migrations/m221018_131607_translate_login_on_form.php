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
 * Class m221018_131607_translate_login_on_form
 */
class m221018_131607_translate_login_on_form extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
       $this->addTranslation('main',[
            'ALREADY_HAVE_AN_ACCOUNT' => 'Already have an account? Login',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m221018_131607_translate_login_on_form cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m221018_131607_translate_login_on_form cannot be reverted.\n";

        return false;
    }
    */
}
