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

namespace common\api\models\AR\Manufacturer;

use common\api\models\AR\EPMap;
use common\helpers\Seo;

class Info extends EPMap
{
    protected $hideFields = [
        'manufacturers_id',
        'languages_id',
    ];

    /**
     * @var EPMap
     */
    public $parentObject;

    public static function tableName()
    {
        return TABLE_MANUFACTURERS_INFO;
    }

    public static function primaryKey()
    {
        return ['manufacturers_id', 'languages_id'];
    }

    public function parentEPMap(EPMap $parentObject)
    {
        $this->parentObject = $parentObject;
        $this->manufacturers_id = $parentObject->manufacturers_id;
    }

    public static function getAllKeyCodes()
    {
        $keyCodes = [];
        foreach (\common\classes\language::get_all() as $lang){
            $keyCode = $lang['code'];
            $keyCodes[$keyCode] = [
                'manufacturers_id' => null,
                'languages_id' => $lang['id'],
            ];
        }
        return $keyCodes;
    }

    public function beforeSave($insert)
    {
        if ( empty($this->manufacturers_seo_name) && is_object($this->parentObject) ) {
            $this->manufacturers_seo_name = Seo::makeSlug($this->parentObject->manufacturers_name);
        }
        return parent::beforeSave($insert);
    }

}