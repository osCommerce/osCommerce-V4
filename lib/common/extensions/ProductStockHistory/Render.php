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

namespace common\extensions\ProductStockHistory;

class Render extends \common\classes\extended\Widget {

    public $params;
    public $template;
    
    public function run() {
        return $this->render($this->template, $this->params);
    }
    
}