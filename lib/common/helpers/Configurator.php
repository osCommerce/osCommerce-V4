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

namespace common\helpers;

use yii\helpers\ArrayHelper;
use common\helpers\Product;
use common\helpers\Inventory as InventoryHelper;
use common\extensions\ProductDesigner\models as ProductDesignerORM;

class Configurator {
    use SqlTrait;

// moved to extensions\ProductsConfigurator\helpers\Configurator::*
//    public static function get_pctemplates() {
//    public static function pctemplates_description($pctemplates_id, $language_id = '') {
//    public static function getDetails($params, $attributes_details = array()) {
//    public static function elements_name($elements_id, $language_id = '') {
    
    /**
     * build select options to product designer field
     * @return array
     */
    public static function get_product_designer_templates() {
        $pctemplates_array = array(array('id' => '0', 'text' => TEXT_NONE));
        
        $aProductDesignerTemplates = ProductDesignerORM\ProductDesignerTemplate::find()->all();
        
        foreach($aProductDesignerTemplates as $aProductDesignerTemplate)
        {
            $pctemplates_array[] = [
                'id' => $aProductDesignerTemplate->id,
                'text' => $aProductDesignerTemplate->name
            ];
        }

        return $pctemplates_array;
    }

    public static function get_products_price_configurator($products_id, $qty = 1) {
        $configuratorInstance = \common\models\Product\ConfiguratorPrice::getInstance($products_id);
        return $configuratorInstance->getConfiguratorPrice([
            'qty' => $qty,
        ]);
    }

}
