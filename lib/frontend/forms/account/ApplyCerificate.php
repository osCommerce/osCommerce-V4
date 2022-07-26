<?php
namespace frontend\forms\account;

use Yii;

class ApplyCerificate extends \yii\base\Model{
    
    public $gv_redeem_code;
    
    public function rules() {
        return [
            ['gv_redeem_code', "required"],
        ];
    }
    
    public function checkGvCertificate(){
        
        $currencies = \Yii::$container->get('currencies');
        $gv_result = \common\models\Coupons::getCouponByCode($this->gv_redeem_code, true);
        
        if($gv_result && !Yii::$app->user->isGuest){
            
            if ( $gv_result->redeemTrack && ($gv_result->coupon_type == 'G')) {
                return array('error' => true, 'message' => ERROR_NO_INVALID_REDEEM_GV);
            }
            
            if ($gv_result->coupon_type == 'G'){
                
                $new_amount = Yii::$app->user->getIdentity()->increaseCreditAmount($gv_result);
                
                $gv_result->setAttribute('coupon_active', 'N');
                $gv_result->addRedeemTrack(Yii::$app->user->getId())
                        ->update(false);
                
                return array('error' => false, 
                    'message' => ERROR_REDEEMED_AMOUNT . $currencies->format($gv_result->coupon_amount, false, $gv_result->coupon_currency),
                    'new_amount' => $currencies->format($new_amount, false, $gv_result->coupon_currency));
            }
        } else {
            return array('error' => true, 'message' => ERROR_NO_INVALID_REDEEM_GV);
        }
    }
}