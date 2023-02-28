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
 * Class m230227_095742_themes_feb_upd
 */
class m230227_095742_themes_feb_upd extends Migration
{
    /**
     * @inheritdoc
     */
    public function Up()
    {
		$this->updateTheme('deals', 'lib/console/migrations/themes/b2b-supermarket/migration-deals-1677490948365.json');
		$this->updateTheme('printshop', 'lib/console/migrations/themes/printshop/migration-printshop-1677483151197.json');
		$this->updateTheme('furniture', 'lib/console/migrations/themes/furniture/desktop/migration-furniture-1677483369210.json');
		$this->updateTheme('furniture-mobile', 'lib/console/migrations/themes/furniture/mobile/migration-furniture-mobile-1677483418789.json');
		$this->updateTheme('watch', 'lib/console/migrations/themes/watch/desktop/migration-watch-1677483604223.json');
		$this->updateTheme('watch-mobile', 'lib/console/migrations/themes/watch/mobile/migration-watch-mobile-1677483767655.json');
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
        echo "m230227_095742_themes_feb_upd cannot be reverted.\n";

        return false;
    }
    */
}
