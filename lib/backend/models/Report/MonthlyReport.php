<?php

namespace backend\models\Report;

use Yii;

class MonthlyReport extends BasicReport implements ReportInterface {

    CONST DELIMETER = "/";
    CONST SHOW_ROWS = 10;

    protected $start_month;
    protected $end_month;
    protected $start_year;
    protected $end_year;

    protected $start_month_cmp;
    protected $end_month_cmp;
    protected $start_year_cmp;
    protected $end_year_cmp;

    private $start_custom;
    private $end_custom;
    protected $all_params = [];
    private $name = 'monthly';
    protected $sql_params = [
        'group' => ['month', 'year'],
        'select_period' => 'date',
    ];
    protected $range = [
        'year' => TITLE_YEAR, 'all' => TEXT_ALL_PERIOD, 'custom' => TEXT_CUSTOM,
    ];
    protected $current_range;

    public function __construct($data) {
        switch ($data['range']) {
            case 'year':
                if (isset($data['year'])) {
                    $this->start_month = '01';
                    $this->start_year = $data['year'];
                    $this->end_month = '12';
                    $this->end_year = $data['year'];
                    if (isset($data['year_cmp']) AND ((int)$data['year_cmp'] > 0) AND ($data['year'] != $data['year_cmp'])) {
                        $this->start_month_cmp = '01';
                        $this->start_year_cmp = $data['year_cmp'];
                        $this->end_month_cmp = '12';
                        $this->end_year_cmp = $data['year_cmp'];
                    }
                }
                break;
            case 'custom':
                if (isset($data['start_custom']) && !empty($data['start_custom'])) {
                    $start_custom = $this->parseDate($data['start_custom'], false);
                    $this->start_custom = $data['start_custom'];
                    $this->start_month = $start_custom['month'];
                    $this->start_year = $start_custom['year'];
                }

                if (isset($data['end_custom']) && !empty($data['end_custom'])) {
                    $end_custom = $this->parseDate($data['end_custom'], false);
                    $this->end_custom = $data['end_custom'];
                    $this->end_month = $end_custom['month'];
                    $this->end_year = $end_custom['year'];
                }
                break;
            case 'all':
                $this->start_month = '01';
                $this->start_year = date("Y");
                $this->end_month = '12';
                $this->end_year = date("Y");
                $start = $this->getFirstDatePurchase();
                if ($start) {
                    $this->start_month = date('m', strtotime($start));
                    $this->start_year = date('Y', strtotime($start));
                }
                $end = $this->getLastDatePurchase();
                if ($end) {
                    $this->end_month = date('m', strtotime($end));
                    $this->end_year = date('Y', strtotime($end));
                }
                break;
        }

        if (isset($data['range']))
            $this->current_range = $data['range'];

        if (empty($this->start_month))
            $this->start_month = '01';
        if (empty($this->end_month))
            $this->end_month = '12';
        if (empty($this->start_year))
            $this->start_year = date("Y");
        if (empty($this->end_year))
            $this->end_year = date("Y");

        //need ordering check

        parent::__construct($data);
    }

    public function getOptions($range) {
        switch ($range) {
            case 'all' :
                return '';
                break;
            case 'year' :
                return Yii::$app->controller->renderAjax('year', [
                            'year' => $this->start_year,
                            'year_cmp' => trim($this->start_year_cmp),
                            'years' => $this->getYearsList(),
                ]);
                break;
            case 'custom':
                return Yii::$app->controller->renderAjax('custom_month_year', [
                            'start_custom' => $this->start_custom,
                            'end_custom' => $this->end_custom,
                            'holder' => TEXT_MONTH_COMMON . "/" . strtolower(TITLE_YEAR),
                ]);
                break;
        }
    }

    public function loadPurchases($for_map = false) {
        $where = " ( o.date_purchased between '" . $this->start_year . "-" . $this->start_month . "-01 00:00:00' and '" . $this->end_year . "-" . $this->end_month . "-31 23:59:59' ) ";
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
                    $new_data = $this->prepareMonthRange($template, "M Y", $this->class_range);
                    $filled = true;
                }
                if (!empty($v['period'])) {
                    $data[$k]['period'] = date("M Y", strtotime($v['period']));
                    $data[$k]['period_full'] = date("m/d/Y H:00:00", strtotime($v['period']));
                    $data[$k]['cur_row'] = date('Y-m') == date('Y-m', strtotime($v['period']) );
                    $new_data[date("m-Y", strtotime($v['period']))] = $data[$k];
                }
            }
            $_temp = [];
            foreach ($new_data as $kday => $vday) {
                $_temp[] = $vday;
            }
            $data = $_temp;
            //$this->end_month = date("m", strtotime($v['period']));

            if (($this->current_range == 'year') AND ((int)$this->start_year_cmp > 0)) {
                $data = $this->comparePurchases($data);
            }
        }
        return $data;
    }

    public function getRange() {
        return date("M, Y", mktime(0, 0, 0, $this->start_month, 1, $this->start_year)) . ' - ' . date("M, Y", mktime(0, 0, 0, $this->end_month, 1, $this->end_year));
    }

    public function getTableTitle() {
        return TEXT_SALES_MONTHLY_STATISTICS;
    }

    public function convertColumnTitle($value) {
        if ($value == 'period') {
            return parent::convertColumnTitle(TEXT_MONTH_COMMON);
        }
        return parent::convertColumnTitle($value);
    }

    public function getRowsCount() {
        if (($this->start_month != $this->end_month &&
                $this->start_year == $this->end_year) ||
                ($this->start_month == $this->end_month &&
                $this->start_year != $this->end_year)
        ) {
            return 25;
        }
        return self::SHOW_ROWS;
    }

}
