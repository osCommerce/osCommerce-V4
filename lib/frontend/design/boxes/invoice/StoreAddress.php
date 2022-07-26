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

namespace frontend\design\boxes\invoice;

use Yii;
use yii\base\Widget;
use frontend\design\Info;

class StoreAddress extends Widget
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
        if (isset($this->settings[0]['address_spacer']) && $this->settings[0]['address_spacer']){
            $spacer = $this->settings[0]['address_spacer'];
        } else {
            $spacer = '<br>';
        }
        if (isset($this->params['platform_id']) && $this->params['platform_id']) {
            $data = Info::platformData($this->params['platform_id']);
        } else {
            $data = Info::platformData();
        }
        if (isset($this->settings[0]['pdf']) && $this->settings[0]['pdf']){
            return \common\helpers\Address::address_format(\common\helpers\Address::get_address_format_id($data['country_id']), $data, 1, ' ', $spacer);
        } else {
            return '<div>' . \common\helpers\Address::address_format(\common\helpers\Address::get_address_format_id($data['country_id']), $data, 1, ' ', $spacer) . '</div>';
        }
    }
}