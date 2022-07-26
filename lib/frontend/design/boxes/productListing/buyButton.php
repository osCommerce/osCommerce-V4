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

class buyButton extends Widget
{

    public $file;
    public $params;
    public $settings;

    public function run()
    {
        return '
<div class="buyButton ">
    <a href="" class="btn-1 btn-buy add-to-cart" rel="nofollow" title="Add to Basket">Add to Basket</a>
    <a href="" class=" btn-1 btn-cart in-cart" rel="nofollow" title="In your cart" style="display: none">In your cart</a>
    <span class="btn-1 btn-preloader" style="display: none"></span>
</div>';
    }
}