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

namespace common\helpers;

use Yii;
use common\models\PageStatus as mPageStatus;
use common\models\PageStatusSwitch;
use common\helpers\Html;

class PageStatus {

    public const PAGESTATUS_CACHE_LIFETIME = 5;
    public const PAGE_STATUSES = [
        'public' => STATUS_PUBLIC,
        'draft' => STATUS_DRAFT
    ];

    public const PAGE_STATUS_PERIODS = [
        'once' => STATUS_PERIOD_ONCE,
        'year' => STATUS_PERIOD_EVERY_YEAR,
        'month' => STATUS_PERIOD_EVERY_MONTH,
        'week' => STATUS_PERIOD_EVERY_WEEK,
        'day' => STATUS_PERIOD_EVERY_DAY
    ];

    public static function getIds($status, $type)
    {
        self::switchStatuses();

        $query_key = (string)$status.'&'.(string)$type;
        static $page_status_ids = [];
        if ( !isset($page_status_ids[$query_key]) ) {
            $pageStatuses = mPageStatus::find()->where([
                'type' => $type,
                'status' => $status
            ])
                ->cache(self::PAGESTATUS_CACHE_LIFETIME)
                ->asArray()->all();

            $ids = [];
            foreach ($pageStatuses as $pageStatus) {
                $ids[] = $pageStatus['page_id'];
            }
            $page_status_ids[$query_key] = $ids;
        }
        return $page_status_ids[$query_key];
    }

    public static function isStatus($status, $type, $pageId)
    {
        self::switchStatuses();

        $exists = mPageStatus::find()->where([
            'type' => $type,
            'page_id' => $pageId,
            'status' => $status
        ])->exists();

        return $exists;
    }

    public static function switchStatuses()
    {
        static $changed = false;
        if ($changed) return;
        $changed = true;

        $pageStatusSwitchers = PageStatusSwitch::find()
            ->where(['<', 'date', new \yii\db\Expression('NOW()')])
            ->orderBy('date')
            ->all();

        $now = new \DateTime('NOW');

        foreach ($pageStatusSwitchers as $pageStatusSwitcher) {

            $pageStatus = mPageStatus::findOne(['page_status_id' => $pageStatusSwitcher->page_status_id]);
            $pageStatus->status = $pageStatusSwitcher->status;
            $pageStatus->save();

            $date = date_create_from_format(
                \common\helpers\Date::DATABASE_DATETIME_FORMAT,
                $pageStatusSwitcher->date
            );

            while (date_diff($now, $date)->invert) {
                switch ($pageStatusSwitcher->period) {
                    case 'year':
                        $date->modify('+1 year');
                        break;
                    case 'month':
                        $date->modify('first day of next month');
                        $date->modify('+' . ($pageStatusSwitcher->day - 1) . ' days');
                        break;
                    case 'week':
                        $date->modify('+1 week');
                        break;
                    case 'day':
                        $date->modify('+1 day');
                        break;
                    default:
                        break 2;
                }
            }

            if ($pageStatusSwitcher->period == 'once') {
                $pageStatusSwitcher->delete();
            } else {
                $pageStatusSwitcher->date = $date->format(\common\helpers\Date::DATABASE_DATETIME_FORMAT);
                $pageStatusSwitcher->save();
            }
        }
    }

    public static function saveScheduledStatuses($type, $pageId, $statuses)
    {
        $pageStatusId = mPageStatus::findOne(['type' => $type, 'page_id' => $pageId])->page_status_id ?? null;

        if ($pageStatusId) {
            PageStatusSwitch::deleteAll(['page_status_id' => $pageStatusId]);
        } else {
            $pageStatus = new mPageStatus();
            $pageStatus->type = $type;
            $pageStatus->page_id = $pageId;
            $pageStatus->status = 'draft';

            $pageStatus->save();
            $pageStatusId = $pageStatus->page_status_id;
        }

        foreach ($statuses['action'] as $key => $action) {
            $period = $statuses['period'][$key];
            $entryDate = $statuses['date'][$key];
            $day = $statuses['day'][$key];
            if (!$entryDate) continue;

            switch ($period) {
                case 'year':
                    $format = 'd M g:i a';
                    break;
                case 'month':
                    $format = 'd g:i a';
                    break;
                case 'week':
                    $format = 'g:i a';
                    break;
                case 'day':
                    $format = 'g:i a';
                    break;
                default:
                    $format = 'd M Y g:i a';
            }

            $date  = date_create_from_format($format, $entryDate);

            if ($period == 'month') {
                preg_match('/^[0-9]{1,2}/', $entryDate, $matches);
                $date->modify('first day of this month');
                $date->modify('+' . ($matches[0] - 1) . ' days');
            } elseif ($period == 'week') {
                $date = new \DateTime('NOW');
                $time  = date_create_from_format($format, $entryDate);
                $date->modify('Monday this week');
                $date->setTime($time->format('G'), $time->format('i'));
                $date->modify('+' . $day . ' days');
            }

            $now = new \DateTime('NOW');
            $dateDiff = date_diff($now, $date);

            if ($dateDiff->invert) {
                switch ($period) {
                    case 'year':
                        $date->modify('+1 year');
                        break;
                    case 'month':
                        $date->modify('first day of next month');
                        $date->modify('+' . ($matches[0] - 1) . ' days');
                        break;
                    case 'week':
                        $date->modify('+1 week');
                        break;
                    case 'day':
                        $date->modify('+1 day');
                        break;
                    default:
                        continue 2;
                }
            }

            $pageStatusSwitch = new PageStatusSwitch();
            $pageStatusSwitch->page_status_id = $pageStatusId;
            $pageStatusSwitch->status = $action;
            $pageStatusSwitch->period = $period;
            $pageStatusSwitch->date = $date->format(\common\helpers\Date::DATABASE_DATETIME_FORMAT);
            $pageStatusSwitch->save();
        }
    }

    public static function showStatus($type, $pageId)
    {
        self::switchStatuses();

        $page = mPageStatus::find()->where([
            'type' => $type,
            'page_id' => $pageId
        ])->asArray()->one();

        return Html::tag('span', self::PAGE_STATUSES[$page['status']], ['class' => 'current-page-status']);
    }

    public static function showButton($type, $pageId, $statuses = [])
    {
        return \backend\design\ChangeStatus::widget(['element' => 'button', 'type' => $type, 'pageId' => $pageId, 'statuses' => $statuses]);
    }

    public static function showDropdown($type, $pageId, $statuses = [])
    {
        return \backend\design\ChangeStatus::widget(['element' => 'dropdown', 'type' => $type, 'pageId' => $pageId, 'statuses' => $statuses]);
    }

    public static function showSchedule($type, $pageId, $statuses = [], $periods = [])
    {
        return \backend\design\ChangeStatus::widget(['element' => 'schedule', 'type' => $type, 'pageId' => $pageId, 'statuses' => $statuses, 'periods' => $periods]);
    }
}
