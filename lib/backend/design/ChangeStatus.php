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

namespace backend\design;

use Yii;
use yii\base\Widget;
use common\models\PageStatus;
use common\models\PageStatusSwitch;
use common\helpers\PageStatus as hPageStatus;

class ChangeStatus extends Widget
{
    public $type;
    public $element;
    public $pageId;
    public $statuses = [];
    public $periods = [];

    public function init(){
        parent::init();
    }

    public function run()
    {
        switch ($this->element) {
            case 'button': return $this->switcher('button');
            case 'dropdown': return $this->switcher('dropdown');
            case 'schedule': return $this->schedule();
        }

        return '';
    }

    private function switcher($name)
    {
        $status = PageStatus::find()->where([
            'type' => $this->type,
            'page_id' => $this->pageId,
        ])->asArray()->one();

        $statusKeys = [];

        $statuses = [];
        if (count($this->statuses) == 0) {
            $statuses = hPageStatus::PAGE_STATUSES;
            foreach ($statuses as $statusKey => $statusTitle) {
                $statusKeys[] = $statusKey;
            }
        } else {
            foreach (hPageStatus::PAGE_STATUSES as $statusKey => $statusTitle) {
                if (in_array($statusKey, $this->statuses)) {
                    $statuses[$statusKey] = $statusTitle;
                }
            }
            $statusKeys = $this->statuses;
        }

        return $this->render('change-status-' . $name . '.tpl', [
            'id' => rand (1, 100000),
            'pageStatusActions' => $this->statusActions(),
            'status' => $status['status'],
            'statuses' => $statuses,
            'data' => json_encode([
                'statuses' => $statuses,
                'statusKeys' => $statusKeys,
                'status' => $status['status'],
                'pageStatusActions' => $this->statusActions(),
                'pageId' => $this->pageId,
                'type' => $this->type
            ])
        ]);
    }

    private function schedule()
    {
        $pageSwitchers = PageStatusSwitch::find()->alias('pss')
            ->innerJoin(PageStatus::tableName() . ' ps', 'ps.page_status_id = pss.page_status_id')
            ->where([
                'ps.type' => $this->type,
                'ps.page_id' => $this->pageId,
            ])
            ->asArray()->all();

        foreach ($pageSwitchers as $key => $pageSwitcher) {
            $date  = date_create_from_format(\common\helpers\Date::DATABASE_DATETIME_FORMAT, $pageSwitcher['date']);
            $pageSwitchers[$key]['day'] = -1;

            switch ($pageSwitcher['period']) {
                case 'year':
                    $pageSwitchers[$key]['date'] = $date->format('d M g:i A');
                    break;
                case 'month':
                    $pageSwitchers[$key]['date'] = $date->format('d g:i A');
                    break;
                case 'week':
                    $pageSwitchers[$key]['date'] = $date->format('g:i A');
                    $pageSwitchers[$key]['day'] = $date->format('N') - 1;
                    break;
                case 'day':
                    $pageSwitchers[$key]['date'] = $date->format('g:i A');
                    break;
                default:
                    $pageSwitchers[$key]['date'] = $date->format('d M Y g:i A');
            }
        }

        return $this->render('change-status.tpl', [
            'pageSwitchers' => $pageSwitchers,
            'pageStatusActions' => $this->statusActions(),
            'pageStatusPeriods' => $this->statusPeriods(),
            'weekDays' => [TEXT_MONDAY, TEXT_TUESDAY, TEXT_WEDNESDAY, TEXT_THURSDAY, TEXT_FRIDAY, TEXT_SATURDAY, TEXT_SUNDAY],
        ]);
    }

    private function statusActions()
    {
        $actions = [];
        if (count($this->statuses) > 0) {
            foreach ($this->statuses as $status) {
                if (hPageStatus::PAGE_STATUSES[$status]) {
                    $actions[$status] = sprintf(STATUS_MOVE_TO, hPageStatus::PAGE_STATUSES[$status]);
                }
            }
        } else {
            foreach (hPageStatus::PAGE_STATUSES as $status => $title) {
                $actions[$status] = sprintf(STATUS_MOVE_TO, $title);
            }
        }
        return $actions;
    }

    private function statusPeriods()
    {
        if (count($this->periods) == 0) {
            return hPageStatus::PAGE_STATUS_PERIODS;
        }
        $periods = [];
        foreach ($this->periods as $period) {
            if (hPageStatus::PAGE_STATUS_PERIODS[$period]) {
                $periods[$period] = hPageStatus::PAGE_STATUS_PERIODS[$period];
            }
        }
        return $periods;
    }
}