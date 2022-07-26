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
use frontend\design\IncludeTpl;

class Html_box extends Widget
{

    public $file;
    public $params;
    public $settings;
    private static $param;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        $content = \common\classes\TlUrl::replaceUrl($this->settings[0]['text']);
        $content = \frontend\design\Info::translateKeys($content);
        return IncludeTpl::widget(['file' => 'boxes/html.tpl', 'params' => ['text' => $content]]);
    }
}