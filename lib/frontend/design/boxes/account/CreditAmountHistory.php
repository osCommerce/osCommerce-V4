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
use common\helpers\Date as DateHelper;

class CreditAmountHistory extends Widget
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
        global $navigation;
        if ( Yii::$app->user->isGuest ) {
            $navigation->set_snapshot();
            tep_redirect(tep_href_link('account/login','','SSL'));
        }
        \common\helpers\Translation::init('account/history');

        $currencies = \Yii::$container->get('currencies');

        $history = [];
        $customer_history_query = tep_db_query("select * from " . TABLE_CUSTOMERS_CREDIT_HISTORY . " where customers_id='" . (int)Yii::$app->user->getId() . "' and credit_type = '0' order by customers_credit_history_id");
        while ($customer_history = tep_db_fetch_array($customer_history_query)) {
            $admin = '';
            if ($customer_history['admin_id'] > 0) {
                $check_admin_query = tep_db_query( "select * from admin where admin_id = '" . (int)$customer_history['admin_id'] . "'" );
                $check_admin = tep_db_fetch_array( $check_admin_query );
                if (is_array($check_admin)) {
                    $admin =  $check_admin['admin_firstname'] . ' ' . $check_admin['admin_lastname'];
                }
            }
            $history[] = [
                'date' => DateHelper::datetime_short($customer_history['date_added']),
                'credit' => $customer_history['credit_prefix'] . ($customer_history['credit_type'] ? $customer_history['credit_amount'] : $currencies->format($customer_history['credit_amount'], true, $customer_history['currency'], $customer_history['currency_value'])),
                'notified' => $customer_history['customer_notified'],
                'comments' => $customer_history['comments'],
                'admin' => $admin,
            ];
        }

        if (count($history) == 0 && \frontend\design\Info::isAdmin()) {
            return '';
        }
        return IncludeTpl::widget(['file' => 'boxes/account/credit-amount-history.tpl', 'params' => [
            'history' => $history,
            'settings' => $this->settings,
            'id' => $this->id,
        ]]);
    }
}