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

namespace frontend\design\boxes\info;

use common\components\InformationPage;
use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;

class Content extends Widget
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
        $infoId = (int)Yii::$app->request->get('info_id');
        if(!$infoId) return '';

        $infoData = InformationPage::getFrontendDataVisible($infoId);
        $html = \frontend\design\Info::widgetToContent($infoData['description']);
        $html = \common\classes\TlUrl::replaceUrl($html);
        $html = \common\classes\PageComponents::addComponents($html);
        $html = \frontend\design\EditData::addEditDataTeg(stripslashes($html), 'info', 'description', $infoId);

        return IncludeTpl::widget(['file' => 'boxes/info/content.tpl', 'params' => ['content' => $html ]]);
    }
}