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

namespace frontend\design\boxes\cart;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use common\classes\Images;

class GiveAway extends Widget
{

    public $type;
    public $settings;
    public $params;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        global $cart;
        if ($cart->count_contents() == 0) {
            return \frontend\design\Info::hideBox($this->id, $this->settings[0]['hide_parents']);
        }

        $products = \common\helpers\Gifts::getGiveAways();

        if ( !is_array($products) || count($products)==0 ) {
            return \frontend\design\Info::hideBox($this->id, $this->settings[0]['hide_parents']);
        }

        return IncludeTpl::widget([
            'file' => 'boxes/cart/give-away.tpl',
            'params' => [
                'products' => $products,
            ]]);
    }
}