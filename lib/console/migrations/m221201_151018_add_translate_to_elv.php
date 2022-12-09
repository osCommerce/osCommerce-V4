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
 * Class m221201_151018_add_translate_to_elv
 */
class m221201_151018_add_translate_to_elv extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('extensions/error-log-viewer', [
            'EXT_ELV_ERR_NO_FILE_TO_DOWNLOAD' => 'No logs to download',
            'EXT_ELV_ERR_CREATE_ZIP' => 'Failure to create zip file. Check permission',
            ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->removeTranslation('extensions/error-log-viewer', [
            'EXT_ELV_ERR_NO_FILE_TO_DOWNLOAD',
            'EXT_ELV_ERR_CREATE_ZIP',
        ]);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m221201_151018_add_translate_to_elv cannot be reverted.\n";

        return false;
    }
    */
}
