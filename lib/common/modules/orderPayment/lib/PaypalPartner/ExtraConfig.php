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
    public $link_params;

    public function init() {

    }

    public function run() {

        $ret = '';
        $next = \Yii::$app->request->get('ppp_next', 0);
        $platform_id = (int) \Yii::$app->request->get('platform_id', 0);
        $urlParams = ['callback/webhooks.payment.' . $this->module->code, 'platform_id' => $platform_id];
        $mode = $this->module->getMode();

        $loginLink = \Yii::$app->urlManager->createAbsoluteUrl($urlParams + ['action' => 'processOnBoard', 'ppexists' => 1], null, true);
        $boardLink = \Yii::$app->urlManager->createAbsoluteUrl($urlParams + ['action' => 'processOnBoard', 'seller_type' => ($next == 3 ? 'i' : 'b')], null, true);
        $checkBoardLink = \Yii::$app->urlManager->createAbsoluteUrl($urlParams + ['action' => 'checkOnBoarded', 'result' => 'show'], null, true);
        $checkLink = \Yii::$app->urlManager->createAbsoluteUrl($urlParams + ['action' => 'processOnBoard', 'subaction'=> 'checkDetails', 'result' => 'show'], null, true);

        //$unBoardLink = tep_catalog_href_link('callback/webhooks.payment.' . $this->module->code, http_build_query(['action'=>'deleteSeller', 'platform_id' => $platform_id]));
        $unBoardLink = \Yii::$app->urlManager->createAbsoluteUrl($urlParams + ['action' => 'deleteSeller', 'ck' => $this->seller->tracking_id], null, true);
        $whSubscribeLink = \Yii::$app->urlManager->createAbsoluteUrl($urlParams + ['action' => 'subscribeSeller', 'ck' => $this->seller->tracking_id], null, true);

        $fetchKeysUrl = \Yii::$app->urlManager->createAbsoluteUrl(['callback/webhooks.payment.' . $this->module->code, 'action' => 'sellerDetails'], null, true);

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

//2check and remove - callback controller is updated.
        $curUrl = \Yii::$app->urlManager->createAbsoluteUrl('/');
        if (strpos($curUrl, $base_url) !== 0) {
            $cor = true;
            $base_url .= 'admin';
        }

        $ownAPILink = \Yii::$app->urlManager->createAbsoluteUrl(['modules/edit', 'platform_id' => $platform_id, 'set' => 'payment', 'module' => $this->module->code, 'ppp_next' => 4, 'test_mode' => ($mode != 'Live')]) . '#extra';
        $editLink = \Yii::$app->urlManager->createAbsoluteUrl(['modules/edit', 'platform_id' => $platform_id, 'set' => 'payment', 'module' => $this->module->code]) . '#extra';

        if ($this->mode == 'form' && !empty($this->seller->payer_id) && $this->seller->status>0 /*&& !($this->module->hasOwnKeys() || $next == 4)*/) {
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
        $installPPP = $this->module->getInstallOptions($platform_id, true);
        $boardingActive = (($mode != 'Live' && in_array($installPPP, [1,3])) || ($mode == 'Live' && in_array($installPPP, [2,3])));

        $whListNames = $this->module->getSubscribedWebHooks();
        if (empty($whListNames)) {
            $whListNames = false;
        }

        $neededWebHooks = $this->module->getNeededWebHooks();

        if (is_array($whListNames) && !empty($whListNames) && is_array($whListNames) && is_array($neededWebHooks)) {
            $subEvents = array_diff($neededWebHooks, $whListNames);
        } else {
            $subEvents = $neededWebHooks;
        }

        switch ($this->mode) {
            case 'form':
                $ret = $this->render('extra', [
                  'seller' => $this->seller,
                  'cor' => $cor,
                  'boardViaLink' => $this->module->boardViaLink(),
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
                  'ownAPIDetails' => ((!empty($this->seller->own_client_id) && !empty($this->seller->own_client_secret)) || $next == 4),
                  'boardingMode' => $this->module::BOARDING_MODE,
                  'fetchKeysUrl' => $fetchKeysUrl,
                  'webhooksList' => $whListNames,
                  'missedWh' => !empty($subEvents),
                  'webhooksRequiredList' => $neededWebHooks,
                  'whSubscribeLink' => $whSubscribeLink,
                  'checkLink' => $checkLink,
                  'ppp_mode' => ($this->module->getPartnerId('Sandbox') == $this->seller->partner_id)?'Sandbox':'Live',
                  'checkVal' => md5(\Yii::$app->params['secKey.global']),
                ]);
                break;
            case 'signup': // grant permissions
                $ret = $this->render('signup', [
                  'url' => $this->url,
                  'errors' => $this->errors,
                  'boardingMode' => $this->module::BOARDING_MODE,
                  'fetchKeysUrl' => $fetchKeysUrl,
                ]);
                break;
            case 'signupLink': // grant permissions
                $ret = $this->render('signup_link', [
                  'errors' => $this->errors,
                  'boardingMode' => $this->module::BOARDING_MODE,
                  'link_params' => $this->link_params,
                  'fetchKeysUrl' => $fetchKeysUrl,
                  'mode' => $mode
                ]);
                break;
            case 'info':
                $ret = $this->render('info', [
                  'errors' => $this->errors,
                  'messages' => $this->messages,
                  'boardingMode' => $this->module::BOARDING_MODE,
                  'fetchKeysUrl' => $fetchKeysUrl,
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
                  'boardingMode' => $this->module::BOARDING_MODE,
                  'fetchKeysUrl' => $fetchKeysUrl,
                ]);
                break;
        }
        return $ret;
    }

}
