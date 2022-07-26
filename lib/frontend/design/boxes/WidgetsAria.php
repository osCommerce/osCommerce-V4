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
use frontend\design\Block;
use frontend\design\Info;

class WidgetsAria extends Widget
{

    public $settings;
    public $params;
    public $id;

    public function init()
    {
        parent::init();
    }

    public function run()
    {

        if (!$this->settings[0]['aria_name']) {
            $this->settings[0]['aria_name'] = 'demo';
        }
        return Block::widget(['name' => $this->settings[0]['aria_name'], 'params' => ['params' => $this->params]]);
    }
}