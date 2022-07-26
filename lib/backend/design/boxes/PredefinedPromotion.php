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
use common\classes\platform;
use common\helpers\Date;

class PredefinedPromotion extends Widget
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
      if (\common\helpers\Acl::checkExtensionAllowed('Promotions')) {
          return '';
      }

    $platform_id = platform::currentId();
    if (!$platform_id)
        $platform_id = platform::defaultId ();
    
    $promo = [];
    foreach(\common\models\promotions\Promotions::getCurrentPromotions($platform_id, [0, 1])->indexBy('promo_id')->all() as $promo_id => $obj){
        $hasRange = intval($obj->promo_date_start) || intval($obj->promo_date_expired);
        $promo[$promo_id] = $obj->promo_label . ($hasRange? " (" . (intval($obj->promo_date_start)? Date::date_short($obj->promo_date_start) :'') . "-" . (intval($obj->promo_date_expired)? Date::date_short($obj->promo_date_expired) :'')  . ")" : '');
    }

    return $this->render('predefiend-promotion.tpl', [
      'id' => $this->id,
      'params' => $this->params,
      'promo' => $promo,
      'settings' => $this->settings,
      'visibility' => $this->visibility,
    ]);
  }
}