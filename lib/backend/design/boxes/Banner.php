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

namespace backend\design\boxes;

use Yii;
use yii\base\Widget;

class Banner extends Widget
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
		global $languages_id, $language;
		$banners = array();
    $sql = tep_db_query("select distinct banners_group from " . TABLE_BANNERS_NEW . " nb where 1/* and nb.status = '1'*/ ");
    while ($row=tep_db_fetch_array($sql)){
      $banners[] = $row;
    }

    /* support old versions */
    $this->settings[0]['banners_group'] = $this->settings[0]['banners_group'] ?? null;
    $this->settings[0]['banners_type'] = $this->settings[0]['banners_type'] ?? null;
    if (!$this->settings[0]['banners_group'] && $this->params) $this->settings[0]['banners_group'] = $this->params;
    if (!$this->settings[0]['banners_type']) {
      $type_sql_query = tep_db_query("select nb.banner_type from " . TABLE_BANNERS_TO_PLATFORM . " nb2p, " . TABLE_BANNERS_NEW . " nb where nb.banners_group = '" . $this->settings[0]['banners_group'] . "' AND nb2p.banners_id=nb.banners_id AND nb2p.platform_id='" . \common\classes\platform::currentId() . "' limit 1");
      if (tep_db_num_rows($type_sql_query) > 0) {
        $type_sql = tep_db_fetch_array($type_sql_query);
        $type_array = $type_sql['banner_type'];
        $type_exp = explode(';', $type_array);
        if (isset($type_exp) && !empty($type_exp)) {
          $this->settings[0]['banners_type'] = $type_exp[0];
        } else {
          $this->settings[0]['banners_type'] = $type_sql['banner_type'];
        }
      }
    }
    /* /support old versions */

    return $this->render('banner.tpl', [
      'id' => $this->id,
      'params'=> $this->params,
      'banners' => $banners,
      'settings' => $this->settings,
      'visibility' => $this->visibility,
    ]);
  }
}