<?php
/**
 * Transactional Midle Ware for Paypal modules
 * This file is part of osCommerce ecommerce platform.
 * osCommerce the ecommerce
 *
 * @link https://www.oscommerce.com
 * @copyright Copyright (c) 2000-2022 osCommerce LTD
 *
 * Released under the GNU General Public License
 * For the full copyright and license information, please view the LICENSE.TXT file that was distributed with this source code.
 */

namespace common\modules\orderPayment\lib\PaypalPartner;

class ExtraConfig extends \yii\base\Widget {

    /** @prop models\SellerInfo $seller */
    public $seller;
    public $address;
    /** @prop \common\modules\orderPayment\paypal_partner $module */
    public $module;
    public $url;
    public $errors;
    public $messages;
    public $mode = 'form';

    public function init() {

    }

    public function run() {

        $ret = '';
        $next = \Yii::$app->request->get('ppp_next', 0);
        $platform_id = (int) \Yii::$app->request->get('platform_id', 0);
        $loginLink = \Yii::$app->urlManager->createAbsoluteUrl(['callback/webhooks.payment.' . $this->module->code, 'action' => 'processOnBoard', 'ppexists' => 1, 'platform_id' => $platform_id], null, true);
        $boardLink = \Yii::$app->urlManager->createAbsoluteUrl(['callback/webhooks.payment.' . $this->module->code, 'action' => 'processOnBoard', 'platform_id' => $platform_id, 'seller_type' => ($next == 3 ? 'i' : 'b')], null, true);
        $checkBoardLink = \Yii::$app->urlManager->createAbsoluteUrl(['callback/webhooks.payment.' . $this->module->code, 'action' => 'checkOnBoarded', 'result' => 'show', 'platform_id' => $platform_id], null, true);

        //$unBoardLink = tep_catalog_href_link('callback/webhooks.payment.' . $this->module->code, http_build_query(['action'=>'deleteSeller', 'platform_id' => $platform_id]));
        $unBoardLink = \Yii::$app->urlManager->createAbsoluteUrl([
          'callback/webhooks.payment.' . $this->module->code,
          'action' => 'deleteSeller',
          'platform_id' => $platform_id,
          'ck' => $this->seller->tracking_id
            ], null, true);

        $base_url = $cor = false;

        $platform = \common\models\Platforms::findOne($platform_id);
        $platform = ($platform ? $platform->toArray() : false);
        if ($platform && !empty($platform['platform_url'])) {
            $ssl = true; //nice2have current SSL status (live systems - all should be ssl on
            $base_url = ($platform['ssl_enabled'] == 2 || ($ssl && $platform['ssl_enabled'])) ? ('https://' . (!empty($platform['platform_url_secure']) ? $platform['platform_url_secure'] : $platform['platform_url']) . '/') : ('http://' . $platform['platform_url'] . '/');
        }
        if (empty($base_url)) {
            $base_url = \Yii::$app->get('platform')->getConfig($platform_id)->getCatalogBaseUrl(true); ///mmmmm shit
        }

        $curUrl = \Yii::$app->urlManager->createAbsoluteUrl('/');
        if (strpos($curUrl, $base_url) !== 0) {
            $cor = true;
            $base_url .= 'admin';
        }

        $ownAPILink = \Yii::$app->urlManager->createAbsoluteUrl(['modules/edit', 'platform_id' => $platform_id, 'set' => 'payment', 'module' => $this->module->code, 'ppp_next' => 4, 'test_mode' => ($this->module->getMode() != 'Live')]) . '#extra';
        $editLink = \Yii::$app->urlManager->createAbsoluteUrl(['modules/edit', 'platform_id' => $platform_id, 'set' => 'payment', 'module' => $this->module->code]) . '#extra';

        if ($this->mode == 'form' && !($this->module->hasOwnKeys() || $next == 4)) {
            $tmp = $this->module->getBoardingDetails($platform_id, $this->seller);
        }
        $ccpActive = false;
        $sellerBoardingDetails = [];

        if (is_array($tmp ?? null)) {
            foreach (['errors', 'warnings', 'info'] as $group) {
                if (!empty($tmp[$group])) {
                    $sellerBoardingDetails[$group] = $tmp[$group];
                }
            }
            $ccpActive = $this->module->CCPActive($this->seller);
            $seller3DS = $this->module->get3DSSettings($this->seller);
        }
        //1 - sandbox 2 - live 3 - both partner's keys
        $installPPP = $this->module->getInstallOptions($platform_id);
        $tmp = $this->module->getMode();
        $boardingActive = (($tmp != 'Live' && in_array($installPPP, [1,3])) || ($tmp == 'Live' && in_array($installPPP, [2,3])));

        switch ($this->mode) {
            case 'form':
                $ret = $this->render('extra', [
                  'seller' => $this->seller,
                  'cor' => $cor,
                  'boardingActive' => $boardingActive,
                  'platform_base_url' => $base_url,
                  'address' => $this->address,
                  'module' => $this->module,
                  'errors' => $this->errors,
                  'messages' => $this->messages,
                  'gologin' => ($next == 1),
                  'loginLink' => $loginLink,
                  'boardLink' => $boardLink,
                  'checkBoardLink' => $checkBoardLink,
                  'unBoardLink' => $unBoardLink,
                  'ownAPILink' => $ownAPILink,
                  'editLink' => $editLink,
                  'sellerBoardingDetails' => $sellerBoardingDetails,
                  'seller3DS' => $seller3DS,
                  'ccpActive' => $ccpActive,
                  'ownAPIDetails' => ($this->module->hasOwnKeys() || $next == 4),
                ]);
                break;
            case 'signup': // grant permissions
                $ret = $this->render('signup', [
                  'url' => $this->url,
                  'errors' => $this->errors,
                ]);
                break;
            case 'info':
                $ret = $this->render('info', [
                  'errors' => $this->errors,
                  'messages' => $this->messages,
                ]);
                break;
            case 'login':
                $ret = $this->render('extra', [
                  'login' => 1,
                  'seller' => $this->seller,
                  'cor' => $cor,
                  'platform_base_url' => $base_url,
                  'address' => $this->address,
                  'module' => $this->module,
                  'errors' => $this->errors,
                  'messages' => $this->messages,
                ]);
                break;
        }
        return $ret;
    }

}
