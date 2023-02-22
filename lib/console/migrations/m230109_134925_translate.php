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
 * Class m230109_134925_translate
 */
class m230109_134925_translate extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->update('translation',
            ['translation_entity' => 'admin/main'],
            ['translation_key' => 'TEXT_PRODUCT_NOT_SELECTED', 'translation_entity' => 'admin/orders']
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
    }
}
