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
use yii\helpers\ArrayHelper;

class AssignedCategories extends EPMap
{

    protected $hideFields = [
        'products_id',
    ];

    public static function tableName()
    {
        return TABLE_PRODUCTS_TO_CATEGORIES;
    }

    public static function primaryKey()
    {
        return ['products_id', 'categories_id'];
    }

    public function exportArray(array $fields = [])
    {
        $data = parent::exportArray($fields);
        if (count($fields)==0 || in_array('categories_path', $fields) || in_array('categories_path_array',$fields)) {
            $categoriesArr = \common\helpers\Categories::generate_category_path($this->categories_id);
            if (count($fields)==0 || in_array('categories_path', $fields)) {
                $data['categories_path'] = implode(';', ArrayHelper::getColumn($categoriesArr[0], 'text'));
            }
            if (count($fields)==0 || in_array('categories_path_array',$fields)) {
                $data['categories_path_array'] = $categoriesArr[0];
            }
        }
        return $data;
    }

    public function importArray($data)
    {
        if (isset($data['categories_path'])) {
            $tools = new \backend\models\EP\Tools();
            $data['categories_id'] = $tools->tep_get_categories_by_name($data['categories_path']);
        }

        return parent::importArray($data);
    }

    public function parentEPMap(EPMap $parentObject)
    {
        $this->products_id = $parentObject->products_id;
        parent::parentEPMap($parentObject);
    }


    public function matchIndexedValue(EPMap $importedObject)
    {
        if ( !is_null($importedObject->categories_id) && !is_null($this->categories_id) && $importedObject->categories_id==$this->categories_id ){
            $this->pendingRemoval = false;
            return true;
        }
        return false;
    }

}
