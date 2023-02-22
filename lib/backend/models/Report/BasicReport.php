<?php

namespace backend\models\Report;

use Yii;
use yii\helpers\ArrayHelper;

class BasicReport {

    private $isLoop = false;

    protected $request = [];
    protected $orders_avg = false;
    protected $total_avg = false;
    protected $interval = 0;
    protected $class_range = [];

    public function __construct($data) {
        $this->request = $data;
        $this->all_params['modules'] = $this->loadOtModules();
    }

    public function getRangeList() {
        if (is_array($this->range)) {
            foreach ($this->range as $key => $value) {
                $this->range[$key] = ucfirst($value);
            }
        }
        return Yii::$app->controller->renderAjax('ranges', [
                    'range' => $this->range,
                    'current_range' => $this->getCurrentRange(),
        ]);
    }

    public function getCurrentRange() {
        return $this->current_range;
    }

    public function parseDate($date, $full = true) {
        $ex = explode(static::DELIMETER, $date);
        if (!$full) {
            return ['month' => $ex[0], 'year' => $ex[1]];
        } else {
            return ['day' => $ex[0], 'month' => $ex[1], 'year' => $ex[2]];
        }
    }

    public function getRawData($where = "", $for_map = false) {
        $_join = '';
        $_summ = '';
        if (isset($this->all_params['modules']) && is_array($this->all_params['modules'])) {
            foreach ($this->all_params['modules'] as $_module_var) {
                $_module = $_module_var['class'];
                if ($_module == 'ot_due') {
                    $$_module = "if((ifnull({$_module}.value_inc_tax, 0) > 0), {$_module}.value_inc_tax, 0)";
                } else {
                    $$_module = "ifnull({$_module}.value_inc_tax, 0)";
                }
            }
            foreach ($this->all_params['modules'] as $_module_var) {
                $_module = $_module_var['class'];
                if ($_module == 'ot_paid') {
                    $$_module = ("ifnull({$_module}.value_inc_tax, "
                        . ((isset($ot_total) AND isset($ot_due))
                            ? ('(' .$ot_total . ' - ' . $ot_due
                                . (isset($ot_refund) ? " + {$ot_refund}" : '')
                                . (isset($ot_commission) ? " - {$ot_commission}" : '')
                                . ')'
                            )
                            : '0'
                        )
                    . ")");
                }
                $_summ .= ", sum({$$_module} * o.currency_value) as {$_module}";
                if ($_module == 'ot_tax') {
                    $_join .= " left join (SELECT o.orders_id, sum(value_inc_tax) AS value_inc_tax FROM " . TABLE_ORDERS_TOTAL . " ot inner join " . TABLE_ORDERS . " o on o.orders_id = ot.orders_id and {$where} where  ot.class='ot_tax' group by o.orders_id) {$_module} ON ({$_module}.orders_id = o.orders_id) ";// if multiple
                } elseif ($_module == 'ot_coupon') {
                    $_join .= " left join (SELECT o.orders_id, sum(value_inc_tax) AS value_inc_tax FROM " . TABLE_ORDERS_TOTAL . " ot inner join " . TABLE_ORDERS . " o on o.orders_id = ot.orders_id and {$where} where ot.class = 'ot_coupon' " . (defined('MODULE_ORDER_TOTAL_COUPON_TOTAL') ? " and ot.title != '" . tep_db_input(MODULE_ORDER_TOTAL_COUPON_TOTAL). ":'" : '') . " group by o.orders_id) {$_module} ON ({$_module}.orders_id = o.orders_id) "; // multi-coupon with total discount line dirty hack
                } else {
                    $_join .= " left join " . TABLE_ORDERS_TOTAL . " {$_module} on o.orders_id = {$_module}.orders_id and {$_module}.class='" . $_module . "' ";
                }
            }
        }
        //echo '<pre>';print_r($this);die;
        if (isset($this->request['status'])) {
            if (is_array($this->request['status']) && count($this->request['status'])) {
                $where .= " and o.orders_status in (" . implode(",", $this->request['status']) . ")";
            }
        }

        if (isset($this->request['payment_methods'])) {
            if (is_array($this->request['payment_methods']) && count($this->request['payment_methods'])) {
                $where .= " and o.payment_class in ('" . implode("','", $this->request['payment_methods']) . "')";
            }
        }

        if (isset($this->request['shipping_methods'])) {
            if (is_array($this->request['shipping_methods']) && count($this->request['shipping_methods'])) {
                $where .= " and o.shipping_class in ('" . implode("','", $this->request['shipping_methods']) . "')";
            }
        }

        if (isset($this->request['platforms'])) {
            if (is_array($this->request['platforms']) && count($this->request['platforms'])) {
                $where .= " and o.platform_id in (" . implode(",", $this->request['platforms']) . ")";
            }
        }

        if (isset($this->request['customer_groups'])) {
            if (is_array($this->request['customer_groups']) && count($this->request['customer_groups'])>0) {
                $_join .= " INNER JOIN ".TABLE_CUSTOMERS." cust ON cust.customers_id=o.customers_id AND cust.groups_id IN (" . implode(",", array_map('intval',$this->request['customer_groups'])) . ") ";
            }
        }

        if ( isset($this->request['walkin']) && is_array($this->request['walkin']) ){
            if (count($this->request['walkin'])>0){
                $where .= " and o.admin_id in (" . implode(",", $this->request['walkin']) . ")";
            }
        }

        if (isset($this->request['zones'])) {
            if (is_array($this->request['zones']) && count($this->request['zones'])) {
                $where .= " and (o.delivery_country in ( select c.countries_name from " . TABLE_COUNTRIES . " c where c.countries_id in ('" . implode("','", $this->request['zones']) . "') ) or"
                        . " o.billing_country in ( select c.countries_name from " . TABLE_COUNTRIES . " c where c.countries_id in ('" . implode("','", $this->request['zones']) . "') )  )";
            }
        }

        if (isset($this->request['country'])){
            if (is_array($this->request['country']) && count($this->request['country'])) {
                $where .= " and (o.delivery_country in ( select c.countries_name from " . TABLE_COUNTRIES . " c where c.countries_id in ('" . implode("','", $this->request['country']) . "') ) or"
                    . " o.billing_country in ( select c.countries_name from " . TABLE_COUNTRIES . " c where c.countries_id in ('" . implode("','", $this->request['country']) . "') )  )";
            }
        }

        if (isset($this->request['state'])){
            $where .= " and (o.delivery_state like '%{$this->request['state']}%') ";
        }

        if (isset($this->request['sps'])){
            $where .= " and (o.delivery_street_address like '%{$this->request['sps']}%' or o.delivery_suburb like '%{$this->request['sps']}%' or o.delivery_postcode like '%{$this->request['sps']}%' ) ";
        }

        $group_by = "";
        if (isset($this->sql_params['group']) && is_array($this->sql_params['group'])) {
            $group_by = " group by ";
            foreach ($this->sql_params['group'] as $_group) {
                $group_by .= $_group . "(o.date_purchased),";
            }
            $group_by = substr($group_by, 0, -1);
        }

        //need convert to main currency
        if ($for_map) {
            $sql = "select o.lat, o.lng, o.delivery_address_format_id, o.delivery_street_address, o.delivery_suburb, o.delivery_city, o.delivery_postcode, o.delivery_state, o.delivery_country from " . TABLE_ORDERS . " o " . $_join . " where {$where} and o.lat not in (0 , 9999) and o.lng not in (0 , 9999) order by o.date_purchased";
        } else {
            $sql = "select {$this->sql_params['select_period']}(o.date_purchased) as period, count(o.orders_id) as orders {$_summ}, group_concat(o.orders_id) as orders_ids from " . TABLE_ORDERS . " o " . $_join . " where {$where} " . $group_by . " order by o.date_purchased";
        }

        Yii::$app->getDb()->createCommand("SET SESSION group_concat_max_len = 5000000")->query();
        $_query = Yii::$app->getDb()->createCommand($sql)->queryAll();
        $data = [];
        //echo $sql;die;
        if (is_array($_query) && count($_query)) {
            foreach($_query as $row ){
                $row['period_full'] = date('m/d/Y H:i:s', strtotime($row['period']));
                $row['cur_row'] = date('Y-M-d:H') == date('Y-M-d:H', strtotime($row['period']) );
                if (ArrayHelper::getValue($this->request, 'with_products') && !$for_map){
                    $this->collectProducts($row);
                }
                if ((isset($this->request['chart_group_item']['profit_amount']) || isset($this->request['chart_group_item']['profit_percent'])) && !$for_map){
                    $this->collectProfit($row);
                }
                unset($row['orders_ids']);
                array_push($data, $row);
            }
        } else {
            $ot = yii\helpers\ArrayHelper::getColumn($this->all_params['modules'], 'class');
            $empty_row = array_merge(['period', 'orders', 'period_full'], $ot);
            $empty_row = array_flip($empty_row);
            foreach ($empty_row as $k => $value) {
                $empty_row[$k] = '';
            }
            $data[0] = $empty_row;
        }
        //echo'<pre>';print_r($empty_row);die;
        return $data;

        //$sql = "select dayofmonth(o.date_purchased) as report_day, count(*) as report_total, sum(ot.value) as report_total_sum, ot.class from " . TABLE_ORDERS . " o inner join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id) where month(o.date_purchased) = '" . $month . "' and year(o.date_purchased) = '" . $year . "' " . self::$sel_status_sql . " group by dayofmonth(o.date_purchased), ot.class order by report_day, ot.sort_order ";
        //$orders"select month(o.date_purchased) as report_day, count(*) as report_total, sum(ot.value) as report_total_sum, ot.class from " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id) where year(o.date_purchased) = '" . $year . "' " . self::$sel_status_sql . " group by month(o.date_purchased), ot.class order by report_day, ot.sort_order");
        //               "select year(o.date_purchased) as report_day, count(*) as report_total, sum(ot.value) as report_total_sum, ot.class from " . TABLE_ORDERS . " o inner join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id) where ".(USE_MARKET_PRICES == 'True' ? "o.currency = '" . tep_db_input($_GET['currency'] ? $_GET['currency'] : DEFAULT_CURRENCY) . "'" : '1')." ". self::$sel_status_sql . " group by year(o.date_purchased), ot.class order by report_day, ot.sort_order "
    }

