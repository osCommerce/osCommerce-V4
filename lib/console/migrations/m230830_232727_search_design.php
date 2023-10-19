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
 * Class m230830_232727_search_design
 */
class m230830_232727_search_design extends Migration
{
    /**
     * @inheritdoc
     */
   

    
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
		$this->updateTheme('furniture', 'lib/console/migrations/themes/furniture/desktop/migration-furniture-1693434513928.json');
		$this->updateTheme('furniture-mobile', 'lib/console/migrations/themes/furniture/mobile/migration-furniture-mobile-1693436244798.json');
		$this->updateTheme('furniture-mobile', 'lib/console/migrations/themes/furniture/mobile/migration-furniture-mobile-1693437631878.json');
		$this->updateTheme('deals', 'lib/console/migrations/themes/b2b-supermarket/migration-deals-1693436862639.json');
		$this->updateTheme('deals', 'lib/console/migrations/themes/b2b-supermarket/migration-deals-1693437726545.json');
		$this->updateTheme('printshop', 'lib/console/migrations/themes/printshop/migration-printshop-1693437092700.json');
		$this->updateTheme('printshop', 'lib/console/migrations/themes/printshop/migration-printshop-1693437795657.json');
		$this->updateTheme('watch', 'lib/console/migrations/themes/watch/desktop/migration-watch-1693437386080.json');
		$this->updateTheme('watch-mobile', 'lib/console/migrations/themes/watch/mobile/migration-watch-mobile-1693437434095.json');
		$this->updateTheme('watch-mobile', 'lib/console/migrations/themes/watch/mobile/migration-watch-mobile-1693437874857.json');
    }

    public function down()
    {
        echo "m230821_182640_themes_changes cannot be reverted.\n";

        return false;
    }
    
}
