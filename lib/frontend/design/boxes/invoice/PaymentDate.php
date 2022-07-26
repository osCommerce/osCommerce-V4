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
use common\helpers\Date;

class PaymentDate extends Widget
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
        if (!empty($this->settings[0]['custom_format'])) {
            $rawDate = Date::date_long($this->params["order"]->info['date_purchased'], $this->settings[0]['custom_format']);
        } else {
            $rawDate = Date::date_long($this->params["order"]->info['date_purchased']);
        }

        return Date::translateDate($rawDate, $this->params['language_id']);
    }
}