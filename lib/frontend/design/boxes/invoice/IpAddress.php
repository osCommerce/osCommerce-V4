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

class IpAddress extends Widget
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

        if ($order->parent_id) {
            $order_id = $order->parent_id;
        } elseif ( method_exists($order, 'getOrderNumber') ) {
            $order_id = $order->getOrderNumber();
        } else {
            $order_id = $order->order_id;
        }

        if (!$order_id) {
            return '';
        }

        $system = \common\helpers\System::get_ga_detection($order_id);

        return $system->ip_address;
    }
}