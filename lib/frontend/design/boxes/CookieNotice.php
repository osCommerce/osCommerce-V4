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

use yii\base\Widget;
use frontend\design\IncludeTpl;
use frontend\design\Info;

class CookieNotice extends Widget
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
        Info::addBoxToCss('cookie-notice');

        if (!\common\helpers\Acl::checkExtensionAllowed('CookieNotice', 'allowed')) {
            return '';
        }
        if (Info::isAdmin()) {
            return '';
        }
        Info::addBoxToCss('cookie-notice');
        $revisionTimestamp = 0;
        if (defined('COOKIE_NOTICE_ID') && intval(COOKIE_NOTICE_ID) > 0) {
            $languages_id = \Yii::$app->settings->get('languages_id');
            $q = \common\models\Information::find()->andWhere(['languages_id' => $languages_id, 'information_id' => intval(COOKIE_NOTICE_ID)])->asArray()->one();
            if ($q) {
                $revisionTimestamp = strtotime($q['last_modified']);
            }
        }
        return IncludeTpl::widget(['file' => 'boxes/cookie-notice.tpl', 'params' => [
            'settings' => $this->settings,
            'revisionTimestamp' => $revisionTimestamp // timestamp of cookie policy document
        ]]);
    }
}