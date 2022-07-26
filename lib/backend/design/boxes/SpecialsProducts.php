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

class SpecialsProducts extends Widget
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
    $languages_id = \Yii::$app->settings->get('languages_id');
    $specialsTypesArr = \common\models\SpecialsTypes::find()->where([
          'language_id' => $languages_id
        ])
        ->select('specials_type_name, specials_type_id')
        ->asArray()->indexBy('specials_type_id')->column();
    if (!is_array($specialsTypesArr)) {
      $specialsTypesArr = [];
    }
    $specialsTypesArr[0] = '';
    ksort($specialsTypesArr);

    return $this->render('specials-products.tpl', [
      'id' => $this->id, 'params' => $this->params, 'settings' => $this->settings,
      'visibility' => $this->visibility,
      'types' => $specialsTypesArr,
    ]);
  }
}