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
use common\helpers\Seo;

class Description extends EPMap
{

    /**
     * @var EPMap
     */
    protected $parentObject;

    protected $hideFields = [
        'products_id',
        'language_id',
        'platform_id',
        'department_id',
        'products_name_soundex',
        'products_description_soundex',
    ];

    public static function getAllKeyCodes()
    {
        $keyCodes = [];
        $platforms = \common\models\Platforms::getPlatformsByType("non-virtual")->all();
        foreach($platforms as $platform){
            foreach (\common\classes\language::get_all() as $lang){
                $keyCode = $lang['code'].'_'.$platform->platform_id;
                $keyCodes[$keyCode] = [
                    'products_id' => null,
                    'language_id' => $lang['id'],
                    'platform_id' => $platform->platform_id,
                    'department_id' => 0,
                ];
            }
        }
        if (defined('SUPERADMIN_ENABLED') && SUPERADMIN_ENABLED && \Yii::$app->has('department')){
            $active_department = \Yii::$app->get('department')->getActiveDepartmentId();
            foreach (\common\classes\department::getCatalogAssignList() as $department) {
                if ($department['id'] != $active_department) continue;
                $keyCode = $lang['code'].'_0_'.$department['id'];
                $keyCodes[$keyCode] = [
                    'products_id' => null,
                    'language_id' => $lang['id'],
                    'platform_id' => \common\classes\platform::defaultId(),
                    'department_id' => $department['id'],
                ];
            }
        }

        return $keyCodes;
    }

    public static function tableName()
    {
        return TABLE_PRODUCTS_DESCRIPTION;
    }

    public static function primaryKey()
    {
        return ['products_id', 'language_id', 'platform_id'];
    }

    public function parentEPMap(EPMap $parentObject)
    {
        $this->products_id = $parentObject->products_id;
        $this->parentObject = $parentObject;
    }

    public function beforeSave($insert)
    {

        $this->products_seo_page_name = \common\helpers\Seo::makeProductSlug($this, $this->parentObject);

        return parent::beforeSave($insert);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ( isset($changedAttributes['products_seo_page_name']) ) {
            if ($ext = \common\helpers\Acl::checkExtensionAllowed('SeoRedirectsNamed', 'allowed')) {
                $ext::trackProductLinks($this->products_id, $this->language_id, $this->platform_id,
                    ['products_seo_page_name'=>$this->products_seo_page_name],
                    ['products_seo_page_name'=>$changedAttributes['products_seo_page_name']]
                );
            }
        }
    }


}