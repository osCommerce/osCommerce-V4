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

namespace frontend\design\boxes\productListing;

use yii\base\Widget;

class rating extends Widget
{

    public $file;
    public $params;
    public $settings;

    public function run()
    {
        return '
<div class="rating"><span class="rating-4"></span></div>';
    }
}