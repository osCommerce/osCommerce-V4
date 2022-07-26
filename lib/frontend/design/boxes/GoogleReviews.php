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

namespace frontend\design\boxes;

use Yii;
use yii\base\Widget;
use common\components\GoogleTools;

class GoogleReviews extends Widget {

    public $file;
    public $params;
    public $settings;
    private $module;

    public function init() {
        parent::init();
    }

    public function run() {
        $provider = GoogleTools::instance()->getModulesProvider();
        $reviews = $provider->getActiveByCode('reviews', \common\classes\platform::currentId());
        if ($reviews && $reviews->params['status']) {
            $postition = $this->settings[0]['position'] ?? "BOTTOM_RIGHT";
            return $reviews->getBadgeCode(false, $postition);
        }
    }

}