    protected function comparePurchases($baseArray = array())
    {
        if ($this->isLoop == true) {
            return $baseArray;
        }
        $rangeList = [
            'start_year',
            'start_month',
            'start_day',
            'end_year',
            'end_month',
            'end_day',
        ];
        $restoreList = [];
        foreach ($rangeList as $key) {
            if (isset($this->{$key}) AND isset($this->{$key . '_cmp'})) {
                $restoreList[$key] = $this->{$key};
                $this->{$key} = $this->{$key . '_cmp'};
            }
        }
        unset($rangeList);
        $this->isLoop = true;
        $compareArray = $this->loadPurchases(false);
        $this->isLoop = false;
        foreach ($baseArray as $key => &$bv) {
            if (isset($compareArray[$key])) {
                $cv = $compareArray[$key];
                foreach ($cv as $cvt => $cvv) {
                    if (isset($bv[$cvt]) AND is_numeric($cvv) AND !is_bool($cvv) AND ((float)$cvv > 0)) {
                        $bv[$cvt] = ((float)$bv[$cvt] - (float)$cvv);
                    }
                }
                unset($cvt);
                unset($cvv);
                unset($cv);
            }
        }
        unset($compareArray);
        unset($key);
        unset($bv);
        foreach ($restoreList as $key => $value) {
            $this->{$key} = $value;
        }
        unset($value);
        unset($key);
        unset($restoreList);
        return $baseArray;
    }

