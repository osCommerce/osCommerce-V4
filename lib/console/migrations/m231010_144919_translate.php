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
 * Class m231010_144919_translate
 */
class m231010_144919_translate extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('admin/platforms', [
            'ASSIGN_BANNERS_TO_PLATFORM' => 'Assign banners to this platform',
            'SKIP_BANNERS_ASSIGN_THEME' => 'Skip banners and assign theme',
            'ASSIGNED_THEME' => 'Assigned theme',
        ], true);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
    }
}
