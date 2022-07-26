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

namespace frontend\design\boxes\account;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;

class SpendBalance extends Widget
{

    public $file;
    public $params;
    public $settings;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        $currencies = \Yii::$container->get('currencies');
        $customers_id = (int)Yii::$app->user->getId();

        $coupons = [];
        
        $spendArray = [];
        $redeem = \common\models\CouponRedeemTrack::find()
                ->where(['customer_id' => $customers_id])
                ->andWhere(['spend_flag' => 1])
                ->asArray()
                ->all();
        foreach ($redeem as $record) {
            if (isset($spendArray[$record['coupon_id']])) {
                $spendArray[$record['coupon_id']] += $record['spend_amount'];
            } else {
                $spendArray[$record['coupon_id']] = $record['spend_amount'];
            }
        }
        
        foreach ($spendArray as $coupon_id => $spend_amount) {
            $result = \common\models\Coupons::find()->active()->andWhere(['coupon_id' => (int) $coupon_id])->one();
            if ($result instanceof \common\models\Coupons) {
                $coupon_amount = $result->coupon_amount - $spend_amount;
                if ($coupon_amount < 0) {
                    $coupon_amount = 0;
                }
                if ($coupon_amount > 0) {
                    $coupons[$result->coupon_code] = $currencies->format($coupon_amount * $currencies->get_market_price_rate($result->coupon_currency, DEFAULT_CURRENCY));
                }
            }
        }
        
        if (count($coupons) == 0) {
            return '';
        }
        
        return IncludeTpl::widget(['file' => 'boxes/account/spend-balance.tpl', 'params' => [
            'coupons' => $coupons,

        ]]);
    }
}