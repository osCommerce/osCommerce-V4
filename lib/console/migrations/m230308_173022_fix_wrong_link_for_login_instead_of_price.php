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
 * Class m230308_173022_fix_wrong_link_for_login_instead_of_price
 */
class m230308_173022_fix_wrong_link_for_login_instead_of_price extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('main', ['TEXT_PLEASE_LOGIN' => 'Please <a href="%s">log in</a> to see your price'], true);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m230308_173022_fix_wrong_link_for_login_instead_of_price cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230308_173022_fix_wrong_link_for_login_instead_of_price cannot be reverted.\n";

        return false;
    }
    */
}
