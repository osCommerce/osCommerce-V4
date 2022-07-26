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

class GiftCards extends Widget
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
        $customer = Yii::$app->user->getIdentity();
        $currencies = \Yii::$container->get('currencies');

        $giftCards = \common\models\VirtualGiftCardInfo::find()->where([
            'customers_id' => $customer->customers_id
        ])->orderBy('virtual_gift_card_info_id desc')->asArray()->all();

        if (!is_array($giftCards) || count($giftCards) < 1) {
            return \frontend\design\Info::hideBox($this->id, $this->settings[0]['hide_parents']);
        }

        foreach ($giftCards as $key => $giftCard) {
            $giftCards[$key]['pdf'] = Yii::$app->urlManager->createUrl(['account/gift-card-pdf', 'gift_card_id' => $giftCard['virtual_gift_card_info_id']]);
            $giftCards[$key]['price'] = $currencies->display_gift_card_price($giftCard['products_price'], \common\helpers\Tax::get_tax_rate($giftCard['products_tax_class_id']), $giftCard['currency_code']);
        }

        return IncludeTpl::widget(['file' => 'boxes/account/gift-cards.tpl', 'params' => [
            'settings' => $this->settings,
            'id' => $this->id,
            'giftCards' => $giftCards,
        ]]);
    }
}