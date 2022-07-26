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
use yii\db\Expression;

class ProductsFeatured extends ActiveRecord
{
    /**
     * set table name
     * @return string
     */
    public static function tableName()
    {
        return 'featured';
    }

    public function beforeSave($insert)
    {
        if ( $insert ) {
            if ( empty($this->featured_date_added) ) {
                $this->featured_date_added = new Expression('NOW()');
            }
        }
        return parent::beforeSave($insert);
    }

}