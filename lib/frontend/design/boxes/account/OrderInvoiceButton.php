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

namespace frontend\design\boxes\account;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use frontend\design\SplitPageResults;
use common\helpers\Date as DateHelper;

class OrderInvoiceButton extends Widget
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
      if (defined('GROUPS_IS_SHOW_PRICE') && GROUPS_IS_SHOW_PRICE == false) {
        return '';
      }
        $order_id = (int)Yii::$app->request->get('order_id');

        $pay_link = false;
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('UpdateAndPay', 'allowed')) {
            $pay_link = $ext::payLink($order_id);
        }

        return IncludeTpl::widget(['file' => 'boxes/account/order-invoice-button.tpl', 'params' => [
            'settings' => $this->settings,
            'id' => $this->id,
            'pay_link' => $pay_link,
            'print_order_link' => tep_href_link(FILENAME_ORDERS_PRINTABLE, \common\helpers\Output::get_all_get_params(array('orders_id')) . 'orders_id=' . $order_id),
        ]]);
    }
}