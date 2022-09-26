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
 * Class m220922_140555_default_sorting
 */
class m220922_140555_default_sorting extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->update('configuration', [
            'configuration_value' => 'na',
        ], [
            'configuration_key' => 'PRODUCT_LISTING_DEFAULT_SORT_ORDER',
            'configuration_value' => 'nd',
        ]);

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
    }
}
