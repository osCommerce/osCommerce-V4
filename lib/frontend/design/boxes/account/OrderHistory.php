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

class OrderHistory extends Widget
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
        $languages_id = \Yii::$app->settings->get('languages_id');
        $order_id = (int)Yii::$app->request->get('order_id');

        $statuses_query = tep_db_query("select os.orders_status_name, osh.date_added, osh.comments from " . TABLE_ORDERS_STATUS . " os, " . TABLE_ORDERS_STATUS_HISTORY . " osh where osh.orders_id = '" . (int)$order_id . "' and osh.orders_status_id = os.orders_status_id and os.language_id = '" . (int) $languages_id . "' order by osh.date_added");
        $order_statusses = array();
        while ($statuses = tep_db_fetch_array($statuses_query)) {
            $statuses['date'] = DateHelper::date_short($statuses['date_added']);
            $statuses['status_name'] = $statuses['orders_status_name'];
            $statuses['comments_new'] = (empty($statuses['comments']) ? '&nbsp;' : nl2br(\common\helpers\Output::output_string_protected($statuses['comments'])));
            $order_statusses[] = $statuses;
        }

        return IncludeTpl::widget(['file' => 'boxes/account/order-history.tpl', 'params' => [
            'settings' => $this->settings,
            'order_statusses' => $order_statusses,
        ]]);
    }
}