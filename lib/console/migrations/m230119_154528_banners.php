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
 * Class m230119_154528_banners
 */
class m230119_154528_banners extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if (!$this->isFieldExists('fit', 'banners_groups_images')) {
            $this->addColumn('banners_groups_images', 'fit', $this->string(32)->notNull()->defaultValue(''));
        }
        if (!$this->isFieldExists('position', 'banners_groups_images')) {
            $this->addColumn('banners_groups_images', 'position', $this->string(32)->notNull()->defaultValue(''));
        }

        $this->addTranslation('admin/main',[
            'ADD_TO_DESCRIPTION' => 'Add to description',
            'SHOW_COLUMNS' => 'Show columns',
            'IMAGE_FIT' => 'Image fit',
            'IMAGE_FIT_COVER' => 'cover',
            'IMAGE_FIT_FILL' => 'fill',
            'IMAGE_FIT_CONTAIN' => 'contain',
            'IMAGE_FIT_NONE' => 'none',
            'IMAGE_FIT_SCALE_DOWN' => 'scale-down',
            'IMAGE_POSITION' => 'Image position',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
    }
}
