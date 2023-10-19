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

namespace backend\design\boxes\product;

use common\helpers\Translation;
use Yii;
use yii\base\Widget;
use yii\helpers\Html;

class CustomBundle extends Widget
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
      global $languages_id;

      $xsellTypeVariants = [
          0 => Translation::getTranslationValue('FIELDSET_ASSIGNED_XSELL_PRODUCTS','admin/categories'),
      ];

      if ($ext = \common\helpers\Acl::checkExtensionAllowed('UpSell'))
      {
          $tmp = $ext::getXsellTypeList();
      }else{
          $tmp = null;
      }

      if (is_array($tmp)) {
        $xsellTypeVariants += $tmp;
      }
      
      $platformList = \common\classes\platform::getList();

      return $this->render('../../views/custom-bundle.tpl', [
          'id' => $this->id, 'params' => $this->params, 'settings' => $this->settings,
          'visibility' => $this->visibility,
          'xsellTypeVariants' => $xsellTypeVariants,
          'platformList' => $platformList,
      ]);
  }
}