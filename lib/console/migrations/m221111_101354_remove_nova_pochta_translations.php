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
 * Class m221111_101354_remove_nova_pochta_translations
 */
class m221111_101354_remove_nova_pochta_translations extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->removeTranslation('main', [
            'ENTRY_NP_AREA_SELECT',
            'ENTRY_NP_CITY_SELECT',
            'ENTRY_NP_WAREHOUSE_SELECT',
            'TEXT_NALOZHENNUJ_PLATEZH_NP',
        ]);
        $this->removeTranslation('admin/main', [
            'ENTRY_NP_AREA_SELECT',
            'ENTRY_NP_CITY_SELECT',
            'ENTRY_NP_WAREHOUSE_SELECT',
            'TEXT_NALOZHENNUJ_PLATEZH_NP',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m221111_101354_remove_nova_pochta_translations cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m221111_101354_remove_nova_pochta_translations cannot be reverted.\n";

        return false;
    }
    */
}
