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
 * Class m230823_143022_acl
 */
class m230823_143022_acl extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('admin/main', [
            'FILE_UPLOAD' => 'Allow file uploads',
        ]);
        $this->appendAcl(['FILE_UPLOAD']);
        $this->db->createCommand("UPDATE `access_control_list` SET `sort_order` = '0' WHERE `access_control_list_key` = 'FILE_UPLOAD';")->execute();
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->removeTranslation('admin/main', [
            'FILE_UPLOAD',
        ]);
        $this->removeAcl(['FILE_UPLOAD']);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230823_143022_acl cannot be reverted.\n";

        return false;
    }
    */
}
