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
 * Class m231222_133418_copy_product_review
 */
class m231222_133418_copy_product_review extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('admin/reviews', ['TEXT_INFO_COPY_REVIEW_INTRO' => 'Please choose a new product you wish to copy this review to']);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m231222_133418_copy_product_review cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m231222_133418_copy_product_review cannot be reverted.\n";

        return false;
    }
    */
}
