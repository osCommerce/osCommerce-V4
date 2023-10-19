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
 * Class m230323_115002_fix_translation_en6
 */
class m230323_115002_fix_translation_en6 extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('admin/main',
            [
                'TEXT_NO_SELECTED_CATEGORIES'   => "You haven't select categories\nPlease, select at least one!",
                'TEXT_NO_SELECTED_CUSTOMERS'    => "You haven't select customers\nPlease, select at least one!",
                'TEXT_NO_SELECTED_ORDERS'       => "You haven't select orders\nPlease, select at least one!",
                'TEXT_NO_SELECTED_PRODUCTS'     => "You haven't select products\nPlease, select at least one!",
            ],
            true);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m230323_115002_fix_translation_en6 cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230323_115002_fix_translation_en6 cannot be reverted.\n";

        return false;
    }
    */
}
