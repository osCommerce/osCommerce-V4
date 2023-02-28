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
 * Class m230228_075030_fix_banners_upd_files
 */
class m230228_075030_fix_banners_upd_files extends Migration
{
    /**
     * @inheritdoc
     */
    public function Up()
    {
		$this->updateTheme('furniture', 'lib/console/migrations/themes/furniture/desktop/migration-furniture-1677570329117.json');
		$this->updateTheme('furniture-mobile', 'lib/console/migrations/themes/furniture/mobile/migration-furniture-mobile-1677570375059.json');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {

    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230228_075030_fix_banners_upd_files cannot be reverted.\n";

        return false;
    }
    */
}
