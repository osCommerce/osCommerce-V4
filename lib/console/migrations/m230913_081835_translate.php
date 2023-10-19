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
 * Class m230913_081835_translate
 */
class m230913_081835_translate extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('admin/design', [
            'ADD_WIDGET' => 'Add Widget',
            'EXPORT_BLOCK' => 'Export Block',
            'EDIT_BLOCK' => 'Edit Block',
            'MOVE_BLOCK' => 'Move block',
            'REMOVE_WIDGET' => 'Remove Widget',
            'EDIT_WIDGET' => 'Edit Widget',
            'EDIT_WIDGET_STYLES' => 'Edit widget styles (global for all same widgets)',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
    }
}
