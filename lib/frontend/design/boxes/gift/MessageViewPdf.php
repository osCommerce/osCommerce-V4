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

namespace frontend\design\boxes\gift;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use frontend\design\Info;

class MessageViewPdf extends Widget
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
      $gift_card_id = Yii::$app->request->get('gift_card_id', 0);
      $customer = Yii::$app->user->getIdentity();

      $giftCard = \common\models\VirtualGiftCardInfo::find()->where([
          'virtual_gift_card_info_id' => $gift_card_id,
          'customers_id' => $customer->customers_id
      ])->asArray()->one();

      return $giftCard['virtual_gift_card_message'];
  }
}