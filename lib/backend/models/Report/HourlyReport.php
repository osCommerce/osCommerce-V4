<?php

namespace backend\models\Report;

use Yii;

class HourlyReport extends BasicReport implements ReportInterface {

    CONST DELIMETER = "/";
    CONST SHOW_ROWS = -1;

    protected $start_day;
    protected $end_day;
    protected $start_month;
    protected $end_month;
    protected $start_year;
    protected $end_year;
    protected $day;

    protected $start_day_cmp;
    protected $end_day_cmp;
    protected $start_month_cmp;
    protected $end_month_cmp;
    protected $start_year_cmp;
    protected $end_year_cmp;
    protected $day_cmp;

    protected $start_custom;
    protected $end_custom;
    protected $all_params = [];
    private $name = 'hourly';
    protected $orders_avg = false;
    protected $total_avg = false;
    protected $interval = 0;
    protected $sql_params = [
        'group' => ['hour', 'dayofmonth', 'month', 'year'],
        'select_period' => '',
    ];
    protected $range = [
        'day' => TEXT_DAY, 'month' => TEXT_MONTH_COMMON, 'year' => TITLE_YEAR, 'custom' => TEXT_CUSTOM,
    ];
    protected $current_range;

    public function __construct($data) {
        switch ($data['range']) {
            case 'day':
                if (isset($data['day']) && !empty($data['day'])) {
                    $day = $this->parseDate($data['day'], true);
                    $this->start_day = $day['day'];
                    $this->start_month = $day['month'];
                    $this->start_year = $day['year'];
                    $this->end_day = $day['day'];
                    $this->end_month = $day['month'];
                    $this->end_year = $day['year'];
                    if (isset($data['day_cmp']) AND ($data['day'] != $data['day_cmp'])) {
                        $day_cmp = $this->parseDate($data['day_cmp'], true);
                        if ((int)$day_cmp['year'] > 0) {
                            $this->start_day_cmp = $day_cmp['day'];
                            $this->start_month_cmp = $day_cmp['month'];
                            $this->start_year_cmp = $day_cmp['year'];
                            $this->end_day_cmp = $day_cmp['day'];
                            $this->end_month_cmp = $day_cmp['month'];
                            $this->end_year_cmp = $day_cmp['year'];
                            $this->day_cmp = $this->start_day_cmp . self::DELIMETER . $this->start_month_cmp . self::DELIMETER . $this->start_year_cmp;
                        }
                    }
                }
                break;
            case 'month':
                if (isset($data['month_year']) && !empty($data['month_year'])) {
                    $this->predefineMonthYear($data['month_year']);
                    $this->start_day = 1;
                    $this->end_day = date('t', mktime(0, 0, 0, $this->end_month, 1, $this->end_year));
                }
                break;
            case 'year':
                $this->start_day = 1;
                $this->end_day = 31;
                $this->start_month = 1;
                $this->end_month = 12;
                $this->start_year = $data['year'];
                $this->end_year = $data['year'];
                break;
            case 'custom':
                if (isset($data['start_custom']) && !empty($data['start_custom'])) {
                    $this->predefineStartCustomDayMonthYear($data['start_custom']);
                }
                if (isset($data['end_custom']) && !empty($data['end_custom'])) {
                    $this->predefineEndCustomDayMonthYear($data['end_custom']);
                }
                break;
        }

        if (isset($data['range']))
            $this->current_range = $data['range'];

        if (empty($this->start_day))
            $this->start_day = date("d");
        if (empty($this->end_day))
            $this->end_day = date("d");
        if (empty($this->start_month))
            $this->start_month = date("m");
        if (empty($this->end_month))
            $this->end_month = date("m");
        if (empty($this->start_year))
            $this->start_year = date("Y");
        if (empty($this->end_year))
            $this->end_year = date("Y");

        if (empty($this->start_custom)) {
            $this->day = $this->start_day . self::DELIMETER . $this->start_month . self::DELIMETER . $this->start_year;
        } else {
            $this->day = '';
        }

        $this->checkMonthDayYear();

        if (isset($data['chart_group_item']['orders_avg'])) {
            $this->orders_avg = true;
        }

        if (isset($data['chart_group_item']['total_avg'])) {
            $this->total_avg = true;
        }
        //echo '<pre>';print_r($this);die;
        parent::__construct($data);
    }

    public function getOptions($range) {
        switch ($range) {
            case 'day' :
                return Yii::$app->controller->renderAjax('day_month_year', [
                            'day' => $this->day,
                            'day_cmp' => trim($this->day_cmp),
                ]);
                break;
            case 'month' :
                return Yii::$app->controller->renderAjax('month_year', [
                            'month_year' => $this->month_year,
                            'holder' => TEXT_MONTH_COMMON . "/" . strtolower(TITLE_YEAR),
                ]);
                break;
            case 'year' :
                return Yii::$app->controller->renderAjax('year', [
                            'year' => $this->start_year,
                            'years' => $this->getYearsList(),
                ]);
                break;
            case 'custom':
                return Yii::$app->controller->renderAjax('custom_day_month_year', [
                            'start_custom' => $this->start_custom,
                            'end_custom' => $this->end_custom,
                            'holder' => TEXT_DAY_COMMON . "/" . TEXT_MONTH_COMMON . "/" . strtolower(TITLE_YEAR),
                ]);
                break;
        }
    }

