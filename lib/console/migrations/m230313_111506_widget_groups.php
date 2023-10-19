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
 * Class m230313_111506_widget_groups
 */
class m230313_111506_widget_groups extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if (!$this->isFieldExists('category', 'design_boxes_groups')){
            $this->addColumn('design_boxes_groups', 'category', $this->string(255)->notNull()->defaultValue(''));
        }
        if (!$this->isTableExists('design_boxes_groups_images')){
            $this->createTable('design_boxes_groups_images', [
                'boxes_group_image_id' => $this->primaryKey(),
                'boxes_group_id' => $this->integer(32)->notNull()->defaultValue(0),
                'file' => $this->string(255)->notNull()->defaultValue(''),
            ]);
        }
        if (!$this->isTableExists('design_boxes_groups_languages')){
            $this->createTable('design_boxes_groups_languages', [
                'boxes_group_id' => $this->integer(11)->notNull(),
                'language_id' => $this->integer(11)->notNull(),
                'title' => $this->string(255)->notNull()->defaultValue(''),
                'description' => $this->text()->notNull(),
            ]);
            $this->addPrimaryKey('', 'design_boxes_groups_languages', ['boxes_group_id', 'language_id']);
        }
        if (!$this->isTableExists('design_boxes_groups_category')){
            $this->createTable('design_boxes_groups_category', [
                'boxes_group_category_id' => $this->primaryKey(),
                'parent_category' => $this->string(255)->notNull()->defaultValue(''),
                'name' => $this->string(255)->notNull()->defaultValue(''),
            ]);
        }

        $this->addTranslation('admin/design',[
            'GROUP_NOT_FOUND' => 'Group not found',
            'SHOW_ON_WIDGETS_LIST' => 'Show on widgets list',
            'SHOW_ON_PAGE_TYPE' => 'Show on page-type',
            'ARE_YOU_SURE_DELETE_GROUP' => 'Are you sure you want to delete this widget group?',
            'ADD_ONE_MORE_IMAGE' => 'Add one more image',
            'GROUPS_COUNT' => 'Groups count',
        ]);

        $this->addTranslation('admin/main',[
            'TEXT_CATEGORY' => 'Category',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
    }
}
