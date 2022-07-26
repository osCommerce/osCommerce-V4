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

namespace frontend\design\boxes\subscribers;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use frontend\design\Info;
use common\extensions\Subscribers\models\SubscribersLists;
use common\classes\platform;

class SubscribeForm extends Widget
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

        Info::addJsData(['tr' => [
            'TEXT_NO' => TEXT_NO,
            'TEXT_YES' => TEXT_YES,
            'TEXT_CHECK_EMAIL' => TEXT_CHECK_EMAIL,
        ]]);
      $languages_id = \Yii::$app->settings->get('languages_id');
      $lists = SubscribersLists::find()
          ->andWhere([
            'status' => 1,
            'platform_id' => platform::currentId(),
            'language_id' => $languages_id
            ])
          ->orderBy('sort_order, name')
          ->asArray()->all();


        return IncludeTpl::widget(['file' => 'boxes/subscribers/subscribe-form.tpl', 'params' => [
            'settings' => $this->settings,
            'lists' => $lists,
            'link' => Yii::$app->urlManager->createUrl('subscribers/send-confirmation-subscribe'),
            'subscribers_firstname' => Yii::$app->request->get('subscribers_firstname', ''),
            'subscribers_lastname' => Yii::$app->request->get('subscribers_lastname', ''),
            'subscribers_email_address' => Yii::$app->request->get('subscribers_email_address', '')
        ]]);
    }
}