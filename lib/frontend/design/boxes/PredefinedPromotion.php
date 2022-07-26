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

namespace frontend\design\boxes;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;

class PredefinedPromotion extends Widget {

    public $file;
    public $params;
    public $settings;

    public function init() {
        parent::init();
    }

    public function run() {
        if (!\common\helpers\Acl::checkExtensionAllowed('Promotions')) {
            return '';
        }

        $params = Yii::$app->request->get();
    
        if (!$params['products_id']){
            return '';
        }
        
        if (isset($this->settings[0]['promo_id']) && (int) $this->settings[0]['promo_id'] > 0) {
            $promoId = (int) $this->settings[0]['promo_id'];
            $promo = \common\models\promotions\Promotions::findOne(['promo_id' => $promoId]);
            if ($promo){
                if ($promo->promo_class == 'multidiscount'){
                    return product\Promotions::widget(['params' => ['preview' => true]]);
                }
                $service = new \common\models\promotions\PromotionService();
                $promoObj = $service($promo->promo_class);
                if (method_exists($promoObj, 'getPromotionToProduct')){
                    $promoObj->loadSettings(['platform_id' => (int) \common\classes\platform::currentId(), 'promo_id' => $promo->promo_id]);
                    return $promoObj->getPromotionToProduct($promo, (int)$params['products_id']);
                }
            }
        }
    }
}
