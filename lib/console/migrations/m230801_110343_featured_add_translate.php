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
 * Class m230801_110343_featured_add_translate
 */
class m230801_110343_featured_add_translate extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('admin/featured', [
            'TEXT_SAVE_SORT' => 'Save as current sort',
            'TEXT_LOAD_SORT' => 'Load current sort'
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->removeTranslation('admin/featured', [
            'TEXT_SAVE_SORT',
            'TEXT_LOAD_SORT'
        ]);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230801_110343_featured_add_translate cannot be reverted.\n";

        return false;
    }
    */
}
