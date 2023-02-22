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
 * Class m230106_120153_translate
 */
class m230106_120153_translate extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('admin/main',[
            'ADD_TO_DESCRIPTION' => 'Add to description',
            'SHOW_COLUMNS' => 'Show columns',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
    }
}
