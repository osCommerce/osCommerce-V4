<?php
namespace common\modules\orderPayment\lib;

trait PaypalPartnerTraitFraudNet {

/**
 * random 32 string
 * @return string
 */
    public function getPayPalOrderAPIRequestId() {
        /*global $paypal_partner_requestid;

        if (!tep_session_is_registered('paypal_partner_requestid') || empty($paypal_partner_requestid)) {
        }
        if (!tep_session_is_registered('paypal_partner_requestid') ) {
            tep_session_register('paypal_partner_requestid');
        }*/
        $paypal_partner_requestid = \common\helpers\Password::create_random_value(32);
        return $paypal_partner_requestid;
    }

    public static function fraudnetInit() {

        if (self::getFraudnetSI()) {

            $sandbox = self::getMode()=='Sandbox'?'true':'false';
            $s = self::getFraudNetRSCId(); // according manual - getFraudNetFlowId(); according SDD - always FRAUDNET_SI
            //$s = self::getFraudNetFlowId(); // according manual - getFraudNetFlowId(); according SDD - always FRAUDNET_SI
            $f = self::getFraudNetSessionId();

            \Yii::$app->getView()->on(\common\components\View::EVENT_BEGIN_BODY, function () use ($s, $f, $sandbox) {
                echo '<script type="application/json" fncls="fnparams-dede7cc5-15fd-4c75-a9f4-36c430ee3a99">
{
"f": "' . $f . '",
"s": "' . $s . '",
"sandbox": ' . $sandbox . '
}
</script>
<script type="text/javascript" src="https://c.paypal.com/da/r/fb.js"></script>
';
            });
            \Yii::$app->getView()->on(\common\components\View::EVENT_END_BODY, function () use ($s, $f)  {
                echo '<noscript><img src="https://c.paypal.com/v1/r/d/b/ns?f=' . $f . '&s=' . $s . '&js=0&r=1"</noscript>';
            });

        }
    }

    public static function resetFraudNetSessionId() {
        global $paypal_partner_fnsid;
        $paypal_partner_fnsid = '';
    }

    public static function getFraudNetSessionId() {
        global $paypal_partner_fnsid;

        if (!tep_session_is_registered('paypal_partner_fnsid') || empty($paypal_partner_fnsid)) {
            $paypal_partner_fnsid = \common\helpers\Password::create_random_value(28);
        }
        if (!tep_session_is_registered('paypal_partner_fnsid') ) {
            tep_session_register('paypal_partner_fnsid');
        }
        return $paypal_partner_fnsid;
    }

    public static function getFraudNetRSCId() {
        $ret = self::getFraudnetSI();
        /*if (defined('MODULE_PAYMENT_PAYPAL_PARTNER_FRAUDNET_SI') && !empty(MODULE_PAYMENT_PAYPAL_PARTNER_FRAUDNET_SI)) {
            $ret = MODULE_PAYMENT_PAYPAL_PARTNER_FRAUDNET_SI;
        }*/
        return $ret;
    }

    public static function getFraudnetSI() {
        $partnerId = self::getPartnerId(false); // depends on sandbox/live mode
        $seller = PaypalPartner\models\SellerInfo::find()
            ->where(['platform_id' => \common\classes\platform::currentId(), 'partner_id' => $partnerId, 'status' => 1])
            //->cache(20, (new \yii\caching\TagDependency(['tags' => 'seller-'. $platform_id . '-' . $partnerId])))
            ->one();

        $host = parse_url(\Yii::$app->urlManager->createAbsoluteUrl(['/']));
        return  substr($seller->payer_id . '_' . str_replace('www.', '', $host['host']), 0, 32);

    }

    public static function getFraudNetFlowId() {

        $page_id = 'category-page';
        switch (\Yii::$app->controller->id) {
            case 'checkout':
            case 'payer':
                $page_id = 'checkout-page';
                break;
            case 'index':
                if (\Yii::$app->controller->action->id == 'index') {
                    $page_id = 'home-page';
                }
                break;
            case 'shopping-cart':
                $page_id = 'cart-page';
                break;
            case 'catalog':
                switch (\Yii::$app->controller->action->id) {
                    case 'product':
                        $page_id = 'product-detail-page';
                        break;
                    case 'all-products':
                        $page_id = 'search-result-page';
                        break;
                }
                break;
        }

        $partnerId = self::getPartnerId(false); // depends on sandbox/live mode
        $seller = lib\PaypalPartner\models\SellerInfo::find()
            ->where(['platform_id' => \common\classes\platform::currentId(), 'partner_id' => $partnerId, 'status' => 1])
            //->cache(20, (new \yii\caching\TagDependency(['tags' => 'seller-'. $platform_id . '-' . $partnerId])))
            ->one();
        return  substr($seller->payer_id . '_' . $page_id, 0, 32);
    }

}