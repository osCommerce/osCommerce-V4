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

use common\models\Banners;
use common\models\BannersLanguages;
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
      $banners = Banners::find()
          ->alias('b')
          ->select(['b.banners_group'])->distinct()
          ->join('inner join', BannersLanguages::tableName() . ' bl', 'b.banners_id = bl.banners_id')
          ->orderBy('b.banners_group')
          ->asArray()->all();

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

      $content = $this->render('banner.tpl', [
          'id' => $this->id,
          'params'=> $this->params,
          'banners' => $banners,
          'settings' => $this->settings,
          'visibility' => $this->visibility,
      ]);

      if ($this->params && $this->params['main_content']) {
          return $content;
      }

      return $this->render('settings.tpl', [
          'content' => $content,
          'id' => $this->id,
          'params'=> $this->params,
          'settings' => $this->settings,
          'visibility' => $this->visibility,
      ]);
  }
}