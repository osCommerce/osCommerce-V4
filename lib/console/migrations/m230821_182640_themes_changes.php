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
 * Class m230821_182640_themes_changes
 */
class m230821_182640_themes_changes extends Migration
{
    /**
     * @inheritdoc
     */
   

    
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
		$this->updateTheme('furniture', 'lib/console/migrations/themes/furniture/desktop/migration-furniture-1692642177871.json');
		$this->updateTheme('furniture-mobile', 'lib/console/migrations/themes/furniture/mobile/migration-furniture-mobile-1692642256891.json');
		$this->updateTheme('deals', 'lib/console/migrations/themes/b2b-supermarket/migration-deals-1692642295692.json');
		$this->updateTheme('printshop', 'lib/console/migrations/themes/printshop/migration-printshop-1692642328005.json');
		$this->updateTheme('watch', 'lib/console/migrations/themes/watch/desktop/migration-watch-1692642060118.json');
		$this->updateTheme('watch-mobile', 'lib/console/migrations/themes/watch/mobile/migration-watch-mobile-1692642122999.json');

    }

    public function down()
    {
        echo "m230821_182640_themes_changes cannot be reverted.\n";

        return false;
    }
    
}