    protected function collectProducts(&$row){
        if (isset($row['orders_ids']) && !empty($row['orders_ids'])){
            $orders_ids = explode(",", $row['orders_ids']);
            if ($orders_ids){
                $row['products'] = \common\models\OrdersProducts::find()->select(['products_model', 'products_name', 'avg(final_price) as final_price', 'avg(final_price *((100+products_tax)/100)) as final_price_tax', 'sum(products_quantity) as products_quantity', 'uprid'])->where(['in', 'orders_id', $orders_ids])->asArray()->groupBy('uprid')->all();
                foreach($row['products'] as &$product){
                    $product['products_name'] = str_replace("'","", $product['products_name']);
                }
            }
        }
    }

    protected function collectProfit(&$row) {
        if (isset($row['orders_ids']) && !empty($row['orders_ids'])) {
            $orders_ids = explode(',', $row['orders_ids']);
            if ($orders_ids) {
                $profit = (new \yii\db\Query())->select(['sum(opa.allocate_received * opa.suppliers_price) as cost_amount', 'sum(opa.allocate_received * (op.final_price - opa.suppliers_price)) as profit_amount', 'sum(opa.allocate_received * (op.final_price - opa.suppliers_price)) / sum(opa.allocate_received * opa.suppliers_price) * 100 as profit_percent'])->from(['op' => TABLE_ORDERS_PRODUCTS])->leftJoin(['opa' => 'orders_products_allocate'], 'op.orders_products_id = opa.orders_products_id')->where(['in', 'op.orders_id', $orders_ids])->andWhere('opa.allocate_received > 0')->andWhere('opa.suppliers_price > 0')->one();
                if (isset($this->request['chart_group_item']['cost_amount'])) {
                    if ($profit['cost_amount'] > 0) {
                        $row['cost_amount'] = $profit['cost_amount'];
                    } else {
                        $row['cost_amount'] = '';
                    }
                }
                if (isset($this->request['chart_group_item']['profit_amount'])) {
                    if ($profit['profit_amount'] > 0) {
                        $row['profit_amount'] = $profit['profit_amount'];
                    } else {
                        $row['profit_amount'] = '';
                    }
                }
                if (isset($this->request['chart_group_item']['profit_percent'])) {
                    if ($profit['profit_percent'] > 0) {
                        $row['profit_percent'] = $profit['profit_percent'];
                    } else {
                        $row['profit_percent'] = '';
                    }
                }
            }
        }
    }

