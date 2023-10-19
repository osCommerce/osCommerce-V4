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
use frontend\design\IncludeTpl;

class BillingAddress extends Widget
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
      /*if (isset($this->params["order"]->customer['company'])) {
        $this->params["order"]->billing['company'] = $this->params["order"]->customer['company'];
      }
      if (isset($this->params["order"]->customer['company_vat'])) {
        $this->params["order"]->billing['company_vat'] = $this->params["order"]->customer['company_vat'];
      }*/
    return \common\helpers\Address::address_format($this->params["order"]->billing['format_id'] ?? null, $this->params["order"]->billing ?? null, 1, '', '<br>');
  }
}