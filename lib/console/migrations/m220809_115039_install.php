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
 * Class m220809_115039_install
 */
class m220809_115039_install extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addTranslation('admin/install', [
            'TEXT_ALL_PLATFORMS' => 'All platforms',
            'TEXT_DISCOVER' => 'Discover',
            'TEXT_INSTALL' => 'Install',
            'TEXT_DOWNLOADED' => 'Downloaded',
            'TEXT_INSTALLED' => 'Installed',
            'TEXT_UPDATE' => 'Update',
        ]);
        $this->addTranslation('admin/main', [
            'TEXT_APPLY_FOR_ALL' => 'Apply for all',
        ]);
        $this->appendAcl(['BOX_HEADING_INSTALL', 'TEXT_APPLY_FOR_ALL']);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m220809_115039_install cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220809_115039_install cannot be reverted.\n";

        return false;
    }
    */
}
