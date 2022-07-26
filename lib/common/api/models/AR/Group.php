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

use yii;
use yii\db\Query;
use yii\db\Expression;

class Group extends EPMap
{

    protected $hideFields = [
        'image_active',
        'image_inactive',
    ];

    protected $childCollections = [
    ];

    protected $indexedCollections = [
    ];

    public static function tableName()
    {
        return TABLE_GROUPS;
    }

    public static function primaryKey()
    {
        return ['groups_id'];
    }

    public function beforeSave($insert)
    {
        if ( $insert ) {
//            Yii::$app->getDb()->createCommand("alter table " . self::tableName() . " change groups_id groups_id int(11)")->query();
            if ( empty($this->date_added) ) {
                $this->date_added = new Expression("NOW()");
            }
        }
        return parent::beforeSave($insert);
    }

    public function afterSave($insert, $changedAttributes) {
  //      Yii::$app->getDb()->createCommand("alter table " . self::tableName() . " change groups_id groups_id int(11) auto_increment")->query();
        parent::afterSave($insert, $changedAttributes);
    }

}