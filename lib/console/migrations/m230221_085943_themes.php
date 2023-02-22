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
 * Class m230221_085943_themes
 */
class m230221_085943_themes extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->updateTheme('deals',
            'lib/console/migrations/themes/b2b-supermarket/migration-deals-1676745351343.json');
        $this->updateTheme('printshop',
            'lib/console/migrations/themes/printshop/migration-printshop-1676747684813.json');
        $this->updateTheme('furniture',
            'lib/console/migrations/themes/furniture/desktop/migration-furniture-1676747925812.json');
        $this->updateTheme('furniture-mobile',
            'lib/console/migrations/themes/furniture/mobile/migration-furniture-mobile-1676748061896.json');
        $this->updateTheme('watch',
            'lib/console/migrations/themes/watch/desktop/migration-watch-1676748420667.json');
        $this->updateTheme('watch-mobile',
            'lib/console/migrations/themes/watch/mobile/migration-watch-mobile-1676748593530.json');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
    }
}
