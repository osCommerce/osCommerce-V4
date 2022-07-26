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


use common\api\models\AR\CatalogProperty\PropertyDescription;
use yii\db\Expression;

class CatalogProperty extends EPMap
{
    protected $childCollections = [
        'descriptions' => [],
    ];

    public static function tableName()
    {
        return TABLE_PROPERTIES;
    }

    public static function primaryKey()
    {
        return ['properties_id'];
    }

    public function initCollectionByLookupKey_Descriptions($lookupKeys)
    {
        $loadAll = in_array('*',$lookupKeys);
        foreach(PropertyDescription::getAllKeyCodes() as $keyCode=>$lookupPK){
            $this->childCollections['descriptions'][$keyCode] = null;
            if ( is_null($this->properties_id) ) {
                $this->childCollections['descriptions'][$keyCode] = new PropertyDescription($lookupPK);
            }elseif( $loadAll || in_array($keyCode,$lookupKeys) ) {
                if (!isset($this->childCollections['descriptions'][$keyCode])) {
                    $lookupPK['properties_id'] = $this->properties_id;
                    $this->childCollections['descriptions'][$keyCode] = PropertyDescription::findOne($lookupPK);
                    if (!is_object($this->childCollections['descriptions'][$keyCode])) {
                        $this->childCollections['descriptions'][$keyCode] = new PropertyDescription($lookupPK);
                    }
                }
            }
        }

        return $this->childCollections['descriptions'];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if ( $insert ) {
            if ( empty($this->date_added) ) {
                $this->date_added = new Expression("NOW()");
            }
        }else{
            if ( $this->isModified() ) {
                $this->last_modified = new Expression("NOW()");
            }
        }

        return true;
    }
}