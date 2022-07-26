<?php

namespace backend\models\Report;

use Yii;

class YearlyReport extends BasicReport implements ReportInterface {

    CONST DELIMETER = "/";
    CONST SHOW_ROWS = 10;

    protected $start_year;
    protected $end_year;
    protected $all_params = [];
    private $name = 'yearly';
    protected $sql_params = [
        'group' => ['year'],
        'select_period' => 'date',
    ];
    protected $range = [
        'all' => TEXT_ALL_PERIOD, 'custom' => TEXT_CUSTOM,
    ];
    protected $current_range;

    public function __construct($data) {

        if (isset($data['start_custom']) && !empty($data['start_custom'])) {
            $this->start_year = $data['start_custom'];
        }

        if (isset($data['end_custom']) && !empty($data['end_custom'])) {
            $this->end_year = $data['end_custom'];
        }

        if (tep_not_null($this->start_year) && tep_not_null($this->end_year)) {
            if ($this->start_year > $this->end_year) {
                $_y = $this->end_year;
                $this->end_year = $this->start_year;
                $this->start_year = $_y;
            }
        }

        $years = $this->getYearsList();
        $years = array_values($years);
        if (empty($this->start_year))
            $this->start_year = $years[0];
        if (empty($this->end_year))
            $this->end_year = $years[sizeof($years) - 1];

        //need ordering check 
        //echo '<pre>';print_r($this);die;
        parent::__construct($data);
    }

    public function getOptions($range) {
        switch ($range) {
            case 'all' :
                return '';
                break;
            case 'custom':
                return Yii::$app->controller->renderAjax('yearly_options', ['year' => $this->start_year, 'years' => $this->getYearsList(), 'start_custom' => $this->request['start_custom'], 'end_custom' => $this->request['end_custom']]);
                break;
        }
    }

    public function loadPurchases($for_map = false) {
        $where = " (year(o.date_purchased) between '" . $this->start_year . "' and '" . $this->end_year . "') ";
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
                    $new_data = $this->prepareYearsRange($template, "Y");
                    $filled = true;
                }
                if (!empty($v['period'])) {
                    $data[$k]['period'] = date("Y", strtotime($v['period']));
                    $new_data[date("Y", strtotime($v['period']))] = $data[$k];
                }
            }

            $_temp = [];
            foreach ($new_data as $kyear => $vyear) {
                $_temp[] = $vyear;
            }
            $data = $_temp;
        }
        return $data;
    }

    public function getRange() {
        return date("Y", mktime(0, 0, 0, 1, 1, $this->start_year)) . ' - ' . date("Y", mktime(0, 0, 0, 12, 1, $this->end_year));
    }

    public function getTableTitle() {
        return TEXT_SALES_YEARLY_STATISTICS;
    }

    public function convertColumnTitle($value) {
        if ($value == 'period') {
            return parent::convertColumnTitle(TITLE_YEAR);
        }
        return parent::convertColumnTitle($value);
    }

    public function getRowsCount() {
        return self::SHOW_ROWS;
    }

}
