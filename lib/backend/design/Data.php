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

namespace backend\design;

use Yii;
use common\helpers\Translation;
use backend\components\Navigation;

class Data
{
    public static $jsGlobalData = [];

    public static function addJsData($arr = []){
        self::$jsGlobalData = \yii\helpers\ArrayHelper::merge(self::$jsGlobalData, $arr);
    }

    private static $layoutTranslationsList = [
        'TEXT_MY_ACCOUNT',
        'TEXT_HEADER_LOGOUT',
        'DIR_WS_CATALOG_IMAGES',
        'TEXT_HEADER_CONTACT_US',
        'TEXT_SUPPORT',
        'TEXT_ECOMMERCE_DEVELOPMENT',
        'TEXT_EVERYDAY_ACTIVITIES',
        'TEXT_FULL_MENU',
        'TEXT_CURRENT_TIME',
        'TEXT_SERVER_TIME',
        'TEXT_COPYRIGHT',
        'TEXT_COPYRIGHT_HOLBI',
        'TEXT_FOOTER_BOTTOM',
        'TEXT_FOOTER_COPYRIGHT',
        'HEADER_PHONE',
        'TEXT_ENTERED_CHARACTERS',
        'TEXT_LEFT_CHARACTERS',
        'TEXT_OVERFLOW_CHARACTERS',
        'TEXT_VIEW_SHOP',
        'SW_ON',
        'SW_OFF',
    ];

    private static $wlList = [
        'WL_ENABLED',
        'WL_COMPANY_NAME',
        'WL_COMPANY_PHONE',
        'WL_CONTACT_TEXT',
        'WL_CONTACT_WWW',
        'WL_CONTACT_URL',
        'WL_SUPPORT_URL',
        'WL_SUPPORT_TEXT',
        'WL_SUPPORT_WWW',
        'WL_SERVICES_TEXT',
    ];

    private static $dayOfWeek = [
        'dayOfWeek' => [
            TEXT_SUNDAY,
            TEXT_MONDAY,
            TEXT_TUESDAY,
            TEXT_WEDNESDAY,
            TEXT_THURSDAY,
            TEXT_FRIDAY,
            TEXT_SATURDAY
        ]
    ];
    private static $monthNames = [
        'monthNames' => [
            TEXT_JAN,
            TEXT_FAB,
            TEXT_MAR,
            TEXT_APR,
            TEXT_MAY,
            TEXT_JUN,
            TEXT_JUL,
            TEXT_AUG,
            TEXT_SEP,
            TEXT_OCT,
            TEXT_NOV,
            TEXT_DEC
        ]
    ];

    private static $editImageTranslationsList = [
        'UPLOAD_FROM_COMPUTER',
        'TEXT_OR',
        'UPLOAD_FROM_GALLERY',
        'IMAGE_UPLOAD',
        'TEXT_DROP_FILES',
        'TEXT_EDIT_IMAGE',
        'TEXT_AFTER_SAVING',
        'TEXT_CHOOSE_SIDE_COLOR',
        'IMAGE_CANCEL',
        'IMAGE_SAVE',
        'TEXT_ALIGN_BORDERS',
        'TEXT_THEMES_FOLDER',
        'TEXT_GENERAL_FOLDER',
        'TEXT_ALL_FILES',
        'TEXT_POOR_QUALITY',
        'OPTION_NONE',
        'IMAGE_APPLY',
    ];

    public static function getJsonData(){
        return addslashes(json_encode(self::$jsGlobalData));
    }

