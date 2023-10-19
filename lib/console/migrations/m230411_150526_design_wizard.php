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
 * Class m230411_150526_design_wizard
 */
class m230411_150526_design_wizard extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('admin/design',[
            'TEXT_CONTINUE' => 'Continue',
            'TEXT_FINISH' => 'Finish',
            'ENTER_THEME_NAME' => 'Please enter Theme name',
            'CREATE_THEME' => 'Create theme',
            'PLEASE_ENTER_NAME' => 'Please enter name',
            'CREATE_GROUP' => 'Create group',
            'TEXT_HEADER' => 'Header',
            'TEXT_FOOTER' => 'Footer',
            'TEXT_COLOR_SCHEME' => 'Color scheme',
            'THEME_ALREADY_EXISTS' => 'This theme already exists',
            'SYSTEM_NOT_READY' => 'The system is not ready to create a new theme, please wait a minute and try again.',
            'THEME_ADDED' => 'Theme added',
            'GROUP_APPLIED' => 'Group applied',
            'GROUP_FILE_NOT_FOUND' => 'Group file not found',
            'TEXT_APPLIED' => 'Applied',
            'CATEGORY_ALREADY_EXISTS' => 'Category %s already exists in this category',
            'FILE_ALREADY_EXISTS' => 'File "%s" already exists, please enter other name',
            'CHOOSE_PAGES' => 'Choose pages',
            'PLEASE_CHOOSE_THEME' => 'Please choose theme',
            'PLEASE_CHOOSE_PAGES' => 'Please choose pages',
            'PLEASE_ENTER_FILE_NAME' => 'Please enter file name',
            'PLEASE_CHOOSE_CATEGORY' => 'Please choose category',
            'PLEASE_ENTER_TITLE' => 'Please enter title',
            'SHOW_EMPTY_CATEGORIES' => 'Show empty categories',
            'PLEASE_ENTER_CATEGORY_NAME' => 'Please enter category name',
            'EXPORT_FONTS' => 'Export fonts',
            'EXPORT_COLORS' => 'Export colors',
            'EXPORT_STYLES' => 'Export styles',
            'TEXT_NAME' => 'Name',
            'SAVE_TO_THEME_WIZARD' => 'Save to theme wizard',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
    }
}
