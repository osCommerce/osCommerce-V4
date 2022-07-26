<?php

/*
 * This file is part of osCommerce ecommerce platform.
 * osCommerce the ecommerce
 * 
 * @link https://www.oscommerce.com
 * @copyright Copyright (c) 2005 Holbi Group Ltd
 * 
 * Released under the GNU General Public License
 * For the full copyright and license information, please view the LICENSE.TXT file that was distributed with this source code.
 */

namespace common\api\models\AR\Products;

use yii;
use yii\db\Query;
use yii\db\Expression;
use common\api\models\AR\EPMap;

class SetProducts extends EPMap
{

    protected $hideFields = [
//        'product_id',
//        'sets_id',
    ];

    protected $parentObject;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
    }

    public static function tableName()
    {
        return TABLE_SETS_PRODUCTS;
    }

    public static function primaryKey()
    {
        return ['product_id', 'sets_id'];
    }
    
    public function parentEPMap(EPMap $parentObject)
    {
        $this->sets_id = $parentObject->products_id;
        $this->parentObject = $parentObject;

        parent::parentEPMap($parentObject);
    }

}