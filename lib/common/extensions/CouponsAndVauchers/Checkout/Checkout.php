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

namespace common\extensions\CouponsAndVauchers\Checkout;

use Yii;

class Checkout extends \yii\base\Widget {

    public $name;
    public $params;
    public $settings;
    public $id;

    public function init() {
        parent::init();
    }

    public function run() {
        if (!\common\helpers\Acl::checkExtensionAllowed('CouponsAndVauchers', 'allowed')) return '';
        $manager = $this->params['manager'];
        return \common\extensions\CouponsAndVauchers\CouponsAndVauchers::checkoutCouponVoucher($manager->getCreditModules(), $this->id);
    }
    
}
