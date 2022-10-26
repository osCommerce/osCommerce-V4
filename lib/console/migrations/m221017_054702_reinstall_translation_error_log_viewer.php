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
 * Class m221017_054702_reinstall_translation_error_log_viewer
 */
class m221017_054702_reinstall_translation_error_log_viewer extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->reinstallExtTranslation('ErrorLogViewer');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m221017_054702_reinstall_translation_error_log_viewer cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m221017_054702_reinstall_translation_error_log_viewer cannot be reverted.\n";

        return false;
    }
    */
}
