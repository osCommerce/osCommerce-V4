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

class Taxable extends Widget
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

    return $this->render('taxable.tpl', [
      'id' => $this->id,
      'displayModes' => [
        '' => (defined('TEXT_PULLDOWN')?TEXT_PULLDOWN:'Pulldown'),
        'checkbox' => (defined('TEXT_CHECKBOX')?TEXT_CHECKBOX:'Checkbox'),
        'checkbox-switcher' => (defined('TEXT_SWITCHER')?TEXT_SWITCHER:'Switcher'),
        'radio' => (defined('TEXT_RADIO')?TEXT_RADIO:'Radio')
        ],
      'params' => $this->params,
      'settings' => $this->settings,
      'visibility' => $this->visibility,
    ]);
  }
}