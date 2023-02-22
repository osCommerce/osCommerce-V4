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
 * Class m221208_143240_designer
 */
class m221208_143240_designer extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('admin/design',[
            'SAVE_TO_WIDGET_GROUPS' => 'Save to widget groups',
            'WIDGET_GROUP_CATEGORY' => 'Widget group category',
            'NO_CATEGORIZED' => 'No categorized',
            'DOWNLOAD_ON_MY_COMPUTER' => 'Download on my computer',
            'SAVED_TO_GROUPS' => '%s saved to groups',
            'EDIT_WIDGETS' => 'Edit Widgets',
            'EDIT_TEXTS' => 'Edit Texts',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
    }
}
