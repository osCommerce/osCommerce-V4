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

namespace frontend\design\boxes\product;

use Yii;
use yii\base\Widget;
use frontend\design\Info;

class ButtonsSample extends Widget
{

    public $file;
    public $params;
    public $settings;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('Samples', 'allowed')) {
            return $ext::productBlock();
        } elseif (Info::isAdmin()) {
            return 'Request for sample Button (Samples not installed)';
        } else {
            return '';
        }
    }
}