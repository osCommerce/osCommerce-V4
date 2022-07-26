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

final class analytics extends AbstractGoogle {

    use adTrait;

    public $config;
    public $code = 'analytics';

    public function getParams() {

        $this->config = [
            $this->code => [
                'name' => 'Google Analytics',
                'fields' => [
                    [
                        'comment' => 'Universal Analytics (analytics.js)',
                        'name' => 'code',
                        'value' => 'UA-',
                        'type' => 'text'
                    ],
                    [
                        'comment' => 'Google Analytics 4 (gtag.js)',
                        'name' => 'code_',
                        'value' => 'G-',
                        'type' => 'text'
                    ],
                ],
                'pages' => [
                    'all',
                ],
                'priority' => 1,
                'example' => true
            ],
        ];
        return $this->config;
    }

    public function renderWidget($example = false) {
        global $request_type;
        if (\Yii::$app->response->getIsNotFound())
            return;
        $elements = $this->config[$this->code];
        if ($request_type == 'SSL') {
            $_server = HTTPS_SERVER;
        } else {
            $_server = HTTP_SERVER;
        }
        if (\frontend\design\Info::isTotallyAdmin()) {
            $path = $_server . DIR_WS_CATALOG . 'themes/basic/js/analytics.js';
        } else {
            $_path = $_server . DIR_WS_HTTP_CATALOG;
            if (is_link($_path)) {
                $_path = readlink($_path);
            }
            $path = $_path . 'themes/basic/js/analytics.js';
        }
        $ua_code = $elements['fields'][0]['value'];
        $ga4_code = $elements['fields'][1]['value'];
        if ($example) {
            if ($ga4_code == 'G-') $ga4_code = 'G-XXXXXXXX';
            if ($ua_code == 'UA-') $ua_code = 'UA-XXXXXXXX';
            return htmlspecialchars($this->getSelectedCode($ga4_code, $ua_code, $path));
        } else {
            if ($ga4_code == 'G-') $ga4_code = '';
            if ($ua_code == 'UA-') $ua_code = '';
            return $this->getSelectedCode($ga4_code, $ua_code, $path);
        }
    }

    private function getSelectedCode($ga4_code, $ua_code, $path) {
        $return = '';
        if ($ga4_code) {
            $return .= <<<EOD
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id={$ga4_code}"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', '$ga4_code');
</script>

EOD;
        } 
        if ($ua_code) {
            $return .= <<<EOD
<!-- Universal analytics (analytics.js) - Google Analytics -->
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','$path','ga');
  
  ga('create', '$ua_code', 'auto');
  ga('send', 'pageview');
  if (!localStorage.hasOwnProperty('ga_cookie')){
      localStorage.ga_cookie = 'false';
  }
  tl(function(){{$this->collectCookie()}})
</script>

EOD;
        }
        return $return;
    }

    public function renderExample() {
        return "<pre>" . $this->renderWidget(true) . "</pre>";
    }

}
