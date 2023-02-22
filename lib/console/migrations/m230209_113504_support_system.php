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
 * Class m230209_113504_support_system
 */
class m230209_113504_support_system extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if (method_exists($this, 'isOldExtension'))
        {
            if (!$this->isOldExtension('SupportSystem'))
            {
                $this->removeTranslation('admin/main', ['BOX_SUPPORT_SYSTEM_SETTINGS']);
                $this->removeAcl(['TEXT_SETTINGS', 'BOX_HEADING_CONFIGURATION', 'BOX_SUPPORT_SYSTEM_SETTINGS']);
                $this->removeAdminMenu('BOX_SUPPORT_SYSTEM_SETTINGS');

                $this->removeTranslation('admin/support-system');
                $this->removeTranslation('support-system');

                $this->removeConfigurationKeys([
                    'USE_SUPPORT_SYSTEM',
                    'DEFAULT_LIMIT_TEXT',
                    'SHOW_VIDEO_IN_PREVIE',
                    'OPEN_TOPIC_NEW_WINDOW',
                ]);

                if ($this->isTableExists('configuration_group')) {
                    $this->getDb()->createCommand("DELETE FROM `configuration_group` WHERE `configuration_group_title` = 'Support Settings'")->execute();
                }
            }
            if (!\common\helpers\Extensions::isInstalled('SupportSystem'))
            {
                $this->dropTableIfExists('support_system_info');
                $this->dropTableIfExists('support_system');
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $tableOption = 'ENGINE=InnoDB DEFAULT CHARSET=utf8';

        $this->createTable('support_system', [
            'topic_id' => $this->primaryKey(),
            'products_id' => $this->integer()->notNull(),
            'date_added' => $this->dateTime(),
            'last_modified' => $this->dateTime(),
            'status'    => $this->integer(1),
            'sort_order' => $this->integer()->defaultValue(0)
        ],$tableOption);
        $this->createIndex('idx_tp', 'support_system', ['products_id', 'topic_id']);

        $this->createTable('support_system_info', [
            'topic_id' => $this->integer()->notNull(),
            'language_id' => $this->integer(4)->notNull(),
            'platform_id' => $this->integer()->notNull(),
            'info_seo_name' => $this->string(255),
            'info_title' => $this->string(255),
            'info_text' => $this->text(),
            'info_video' => $this->string(255),
            'info_meta_title' => $this->string(255),
            'info_meta_description' => $this->text(),
        ], $tableOption);
        $this->addForeignKey('idx_fk', 'support_system_info', 'topic_id', 'support_system', 'topic_id');
        $this->addPrimaryKey('pk_idx', 'support_system_info', ['topic_id', 'language_id', 'platform_id']);

        $this->addTranslation('admin/main',[
            'BOX_SUPPORT_SYSTEM' => 'Support System',
        ]);
        $this->appendAcl(['BOX_HEADING_CATALOG', 'BOX_SUPPORT_SYSTEM']);
        $this->addAdminMenuAfter([
            'path' => 'support-system',
            'title' => 'BOX_SUPPORT_SYSTEM'
        ],'BOX_CATALOG_COMPETITORS');

        $this->getDb()->createCommand("INSERT INTO `configuration_group` (
                `configuration_group_id` ,`configuration_group_title` ,`configuration_group_description` ,`sort_order` ,`visible`)
            VALUES 
                (null,  'Support Settings',  'Support Settings options',  '501',  '1')
        ;")->execute();
        $gId = $this->getDb()->getLastInsertID();
        $this->getDb()->createCommand("INSERT INTO `configuration` (
                `configuration_id` ,
                `configuration_title` ,
                `configuration_key` ,
                `configuration_value` ,
                `configuration_description` ,
                `configuration_group_id` ,
                `sort_order` ,
                `last_modified` ,
                `date_added` ,
                `use_function` ,
                `set_function`
            ) 
            VALUES 
                (NULL ,  'Use Support System',  'USE_SUPPORT_SYSTEM',  'false',  'Use Support System module',  '".$gId."',  '1', NULL ,  '0000-00-00 00:00:00', NULL , 'tep_cfg_select_option(array(''true'', ''false''),'),
                (NULL ,  'Length of limited text',  'DEFAULT_LIMIT_TEXT',  '250',  'Length of limited text in preview',  '".$gId."',  '2', NULL ,  '0000-00-00 00:00:00', NULL , NULL),
                (NULL ,  'Show video iframe on preview',  'SHOW_VIDEO_IN_PREVIE',  'true',  'Show video iframe on preview',  '".$gId."',  '3', NULL ,  '0000-00-00 00:00:00', NULL ,  'tep_cfg_select_option(array(''true'', ''false''),'),
                (NULL ,  'Open topic at new window',  'OPEN_TOPIC_NEW_WINDOW',  'false',  'Open topic at new window',  '".$gId."',  '4', NULL ,  '0000-00-00 00:00:00', NULL ,  'tep_cfg_select_option(array(''true'', ''false''),')
            ;")->execute();
        $this->addTranslation('admin/main',[
            'BOX_SUPPORT_SYSTEM_SETTINGS' => 'Support System Settings',
        ]);
        $this->appendAcl(['TEXT_SETTINGS', 'BOX_HEADING_CONFIGURATION', 'BOX_SUPPORT_SYSTEM_SETTINGS']);
        $this->addAdminMenuAfter([
            'path' => 'configuration/index?groupid='.$gId,
            'title' => 'BOX_SUPPORT_SYSTEM_SETTINGS'
        ],'BOX_CONFIGURATION_BONUS_PROGRAMS');
        $this->addTranslation('admin/support-system', [
            'HEADING_TITLE'  => 'Support System',
            'TEXT_CREATE_NEW_TOPIC' => 'New Topic',
            'TEXT_EDIT_TOPIC' => 'Edit Topic',
            'TEXT_REMOVED' => 'Removed',
            'TEXT_SELECT_PRODUCT_PROMPT' => 'Please select product to new topic',
            'TEXT_CHANGE_PRODUCT' => 'Change Product',
            'TEXT_SELECT_PRODUCT' => 'Select Product',
        ]);
        $this->addTranslation('support-system', [
            'EMPTY_TOPICS_RANGE' => 'There are currently no support topics in this range.',
            'HEADING_TITLE'  => 'Support System',
        ]);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230209_113504_support_system cannot be reverted.\n";

        return false;
    }
    */
}
