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

namespace backend\models\EP\Provider\Trueloaded;

use Yii;
use common\api\models\XML\IOCore;
use yii\db\Expression;

class Orders extends XmlBase
{

    public function init()
    {

        $this->ConfigureMap = IOCore::getExportStructure('orders');
        parent::init();

    }

    public function prepareExport($useColumns, $filter)
    {
        if ( is_array($filter) ) {
            if (isset($filter['platform_id']) && !empty($filter['platform_id'])) {
                $this->activeQuery->andWhere(['=', 'platform_id', (int)$filter['platform_id']]);
            }

            $order_filter = (isset($filter['order']) && is_array($filter['order']))?$filter['order']:[];

            if (isset($order_filter['date_type_range']) && $order_filter['date_type_range']=='exact')
            {
                if (!empty($order_filter['date_from'])) {
                    $this->activeQuery->andWhere(['>=', 'date_purchased', substr($order_filter['date_from'], 0, 10) . ' 00:00:00']);
                }
                if (!empty($order_filter['date_to'])) {
                    $this->activeQuery->andWhere(['<=', 'date_purchased', substr($order_filter['date_to'], 0, 10) . ' 23:59:59']);
                }
            }
            elseif(isset($order_filter['date_type_range']) && $order_filter['date_type_range']=='year/month')
            {
                $year = $order_filter['year'];
                $this->activeQuery->andWhere(['=', new Expression('YEAR(date_purchased)'), $year]);
                $month = $order_filter['month'];
                if ( !empty($month) ) {
                    $this->activeQuery->andWhere(['=', new Expression('DATE_FORMAT(date_purchased,\'%Y%m\')'), $year.sprintf('%02s',(int)$month)]);
                }
            }
            elseif(isset($order_filter['date_type_range']) && $order_filter['date_type_range']=='presel')
            {
                switch ($order_filter['interval']) {
                    case 'week':
                        $this->activeQuery->andWhere(['=', 'date_purchased', date('Y-m-d', strtotime('monday this week'))]);
                        break;
                    case 'month':
                        $this->activeQuery->andWhere(['=', 'date_purchased', date('Y-m-d', strtotime('first day of this month'))]);
                        break;
                    case 'year':
                        $this->activeQuery->andWhere(['>=', 'date_purchased', date("Y") . "-01-01"]);
                        break;
                    case '1':
                        $this->activeQuery->andWhere(['>=', 'date_purchased', date('Y-m-d')]);
                        break;
                    case '3':
                    case '7':
                    case '14':
                    case '30':
                        $this->activeQuery->andWhere(['>=', 'date_purchased', date('Y-m-d',strtotime('-'.$order_filter['interval'].' days'))]);
                        break;
                }
            }
        }
        parent::prepareExport($useColumns, $filter);
    }

    public function clearLocalData()
    {
        tep_db_query("TRUNCATE TABLE " . TABLE_ORDERS);
        tep_db_query("TRUNCATE TABLE " . TABLE_ORDERS_PRODUCTS);
        tep_db_query("TRUNCATE TABLE " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES);
        tep_db_query("TRUNCATE TABLE " . TABLE_ORDERS_PRODUCTS_DOWNLOAD);
        tep_db_query("TRUNCATE TABLE " . TABLE_ORDERS_STATUS_HISTORY);
        tep_db_query("TRUNCATE TABLE " . TABLE_ORDERS_TOTAL);
        tep_db_query("TRUNCATE TABLE " . TABLE_ORDERS_HISTORY);

        \common\models\OrdersProductsAllocate::deleteAll();
        \common\models\OrdersSplinters::deleteAll();
        \common\models\OrdersTransactionsChildren::deleteAll();
        \common\models\OrdersTransactions::deleteAll();
        \common\models\EcommerceTracking::deleteAll();

        $schemaCheck = Yii::$app->get('db')->schema->getTableSchema('amazon_payment_orders');
        if ( $schemaCheck ) {
            tep_db_query("TRUNCATE TABLE amazon_payment_orders");
        }
        $schemaCheck = Yii::$app->get('db')->schema->getTableSchema('klarna_order_reference');
        if ( $schemaCheck ) {
            tep_db_query("TRUNCATE TABLE klarna_order_reference");
        }

        tep_db_query("TRUNCATE TABLE tracking_numbers");
        tep_db_query("TRUNCATE TABLE tracking_numbers_to_orders_products");

        tep_db_query("TRUNCATE TABLE ep_holbi_soap_link_orders");
        tep_db_query("TRUNCATE TABLE ep_holbi_soap_kv_storage");

        tep_db_query("TRUNCATE TABLE ga");
    }

}