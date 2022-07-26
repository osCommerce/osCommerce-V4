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

namespace backend\design\boxes\invoice;

use Yii;
use yii\base\Widget;

class OrderId extends Widget
{

  public $id;
  public $params;
  public $settings;
  public $visibility;

  public function init()
  {
    parent::init();
  }

  public function run()
  {
      $inv_part = '';
      /** @var \common\extensions\InvoiceNumberFormat\InvoiceNumberFormat $iext */
      if ($iext = \common\helpers\Acl::checkExtensionAllowed('InvoiceNumberFormat', 'allowed')) {
          $inv_part = $iext::widgetSettings($this->settings);
      }
    return $this->render('../../views/invoice/order-id.tpl', [
      'id' => $this->id, 'params' => $this->params, 'settings' => $this->settings,
      'invoice_part' => $inv_part,
      'visibility' => $this->visibility,
    ]);
  }
}