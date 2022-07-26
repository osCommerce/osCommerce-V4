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

class Transactions extends Widget
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
        \common\helpers\Translation::init('admin/orders');
        \common\helpers\Translation::init('admin/design');
        \common\helpers\Translation::init('admin/main');
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

        $transactions = \common\models\OrdersTransactions::find()
            ->where(['orders_id' => $order_id])->orderBy('date_created asc')->all();

        if (!$transactions) {
            return '';
        }

        $manager = \common\services\OrderManager::loadManager();
        $currencies = Yii::$container->get('currencies');

        $html = '
      <table style="width: 100%" cellpadding="5">
          <tr>
            <td style="padding-left: 0; width: 20%; background-color: #eeeeee; ">' . TEXT_TRANSACTION_ID . '</td>
            <td style="width:30%; background-color: #eee; ">' . BOX_MODULES_PAYMENT . '</td>
            <td style="width:11%; background-color: #eee; text-align: center">' . TEXT_TRANSACTION_AMOUNT . '</td>
            <td style="width:20%; background-color: #eee; ">' . ENTRY_STATUS . ' / ' . TEXT_DATE . '</td>
            <td style="text-align:right;width:19%; background-color: #eee; ">' . TABLE_HEADING_COMMENTS . '</td>
          </tr>';

        foreach ($transactions as $transaction) {
            $html .= '
            <tr>
                <td style=" border-top: 1px solid #ccc">' . $transaction->transaction_id . '</td>
                <td style=" border-top: 1px solid #ccc">' . $manager->getPaymentCollection()->get($transaction->payment_class)->title . '</td>
                <td style=" border-top: 1px solid #ccc; text-align: center">' . $currencies->format($transaction->transaction_amount, false, $transaction->transaction_currency) . '</td>
                <td style=" border-top: 1px solid #ccc">
                    ' . \yii\helpers\Inflector::humanize($transaction->transaction_status) . '<br>
                    ' . \common\helpers\Date::formatDateTime($transaction->date_created) . '
                </td>
                <td style=" border-top: 1px solid #ccc; text-align: right">' . $transaction->comments . '</td>
            </tr>
            ';
        }

        $html .= '</table>';

        return $html;
    }
}