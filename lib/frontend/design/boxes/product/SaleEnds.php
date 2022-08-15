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

namespace frontend\design\boxes\product;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;

class SaleEnds extends Widget
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
        $params = Yii::$app->request->get();

        if (!$params['products_id']) {
            return '';
        }

        $product = \common\models\Specials::find()
                ->select('expires_date')
                ->where(['products_id' => (int)$params['products_id'], 'status' => 1])->asArray()->one();

        if (!$product && \common\helpers\Acl::checkExtensionAllowed('Promotions')) {
            $product['expires_date'] = \common\extensions\Promotions\helpers\Salemaker::getFirstExpiringPromoTo($params['products_id']);
            if (!$product['expires_date']){
                return '';
            }
        }
        
        $interval = \common\helpers\Date::getLeftIntervalTo($product['expires_date']);
        
        if (!$interval || $interval->invert){
            return '';
        }
        
        return IncludeTpl::widget(['file' => 'boxes/product/sale-ends.tpl', 'params' => [
            'expiresDate' => \common\helpers\Date::date_short($product['expires_date']),
            'interval' => $interval,
            'id'=> $this->id
        ]]);
    }
}