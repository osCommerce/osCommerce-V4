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

namespace frontend\design;

use yii\base\Widget;

class DatePickerJs extends Widget
{

    public $selector;
    public $params;
    public $onlyDaysCurrent;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        \common\helpers\Translation::init('js');
        return IncludeTpl::widget([
            'file' => 'boxes/date-picker-js.tpl',
            'params' => [
                        'selector' => $this->selector,
                        'params' => $this->params,
                        'onlyDaysCurrent' => $this->onlyDaysCurrent
        ]]);
    }

}
