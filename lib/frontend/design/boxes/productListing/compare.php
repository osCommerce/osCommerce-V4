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

class compare extends Widget
{

    public $file;
    public $params;
    public $settings;

    public function run()
    {
        return '
<div class="compare ">
<label class="checkbox">
    <input type="checkbox" name="compare[]" value="1162"><span></span> ' . TEXT_SELECT_TO_COMPARE . '
</label>
</div>';
    }
}