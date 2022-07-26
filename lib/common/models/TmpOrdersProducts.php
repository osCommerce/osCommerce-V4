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

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

class TmpOrdersProducts extends ActiveRecord
{
    /**
     * set table name
     * @return string
     */
    public static function tableName()
    {
        return 'tmp_orders_products';
    }
    
    public function beforeDelete() {
        if ($this->orders_products_id){
            TmpOrdersProductsAttributes::deleteAll(['orders_products_id' => $this->orders_products_id]);
            TmpOrdersProductsDownload::deleteAll(['orders_products_id' => $this->orders_products_id]);
        }
        return parent::beforeDelete();
    }

}
