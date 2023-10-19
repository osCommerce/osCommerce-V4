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
use common\models\BannersGroups;
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
      \common\helpers\Translation::init('admin/banner_manager');
    parent::init();
  }

  public function run()
  {
      $bannersGroups = BannersGroups::find()
          ->orderBy('banners_group')
          ->asArray()->all();

      foreach ($bannersGroups as $key => $bannersGroup) {
          $bannersGroups[$key]['count'] = Banners::find()
              ->where(['group_id' => $bannersGroup['id']/*, 'status' => 1*/])->count();
      }

      /* support old versions */
      $this->settings[0]['banners_group'] = $this->settings[0]['banners_group'] ?? null;
      $this->settings[0]['banners_type'] = $this->settings[0]['banners_type'] ?? null;
      if (!$this->settings[0]['banners_group'] && $this->params) $this->settings[0]['banners_group'] = $this->params;

      if (!$this->settings[0]['banners_type'] && $this->settings[0]['banners_group']) {
          $banner = Banners::find()->alias('b')
              ->leftJoin(BannersGroups::tableName() . ' bg', 'bg.id = b.group_id')
              ->where(['bg.banners_group' => $this->settings[0]['banners_group']])
              ->asArray()->one();
          if ($banner && $banner['banner_type']) {
              $type_array = $banner['banner_type'];
              $type_exp = explode(';', $type_array);
              if (isset($type_exp) && !empty($type_exp)) {
                  $this->settings[0]['banners_type'] = $type_exp[0];
              } else {
                  $this->settings[0]['banners_type'] = $banner['banner_type'];
              }
          }
      }
      /* /support old versions */

      $microtime = \common\models\DesignBoxesTmp::findOne($this->id)->microtime ?? '';
      $microtime = substr($microtime, 0, strripos($microtime, '.'));

      $content = $this->render('banner.tpl', [
          'id' => $this->id,
          'params'=> $this->params,
          'bannersGroups' => $bannersGroups,
          'settings' => $this->settings,
          'visibility' => $this->visibility,
          'microtime' => $microtime,
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