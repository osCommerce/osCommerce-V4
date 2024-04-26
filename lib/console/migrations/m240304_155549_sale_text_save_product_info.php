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
 * Class m240304_155549_sale_text_save_product_info
 */
class m240304_155549_sale_text_save_product_info extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('main', [
            'SALE_TEXT_SAVE_PRODUCT_INFO' => '%s',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m240304_155549_sale_text_save_product_info cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240304_155549_sale_text_save_product_info cannot be reverted.\n";

        return false;
    }
    */
}
