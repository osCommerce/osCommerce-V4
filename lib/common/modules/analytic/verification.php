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

use common\components\google\modules\AbstractGoogle;
use common\components\google\modules\GoogleInterface;
use common\classes\platform;

final class verification extends AbstractGoogle {

    public $config;
    public $code = 'verification';

    public function getParams() {

        $this->config = [
            $this->code => [
                'name' => 'Google Site Verification',
                'fields' => [
                    [
                        'name' => 'code',
                        'value' => '',
                        'type' => 'text'
                    ]
                ],
                'example' => true,
            ],
        ];
        return $this->config;
    }

    public function renderWidget() {
        return false;
    }

    public function renderExample() {
        if ($this->params['platform_id']){
            $code = $this->config[$this->code]['fields'][0]['value'];
                return "<pre>" . <<<EOD
&lt;meta name="google-site-verification" content="{$code}" /&gt;
EOD
                        . "</pre>";
        }
        return;
    }

}
