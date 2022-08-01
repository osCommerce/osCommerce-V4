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

namespace frontend\components;


class Observer extends \yii\base\Component {
    
    /*
     * registering Observer Events to avoid using controolers 
     */
    public function registerEvents(){

        if (\common\helpers\Acl::checkExtensionAllowed('Promotions')) {
            \common\extensions\Promotions\models\PromotionService::setEventPromoCode();

            \common\extensions\Promotions\models\PromotionService::checkEventPromoCode();
        }
        
        if ($ext = \common\helpers\Acl::checkExtension('SupplierPurchase', 'allowed')){
            if ($ext::allowed()){
                $ext::recalculateTotal();
            }
        }
    }
    
}
