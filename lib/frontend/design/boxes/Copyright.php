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
use frontend\design\Info;

class Copyright extends Widget
{

    public $file;
    public $params;
    public $settings;
    private static $page_block;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        $languages_id = \Yii::$app->settings->get('languages_id');
        $data = Info::platformData();

        if (Yii::$app->id == 'app-console'){
            $text = \common\helpers\Translation::getTranslationValue('TEXT_COPYRIGHT', 'main', \common\classes\language::defaultId());
            return '<div>' . strip_tags(sprintf($text, date("Y"), @$data['company'])) . '</div>';
        }

        $info_id = (int)Yii::$app->request->get('info_id');

        $information = \common\models\Information::find()
            ->select(['seo_page_name'])
            ->where([
                'information_id' => $info_id,
                'platform_id' => \common\classes\platform::currentId(),
                'visible' => '1',
                'languages_id' => $languages_id,
            ])
            ->asArray()->one();

        $text = \common\helpers\Translation::getTranslationValue('TEXT_COPYRIGHT', 'main');

        if (isset($information['seo_page_name']) && $information['seo_page_name'] && preg_match('/href=\"[^"]{0,}' . $information['seo_page_name'] . '[^"]{0,}\"/', $text)) {
            $text = preg_replace('/<a href=\"[^"]{0,}' . $information['seo_page_name'] . '[^"]{0,}\">([^\<]+)<\/a>/', '<span>$1</span>', $text);
        }

        self::$page_block = (isset($this->params['params']['page_block']) ? $this->params['params']['page_block'] : '');

        $text = preg_replace_callback('/href=\"([^"]{0,})\"/', self::class . '::createUrl', $text);

        return '<div>' . sprintf($text, date("Y"), @$data['company']) . '</div>';
    }

    private static function createUrl($matches){

        if (strpos($matches[1], 'http') === 0 || strpos($matches[1], '//') === 0) {
            return $matches[0];
        }

        $page_block = self::$page_block ?? Info::pageBlock();

        if ($page_block == 'orders' || $page_block == 'email' || $page_block == 'packingslip' || $page_block == 'invoice' || $page_block == 'pdf' || $page_block == 'pdf_cover' || $page_block == 'gift_card') {
            $text = str_replace($matches[1], Yii::$app->urlManager->createAbsoluteUrl($matches[1]), $matches[0]);
        } else {
            $text = str_replace($matches[1], Yii::$app->urlManager->createUrl($matches[1]), $matches[0]);
        }
        return $text;
    }
}