<?php

/**
 * This file is part of True Loaded.
 *
 * @link http://www.holbi.co.uk
 * @copyright Copyright (c) 2005 Holbi Group LTD
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace common\modules\analytic;

use common\classes\platform;
use common\components\google\modules\AbstractGoogle;

final class tagmanger extends AbstractGoogle {
    
    use adTrait;

    public $config;
    public $code = 'tagmanger';

    public function getParams() {

        $this->config = [
            $this->code => [
                'name' => 'Google Tag Manager',
                'fields' => [
                    [
                        'name' => 'code',
                        'value' => 'GTM-',
                        'type' => 'text'
                    ],
                    [
                        'name' => 'collect_measuring',
                        'value' => '1',
                        'type' => 'checkbox',
                        'comment' => '<div class="ord-total" style="text-align:left!important;float: right;"><div class="ord-total-info" style="left:0!important;">LEGACY DATALAYER format. Collect Measuring products, checkout funnel, promo</div></div>',
                    ],
                    [
                        'name' => 'collect_measuring_ga4',
                        'value' => '1',
                        'type' => 'checkbox',
                        'comment' => '<div class="ord-total" style="text-align:left!important;float: right;"><div class="ord-total-info" style="left:0!important;">Pass Details in GA-4 format</div></div>',
                    ],
                    [
                        'name' => 'auto_update',
                        'value' => '1',
                        'type' => 'checkbox',
                        'comment' => '<div class="ord-total" style="text-align:left!important;float: right;"><div class="ord-total-info" style="left:0!important;">Use AutoUpdate when do not use cron for updating GTM code. It is processing during site surfing</div></div>',
                    ],
                    [
                        'name' => 'consent_mode',
                        'value' => '0',
                        'type' => 'checkbox',
                        'comment' => '<div class="ord-total" style="text-align:left!important;float: right;"><div class="ord-total-info" style="left:0!important;">For an introduction to consent mode, read <a href="https://developers.google.com/tag-platform/security/concepts/consent-mode">Consent mode overview</a>.</div></div>',
                    ],
                    [
                        'name' => 'debug_mode',
                        'value' => '0',
                        'type' => 'checkbox',
                        'comment' => '<div class="ord-total" style="text-align:left!important;float: right;"><div class="ord-total-info" style="left:0!important;">In Debug mode it will be used remote GTM script</div></div>',
                    ]
                ],
                'example' => true
            ],
        ];
        return $this->config;
    }

    public function renderWidget($example = false) {
        return false;
    }

    public function getSelectedCode($gtm_code = '', $part = 1, $parent = null) {
        if (\Yii::$app->response->getIsNotFound()) return;
        $localScriptPath = $this->getGTM($gtm_code, $parent);
        $consent_mode_key = array_search('consent_mode', array_column($this->config[$this->code]['fields'], 'name'));
        if ($part === 1) {
            $consentModeJS = '';
            if ($consent_mode_key > 0 && $this->config[$this->code]['fields'][$consent_mode_key]['value']) {
                $consentModeJS = <<<EOD
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}

if(localStorage.getItem('consentMode') === null){
    gtag('consent', 'default', {
        'ad_storage': 'denied',
        'ad_user_data': 'denied',
        'ad_personalization': 'denied',
        'analytics_storage': 'denied',
        'functionality_storage': 'denied',
        'security_storage': 'denied',
        'personalization_storage': 'denied',
    });
} else {
    gtag('consent', 'default', JSON.parse(localStorage.getItem('consentMode')));
}
</script>
EOD;
            }
            return <<<EOD
$consentModeJS
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
"{$localScriptPath}";f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','$gtm_code');tl(function(){{$this->collectCookie()} });</script>
<!-- End Google Tag Manager -->
EOD;
        }
        if ($part === 2) {
            $consentModeBanner = '';
            if ($consent_mode_key > 0 && $this->config[$this->code]['fields'][$consent_mode_key]['value']) {
                $consentModeBanner = <<<EOD
<div class="consentBox">
  <div id="cookie-consent-banner" class="cookie-consent-banner" style="display:none;">
    <h3>Cookie settings</h3>
    <p>We use cookies to provide you with the best possible experience. They also allow us to analyze user behavior in order to constantly improve the website for you.</p>
    <button id="btn-accept-all" class="cookie-consent-button btn-success">Accept All</button>
    <button id="btn-accept-some" class="cookie-consent-button btn-outline">Accept Selection</button>
    <button id="btn-reject-all" class="cookie-consent-button btn-grayscale">Reject All</button>
    <div class="cookie-consent-options">
      <label><input id="consent-necessary" type="checkbox" value="Necessary" checked disabled>Necessary</label>
      <label><input id="consent-analytics" type="checkbox" value="Analytics" checked>Analytics</label>
      <label><input id="consent-preferences" type="checkbox" value="Preferences" checked>Preferences</label>
      <label><input id="consent-marketing" type="checkbox" value="Marketing" checked>Marketing</label>
    </div>
  </div>
<div>
<script>
  function hideBanner() {
    document.getElementById('cookie-consent-banner').style.display = 'none';
  }
  if(localStorage.getItem('consentMode') === null) {
    document.getElementById('btn-accept-all').addEventListener('click', function() {
      setConsent({
        necessary: true,
        analytics: true,
        preferences: true,
        marketing: true
      });
      hideBanner();
    });
    document.getElementById('btn-accept-some').addEventListener('click', function() {
      setConsent({
        necessary: true,
        analytics: document.getElementById('consent-analytics').checked,
        preferences: document.getElementById('consent-preferences').checked,
        marketing: document.getElementById('consent-marketing').checked
      });
      hideBanner();
    });
    document.getElementById('btn-reject-all').addEventListener('click', function() {
      setConsent({
        necessary: false,
        analytics: false,
        preferences: false,
        marketing: false
      });
      hideBanner();
    });
    document.getElementById('cookie-consent-banner').style.display = 'block';
  }
  function setConsent(consent) {
    const consentMode = {
      'ad_storage': consent.marketing ? 'granted' : 'denied',
      'ad_user_data': consent.marketing ? 'granted' : 'denied',
      'ad_personalization': consent.marketing ? 'granted' : 'denied',
      'analytics_storage': consent.analytics ? 'granted' : 'denied',
      'functionality_storage': consent.necessary ? 'granted' : 'denied',
      'security_storage': consent.necessary ? 'granted' : 'denied',
      'personalization_storage': consent.preferences ? 'granted' : 'denied',
    };
    gtag('consent', 'update', consentMode);
    localStorage.setItem('consentMode', JSON.stringify(consentMode));
  }
</script>
EOD;
            }
            return <<<EOD
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id={$gtm_code}"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
$consentModeBanner
EOD;
        }
    }

    public function renderExample() {
        global $request_type;

        $elements = $this->config[$this->code];
        $gtm_code = $elements['fields'][0]['value'];
        $this->config[$this->code]['head'] = $this->getSelectedCode($gtm_code, 1);
        $context = ' </div> ' . "<pre>" . htmlspecialchars($this->getSelectedCode($gtm_code, 1)) . "</pre>" . ' </div> ';
        $context .= ' </div> ' . "<pre>" . htmlspecialchars($this->getSelectedCode($gtm_code, 2)) . "</pre>" . ' </div> ';

        return $context;
    }

    public function getGTM($gtm_code = '', $parent = null) {
        //"https://www.googletagmanager.com/gtm.js?id=GTM-K66BMX7"
        $remoteScriptPath = "https://www.googletagmanager.com/gtm.js?id={$gtm_code}";
        if ($this->config[$this->code]['fields'][3]['value']){
            return $remoteScriptPath;
        }
        $localJsFileUri = \frontend\design\Info::themeFile("/js/gtm_{$gtm_code}.js", 'ws');
        $localScriptPath = \frontend\design\Info::themeFile("/js/gtm_{$gtm_code}.js", 'fs');
        
        if (is_object($parent) && !$parent->module->config[$this->code]['fields'][2]['value'] && file_exists($localScriptPath)){
            return $localJsFileUri;
        }
        
        if (!file_exists($localScriptPath) || (file_exists($localScriptPath) && (time() - filectime($localScriptPath) > 900 ) )) { //refresh each 15 min
            $dir = pathinfo($localScriptPath, PATHINFO_DIRNAME);
            try{
                if (!is_dir($dir)) {
                    @mkdir($dir, 0777, true);
                }
                $ctx = stream_context_create(array('http' => array('timeout' => 1)));
                $response = @file_get_contents($remoteScriptPath, 0, $ctx);
                if ($response != false) {
                    $fp = fopen($localScriptPath, 'w');
                    if (is_writable($localScriptPath)) {
                        //$config = new \common\classes\platform_config(PLATFORM_ID);
                        $config = \Yii::$app->get('platform')->config();
                        $path =  preg_replace("/http.?:/", "", $config->getCatalogBaseUrl(true));
                        $gaPath = $path .'themes/basic/js/';
                        $response = preg_replace("/\/\/www\.google\-analytics\.com\//im", $gaPath, $response);
                        fwrite($fp, $response);
                        fclose($fp);
                    }
                }
            } catch (\Exception $e){}
        }

        if (!file_exists($localScriptPath)) {
            $localScriptPath = $remoteScriptPath;
        } else {
            $localScriptPath = $localJsFileUri;
        }
        return $localScriptPath;
    }

}
