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

class Tokens extends Widget
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
        \common\helpers\Translation::init('account/tokens');

        if (defined($this->settings[0]['text'])) {
            $text = constant($this->settings[0]['text']);
        }
        if (!$text) {
            $text = $this->settings[0]['link'];
            if (!$this->settings[0]['link']) {
                $text = SMALL_IMAGE_BUTTON_EDIT;
            }
        }
        $page = \common\classes\design::pageName($this->settings[0]['link']);

        $manager = \common\services\OrderManager::loadManager();
        $paymentModules = $manager->getPaymentCollection();

        $customer = Yii::$app->user->getIdentity();
        $tokens = \common\models\PaymentTokens::find()->andWhere(['customers_id' => $customer->getId()])->orderBy('payment_class')->all();
        $gateways = $rows = [];
        $prevPayment = '';

        foreach($tokens as $token){
          $t = $token->toArray();
          if ($paymentModules->isPaymentEnabled($t['payment_class'])){
            if (!isset($gateways[$t['payment_class']])) {
              $m = $paymentModules->get($t['payment_class']);
              $gateways[$t['payment_class']]['title'] = (!empty($m->public_title)?$m->public_title:$m->title);
            }
            if ($prevPayment == '' ) {
              $prevPayment  = $t['payment_class'];
            }
            if ($prevPayment != $t['payment_class'] ) {
              $gateways[$prevPayment]['tokens'] = $rows;
              $prevPayment  = $t['payment_class'];
              $rows = [];
            }
            
            if ($page) {
                $t['link_edit'] = Yii::$app->urlManager->createUrl(['account/payment-token-rename', 'page_name' => $page, 'edit' => $t['payment_tokens_id']]);
            } else {
                $t['link_edit'] = Yii::$app->urlManager->createUrl(['account/payment-token-rename', 'edit' => $t['payment_tokens_id']]);
            }

            $t['link_delete'] = Yii::$app->urlManager->createUrl([
                'account/payment-token-delete',
                'token' => $t['token'],
                'id' => $t['payment_tokens_id'],
                'class' => $t['payment_class'],
            ]);
            $t['id'] = $t['payment_tokens_id'];
            $rows[] = $t;

          }
        }
        if ($prevPayment != '' ) {
          $gateways[$prevPayment]['tokens'] = $rows;
        }
//        echo "<PRE>" . print_r($gateways, 1) . "</PRE>";

        return IncludeTpl::widget(['file' => 'account/tokens.tpl', 'params' => [
            'gateways_array' => $gateways,
            'settings' => $this->settings,
            'id' => $this->id,
        ]]); 
    }
}