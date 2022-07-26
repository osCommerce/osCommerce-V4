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

namespace frontend\design\boxes\invoice;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;

class OrderType extends Widget
{

    public $file;
    public $params;
    public $settings;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        \common\helpers\Translation::init('admin/design');
        $order = $this->params["order"];

        return $order->info['admin_id'] ? (defined("TEXT_MADE_BY_ADMIN") ? TEXT_MADE_BY_ADMIN : 'Made by admin') : (defined("TEXT_MADE_ONLINE") ? TEXT_MADE_ONLINE : 'Made online');
    }
}