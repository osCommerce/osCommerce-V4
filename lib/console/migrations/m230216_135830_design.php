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
 * Class m230216_135830_design
 */
class m230216_135830_design extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('admin/design',[
            'EDIT_MODE' => 'Edit mode',
            'WINDOW_WIDTH' => 'Window width',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
    }
}
