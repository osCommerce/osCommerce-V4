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

class HeaderStock extends Widget
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
        return IncludeTpl::widget(['file' => 'boxes/header-stock.tpl', 'params' => [
            'checked' => SHOW_OUT_OF_STOCK,
            'url' => tep_href_link(Yii::$app->controller->id . '/' . Yii::$app->controller->action->id, \common\helpers\Output::get_all_get_params()),
            'text' => $this->params['text'] ?? TEXT_OUT_STOCK
        ]]);
    }
}