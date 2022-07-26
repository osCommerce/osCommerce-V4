<?php

/*
 * This file is part of osCommerce ecommerce platform.
 * osCommerce the ecommerce
 * 
 * @link https://www.oscommerce.com
 * @copyright Copyright (c) 2005 Holbi Group Ltd
 * 
 * Released under the GNU General Public License
 * For the full copyright and license information, please view the LICENSE.TXT file that was distributed with this source code.
 */

namespace frontend\design\boxes\invoice;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;

class PaidMark extends Widget
{
  public $params;
  public $settings;

  public function init()
  {
    parent::init();
  }

  public function run()
  {
    if ($this->params["order"]->isPaid()) {
        return '<div></div>';
    }
    return '';
  }
}
