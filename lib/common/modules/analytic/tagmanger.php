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
        if ($part === 1) {

            return <<<EOD
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
            return <<<EOD
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id={$gtm_code}"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
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
