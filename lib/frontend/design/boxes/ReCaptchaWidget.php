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

namespace frontend\design\boxes;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use common\classes\ReCaptcha;

class ReCaptchaWidget extends Widget
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
    global $lng;
    
    $captcha = new ReCaptcha();
    if (!$captcha->isEnabled()) return;
    
    return IncludeTpl::widget(['file' => 'boxes/captcha.tpl', 'params' => [
      'code' => $lng->get_code(),
      'public_key' => $captcha->getPublicKey(),
      'version' => $captcha->getVersion(),
      'uniqueId' => bin2hex(random_bytes(5))
    ]]);
  }
}