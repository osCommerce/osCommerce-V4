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
 * Class m221223_092305_banners
 */
class m221223_092305_banners extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->alterColumn('banners_new', 'banners_group', $this->string(255)->notNull()->defaultValue(''));

        $this->addTranslation('admin/banner_manager',[
            'IMAGE_VIDEO_WIDTH' => 'Image/video width',
            'OPEN_LINK_IN_NEW_TAB' => 'Open link in new tab',
            'GROUP_RESOLUTIONS' => 'Group resolutions',
            'NO_RESOLUTIONS' => 'No resolutions',
            'EDIT_GROUP' => 'Edit group',
            'WINDOW_WIDTH' => 'Window width',
            'IMAGE_VIDEO_SIZES' => 'Image/video sizes',
        ]);


        // DELETE b FROM banners_new b LEFT JOIN banners_languages l on b.banners_id = l.banners_id WHERE l.banners_id IS NULL
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
    }
}
