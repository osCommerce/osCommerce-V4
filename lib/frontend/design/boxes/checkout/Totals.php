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

namespace frontend\design\boxes\checkout;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;

class Totals extends Widget
{

    public $file;
    public $params;
    public $settings;
    public $manager;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        if (is_object($this->manager)){
            $this->params['manager'] = $this->manager;
        }

        $result = $this->params['manager']->getTotalOutput(true, 'TEXT_CHECKOUT');

        foreach ($result as $key => $value) {
            if ($value['code'] == 'ot_coupon') {
                $result[$key]['coupon'] = '';
                $ex = [];
                preg_match("/\((.*)\):$/", $value['title'], $ex);
                if (is_array($ex) && isset($ex[1])) {
                    $result[$key]['coupon'] = $ex[1];
                }
            }
        }

        $this->params['order_total_output'] = $result;

        $this->params['coupon_remove_action'] = Yii::$app->urlManager->createUrl(['shopping-cart', 'action' => 'remove_cart_total']);
        if ( is_object($this->params['manager']) && $this->params['manager']->hasCart() ){
            if ( $this->params['manager']->getCart() instanceof \common\extensions\Quotations\QuoteCart ){
                $this->params['coupon_remove_action'] = Yii::$app->urlManager->createUrl(['quote-cart', 'action' => 'remove_cart_total']);
            }
        }

        return IncludeTpl::widget(['file' => 'boxes/checkout/totals.tpl', 'params' => $this->params]);
    }
}