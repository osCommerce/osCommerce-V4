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

namespace common\api\models\AR;


use yii\db\Expression;

class Supplier extends EPMap
{

    public static function primaryKey()
    {
        return ['suppliers_id'];
    }


    public static function tableName()
    {
        return 'suppliers';
    }

    public function beforeSave($insert)
    {
        if ( $insert ) {
            if ( empty($this->date_added) ) {
                $this->date_added = new Expression("NOW()");
            }
        }else{
            if ( $this->isModified() ) {
                $this->last_modified = new Expression("NOW()");
            }
        }
        return parent::beforeSave($insert);
    }

}