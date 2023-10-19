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
 * Class m230601_104616_add_translate_for_brand
 */
class m230601_104616_add_translate_for_brand extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('admin/main', ['TEXT_DELETE_MANUFACTURER' => 'Are you sure you want to delete this brand?']);
        $this->addTranslation('admin/main', ['TEXT_HEADING_DELETE_MANUFACTURER' => 'Delete Brand'], true);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m230601_104616_add_translate_for_brand cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230601_104616_add_translate_for_brand cannot be reverted.\n";

        return false;
    }
    */
}
