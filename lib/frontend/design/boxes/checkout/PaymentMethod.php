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

class PaymentMethod extends Widget
{

    public $file;
    public $params;
    public $manager;
    public $settings;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        if (is_object($this->manager)){
            $this->params['manager'] = $this->manager;
        }
        if (isset($this->settings[0]['combine_fields_notes'])) {
          $this->params['combine_fields_notes'] = $this->settings[0]['combine_fields_notes'];
        }

        return IncludeTpl::widget(['file' => 'boxes/checkout/payment-method.tpl', 'params' => $this->params]);
    }
}