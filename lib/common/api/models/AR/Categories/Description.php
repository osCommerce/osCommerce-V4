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

namespace common\api\models\AR\Categories;

use common\api\models\AR\EPMap;
use common\helpers\Seo;

class Description extends EPMap
{

    protected $hideFields = [
        'categories_id',
        'language_id',
        'affiliate_id',
    ];

    public static function tableName()
    {
        return TABLE_CATEGORIES_DESCRIPTION;
    }

    public static function primaryKey()
    {
        return ['categories_id', 'language_id', 'affiliate_id'];
    }

    public function parentEPMap(EPMap $parentObject)
    {
        $this->categories_id = $parentObject->categories_id;
        parent::parentEPMap($parentObject);
    }

    public static function getAllKeyCodes()
    {
        $keyCodes = [];
        foreach (\common\classes\language::get_all() as $lang){
            $keyCode = $lang['code'].'_0';
            $keyCodes[$keyCode] = [
                'categories_id' => null,
                'language_id' => $lang['id'],
                'affiliate_id' => 0,
            ];
        }
        return $keyCodes;
    }

    public function beforeSave($insert)
    {
        if ( empty($this->categories_seo_page_name) ) {
            $this->categories_seo_page_name = Seo::makeSlug($this->categories_name);
            if ( $this->categories_id && $this->categories_seo_page_name) {
                $check_unique_seo_name = tep_db_fetch_array(tep_db_query(
                    "SELECT COUNT(*) AS check_double ".
                    "FROM ".TABLE_CATEGORIES_DESCRIPTION." ".
                    "WHERE categories_id!='".intval($this->categories_id)."' ".
                    " AND categories_seo_page_name='".tep_db_input($this->categories_seo_page_name)."'"
                ));
                if ( $check_unique_seo_name['check_double']>0 ) {
                    $this->categories_seo_page_name .= '-'.intval($this->categories_id);
                }
            }
        }
        return parent::beforeSave($insert);
    }

}