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

class qtyInput extends Widget
{

    public $file;
    public $params;
    public $settings;

    public function run()
    {
        return '
<div class="qtyInput ">    <span class="qty-box"><span class="smaller disabled"></span><input type="text" name="qty" value="1" class="qty-inp" data-max="99999" data-min="1" data-step="1"><span class="bigger"></span></span>
</div>';
    }
}