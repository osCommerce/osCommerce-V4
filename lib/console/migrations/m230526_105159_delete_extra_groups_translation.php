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
 * Class m230526_105159_delete_extra_groups_translation
 */
class m230526_105159_delete_extra_groups_translation extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if (method_exists($this, 'isOldExtension'))
        {
            if (!$this->isOldExtension($code))
            {
                $this->removeTranslation('admin/main', [
                    'TEXT_DEFAULT_GROUP_TITLE',
                    'TEXT_GROUPS_TYPE',
                    'TEXT_GROUPS_TYPE_CODE',
                    'TEXT_GROUPS_TYPE_NAME',
                    'TEXT_GROUPS_TYPE_ADD',
                    'TEXT_GROUPS_TYPE_ADD_SAVED',
                    'TEXT_GROUPS_TYPE_CONFIRM_DELETE'
                ]);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->addTranslation('admin/main', [
            'TEXT_DEFAULT_GROUP_TITLE' => 'Price Level',
            'TEXT_GROUPS_TYPE' => 'Group Types',
            'TEXT_GROUPS_TYPE_CODE' => 'Code',
            'TEXT_GROUPS_TYPE_NAME' => 'Name',
            'TEXT_GROUPS_TYPE_ADD' => 'New Group Types',
            'TEXT_GROUPS_TYPE_ADD_SAVED' => 'Group type has been saved',
            'TEXT_GROUPS_TYPE_CONFIRM_DELETE' => 'Are you sure you want to delete the group type?',
        ]);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230526_105159_delete_extra_groups_translation cannot be reverted.\n";

        return false;
    }
    */
}
