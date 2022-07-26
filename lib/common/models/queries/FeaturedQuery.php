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
 * This is the ActiveQuery class for [[FeaturedQuery]].
 *
 * @see Specials
 */
class FeaturedQuery extends ActiveQuery {
  use DateRangeTrait;

  public function active($active = true) {
    return $this->andWhere(['status' => $active ? 1 : 0]);
  }
  
}
