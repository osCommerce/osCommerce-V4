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
 * Class m221205_103313_elv_translation
 */
class m221205_103313_elv_translation extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if (\common\helpers\Extensions::isInstalled('ErrorLogViewer')) {
            $this->removeTranslation('extensions/error-log-viewer',[
                'EXT_ELV_ERR_EXT_DIR_NOT_WRITABLE',
                'EXT_ELV_ERR_CREATE_ZIP',
            ]);
            $this->addTranslation('extensions/error-log-viewer',[
                'EXT_ELV_ERR_DELETE_OLD_ZIP' => 'Failure to remove old zip file(s). Error: <strong>%s</strong>',
                'EXT_ELV_ERR_CREATE_ZIP' => 'Failure to create zip file. Error: <strong>%s</strong>',
                'EXT_ELV_ERR_CREATE_TMP' => 'Can not create temporary folder',
            ]);
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m221205_103313_elv_translation cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m221205_103313_elv_translation cannot be reverted.\n";

        return false;
    }
    */
}
