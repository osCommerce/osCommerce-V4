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
 * Class m230915_171023_super_login_acl
 */
class m230915_171023_super_login_acl extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('admin/main', [
            'T_SUPER_LOGIN' => 'Super login',
        ]);
        $this->appendAcl(['ACL_CUSTORER', 'T_SUPER_LOGIN']);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m230915_171023_super_login_acl cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230915_171023_super_login_acl cannot be reverted.\n";

        return false;
    }
    */
}
