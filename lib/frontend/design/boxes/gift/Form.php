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

namespace frontend\design\boxes\gift;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use frontend\design\Info;

class Form extends Widget
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
        $currency = \Yii::$app->settings->get('currency');
        $messageStack = \Yii::$container->get('message_stack');
        $giftAmount = [];
        $check_product = tep_db_fetch_array(tep_db_query("select products_id from " . TABLE_PRODUCTS . " where products_model = 'VIRTUAL_GIFT_CARD'"));
        $products_id = $check_product['products_id'];
        if ($products_id > 0) {
            if (USE_MARKET_PRICES == 'True') {
                $gift_card_price_query = tep_db_query("select products_price, products_discount_price from " . TABLE_VIRTUAL_GIFT_CARD_PRICES . " where products_id = '" . (int)$products_id . "' and currencies_id = '" . (int)\Yii::$app->settings->get('currency_id') . "' order by products_price");
                while ($gift_card_price = tep_db_fetch_array($gift_card_price_query)) {
                    $giftAmount[$gift_card_price['products_price']] = [
                        'text' => sprintf(TEXT_SELECTOR_GIFT_AMOUNT, $currencies->format($gift_card_price['products_price'], false), $currencies->format($gift_card_price['products_discount_price'], false)),
                        'price' => $currencies->format($gift_card_price['products_price'], false),
                    ];
                }
            } else {
                $gift_card_price_query = tep_db_query("select products_price, products_discount_price from " . TABLE_VIRTUAL_GIFT_CARD_PRICES . " where products_id = '" . (int)$products_id . "' and currencies_id = '" . (int)$currencies->currencies[$currency]['id'] . "' order by products_price");
                while ($gift_card_price = tep_db_fetch_array($gift_card_price_query)) {
                    $giftAmount[$gift_card_price['products_price']] = [
                        'text' => sprintf(TEXT_SELECTOR_GIFT_AMOUNT, $currencies->format($gift_card_price['products_price']), $currencies->format($gift_card_price['products_discount_price'])),
                        'price' => $currencies->format($gift_card_price['products_price'])
                    ];
                }
            }
        }

        $theme_name = Yii::$app->get('theme_name', '');
        if (!$theme_name && defined("THEME_NAME")) {
            $theme_name = THEME_NAME;
        }

        $cards = \common\models\ThemesSettings::find()
            ->select(['setting_value'])
            ->where([
                'theme_name' => $theme_name,
                'setting_group' => 'added_page',
                'setting_name' => 'gift_card',
            ])
            ->orderBy('setting_value')
            ->asArray()
            ->all();

        $cardDesigns = ['gift_card' => MAIN_GIFT_CARD];
        foreach ($cards as $card) {
            $cardDesigns[\common\classes\design::pageName($card['setting_value'])] = $card['setting_value'];
        }        
        $products_id = Yii::$app->request->get('products_id', 0);
        
        $sendType = 0;
        
        if ($products_id){
            $modelQuery = \common\models\VirtualGiftCardBasket::find()->where(['virtual_gift_card_basket_id' => preg_replace("/\d+\{0\}/","", $products_id), 'virtual_gift_card_code' => '']);
            if (!Yii::$app->user->isGuest){
                $modelQuery->andWhere(['customers_id' => Yii::$app->user->getId()]);
            } else {
                $modelQuery->andWhere(['customers_id' => 0, 'session_id' => Yii::$app->getSession()->get('gift_handler')]);
            }
            $gift = $modelQuery->one();
            if ($gift){
                if (strtotime($gift->send_card_date) > 0){
                    $sendType = 1;
                    $gift->send_card_date = \common\helpers\Date::formatCalendarDate($gift->send_card_date);
                } else {
                    $gift->send_card_date = '';
                }
            }
        } else {
            $gift = new \common\models\VirtualGiftCardBasket();
            if (Yii::$app->request->isPost){
                $gift->products_price = $_POST['gift_card_price'];
                $gift->virtual_gift_card_recipients_name = $_POST['virtual_gift_card_recipients_name'];
                $gift->virtual_gift_card_recipients_email = $_POST['virtual_gift_card_recipients_email'];                
                $gift->virtual_gift_card_message = $_POST['virtual_gift_card_message'];
                $gift->virtual_gift_card_senders_name = $_POST['virtual_gift_card_senders_name'];
                $gift->gift_card_design = $_POST['gift_card_design'];
            }
        }        
        
        $url = $gift->virtual_gift_card_basket_id? ['catalog/gift-card', 'action' => 'add_gift_card', 'products_id' => $products_id] :['catalog/gift-card', 'action' => 'add_gift_card'];
        $messages = '';        
        if ($messageStack->size('virtual_gift_card')){
            $messages = $messageStack->output('virtual_gift_card');
        }

        return IncludeTpl::widget(['file' => 'boxes/gift/form.tpl', 'params' => [
            'params' => $this->params,
            'giftAmount' => $giftAmount,
            'cardDesigns' => $cardDesigns,
            'id' => $this->id,
            'theme_name' => $theme_name,
            'gift' => $gift,
            'url' => $url,
            'sendType' => $sendType,
            'messages' => $messages
        ]]);
    }
}