    public function loadOtModules() {

        $_query = tep_db_query("select class, if(sort_order, sort_order, 50) as sort_order from " . TABLE_ORDERS_TOTAL . " where 1 group by class order by sort_order");
        $data = [];
        $chart_items = $this->getFilteredModules();
        $manager = \common\services\OrderManager::loadManager();
        $totalCollection = $manager->getTotalCollection();
        if (tep_db_num_rows($_query)) {
            while ($row = tep_db_fetch_array($_query)) {
                $ot_module = $row['class'];
                if (empty($ot_module))
                    continue;
                if (is_array($chart_items) && !in_array($ot_module, $chart_items))
                    continue;
                $sort = 0;
                $module = $totalCollection->getModule($ot_module);
                if ($module) {
                    $sort = $module->sort_order;
                }
                $data[$sort] = ['class' => $ot_module, 'title' => \common\helpers\Translation::getTranslationValue("MODULE_ORDER_TOTAL_" . strtoupper(substr($ot_module, 3)) . "_TITLE", 'ordertotal')];
            }
        }
        ksort($data);
        $_data = array_values($data);
        return $_data;
    }

    public function getFilteredModules() {
        if (isset($this->request['chart_group_item']) && is_array($this->request['chart_group_item']) && count($this->request['chart_group_item'])) {
            $keys = array_keys($this->request['chart_group_item']);
            return $keys;
        }
        return false;
    }

    public function getOtModules() {
        return $this->all_params['modules'];
    }

    public function getFirstDatePurchase() {
        $date = tep_db_fetch_array(tep_db_query("select min(date_purchased) as date from " . TABLE_ORDERS . " where 1"));
        if ($date)
            return $date['date'];
        return false;
    }

    public function getLastDatePurchase() {
        $date = tep_db_fetch_array(tep_db_query("select max(date_purchased) as date from " . TABLE_ORDERS . " where 1"));
        if ($date)
            return $date['date'];
        return false;
    }

    public function getYearsList() {
        $years_query = tep_db_query("select distinct year(date_purchased) as year from " . TABLE_ORDERS . " where 1 order by date_purchased");
        $years = [];
        $_prevous = null;
        if (tep_db_num_rows($years_query)) {
            while ($year = tep_db_fetch_array($years_query)) {
                if (!is_null($_prevous) && $_prevous != $year['year']) {
                    if ($year['year'] - $_prevous > 1) {
                        $range = range($_prevous + 1, $year['year'] - 1);
                        if (is_array($range) && count($range)) {
                            foreach ($range as $y) {
                                $years[$y] = $y;
                            }
                        }
                    }
                }
                $years[$year['year']] = $year['year'];
                $_prevous = $year['year'];
            }
        }
        return $years;
    }

