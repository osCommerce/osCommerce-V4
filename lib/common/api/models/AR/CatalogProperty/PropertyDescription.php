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

namespace common\api\models\AR\CatalogProperty;

use common\api\models\AR\EPMap;

class PropertyDescription extends EPMap
{

    public static function tableName()
    {
        return TABLE_PROPERTIES_DESCRIPTION;
    }

    public static function primaryKey()
    {
        return ['properties_id', 'language_id'];
    }

    public function parentEPMap(EPMap $parentObject)
    {
        $this->properties_id = $parentObject->properties_id;
        parent::parentEPMap($parentObject);
    }

    public static function getAllKeyCodes()
    {
        $keyCodes = [];
        foreach (\common\classes\language::get_all() as $lang){
            $keyCode = $lang['code'].'';
            $keyCodes[$keyCode] = [
                'properties_id' => null,
                'language_id' => $lang['id'],
            ];
        }
        return $keyCodes;
    }
}