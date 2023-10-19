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
 * Class m230531_085145_filter_without_images
 */
class m230531_085145_filter_without_images extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('admin/categories', [
            'TEXT_FILTER_WITHOUT_IMAGES' => 'Without images',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m230531_085145_filter_without_images cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230531_085145_filter_without_images cannot be reverted.\n";

        return false;
    }
    */
}