    public function convertColumnTitle($value) {
        if ($value == 'orders_avg') {
            return TEXT_ORDERS_AVG;
        }
        if ($value == 'total_avg') {
            return TEXT_TOTAL_AVG;
        }
        if ($value == 'cost_amount') {
            return TEXT_COST;
        }
        if ($value == 'profit_amount') {
            return TEXT_PROFIT;
        }
        if ($value == 'profit_percent') {
            return TEXT_PROFIT . ' (%)';
        }
        return ucfirst($value);
    }

    public function prepareDaysRange($pattern = [], $date_pattern = '', &$range_class = []) {
        $start = date('Y-m-d', mktime(0, 0, 0, $this->start_month, $this->start_day, $this->start_year));
        $end = date('Y-m-d', mktime(0, 0, 0, $this->end_month, $this->end_day, $this->end_year));
        $date_start = new \DateTime($start);
        $date_end = new \DateTime($end);
        if ($date_end > new \DateTime()) $date_end = new \DateTime("now");
        $interval = $date_end->diff($date_start);
        $result = [];
        if ($interval->days > 0) {
            $this->interval = $interval->days;
            for ($i = 0; $i < $interval->days + 1; $i++) {
                $date = new \DateTime($start);
                $date->add(new \DateInterval('P' . $i . 'D'));
                if(!in_array($date->format("mY"), $range_class)) $range_class[$i] = $date->format("mY");
                if (!empty($date_pattern)) {
                    $pattern['period'] = $date->format($date_pattern);
                    $pattern['period_full'] = $date->format("m/d/Y 00:00:00");
                    $pattern['cur_row'] = date('Y-M-d') == $date->format('Y-M-d');
                }
                $result[$date->format('d-m-Y')] = $pattern;
            }
        } else {
            $date = new \DateTime($start);
            $date->add(new \DateInterval('P0D'));
            if (!empty($date_pattern)) {
                $pattern['period'] = $date->format($date_pattern);
                $pattern['period_full'] = $date->format("m/d/Y 00:00:00");
            }
            $result[$date->format('d-m-Y')] = $pattern;
        }
        return $result;
    }

    public function prepareMonthRange($pattern = [], $date_pattern = '', &$range_class = []) {
        $start = date('Y-m-d', mktime(0, 0, 0, $this->start_month, 1, $this->start_year));
        $end = date('Y-m-d', mktime(0, 0, 0, $this->end_month, 1, $this->end_year));
        $date_start = new \DateTime($start);
        $date_end = new \DateTime($end);
        if ($date_end > new \DateTime()) $date_end = new \DateTime("now");
        $interval = $date_end->diff($date_start);
        $result = [];

        if ($interval->days > 0) {
            $this->interval = $interval->days;
            for ($i = 0; $i < ($interval->m + 1 + $interval->y * 12); $i++) {
                $date = new \DateTime($start);
                $date->add(new \DateInterval('P' . $i . 'M'));
                if(!in_array($date->format("Y"), $range_class)) $range_class[$i] = $date->format("Y");
                if (!empty($date_pattern)) {
                    $pattern['period'] = $date->format($date_pattern);
                    $pattern['period_full'] = $date->format("m/d/Y 00:00:00");
                    $pattern['cur_row'] = date('Y-M') == $date->format('Y-M');
                }
                $result[$date->format('m-Y')] = $pattern;
            }
        } else {
            $date = new \DateTime($start);
            $date->add(new \DateInterval('P0M'));
            if (!empty($date_pattern)) {
                $pattern['period'] = $date->format($date_pattern);
                $pattern['period_full'] = $date->format("m/d/Y 00:00:00");
            }
            $result[$date->format('m-Y')] = $pattern;
        }
        return $result;
    }

    public function prepareYearsRange($pattern = [], $date_pattern = '') {
        $start = $this->start_year;
        $end = $this->end_year;
        $result = [];
        if (is_numeric($end) && is_numeric($start)) {
            for ($i = $start; $i <= $end; $i++) {
                $date = new \DateTime($i . "-01-01");
                $pattern['period'] = $date->format($date_pattern);
                $pattern['period_full'] = $date->format("m/d/Y 00:00:00");
                $result[$i] = $pattern;
            }
        }
        return $result;
    }

