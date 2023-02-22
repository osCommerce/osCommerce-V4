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
 * Class m230214_091359_designer
 */
class m230214_091359_designer extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('admin/design',[
            'IMPORT_THEME' => 'Import theme',
            'BACK_TO_ROOT' => 'Back to root',
            'ADD_THEME_GROUP' => 'Add theme group',
            'BASIC_MODE' => 'Basic',
            'ADVANCED_MODE' => 'Advanced',
            'EXPERT_MODE' => 'Expert',
            'MODE_SWITCH_WARNING' => 'Mode switch warning',
            'ARE_YOU_SURE_MODE_ADVANCED' => 'Are you sure you want to switch designer mode to advanced?',
            'ARE_YOU_SURE_MODE_EXPERT' => 'Are you sure you want to switch designer mode to expert?',
            'ARE_YOU_SURE_MODE_BASIC' => 'Are you sure you want to switch designer mode to basic?',
            'RESPONSIVE_DESIGN' => 'Responsive design',
            'PSEUDO_CLASSES' => 'Pseudo classes',
            'VERTICAL_ALIGN' => 'Vertical align',
            'VERTICAL_ALIGN_BASELINE' => 'baseline',
            'VERTICAL_ALIGN_BOTTOM' => 'bottom',
            'VERTICAL_ALIGN_MIDDLE' => 'middle',
            'VERTICAL_ALIGN_SUB' => 'sub',
            'VERTICAL_ALIGN_SUPER' => 'super',
            'VERTICAL_ALIGN_TEXT_BOTTOM' => 'text-bottom',
            'VERTICAL_ALIGN_TEXT_TEXT_TOP' => 'text-top',
            'VERTICAL_ALIGN_TEXT_TOP' => 'top',
            'TEXT_TRANSFORM' => 'Transform',
            'TEXT_DECORATION' => 'Text decoration',
            'TEXT_DECORATION_NONE' => 'none',
            'TEXT_DECORATION_UNDERLINE' => 'underline',
            'TEXT_DECORATION_LINE_THROUGH' => 'line through',
            'TEXT_DECORATION_OVERLINE' => 'overline',
            'FONT_STYLE' => 'Font Style',
            'FONT_STYLE_ITALIC' => 'italic',
            'TEXT_CURSOR' => 'Cursor',
            'CURSOR_CROSSHAIR' => 'crosshair',
            'CURSOR_HELP' => 'help',
            'CURSOR_MOVE' => 'move',
            'CURSOR_POINTER' => 'pointer',
            'CURSOR_PROGRESS' => 'progress',
            'CURSOR_TEXT' => 'text',
            'CURSOR_WAIT' => 'wait',
            'CURSOR_RESIZE' => 'resize',
            'BOX_SHADOW' => 'Box shadow',
            'TEXT_OPACITY' => 'Opacity',
            'TEXT_ROTATE' => 'Rotate',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
    }
}
