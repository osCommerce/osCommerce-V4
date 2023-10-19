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
 * Class m230818_120111_hide_empty_brands
 */
class m230818_120111_hide_empty_brands extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('admin/design', [
            'SHOW_EMPTY_BRANDS' => 'Show empty brands',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
    }
}
