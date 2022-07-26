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

namespace frontend\forms\promotions;
 
use Yii;
use yii\base\Model;

class PromoCodeForm extends Model {
    
    public $promo_code;
    public $promo_action;
    
    public function rules() {
        return [
            [['promo_action', 'promo_code'], 'required'],
            ['promo_action', 'validPromoAction']
        ];
    }
    
    public function validPromoAction($attribute, $params, $validator){
        if ($this->promo_action != 'apply-promo-code') {
            $this->addError($attribute, 'error');
        }
    }
    
}