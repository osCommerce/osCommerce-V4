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
 * Class m230816_163826_themes_oscomerce
 */
class m230816_163826_themes_oscomerce extends Migration
{
    /**
     * @inheritdoc
     */
   
    
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
		$this->updateTheme('furniture', 'lib/console/migrations/themes/furniture/desktop/migration-furniture-1692363795378.json');
		$this->updateTheme('furniture-mobile', 'lib/console/migrations/themes/furniture/mobile/migration-furniture-mobile-1692363839444.json');
		$this->updateTheme('deals', 'lib/console/migrations/themes/b2b-supermarket/migration-deals-1692363875295.json');
		$this->updateTheme('printshop', 'lib/console/migrations/themes/printshop/migration-printshop-1692363910094.json');
		$this->updateTheme('watch', 'lib/console/migrations/themes/watch/desktop/migration-watch-1692364004609.json');
		$this->updateTheme('watch-mobile', 'lib/console/migrations/themes/watch/mobile/migration-watch-mobile-1692364046285.json');

    }

    public function down()
    {
        echo "m230816_163826_themes_oscomerce cannot be reverted.\n";

        return false;
    }
    
}
