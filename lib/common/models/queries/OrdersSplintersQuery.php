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

class OrdersSplintersQuery extends ActiveQuery {
  
/**
 *
 * @param int $status splinters_status
 * @param bool $withSubId ['is not', 'splinters_suborder_id', null]
 * @return $this
 */
    public function status($status, $withSubId = false){
        $query = $this->andWhere(['splinters_status' => $status]);
        if ($withSubId){
            $query->andWhere(['is not', 'splinters_suborder_id', null]);
        }
        return $query;
    }
    
    public function type($type){
        return $this->andWhere(['splinters_type' => $type]);
    }
}