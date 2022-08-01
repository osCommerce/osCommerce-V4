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

class PointsEarntHistory extends Widget
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
        $customer_history_query = tep_db_query("select * from " . TABLE_CUSTOMERS_CREDIT_HISTORY . " where customers_id='" . (int)Yii::$app->user->getId() . "' and credit_type = '1' order by customers_credit_history_id");
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
                'date' => DateHelper::datepicker_date($customer_history['date_added']),
                'credit' => $customer_history['credit_prefix'] . ($customer_history['credit_type'] ? $customer_history['credit_amount'] : $currencies->format($customer_history['credit_amount'], true, $customer_history['currency'], $customer_history['currency_value'])),
                'notified' => $customer_history['customer_notified'],
                'comments' => $customer_history['comments'],
                'admin' => $admin,
            ];
        }

        if(\common\helpers\Acl::checkExtensionAllowed('BonusActions')){
            $_history = \common\extensions\BonusActions\models\PromotionsBonusHistory::find()->where('customer_id = :id', [':id' => (int)Yii::$app->user->getId()])->asArray()->all();
            if ($_history){
                $titles = [];
                foreach($_history as $h){
                    if (!isset($titles[$h['bonus_points_id']])){
                        $titles[$h['bonus_points_id']] = \common\extensions\BonusActions\models\PromotionsBonusPoints::find()->where('bonus_points_id = ' . (int)$h['bonus_points_id'])->with('description')->one();
                    }
                    $history[] = [
                        'date' => DateHelper::datepicker_date($h['action_date']),
                        'credit' => '+' . $h['bonus_points_award'],
                        'notified' => 1,
                        'comments' => $titles[$h['bonus_points_id']]->description->points_title,
                        'admin' => '',
                    ];
                }
                \yii\helpers\ArrayHelper::multisort($history, 'date');
            }
        }

        if (count($history) == 0 && \frontend\design\Info::isAdmin()) {
            return '';
        }
        return IncludeTpl::widget(['file' => 'boxes/account/points-earnt-history.tpl', 'params' => [
            'history' => $history,
            'settings' => $this->settings,
            'id' => $this->id,
        ]]);
    }
}