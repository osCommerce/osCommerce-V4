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

class InvoiceId extends Widget
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
    \common\helpers\Translation::init('admin/design');

    if ($this->params["order"] instanceof \common\classes\Splinter){
        return ($this->params["order"]->isInvoice() ? TEXT_INVOICE_PREFIX : TEXT_CREDIT_NOTE_PREFIX ) . $this->params["order"]->order_id;
    }
  }
}