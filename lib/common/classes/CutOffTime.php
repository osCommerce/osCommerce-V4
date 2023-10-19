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

namespace common\classes;
use common\models\Orders;
use common\models\Platforms;

/**
 * CutOffTime object
 */
class CutOffTime {

    /**
     * Data [platform_id][day_of_week] = time
     */
    public $today = [];
    public $nextday = [];

    /**
     * Constructor
     */
    public function __construct() {
        $today = [];
        $nextday = [];
        $cut_off_times_query = tep_db_query("select * from " . TABLE_PLATFORMS_CUT_OFF_TIMES . " where 1");
        while ($record = tep_db_fetch_array($cut_off_times_query)) {
            if (!isset($today[$record['platform_id']])) {
                $today[$record['platform_id']] = [];
            }
            if (!isset($nextday[$record['platform_id']])) {
                $nextday[$record['platform_id']] = [];
            }
            $cut_off_times_days = explode(",", $record['cut_off_times_days']);
            if (is_array($cut_off_times_days)) {
                foreach ($cut_off_times_days as $day) {
                    if ($day == 0) {
                        //everyday
                        for ($i = 1; $i <= 7;$i++) {
                            if (!isset($today[$record['platform_id']][$i]) && !empty($record['cut_off_times_today'])) {
                                $today[$record['platform_id']][$i] = $record['cut_off_times_today'];
                            }
                            if (!isset($nextday[$record['platform_id']][$i]) && !empty($record['cut_off_times_next_day'])) {
                                $nextday[$record['platform_id']][$i] = $record['cut_off_times_next_day'];
                            }
                        }
                    } else {
                        if (!isset($today[$record['platform_id']][$day]) && !empty($record['cut_off_times_today'])) {
                            $today[$record['platform_id']][$day] = $record['cut_off_times_today'];
                        }
                        if (!isset($nextday[$record['platform_id']][$day]) && !empty($record['cut_off_times_next_day'])) {
                            $nextday[$record['platform_id']][$day] = $record['cut_off_times_next_day'];
                        }
                    }
                }
            }
            
        }
        $this->today = $today;
        $this->nextday = $nextday;
        
    }
    
    public function isTodayDelivery($date = '', $platform_id = 0) {
        if (empty($date)) {
            $date = date('Y-m-d H:i:s');
        }
        if ($platform_id == 0 && defined('PLATFORM_ID')) {
            $platform_id = (int)PLATFORM_ID;
        }
        if ($platform_id == 0) {
            return false;
        }
        $timestamp = strtotime($date);
        $dayOfWeek = date('N', $timestamp);
        if (!isset($this->today[$platform_id][$dayOfWeek])) {
            return false;
        }
        $todayDeliveryStamp = strtotime(date('Y-m-d', $timestamp) . ' ' . $this->today[$platform_id][$dayOfWeek]);
        if ($timestamp <= $todayDeliveryStamp) {
            return true;
        }
        return false;
    }
    
    public function isNextDayDelivery($date = '', $platform_id = 0) {
        if (empty($date)) {
            $date = date('Y-m-d H:i:s');
        }
        if ($platform_id == 0 && defined('PLATFORM_ID')) {
            $platform_id = (int)PLATFORM_ID;
        }
        if ($platform_id == 0) {
            return false;
        }
        $timestamp = strtotime($date);
        $dayOfWeek = date('N', $timestamp);
        if (!isset($this->nextday[$platform_id][$dayOfWeek])) {
            return false;
        }
        $nextdayDeliveryStamp = strtotime(date('Y-m-d', $timestamp) . ' ' . $this->nextday[$platform_id][$dayOfWeek]);
        if ($timestamp <= $nextdayDeliveryStamp) {
            return true;
        }
        return false;
    }
    
    public function getTodayDeliveryDate($platform_id = 0) {
        $date = date('Y-m-d H:i:s');
        if ($platform_id == 0) {
            $platform_id = (int)PLATFORM_ID;
        }
        if ($platform_id == 0) {
            return '';
        }
        $timestamp = strtotime($date);
        $dayOfWeek = date('N', $timestamp);
        if (!isset($this->today[$platform_id][$dayOfWeek])) {
            return '';
        }
        $todayDeliveryStamp = strtotime(date('Y-m-d', $timestamp) . ' ' . $this->today[$platform_id][$dayOfWeek]);
        return date('Y-m-d H:i:s' ,$todayDeliveryStamp);
    }
    
    public function getNextDayDeliveryDate($platform_id = 0) {
        $date = date('Y-m-d H:i:s');
        if ($platform_id == 0) {
            $platform_id = (int)PLATFORM_ID;
        }
        if ($platform_id == 0) {
            return '';
        }
        $timestamp = strtotime($date);
        $dayOfWeek = date('N', $timestamp);
        if (!isset($this->nextday[$platform_id][$dayOfWeek])) {
            return '';
        }
        $nextdayDeliveryStamp = strtotime(date('Y-m-d', $timestamp) . ' ' . $this->nextday[$platform_id][$dayOfWeek]);
        return date('Y-m-d H:i:s' ,$nextdayDeliveryStamp);
    }

