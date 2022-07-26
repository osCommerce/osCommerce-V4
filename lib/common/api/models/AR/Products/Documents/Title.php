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

namespace common\api\models\AR\Products\Documents;

use common\api\models\AR\EPMap;

class Title extends EPMap
{

    protected $hideFields = [
        'products_documents_id',
        'language_id',
    ];

    public static function tableName()
    {
        return TABLE_PRODUCTS_DOCUMENTS_TITLES;
    }

    public static function primaryKey()
    {
        return ['products_documents_id', 'language_id',];
    }

    public static function getAllKeyCodes()
    {
        $keyCodes = [];
        foreach (\common\classes\language::get_all() as $lang){
            $keyCode = $lang['code'];
            $keyCodes[$keyCode] = [
                'products_documents_id' => null,
                'language_id' => $lang['id'],
            ];
        }
        return $keyCodes;
    }

    public function parentEPMap(EPMap $parentObject)
    {
        $this->products_documents_id = $parentObject->products_documents_id;
        parent::parentEPMap($parentObject);
    }


}