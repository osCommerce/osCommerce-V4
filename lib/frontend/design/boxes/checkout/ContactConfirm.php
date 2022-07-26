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

namespace frontend\design\boxes\checkout;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;

class ContactConfirm extends Widget
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
        if (!$this->params['manager']){
            return '';
        }

        return IncludeTpl::widget(['file' => 'boxes/checkout/contact-confirm.tpl', 'params' => [
            'data' => $this->params['manager']->getCustomerContactForm(),
            'manager' => $this->params['manager'],
        ]]);
    }
}