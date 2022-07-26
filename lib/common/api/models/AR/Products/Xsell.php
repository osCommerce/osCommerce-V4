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

use backend\models\EP\Tools;
use common\api\models\AR\EPMap;

class Xsell extends EPMap
{

    protected $hideFields = [
        'ID',
        'products_id',
    ];

    public static function tableName()
    {
        return TABLE_PRODUCTS_XSELL;
    }

    public static function primaryKey()
    {
        return ['ID'];
    }

    public function parentEPMap(EPMap $parentObject)
    {
        $this->products_id = $parentObject->products_id;
        parent::parentEPMap($parentObject);
    }

    public function matchIndexedValue(EPMap $importedObject)
    {
        if (
            !is_null($importedObject->xsell_id) && !is_null($this->xsell_id) && $importedObject->xsell_id==$this->xsell_id
            &&
            !is_null($importedObject->xsell_type_id) && !is_null($this->xsell_type_id) && $importedObject->xsell_type_id==$this->xsell_type_id
        ){
            $this->pendingRemoval = false;
            return true;
        }
        return false;
    }

    public function exportArray(array $fields = [])
    {
        $tools = new Tools();
        $data = parent::exportArray($fields);
        $data['xsell_type_name'] = $tools->getXSellTypeName($this->xsell_type_id);
        return $data;
    }

    public function importArray($data)
    {
        if (isset($data['xsell_type_name'])) {
            $tools = new Tools();
            $data['xsell_type_id'] = $tools->getXSellTypeId($data['xsell_type_name']);
        }
        return parent::importArray($data);
    }


}