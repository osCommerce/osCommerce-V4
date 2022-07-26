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

namespace frontend\design;

use Yii;
use yii\base\Widget;

class Css extends Widget
{


  public function init()
  {
    parent::init();
  }

  public function run()
  {

    $css = tep_db_fetch_array(tep_db_query("select setting_value from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . THEME_NAME . "' and setting_group = 'css' and setting_name = 'css'"));
    $javascript = tep_db_fetch_array(tep_db_query("select setting_value from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . THEME_NAME . "' and setting_group = 'javascript' and setting_name = 'javascript'"));
    $css_val = '';

    if ($css['setting_value']){
      $css_val .= '
<style type="text/css">
' . $css['setting_value'] . '
</style>';
    }

    if ($javascript['setting_value']){
      $css_val .= '
<script type="text/javascript">
' . $javascript['setting_value'] . '
</script>';
    }

    return $css_val;
  }
}