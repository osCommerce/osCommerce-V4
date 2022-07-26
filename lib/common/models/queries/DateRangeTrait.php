<?php

/*
 * This file is part of osCommerce ecommerce platform.
 * osCommerce the ecommerce
 * 
 * @link https://www.oscommerce.com
 * @copyright Copyright (c) 2005 Holbi Group Ltd
 * 
 * Released under the GNU General Public License
 * For the full copyright and license information, please view the LICENSE.TXT file that was distributed with this source code.
 */

namespace common\models\queries;

trait DateRangeTrait {

  public static $startEpoch = '0000-00-00 00:00:00';
  public static $expireDateField = 'expires_date';
  public static $startDateField = 'start_date';

  public function dateInRange($dateTime = null) {
    if (empty($dateTime)) {
      $dateTime = date("Y-m-d H:i:s");
    } else {
/// nice to have - validate date format
    }
    return $this->startBefore($dateTime)->endAfter($dateTime);
  }

  /**
   * for active on (could be active in the specified date range, either all the time or partially)
   * @param type $startDate
   * @param type $endDate
   */
  public function datesInRange($startDate, $endDate = null) {
    return $this->andWhere([
          'or',
          //specials start between the dates
          [
            'and',
            static::startAfterArray($startDate),
            static::startBeforeArray($endDate)
          ],
          //specials end between the dates
          [
            'and',
            static::endAfterArray($startDate),
            static::endBeforeArray($endDate)
          ],
          //specials full cover (specials starts before ends after)
          [
            'and',
            static::startBeforeArray($startDate),
            static::endAfterArray($endDate)
          ],
          //specials within (specials starts after ends before )
          [
            'and',
            static::startAfterArray($startDate),
            static::endBeforeArray($endDate)
          ],
    ]);
  }

  public function startBefore($dateTime = null) {
    if (empty($dateTime)) {
      $dateTime = date("Y-m-d H:i:s");
    } else {
/// nice to have - validate date format
    }
    return $this->andWhere(static::startBeforeArray($dateTime));
  }

  public function startAfter($dateTime = null) {
    if (empty($dateTime)) {
      $dateTime = date("Y-m-d H:i:s");
    } else {
/// nice to have - validate date format
    }
    return $this->andWhere(static::startAfterArray($dateTime));
  }

  public function endBefore($dateTime = null) {
    if (empty($dateTime)) {
      $dateTime = date("Y-m-d H:i:s");
    } else {
/// nice to have - validate date format
    }
    return $this->andWhere(static::endBeforeArray($dateTime));
  }

  /**
   * after date or null (not specified)
   * @param datetime $dateTime default - today
   * @return type
   */
  public function endAfter($dateTime = null) {
    if (empty($dateTime)) {
      $dateTime = date("Y-m-d H:i:s");
    } else {
/// nice to have - validate date format
    }
    return $this->andWhere(static::endAfterArray($dateTime));
  }

  private static function endBeforeArray($dateTime) {
    return
      //'and',
      ['<=', self::$expireDateField, $dateTime]
      //['=', self::$expireDateField, self::$startEpoch]
    ;
  }

  private static function endAfterArray($dateTime) {
    return [
      'or',
      ['>=', self::$expireDateField, $dateTime],
      ['=', self::$expireDateField, self::$startEpoch], //workaround not null default 0000-00-00 00:00:00 | 1970-01-01 01:00:00
      ['is', self::$expireDateField, null]
    ];
  }

  private static function startBeforeArray($dateTime) {
    return [
      'or',
      ['<=', self::$startDateField, $dateTime],
      ['=', self::$startDateField, self::$startEpoch], //workaround not null default 0000-00-00 00:00:00 | 1970-01-01 01:00:00
      ['is', self::$startDateField, null]
    ];
  }

  private static function startAfterArray($dateTime) {
    return [
      '>=', self::$startDateField, $dateTime
    ];
  }
}
