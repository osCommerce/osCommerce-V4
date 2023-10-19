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

use yii;
use yii\db\Query;
use yii\db\Expression;
use common\api\models\AR\EPMap;

class SupplierProduct extends EPMap
{
    
    protected $hideFields = [
    ];
    
    protected $parentObject;
        
    public function __construct(array $config = [])
    {        
        parent::__construct($config);
    }

    public static function tableName()
    {
        return 'suppliers_products';
    }

    public static function primaryKey()
    {
        return ['products_id', 'uprid', 'suppliers_id'];
    } 
    
    public function beforeSave($insert)
    {
        if (is_null($this->status)) $this->status = 1;
        return parent::beforeSave($insert);
    }
    
    public function parentEPMap(EPMap $parentObject)
    {
        $this->products_id = $parentObject->products_id;
//        if (is_subclass_of($parentObject, 'backend\models\EP\Provider\Inventory')){
        if (is_subclass_of($parentObject, 'common\extensions\Inventory\EP\Providers\Inventory')){
            $this->uprid = $parentObject->products_id;
        } else {
            $this->uprid = $parentObject->products_id;
        }
        $this->parentObject = $parentObject;
        
        parent::parentEPMap($parentObject);
    }
    
}