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

namespace common\widgets;

class JSLanguage extends \yii\bootstrap\Widget {

    public $list;

    public function init() {
        parent::init();
    }

    public function run() {

        echo ' var $tranlations = {}; ' . "\n";
        if (is_array($this->list) && count($this->list) > 0) {
            foreach ($this->list as $key => $value) {
                echo ' $tranlations.' . $key . ' = ' . json_encode((string)$value) . ';' . "\n";
            }
        }
        echo '$tranlations.baseurl = "' . (getenv('HTTPS') == 'on' ? HTTPS_SERVER : HTTP_SERVER) . (defined('DIR_WS_ADMIN') ? DIR_WS_ADMIN : DIR_WS_HTTP_CATALOG) . '";' . "\n";
    }

}
