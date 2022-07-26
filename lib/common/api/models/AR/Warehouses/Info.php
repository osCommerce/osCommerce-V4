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

namespace common\api\models\AR\Warehouses;


use common\api\models\AR\EPMap;
use yii\db\Expression;

class Info extends EPMap
{

    protected $hideFields = [
        'warehouse_id',
        'time_long',
        'token'
    ];

    protected $parentObject;

    public static function tableName()
    {
        return TABLE_WAREHOUSES_OPEN_HOURS;
    }

    public static function primaryKey()
    {
        return ['warehouse_id'];
    }

    public function parentEPMap(EPMap $parentObject)
    {
        $this->warehouse_id = $parentObject->warehouse_id;
        //echo '<pre>'; var_dump($this->warehouse_id); echo '</pre>';
        parent::parentEPMap($parentObject);
    }

    public function matchIndexedValue(EPMap $importedObject)
    {
        $this->pendingRemoval = false;
        return true;
        //return parent::matchIndexedValue($importedObject);
    }

}