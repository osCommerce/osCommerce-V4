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

namespace frontend\design;

use Yii;

class EditData
{
    public static function jsData()
    {
        global $languages_id;

        return [
            'TEXT_INFORMATION' => \common\helpers\Translation::$translations['TEXT_INFORMATION']['value'],
            'DIR_WS_HTTP_ADMIN_CATALOG' => HTTP_SERVER . DIR_WS_HTTP_CATALOG . DIR_WS_HTTP_ADMIN_CATALOG,
            'setFrontendTranslationTimeUrl' => Yii::$app->urlManager->createUrl('index/set-frontend-translation-time'),
            'platformId' => \common\classes\platform::currentId(),
            'languageId' => $languages_id,
            'isGuest' => Yii::$app->user->isGuest,
        ];
    }

    public static function getJsData()
    {
        return json_encode(self::jsData());
    }

    public static function addOnFrontend()
    {
        $html = '';
        if (\common\helpers\Acl::isFrontendTranslation()) {
            return '
<script>
    tl("' . Info::themeFile('/js/edit-data.js') . '", function(){
        editData.loader("' . addslashes(self::getJsData()) . '")
    })
</script>
            ';
        }
        if (\common\helpers\Acl::isFrontendTranslation() || Info::isAdmin() && Yii::$app->request->get('texts')) {
            $html = '
<link rel="stylesheet" href="' . Info::themeFile('/css/edit-data.css') . '"/>
            ';
        }

        return $html;
    }

    public static function addEditDataTeg($content, $pageType, $fieldName, $id = 0, $split = 0)
    {
        if (!\common\helpers\Acl::isFrontendTranslation() && !(Info::isAdmin() && Yii::$app->request->get('texts'))) {
            return $content;
        }

        $accessLevels = explode(',', Yii::$app->request->cookies->get('frontend_translation'));
        if (!\common\helpers\Acl::rule(self::getAccessRule($pageType), 0, $accessLevels)) {
            return $content;
        }

        return '<span class="edit-data-element"
            data-edit-data-page="' . $pageType . '"
            data-edit-data-field="' . $fieldName . '"
            data-edit-data-id="' . $id . '"
            data-edit-data-split="' . $split . '">' . $content . '</span>';

    }

    public static function getAccessRule ($pageType){
        switch ($pageType) {
            case 'seo': return ['BOX_HEADING_SEO', 'BOX_META_TAGS'];
            case 'info': return ['BOX_HEADING_DESIGN_CONTROLS', 'BOX_INFORMATION_MANAGER'];
            case 'menu': return ['BOX_HEADING_DESIGN_CONTROLS', 'FILENAME_CMS_MENUS'];
        }
        return '';
    }

    public static function addEditDataTegTranslation($content, $key)
    {
        if (\common\helpers\Acl::isFrontendTranslation() || Info::isAdmin() && Yii::$app->request->get('texts')) {
            $entity = \common\helpers\Translation::$translations[$key];

            return '<span
                class="translation-key"
                data-translation-key="' . $key . '"
                data-translation-entity="' . $entity['entity'] . '">' . $content . '</span>';
        } else {
            return $content;
        }
    }


}