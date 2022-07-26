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

namespace frontend\design\boxes\email;

use Yii;
use yii\base\Widget;

class Date extends Widget
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
      \common\helpers\Translation::init('js');

      $monthNames = [ DATEPICKER_MONTH_JAN, DATEPICKER_MONTH_FEB, DATEPICKER_MONTH_MAR, DATEPICKER_MONTH_APR, DATEPICKER_MONTH_MAY, DATEPICKER_MONTH_JUN, DATEPICKER_MONTH_JUL, DATEPICKER_MONTH_AUG, DATEPICKER_MONTH_SEP, DATEPICKER_MONTH_OCT, DATEPICKER_MONTH_NOV, DATEPICKER_MONTH_DEC ];
      //$monthNames = [TEXT_JAN, TEXT_FAB, TEXT_MAR, TEXT_APR, TEXT_MAY, TEXT_JUN, TEXT_JUL, TEXT_AUG, TEXT_SEP, TEXT_OCT, TEXT_NOV, TEXT_DEC];
          
      return strftime("%e") . ' ' . $monthNames[date("n")-1] . ' ' . strftime("%Y");
  }
}