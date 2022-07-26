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

namespace common\models\queries;

use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[Specials]].
 *
 * @see Specials
 */
class SpecialsQuery extends ActiveQuery {
  use DateRangeTrait;

  public function active($active = true) {
    if ($active) {
      $ret = $this->andWhere('status>0');
    } else {
      $ret = $this->andWhere('status<1');
    }
    return $ret;
  }

  /**
   * could include not activated yet
   * @param type $expired
   * @return type
   */
  public function expired($expired = true) {
    if ($expired) {
      $ret = $this->andWhere('expires_date < now()');
    } else {
      $ret = $this->andWhere('expires_date > now() or expires_date is null or expires_date <="' . self::$startEpoch . '"');
    }
    return $ret;
  }
  
}
