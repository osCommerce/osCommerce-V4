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

class UnsubscribeForm extends Widget
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
        return IncludeTpl::widget(['file' => 'boxes/subscribers/unsubscribe-form.tpl', 'params' => [
            'settings' => $this->settings,
            'link' => Yii::$app->urlManager->createUrl('subscribers/send-confirmation-unsubscribe'),
            'subscribers_firstname' => Yii::$app->request->get('subscribers_firstname', ''),
            'subscribers_lastname' => Yii::$app->request->get('subscribers_lastname', ''),
            'subscribers_email_address' => Yii::$app->request->get('subscribers_email_address', '')
        ]]);
    }
}