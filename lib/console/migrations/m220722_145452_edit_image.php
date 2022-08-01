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
 * Class m220722_145452_edit_image
 */
class m220722_145452_edit_image extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('admin/main',[
            'TEXT_EDIT_IMAGE' => 'Edit image',
            'TEXT_AFTER_SAVING' => 'After saving, paints the side space',
            'TEXT_CHOOSE_SIDE_COLOR' => 'Choose side space color',
            'TEXT_ALIGN_BORDERS' => 'Align borders',
            'TEXT_POOR_QUALITY' => 'Allow poor quality',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->removeTranslation('admin/main', [
            'TEXT_EDIT_IMAGE',
            'TEXT_AFTER_SAVING',
            'TEXT_CHOOSE_SIDE_COLOR',
            'TEXT_ALIGN_BORDERS',
            'TEXT_POOR_QUALITY',
        ]);
    }
}
