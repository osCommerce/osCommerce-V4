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
 * Class m230413_121603_fix_sorting_translations
 */
class m230413_121603_fix_sorting_translations extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $fix = [
            'TEXT_BY_MODEL' => 'Model A-Z',
            'TEXT_BY_MANUFACTURER' => 'Brand A-Z',
            'TEXT_BY_QUANTITY' => 'Quantity Low to High',
            'TEXT_BY_WEIGHT' => 'Weight Low to High',
            'TEXT_BY_DATE' => 'Date New to Old',
            'TEXT_BY_POPULARITY' => 'Popularity Low to High',
        ];

        $this->addTranslation('main', $fix, true);
        $this->addTranslation('admin/design', $fix, true);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m230413_121603_fix_sorting_translations cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230413_121603_fix_sorting_translations cannot be reverted.\n";

        return false;
    }
    */
}
