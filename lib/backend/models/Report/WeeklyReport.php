<?php

namespace backend\models\Report;

use Yii;

class WeeklyReport extends BasicReport implements ReportInterface {

    CONST DELIMETER = "/";
    CONST SHOW_ROWS = 50;

    protected $start_day;
    protected $end_day;
    protected $start_month;
    protected $end_month;
    protected $start_year;
    protected $end_year;

    protected $start_day_cmp;
    protected $end_day_cmp;
    protected $start_month_cmp;
    protected $end_month_cmp;
    protected $start_year_cmp;
    protected $end_year_cmp;

    private $week;
    private $week_cmp;

    protected $all_params = [];
    private $name = 'weekly';
    protected $sql_params = [
        'group' => ['day', 'month', 'year'],
        'select_period' => 'date',
    ];
    protected $range = [
        'week' => TEXT_WEEK_COMMON, 'custom' => TEXT_CUSTOM,
    ];
    protected $current_range;

    public function __construct($data) {
        switch ($data['range']) {
            case 'week':
                if (isset($data['week'])) {
                    $this->week = $data['week'];
                    $range = $this->parseDate($data['week'], true);
                    if ($range) {
                        $this->start_day = $range->start['day'];
                        $this->start_month = $range->start['month'];
                        $this->start_year = $range->start['year'];
                        $this->end_day = $range->end['day'];
                        $this->end_month = $range->end['month'];
                        $this->end_year = $range->end['year'];
                        if (isset($data['week_cmp']) AND ($data['week'] != $data['week_cmp'])) {
                            $week_cmp = $this->parseDate($data['week_cmp'], true);
                            if (isset($week_cmp->start) AND isset($week_cmp->end) AND ((int)$week_cmp->start['year'] > 0) AND ((int)$week_cmp->end['year'] > 0)) {
                                $this->week_cmp = $data['week_cmp'];
                                $this->start_day_cmp = $week_cmp->start['day'];
                                $this->start_month_cmp = $week_cmp->start['month'];
                                $this->start_year_cmp = $week_cmp->start['year'];
                                $this->end_day_cmp = $week_cmp->end['day'];
                                $this->end_month_cmp = $week_cmp->end['month'];
                                $this->end_year_cmp = $week_cmp->end['year'];
                            }
                        }
                    }
                }
                break;
            case 'custom':
                if (isset($data['start_custom']) && !empty($data['start_custom'])) {
                    $range = $this->parseDate($data['start_custom']);
                    $this->start_day = $range->start['day'];
                    $this->start_month = $range->start['month'];
                    $this->start_year = $range->start['year'];
                }

                if (isset($data['end_custom']) && !empty($data['end_custom'])) {
                    $range = $this->parseDate($data['end_custom']);
                    $this->end_day = $range->start['day'];
                    $this->end_month = $range->start['month'];
                    $this->end_year = $range->start['year'];
                }
                break;
        }

        if (isset($data['range']))
            $this->current_range = $data['range'];

        if (empty($this->start_day))
            $this->start_day = date("d", strtotime("- 7 days "));
        if (empty($this->end_day))
            $this->end_day = date("d");
        if (empty($this->start_month))
            $this->start_month = date("m", strtotime("- 7 days "));
        if (empty($this->end_month))
            $this->end_month = date("m");
        if (empty($this->start_year))
            $this->start_year = date("Y", strtotime("- 7 days "));
        if (empty($this->end_year))
            $this->end_year = date("Y");

        $this->week = $this->start_day . self::DELIMETER . $this->start_month . self::DELIMETER . $this->start_year . "-" . $this->end_day . self::DELIMETER . $this->end_month . self::DELIMETER . $this->end_year;
        //need ordering check
        $this->checkMonthDayYear();
        //echo'<pre>';print_r($this);
        parent::__construct($data);
    }

    public function getOptions($range) {
        switch ($range) {
            case 'week' :
                return Yii::$app->controller->renderAjax('week', [
                            'week' => $this->week,
                            'week_cmp' => trim($this->week_cmp),
                ]);
                break;
            case 'custom':
                return Yii::$app->controller->renderAjax('custom_day_month_year', [
                            'start_custom' => $this->start_custom,
                            'end_custom' => $this->end_custom,
                                //'holder' => TEXT_WEEK_COMMON,
                ]);
                break;
        }
    }

    public function parseDate($date, $isRange = false) {
        $_start = $_end = [];
        if ($isRange) {
            $ex = explode("-", $date);
            $_start = explode(self::DELIMETER, $ex[0]);
            $_end = explode(self::DELIMETER, $ex[1]);
        } else {
            $_start = explode(self::DELIMETER, $date);
        }
        $range = new \stdClass();
        //TODO checkdate
        if (count($_start)) {
            $range->start = [
                'day' => $_start[0],
                'month' => $_start[1],
                'year' => $_start[2],
            ];
        }

        if (count($_end)) {
            $range->end = [
                'day' => $_end[0],
                'month' => $_end[1],
                'year' => $_end[2],
            ];
        }

        return $range;
    }

    public function loadPurchases($for_map = false) {
        $where = " ( o.date_purchased between '" . $this->start_year . "-" . $this->start_month . "-" . $this->start_day . " 00:00:00' and '" . $this->end_year . "-" . $this->end_month . "-" . $this->end_day . " 23:59:59' ) ";
        $data = $this->getRawData($where, $for_map);
        if ($for_map) return $data;
        if (is_array($data)) {
            $filled = false;
            $new_data = [];
            foreach ($data as $k => $v) {
                if (!$filled) {
                    $template = $v;
                    foreach ($template as $key => $value) {
                        if ($key != 'period_full') {
                            $template[$key] = '';
                        }
                    }
                    $new_data = $this->prepareDaysRange($template, "d M Y", $this->class_range);
                    $filled = true;
                }
                if (!empty($v['period'])) {
                    $data[$k]['period'] = date("d M Y", strtotime($v['period']));
                    $data[$k]['period_full'] = date("m/d/Y H:00:00", strtotime($v['period']));
                    $new_data[date("d-m-Y", strtotime($v['period']))] = $data[$k];
                }
            }
            $_temp = [];
            foreach ($new_data as $kday => $vday) {
                $_temp[] = $vday;
            }
            $data = $_temp;
            //$this->end_month = date("m", strtotime($v['period']));

            if (($this->current_range == 'week') AND ((int)$this->start_year_cmp > 0)) {
                $data = $this->comparePurchases($data);
            }
        }
        return $data;
    }

    public function getRange() {
        return date("d M Y", mktime(0, 0, 0, $this->start_month, $this->start_day, $this->start_year)) . ' - ' . date("d M Y", mktime(0, 0, 0, $this->end_month, $this->end_day, $this->end_year));
    }

    public function getTableTitle() {
        return TEXT_SALES_WEEKLY_STATISTICS;
    }

    public function convertColumnTitle($value) {
        if ($value == 'period') {
            return parent::convertColumnTitle(TEXT_DATE);
        }
        return parent::convertColumnTitle($value);
    }

    public function getRowsCount() {
        return self::SHOW_ROWS;
    }

    public function hasDailyItems(){
        return true;
    }

}
