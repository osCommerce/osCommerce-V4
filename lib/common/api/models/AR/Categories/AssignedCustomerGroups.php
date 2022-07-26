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


use backend\models\EP\Tools;
use common\api\models\AR\EPMap;

class AssignedCustomerGroups extends EPMap
{
    protected $hideFields = [
        'categories_id',
    ];

    public static function tableName()
    {
        return 'groups_categories';
    }

    public static function primaryKey()
    {
        return ['categories_id', 'groups_id'];
    }

    public function parentEPMap(EPMap $parentObject)
    {
        $this->categories_id = $parentObject->categories_id;
        parent::parentEPMap($parentObject);
    }


    public function matchIndexedValue(EPMap $importedObject)
    {
        if ( !is_null($importedObject->groups_id) && !is_null($this->groups_id) && $importedObject->groups_id==$this->groups_id ){
            $this->pendingRemoval = false;
            return true;
        }
        return false;
    }

    public function exportArray(array $fields = [])
    {
        $data = parent::exportArray($fields);
        $data['groups_name'] = Tools::getInstance()->getCustomerGroupName($this->groups_id);
        return $data;
    }

    public function importArray($data)
    {
        if (isset($data['groups_name'])) {
            $data['groups_id'] = Tools::getInstance()->getCustomerGroupId($data['groups_name']);
        }
        return parent::importArray($data);
    }
}