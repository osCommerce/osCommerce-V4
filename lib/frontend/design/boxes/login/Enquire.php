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

namespace frontend\design\boxes\login;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use frontend\forms\registration\CustomerRegistration;

class Enquire extends Widget
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

        return IncludeTpl::widget(['file' => 'boxes/login/enquire.tpl', 'params' => array_merge($this->params, [
            'settings' => $this->settings,
            'id' => $this->id,
            'enquireModel' => $this->params['enterModels']['enquire']
        ])]);
    }
}