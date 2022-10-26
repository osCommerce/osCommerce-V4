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
use common\api\models\AR\Products;
use common\api\models\AR\Products\Attributes\Prices as Attributes_Prices;

class Attributes extends EPMap
{

    protected $hideFields = [
        'products_attributes_id',
        'products_id',
        'product_attributes_one_time',
        'products_attributes_filename',
        'products_attributes_maxdays',
        'products_attributes_maxcount',
    ];

    protected $childCollections = [
        'prices' => [],
    ];

    /**
     * @var Products
     */
    protected $parentObject;

    public function __construct(array $config = [])
    {
        $marketPresent = defined('USE_MARKET_PRICES') && USE_MARKET_PRICES=='True';
        $groupsPresent = \common\helpers\Extensions::isCustomerGroupsAllowed();
        if ( !$marketPresent && !$groupsPresent ) {
            unset($this->childCollections['prices']);
        }

        parent::__construct($config);
    }


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return TABLE_PRODUCTS_ATTRIBUTES;
    }

    /**
     * @inheritdoc
     */
    public static function primaryKey()
    {
        return ['products_attributes_id'];
    }

    public function parentEPMap(EPMap $parentObject)
    {
        $this->products_id = $parentObject->products_id;
        $this->parentObject = $parentObject;
        parent::parentEPMap($parentObject);
    }


    public function matchIndexedValue(EPMap $importedObject)
    {
        if (
            !is_null($importedObject->options_id) && !is_null($this->options_id) && $importedObject->options_id==$this->options_id
            &&
            !is_null($importedObject->options_values_id) && !is_null($this->options_values_id) && $importedObject->options_values_id==$this->options_values_id
        ){
            $this->pendingRemoval = false;
            return true;
        }
        return false;
    }

    public function importArray($data)
    {
        $tools = new \backend\models\EP\Tools();
        if (isset($data['options_name'])) {
            $data['options_id'] = $tools->get_option_by_name($data['options_name']);
        }

        if (isset($data['options_values_name'])) {
            $data['options_values_id'] = $tools->get_option_value_by_name($data['options_id'], $data['options_values_name']);
        }

        return parent::importArray($data);
    }

    public function exportArray(array $fields = [])
    {
        $data = parent::exportArray($fields);

        if (count($fields)==0 || in_array('options_name',$fields) || in_array('options_values_name',$fields) || in_array('is_virtual',$fields)) {
            $tools = \backend\models\EP\Tools::getInstance();

            if (count($fields)==0 || in_array('options_name',$fields)) {
                $data['options_name'] = $tools->get_option_name($this->options_id, \common\classes\language::defaultId() );
            }
            if (count($fields)==0 || in_array('options_values_name',$fields)) {
                $data['options_values_name'] = $tools->get_option_value_name($this->options_values_id, \common\classes\language::defaultId() );
            }
            if (count($fields)==0 || in_array('is_virtual',$fields)) {
                $data['is_virtual'] = $tools->is_option_virtual($this->options_id);
            }
        }
        return $data;
    }

    public function initCollectionByLookupKey_Prices($lookupKeys)
    {
        $loadAll = in_array('*',$lookupKeys);
        foreach(Attributes_Prices::getAllKeyCodes() as $keyCode=>$lookupPK){
            $this->childCollections['prices'][$keyCode] = null;
            if ( is_null($this->products_attributes_id) ) {
                $this->childCollections['prices'][$keyCode] = new Attributes_Prices($lookupPK);
            }elseif( $loadAll || in_array($keyCode,$lookupKeys) ) {
                if (!isset($this->childCollections['prices'][$keyCode])) {
                    $lookupPK['products_attributes_id'] = $this->products_attributes_id;
                    $this->childCollections['prices'][$keyCode] = Attributes_Prices::findOne($lookupPK);
                    if (!is_object($this->childCollections['prices'][$keyCode])) {
                        $this->childCollections['prices'][$keyCode] = new Attributes_Prices($lookupPK);
                    }
                }
            }
        }
        return $this->childCollections['prices'];
    }


}