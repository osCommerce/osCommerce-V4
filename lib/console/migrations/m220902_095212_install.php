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
 * Class m220902_095212_install
 */
class m220902_095212_install extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('admin/install', [
            'TEXT_APPLY_MIGRATIONS' => 'Apply migrations...',
            'TEXT_CLEAN_CACHE' => 'Clean cache...',
            'TEXT_CLEAN_SMARTY' => 'Clean smarty...',
            'TEXT_CACHE_FLUSHED' => 'Opcode cache flushed',
            'TEXT_CHECKING' => 'Start checking',
            'TEXT_PACK_INSTALLED' => 'successfully installed',
            'TEXT_PACK_ABORTED' => 'installation aborted',
            'TEXT_ACTION_ERROR' => 'Wrong action',
            'TEXT_TYPE_ERROR' => 'Wrong type',
            'TEXT_CHECKSUM_ERROR' => 'checksum mismatch',
            'TEXT_CHECKSUM_PASSED' => 'checksum passed',
            'TEXT_FILE' => 'File',
            'TEXT_DIRECTORY' => 'Directory',
            'TEXT_ADDED' => 'added',
            'TEXT_MODIFIED' => 'modified',
            'TEXT_COPIED' => 'copied',
            'TEXT_DELETED' => 'deleted',
            'APP_INSTALL_OK' => 'Application successfully installed.',
            'APP_INSTALL_FAIL' => 'Failed to install application.',
            'TEXT_UPDATE_APPLIED' => 'Applied system update',
            'TEXT_CHECK_UPDATES' => 'Check updates for',
            'TEXT_FOUND_UPDATE' => 'Update found',
            'TEXT_UPDATE_FINISH' => 'System update finished',
            'TEXT_NO_UPDATES' => 'Nothing to update',
            'TEXT_FORCE_UPDATE' => 'force update',
            'TEXT_FORCE_UPDATE_INTRO' => 'In this case all local changes will be lost',
            'TEXT_USE' => 'You can use',
            'TEXT_DOWNLOADED' => 'downloaded',
            'TEXT_ALREADY_DOWNLOADED' => 'already downloaded',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m220902_095212_install cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220902_095212_install cannot be reverted.\n";

        return false;
    }
    */
}