    public static function mainData()
    {

        if (WL_ENABLED && WL_COMPANY_LOGO ) {
            self::addJsData(['wl' => [
                'companLogoUrl' => Yii::$app->view->theme->baseUrl . '/img/' . WL_COMPANY_LOGO
            ]]);
        } else {
            self::addJsData(['wl' => [
                'companLogoUrl' => Yii::$app->view->theme->baseUrl . '/img/logo3.svg'
            ]]);
        }
        if (WL_ENABLED && WL_COMPANY_NAME ) {
            self::addJsData(['wl' => [
                'logoAlt' => WL_COMPANY_NAME,
                'wlFooter' => true
            ]]);
        } else {
            self::addJsData(['wl' => [
                'logoAlt' => 'logo'
            ]]);
        }
        if (WL_ENABLED && WL_SERVICES_URL && WL_SERVICES_WWW && WL_SERVICES_TEXT ) {
            self::addJsData(['wl' => [
                'servicesUrl' => WL_SERVICES_WWW
            ]]);
        } else {
            self::addJsData(['wl' => [
                'servicesUrl' => 'http://www.holbi.co.uk/ecommerce-development'
            ]]);
        }
        if (WL_ENABLED && WL_SERVICES_URL && WL_SERVICES_WWW && WL_SERVICES_TEXT ) {
            self::addJsData(['wl' => [
                'servicesText' => WL_SERVICES_TEXT
            ]]);
        } else {
            self::addJsData(['wl' => [
                'servicesText' => TEXT_ECOMMERCE_DEVELOPMENT
            ]]);
        }
        if (WL_ENABLED && WL_SUPPORT_URL && WL_SUPPORT_TEXT && WL_SUPPORT_WWW ) {
            self::addJsData(['wl' => [
                'supportUrl' => WL_SUPPORT_WWW
            ]]);
        } else {
            self::addJsData(['wl' => [
                'supportUrl' => 'http://www.holbi.co.uk/ecommerce-support'
            ]]);
        }
        if (WL_ENABLED && WL_SUPPORT_URL && WL_SUPPORT_TEXT && WL_SUPPORT_WWW ) {
            self::addJsData(['wl' => [
                'supportText' => WL_SUPPORT_TEXT
            ]]);
        } else {
            self::addJsData(['wl' => [
                'supportText' => TEXT_SUPPORT
            ]]);
        }

        if (WL_ENABLED && WL_CONTACT_URL && WL_CONTACT_TEXT && WL_CONTACT_WWW ) {
            self::addJsData(['wl' => [
                'contactUsText' => WL_CONTACT_TEXT
            ]]);
        } else {
            self::addJsData(['wl' => [
                'contactUsText' => TEXT_HEADER_CONTACT_US
            ]]);
        }
        if (WL_ENABLED && WL_CONTACT_URL && WL_CONTACT_TEXT && WL_CONTACT_WWW ) {
            self::addJsData(['wl' => [
                'contactUsUrl' => WL_CONTACT_WWW
            ]]);
        } else {
            self::addJsData(['wl' => [
                'contactUsUrl' => 'http://www.holbi.co.uk/contact-us'
            ]]);
        }

        $layoutTranslationsArr = Translation::translationsForJs(self::$layoutTranslationsList, false);
        $editImageTranslationsArr = Translation::translationsForJs(self::$editImageTranslationsList, false);
        $pageTranslationsArr = Translation::translationsForJs(Yii::$app->controller->view->translations, false);

        $tr = array_merge(
            $layoutTranslationsArr,
            $editImageTranslationsArr,
            $pageTranslationsArr,
            self::$dayOfWeek,
            self::$monthNames
        );

        $wlArr = array_merge(
            Translation::translationsForJs(self::$wlList, false),
            [
                'CONTACT_US_URL' => 'https://www.holbi.co.uk/contact-us',
                'SERVICES_URL' => 'http://www.holbi.co.uk/ecommerce-development',
            ]
        );

        self::addJsData([
            'mainUrl' => preg_replace("/(\/)+$/", '',Yii::$app->urlManager->createAbsoluteUrl('')),
            'baseUrl' => Yii::$app->urlManager->createUrl(''),
            'themeBaseUrl' => Yii::$app->view->theme->baseUrl,
            'frontendUrl' => tep_catalog_href_link(),

            'adminData' => \common\helpers\AdminBox::getData(tep_session_var('login_id')),
            'adminAccountUrl' => Yii::$app->urlManager->createUrl("adminaccount"),
            'adminLogoutUrl' => Yii::$app->urlManager->createUrl("logout"),
            'mainMenu' => json_decode(Navigation::widget(['noHtml' => true])),
            'selectedMenu' => Yii::$app->controller->selectedMenu,
            'pageTitle' => Yii::$app->controller->view->headingTitle,
            'serverTime' => date("U"),
            'serverTimeFormat' => [
                date("Y"),
                date("n") - 1,
                date("j"),
                date("G"),
                date("i"),
                date("s")
            ]
        ]);

        self::addJsData([
            'config' => [
                'META_TITLE_MAX_TAG_LENGTH' => META_TITLE_MAX_TAG_LENGTH,
                'META_DESCRIPTION_TAG_LENGTH' => META_DESCRIPTION_TAG_LENGTH
            ],
            'mainUrl' => preg_replace("/(\/)+$/", '',Yii::$app->urlManager->createAbsoluteUrl('')),
            'tr' => $tr,
            'wl' => $wlArr,
        ]);

    }
}