    public function fillTheTime(&$data, $info) {
        static $last_day;
        $str = strtotime($info['period']);
        $d = date("d-m-Y", $str);
        $_period = '';
        if ($last_day != $d) {
            //$_period = date("d M Y H:00",  mktime(0, 0, 0, date("m", $str), date("d", $str), date("Y", $str)) );
            //$data[$d]['00:00']['period'] = $_period;
            $info['period'] = date("H:00", strtotime($info['period']));
            $last_day = date("d-m-Y", $str);
        } else {
            $info['period'] = date("H:00", strtotime($info['period']));
        }
        $data[$d][date("H:00", mktime(date("H", $str), 0, 0, date("m", $str), date("d", $str), date("Y", $str)))] = $info;

        return;
    }

    public function fillTimeGaps($data, $pattern) {
        $_hours = [];

        for ($i = 0; $i < 24; $i++) {
            $pattern['period'] = date("H:00", mktime($i, 0, 0, date("m"), date("d"), date("Y")));
            $pattern['period_full'] = date("m/d/Y H:00:00", mktime($i, 0, 0, date("m", strtotime($pattern['period_full'])), date("d", strtotime($pattern['period_full'])), date("Y", strtotime($pattern['period_full']))));
            $pattern['cur_row'] = date('Y-m-d:H') == date('Y-m-d:H', mktime($i, 0, 0, date("m", strtotime($pattern['period_full'])), date("d", strtotime($pattern['period_full'])), date("Y", strtotime($pattern['period_full']))) );
            $_hours[$pattern['period']] = $pattern;
        }

        foreach ($data as $day => $value) {
            $data[$day] = $_hours;
            //04/01/2017 12:31:07
            $data[$day]['00:00']['period'] = date("d M Y 00:00", mktime(0, 0, 0, date("m", strtotime($day)), date("d", strtotime($day)), date("Y", strtotime($day))));
        }

        return $data;
    }

    public function calculateAVG($data) {
        if (is_array($data)) {
            $avg = ['orders' => [], 'total' => []];
            foreach ($data as $day => $hours) {
                foreach ($hours as $hour => $value) {
                    if ($this->orders_avg) {
                        $avg['orders'][$hour] += (int) $value['orders'];
                    }
                    if ($this->total_avg) {
                        $avg['total'][$hour] += (int) $value['ot_total'];
                    }
                }
            }
            foreach ($data as $day => $hours) {
                foreach ($hours as $hour => $value) {
                    if ($this->orders_avg) {
                        $this->insertAt($data[$day][$hour], 'orders', 'orders_avg', round($avg['orders'][$hour] / count($data), 0));
                    }
                    if ($this->total_avg) {
                        $this->insertAt($data[$day][$hour], 'ot_total', 'total_avg', round($avg['total'][$hour] / count($data), 2));
                    }
                }
            }
        }
        //echo '<pre>';print_r($data);die;
        return $data;
    }

    public function loadPurchases($for_map = false) {

        $where = " ( o.date_purchased between '" . $this->start_year . "-" . $this->start_month . "-" . $this->start_day . " 00:00:00' and '" . $this->end_year . "-" . $this->end_month . "-" . $this->end_day . "  23:59:59' ) ";
        $data = $this->getRawData($where, $for_map);
        if ($for_map) return $data;
        $new_data = $this->prepareDaysRange();

        if (is_array($data)) {
            $filled = false;
            foreach ($data as $k => $v) {
                if (!$filled) {
                    $template = $v;
                    foreach ($template as $key => $value) {
                        if ($key != 'period_full') {
                            $template[$key] = '';
                        }
                    }
                    $new_data = $this->fillTimeGaps($new_data, $template);
                    $filled = true;
                }
                $this_day = date("d-m-Y", strtotime($v['period']));
                if (!empty($v['period'])) {
                    $this->fillTheTime($new_data, $v);
                }
            }

            if (($this->orders_avg || $this->total_avg) && $this->interval > 0) {
                $new_data = $this->calculateAVG($new_data);
            }
//echo'<pre>';print_r($this);die;
            $_temp = [];
            foreach ($new_data as $kday => $vday) {
                foreach ($vday as $day) {
                    $_temp[] = $day;
                }
            }
            $data = $_temp;

            if (($this->current_range == 'day') AND ((int)$this->start_year_cmp > 0)) {
                $data = $this->comparePurchases($data);
            }
        }

        return $data;
    }

    public function getRange() {
        if ($this->start_day == $this->end_day && $this->start_month == $this->end_month && $this->start_year == $this->end_year) {
            return date("d M Y", mktime(0, 0, 0, $this->start_month, $this->start_day, $this->start_year));
        }
        return date("d M Y", mktime(0, 0, 0, $this->start_month, $this->start_day, $this->start_year)) . ' - ' . date("d M Y", mktime(0, 0, 0, $this->end_month, $this->end_day, $this->end_year));
    }

    public function getTableTitle() {
        return TEXT_SALES_HOURLY_STATISTICS;
    }

    public function convertColumnTitle($value) {
        if ($value == 'period') {
            return TEXT_TIME_DATE;
        }

        return parent::convertColumnTitle($value);
    }

    public function getRowsCount() {
        return self::SHOW_ROWS;
    }

}
