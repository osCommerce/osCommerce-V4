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

namespace frontend\design\boxes\order;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;

class Totals extends Widget
{
    public $file;
    public $params;
    public $settings;
    public $manager;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        $totals = [];
        if ($this->params['order_totals'] ?? false) {
            $totals = $this->params['order_totals'];
        } elseif ($this->params['manager'] ?? false) {
            $totals = $this->params['manager']->getTotalOutput(true, 'TEXT_CHECKOUT');
        }

        return IncludeTpl::widget(['file' => 'boxes/order/totals.tpl', 'params' => [
            'order_totals' => $totals,
        ]]);
    }
}