    /**
     * find date of delivery
     * @param Platforms|NULL $oPlatform
     * @param Orders|NULL $oOrder
     * @param string $format
     * @param bool $in_past
     *              true = returned date can be in past
     *              false = returned date cant be in past
     * @return string
     */
    public function getDeliveryDate(Platforms $oPlatform = null, Orders $oOrder = null, $format = null, $in_past = true)
    {
        $returnDate = null;
        $aAllDaysNum = [];
        // checked date
        $checked_date = new \DateTime($oOrder->date_purchased ?? Null);
        $day_number = $checked_date->format('N');
        $week_number = $checked_date->format('W');
        $year = $checked_date->format('Y');
        $checked_hour = $checked_date->format('H');
        $checked_minutes = $checked_date->format('i');

        $aPlatformsCutOffTimes = $oPlatform->platformsCutOffTimes;
        if($aPlatformsCutOffTimes)
        {
            // get all conditions
            foreach ($aPlatformsCutOffTimes as $oPlatformsCutOffTime)
            {
                // get days of condition
                $aDaysNum = explode(',', $oPlatformsCutOffTime->cut_off_times_days);
                $aAllDaysNum = array_merge($aAllDaysNum, $aDaysNum);
                // get time of condition
                $today_h = \DateTime::createFromFormat('g:i A', $oPlatformsCutOffTime->cut_off_times_today);
                $next_day_h = \DateTime::createFromFormat('g:i A', $oPlatformsCutOffTime->cut_off_times_next_day);

                // if checked day satisfies the condition
                if(($current_day_number = array_search($day_number, $aDaysNum)) !== false)
                {
                    // check today delivery
                    if($today_h)
                    {
                        $pattern_today_date = new \DateTime();
                        $pattern_today_date->setISODate($year, $week_number, $day_number);
                        $pattern_today_date->setTime($today_h->format('H'), $today_h->format('i'));
                        if($checked_hour <= $pattern_today_date->format('H') && $checked_minutes < $pattern_today_date->format('i'))
                        {
                            $returnDate = $pattern_today_date;
                        }
                    }

                    // check tomorrow delivery
                    if($next_day_h)
                    {
                        $pattern_next_date = new \DateTime();
                        $pattern_next_date->setISODate($year, $week_number, $day_number);
                        $pattern_next_date->setTime($next_day_h->format('H'), $next_day_h->format('i'));
                        if($checked_hour <= $pattern_next_date->format('H') && $checked_minutes < $pattern_next_date->format('i'))
                        {
                            $pattern_next_date->modify('+1 day');
                            $returnDate = $pattern_next_date;
                        }
                        else
                        {
                            $pattern_next_date->modify('+2 day');
                            $returnDate = $pattern_next_date;
                        }
                    }

                }
            }
        }

        if (count($aAllDaysNum) == 0) {
            $aAllDaysNum = [1,2,3,4,5,6];
        }
        if (is_null($returnDate)) {
            $pattern_next_date = new \DateTime();
            //$pattern_next_date->setISODate($year, $week_number, $day_number);
            //$pattern_next_date->setTime($checked_hour->format('H'), $checked_hour->format('i'));
            $pattern_next_date->modify('+1 day');
            $returnDate = $pattern_next_date;
        }
        // check if delivery day gets on an affordable day
        // returned date cant be in past
        while(!in_array($returnDate->format('N'), $aAllDaysNum))
        {
            $returnDate->modify('+1 day');
        }

        if((new \DateTime())->diff($returnDate)->days > 0 && !$in_past)
        {
            $returnDate = new \DateTime();

            while(!in_array($returnDate->format('N'), $aAllDaysNum))
            {
                $returnDate->modify('+1 day');
            }
        }
        return $format ?
                    $returnDate->format($format)
                    : $returnDate;
    }

    /**
     * get open & close time of specified day
     * @param Platforms|NULL $oPlatform
     * @param $date
     * @return array|null
     */
    public function getPostalOpenOurs(Platforms $oPlatform = null, $oDate = null)
    {
        is_null($oDate) && $oDate = new \DateTime();
        $oPlatformsOpenHours = $oPlatform->platformsOpenHours;
        foreach ($oPlatformsOpenHours as $oOpenHour)
        {
            if(in_array($oDate->format('N'), explode(',', $oOpenHour->open_days)))
            {
                $open_time_from = \DateTime::createFromFormat('g:i A', $oOpenHour->open_time_from);
                $open_time_to = \DateTime::createFromFormat('g:i A', $oOpenHour->open_time_to);
                return [
                    'open_time_from' => $open_time_from->format('H:i'),
                    'open_time_to' => $open_time_to->format('H:i')
                ];
            }
        }
        return null;
    }

}
