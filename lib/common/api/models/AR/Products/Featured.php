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

namespace common\api\models\AR\Products;


use common\api\models\AR\EPMap;

class Featured extends EPMap
{

    public static function tableName()
    {
        return 'featured';
    }

    public static function primaryKey()
    {
        return ['featured_id'];
    }

    public function parentEPMap(EPMap $parentObject)
    {
        $this->products_id = $parentObject->products_id;
        parent::parentEPMap($parentObject);
    }

    public function matchIndexedValue(EPMap $importedObject)
    {
        $this->pendingRemoval = false;
        return true;
    }

    public function beforeSave($insert)
    {
        if ( $insert && empty($this->featured_date_added)){
            $this->featured_date_added = new \yii\db\Expression('NOW()');
        }
        return parent::beforeSave($insert);
    }

}