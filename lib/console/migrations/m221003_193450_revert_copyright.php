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
 * Class m221003_193450_revert_copyright
 */
class m221003_193450_revert_copyright extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->removeTranslation('admin/main', ['TEXT_COPYRIGHT']);
        $this->addTranslation('admin/main', ['TEXT_COPYRIGHT' => 'Copyright &copy; 2005 &ndash;']);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m221003_193450_revert_copyright cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m221003_193450_revert_copyright cannot be reverted.\n";

        return false;
    }
    */
}