    public function checkMonthDayYear() {
        if (!checkdate($this->start_month, $this->start_day, $this->start_year)) {
            $this->start_day = date("d");
            $this->start_month = date("m");
            $this->start_year = date("Y");
        }

        if (!checkdate($this->end_month, $this->end_day, $this->end_year)) {
            $this->end_day = date("d");
            $this->end_month = date("m");
            $this->end_year = date("Y");
        }

        $check_start = mktime(0, 0, 0, $this->start_month, $this->start_day, $this->start_year);
        $check_end = mktime(0, 0, 0, $this->end_month, $this->end_day, $this->end_year);

        if ($check_start > $check_end) {
            $this->swapDates();
        }
        return $this;
    }

    public function swapDates() {
        if (property_exists($this, 'start_day') && property_exists($this, 'end_day')) {
            $_start_day = $this->start_day;
            $this->start_day = $this->end_day;
            $this->end_day = $_start_day;
        }
        if (property_exists($this, 'start_month') && property_exists($this, 'end_month')) {
            $_start_month = $this->start_month;
            $this->start_month = $this->end_month;
            $this->end_month = $_start_month;
        }
        if (property_exists($this, 'start_year') && property_exists($this, 'end_year')) {
            $_start_year = $this->start_year;
            $this->start_year = $this->end_year;
            $this->end_year = $_start_year;
        }
    }

    public function insertAt(&$mas, $after, $key, $value) {
        $keys = array_keys($mas);
        $values = array_values($mas);
        if ($pos = array_search($after, $keys)) {
            $keys_head = array_slice($keys, 0, $pos + 1);
            $keys_tail = array_slice($keys, $pos + 1);

            $vals_head = array_slice($values, 0, $pos + 1);
            $vals_tail = array_slice($values, $pos + 1);

            array_push($keys_head, $key);
            array_push($vals_head, $value);

            $keys = array_merge($keys_head, $keys_tail);
            $vals = array_merge($vals_head, $vals_tail);

            $mas = array_combine($keys, $vals);
        }
    }

    public function predefineMonthYear($month_year) {
        $this->month_year = $month_year;
        $month_year = $this->parseDate($month_year, false);
        $this->start_month = $month_year['month'];
        $this->start_year = $month_year['year'];
        $this->end_month = $month_year['month'];
        $this->end_year = $month_year['year'];
        $this->end_day = date('t', mktime(0, 0, 0, $this->end_month, 1, $this->end_year));
        return $this;
    }

    public function predefineStartCustomMonthYear($date) {
        $this->start_custom = $date;
        $start_custom = $this->parseDate($date, false);
        $this->start_month = $start_custom['month'];
        $this->start_year = $start_custom['year'];
        $this->end_day = date('t', mktime(0, 0, 0, $this->end_month, 1, $this->end_year));
        return $this;
    }

    public function predefineEndCustomMonthYear($date) {
        $this->end_custom = $date;
        $end_custom = $this->parseDate($date, false);
        $this->end_month = $end_custom['month'];
        $this->end_year = $end_custom['year'];
        $this->end_day = date('t', mktime(0, 0, 0, $this->end_month, 1, $this->end_year));
        return $this;
    }

    public function predefineStartCustomDayMonthYear($date) {
        $this->start_custom = $date;
        $start_custom = $this->parseDate($date);
        $this->start_day = $start_custom['day'];
        $this->start_month = $start_custom['month'];
        $this->start_year = $start_custom['year'];
        return $this;
    }

    public function predefineEndCustomDayMonthYear($date) {
        $this->end_custom = $date;
        $end_custom = $this->parseDate($date);
        $this->end_day = $end_custom['day'];
        $this->end_month = $end_custom['month'];
        $this->end_year = $end_custom['year'];
        return $this;
    }

    public function hasDailyItems() {
        return false;
    }

    public function getDataYear($data) {
        $years = [];
        if (is_array($data)) {
            foreach ($data as $info) {
                $_year = date("Y", strtotime($info['period_full']));
                if (!in_array($_year, $years)) {
                    $years[] = $_year;
                }
            }
        }
        return $years;
    }

    public function setClassRange($class_range){
        $this->class_range = $class_range;
    }

    public function getClassRange(){
        return $this->class_range;
    }

}
