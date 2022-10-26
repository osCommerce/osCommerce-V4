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
 * Class m221025_140007_themes_upd
 */
class m221025_140007_themes_upd extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
		$this->update('information', [
                'information_h1_tag' => ''
            ], [
                'information_id' => 41,
            ]);
        $this->updateTheme('deals', 'lib/console/migrations/themes/b2b-supermarket/migration-deals-1666700796217.json');
		$this->updateTheme('printshop', 'lib/console/migrations/themes/printshop/migration-printshop-1666700636486.json');
		$this->updateTheme('furniture', 'lib/console/migrations/themes/furniture/desktop/migration-furniture-1666597497383.json');
		$this->updateTheme('furniture-mobile', 'lib/console/migrations/themes/furniture/mobile/migration-furniture-mobile-1666597560584.json');
		$this->updateTheme('watch', 'lib/console/migrations/themes/watch/desktop/migration-watch-1666597659424.json');
		$this->updateTheme('watch-mobile', 'lib/console/migrations/themes/watch/mobile/migration-watch-mobile-1666597702573.json');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        echo "m221024_081243_themes_update cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m221025_140007_themes_upd cannot be reverted.\n";

        return false;
    }
    */
}
