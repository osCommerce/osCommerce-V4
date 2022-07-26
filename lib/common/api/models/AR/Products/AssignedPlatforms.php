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

class AssignedPlatforms extends EPMap
{

    protected $hideFields = [
        'products_id',
    ];

    public static function tableName()
    {
        return TABLE_PLATFORMS_PRODUCTS;
    }

    public static function primaryKey()
    {
        return ['products_id', 'platform_id'];
    }

    public function parentEPMap(EPMap $parentObject)
    {
        $this->products_id = $parentObject->products_id;
        parent::parentEPMap($parentObject);
    }

    public function matchIndexedValue(EPMap $importedObject)
    {
        if ( !is_null($importedObject->platform_id) && !is_null($this->platform_id) && $importedObject->platform_id==$this->platform_id ){
            $this->pendingRemoval = false;
            return true;
        }
        return false;
    }

    public function exportArray(array $fields = [])
    {
        $data = parent::exportArray($fields);
        $data['platform_name'] = Tools::getInstance()->getPlatformName($this->platform_id);
        return $data;
    }

    public function importArray($data)
    {
        if (isset($data['platform_name'])) {
            $data['platform_id'] = Tools::getInstance()->getPlatformId($data['platform_name']);
        }
        return parent::importArray($data);
    }

}