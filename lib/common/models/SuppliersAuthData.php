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

/*
 * suppliers_data entity
 * @suppliers_id
 * @email_address
 * @password
 */
class SuppliersAuthData extends \yii\db\ActiveRecord {
    
    public static function tableName() {
        return '{{%suppliers_auth_data}}';
    }
    
    public function getSupplier(){
        return $this->hasOne(Suppliers::className(), ['suppliers_id' => 'suppliers_id']);
    }
    
}