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

use common\classes\design;
use common\helpers\Language;
use common\models\Banners;
use common\models\BannersGroups;
use common\models\BannersGroupsSizes;
use common\models\DesignBoxesSettings;
use common\models\DesignBoxesSettingsTmp;
use common\models\Modules;
use common\models\Themes;
use common\models\ThemesSettings;
use common\models\ThemesStyles;
use common\models\ThemesStylesMain;
use yii\helpers\FileHelper;
use common\classes\Images;
use common\models\DesignBoxes;
use common\models\DesignBoxesTmp;
use Yii;

class Theme
{
    public static $themeFiles = [];
    public static $exportImportType = 'theme';

    public static function export($theme_name, $output='download')
    {
        $tmp_path = DIR_FS_CATALOG;
        $img_path = $tmp_path;
        $tmp_path = \Yii::getAlias('@runtime') . DIRECTORY_SEPARATOR;
        //$tmp_path .= DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;

        $backup_file = $theme_name;

        $zip = new \ZipArchive();
        if ($zip->open($tmp_path . $backup_file . '.zip', \ZipArchive::CREATE) === TRUE) {

            $theme = $theme_name;
            $themeFolder = '/desktop';
            for ($i = 0; $i < 2 && $theme; $i++) {

                $json = self::getThemeJson($theme);
                $themes_arr = [];
                $parents = [];
                $parents_query = tep_db_query("select theme_name, parent_theme from " . TABLE_THEMES);
                while ($item = tep_db_fetch_array($parents_query)) {
                    $themes_arr[$item['theme_name']] = $item['parent_theme'];
                }
                $parent_theme = $theme;
                $parents[] = $theme;
                while ($themes_arr[$parent_theme] ?? null) {
                    $parents[] = $themes_arr[$parent_theme];
                    $parent_theme = $themes_arr[$parent_theme];
                }
                $parents = array_reverse($parents);
                foreach ($parents as $themeParent) {
                    $rootPath = DIR_FS_CATALOG;

                    $path = $rootPath . 'themes' . DIRECTORY_SEPARATOR . $themeParent . DIRECTORY_SEPARATOR;
                    $files = self::themeFiles($themeParent);
                    foreach ($files as $item) {
                        $item = ltrim($item, '\//');
                        $item = str_replace('\\', '/', $item);
                        $zip->addFile($path . $item, $themeFolder . '/' . $item);// add css, js, images, fonts
                    }

                    $tplPath = $rootPath . 'lib' . DIRECTORY_SEPARATOR . 'frontend' . DIRECTORY_SEPARATOR;
                    $tplPath .= 'themes' . DIRECTORY_SEPARATOR . $themeParent . DIRECTORY_SEPARATOR;
                    $tplFiles = self::themeFiles($themeParent, '', true);
                    foreach ($tplFiles as $item) {
                        $item = str_replace('\\', '/', $item);
                        $zip->addFile($tplPath . $item, $themeFolder . '/tpl' . $item);// add tpl files
                    }
                }

                $zip->addFromString ($themeFolder . '/theme-tree.json', $json);

                foreach (Uploads::$archiveImages as $item){// add images from different places, by records in db
                    if (is_file($img_path . $item['old'])){
                        if (!in_array('img' . DIRECTORY_SEPARATOR . $item['new'], $files)) {
                            $item['new'] = str_replace('\\', '/', $item['new']);
                            $zip->addFile($img_path . $item['old'], $themeFolder . '/img/' . $item['new']);
                        }
                    }
                }

                $themeFolder = '/mobile';
                $theme = $theme_name . '-mobile';
            }

            if ($_SESSION['exportItems']['menus'] ?? null) {
                $menus = [];

                foreach ($_SESSION['exportItems']['menus'] as $menu => $checked) {
                    if ($checked == 'false') continue;

                    $menus[$menu] = \common\helpers\MenuHelper::menuTree($menu);
                }

                if (count($menus)) {
                    $menusJson = json_encode($menus);

                    $zip->addFromString('menu.json', $menusJson);
                }
            }

            if ($_SESSION['exportItems']['banners'] ?? null) {
                $banners = [];

                $bannerImages = [];
                foreach ($_SESSION['exportItems']['banners'] as $bannerGroup => $checked) {
                    if ($checked == 'false') continue;

                    $groupData = \common\helpers\Banner::groupData($bannerGroup);
                    $groupImages = \common\helpers\Banner::groupImages($groupData, $bannerImages);

                    $banners['groupSettings'][$bannerGroup] = \common\helpers\Banner::groupSettings($bannerGroup);
                    $banners[$bannerGroup] = $groupImages[1];
                    $bannerImages = $groupImages[0];
                }

                if (count($banners)) {
                    foreach ($bannerImages as $imageName => $imagePath) {
                        if (is_file(DIR_FS_CATALOG_IMAGES . $imagePath)) {
                            $zip->addFile(DIR_FS_CATALOG_IMAGES . $imagePath, $imagePath);
                        }
                    }

                    $bannersJson = json_encode($banners);

                    $zip->addFromString('banners/banners.json', $bannersJson);
                }
            }

            $zip->close();
            $backup_file .= '.zip';

            if ( $output=='filename' ) {
                return $tmp_path . $backup_file;
            }else {
                header('Cache-Control: none');
                header('Pragma: none');
                header('Content-type: application/x-octet-stream');
                header('Content-disposition: attachment; filename=' . $backup_file);

                readfile($tmp_path . $backup_file);
                unlink($tmp_path . $backup_file);
            }
        }

        return '';
    }

    public static function exportBlock($id, $type, $params)
    {
        self::$exportImportType = 'block';
        $fsCatalog = DIR_FS_CATALOG . implode(DIRECTORY_SEPARATOR, ['lib', 'backend', 'design', 'groups']) . DIRECTORY_SEPARATOR;

        $theme_name = $params['theme_name'];

        $designBoxes = \common\models\DesignBoxesTmp::find()->where([
            $type => $id,
            'theme_name' => $theme_name
        ])->orderBy('sort_order')->asArray()->all();

        if ($params['block-name']) {
            $themeArchive = design::pageName($params['block-name']);
        } else {
            $themeArchive = $theme_name . '_' . ($id == 'box' ? $designBoxes[0]['widget_name'] . '_' : '') . $id;
        }

        FileHelper::createDirectory($fsCatalog);
        chmod($fsCatalog, 0755);

        $name = $themeArchive;
        for ($i = 1; $i < 100 && file_exists($fsCatalog . $themeArchive . '.zip'); $i++) {
            $themeArchive = $name . '-' . $i;
        }

        $zip = new \ZipArchive();
        if ($zip->open($fsCatalog . $themeArchive . '.zip', \ZipArchive::CREATE) !== TRUE) {
            return 'Error';
        }

        $boxes = [];
        foreach ($designBoxes as $key => $box) {
            $boxTree = self::blocksTree($box['id']);
            $boxTree['sort_order'] = $key;
            $boxes[] =$boxTree;
        }
        $json = json_encode($boxes);
        $files = [];

        $zip->addFromString ('data.json', $json);

        foreach (self::$themeFiles as $file){
            $path = str_replace('frontend/themes/' . $theme_name . '/', '', $file);
            $path = str_replace('themes/' . $theme_name . '/', 'theme/', $path);
            $zip->addFile(DIR_FS_CATALOG . $file, $path);
            $files[] = $path;
        }

        $zip->addFromString ('files.json', json_encode($files));

        $info = [
            'name' => $params['block-name'] ?? '',
            'name_title' => $params['block-title'] ?? '',
            'groupCategory' => $params['group-categories'] ?? '',
            'comment' => $params['comment'] ?? '',
            'page_type' => $params['page_type'] ?? '',
        ];
        $zip->addFromString ('info.json', json_encode($info));

        if ($params['image']) {
            $zip->addFromString ('images/screenshot.png', base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $params['image'])));
            $zip->addFromString ('images.json', json_encode(['screenshot.png']));
        }

        $zip->close();
        $themeArchive .= '.zip';

        \backend\design\Groups::synchronize();

        if ($params['save-to-groups']) {
            $message = sprintf(SAVED_TO_GROUPS, $params['block-name']);
        } else {
            $message = sprintf(COMMON_CREATED, $params['block-name']);
        }

        return json_encode([
            'text' => $message,
            'filename' => $themeArchive,
            'extensionWidgets' => self::extensionWidgets()
        ]);
    }

    public static function import($themeName, $archiveFilename)
    {
        $path = DIR_FS_CATALOG . 'themes' . DIRECTORY_SEPARATOR . $themeName . DIRECTORY_SEPARATOR;
        $pathDesktop = $path . 'desktop' . DIRECTORY_SEPARATOR;
        $pathMobile = $path . 'mobile' . DIRECTORY_SEPARATOR;
        $arrMobile = '';

        $zip = new \ZipArchive();

        if ($zip->open($archiveFilename, \ZipArchive::CREATE) === TRUE) {
            if (!file_exists($path)) {
                try {
                    FileHelper::createDirectory($path, 0777);
                } catch (\Exception $ex) {
                }
                //mkdir($path);
            }
            if ($zip->extractTo($path)) {
                clearstatcache();
                $extractedFiles = FileHelper::findFiles($path, ['recursive' => true]);
                if (!is_array($extractedFiles)) $extractedFiles = [];
                foreach ($extractedFiles as $extractedFile) {
                    if (is_file($extractedFile)) {
                        @chmod($extractedFile, 0666);
                    } elseif (is_dir($extractedFile)) {
                        @chmod($extractedFile, 0777);
                    }

                }
            }


            if (!is_dir($pathDesktop)) {
                $pathDesktop = $path;
            }
            $arrDesktop = json_decode(file_get_contents($pathDesktop . 'theme-tree.json'), true);
            if (is_file($pathMobile . 'theme-tree.json')) {
                $arrMobile = json_decode(file_get_contents($pathMobile . 'theme-tree.json'), true);
            }

            if (is_file($path . 'menu.json') ) {
                $menuData = json_decode(file_get_contents($path . 'menu.json'), true);
                foreach ($menuData as $menu => $data) {
                    \common\helpers\MenuHelper::createMenu($menu, $data);
                }
            }

            if (is_file($path . 'banners' . DIRECTORY_SEPARATOR . 'banners.json') ) {
                $platformIds = self::getPlatformIds($themeName);
                $bannerData = json_decode(file_get_contents($path . 'banners/banners.json'), true);
                $bannersIds = \common\helpers\Banner::setupBanners($bannerData, $path, $platformIds);
                file_put_contents($path . 'banners-ids.json', json_encode($bannersIds));
            }

            Theme::copyFiles($themeName);
        }else{
            return false;
        }

        if (is_array($arrDesktop) && $themeName){

            Steps::importTheme(['theme_name' => $themeName]);
            Theme::importTheme($arrDesktop, $themeName);
            if ($arrMobile) {
                Theme::importTheme($arrMobile, $themeName . '-mobile');
                Steps::importTheme(['theme_name' => $themeName . '-mobile']);
            }

            \common\models\DesignBoxesCache::deleteAll(['theme_name' => $themeName]);
            \common\models\DesignBoxesCache::deleteAll(['theme_name' => $themeName . '-mobile']);

            Style::createCache($themeName);
            Style::createCache($themeName . '-mobile');

            return true;
        }

        return false;
    }

    public static function importBlock($fileName, $params, $file = '')
    {
        $pathTmp = implode(DIRECTORY_SEPARATOR, [DIR_FS_CATALOG, 'themes', $params['theme_name'], 'tmp']);

        $zip = new \ZipArchive();
        if ($zip->open($fileName, \ZipArchive::CREATE) === TRUE) {
            if (!file_exists($pathTmp)) {
                try {
                    FileHelper::createDirectory($pathTmp, 0777);
                } catch (\Exception $ex) {
                }
            }
            if ($zip->extractTo($pathTmp)) {
                clearstatcache();
            }
        } else {
            return 'Error: archive is broken';
        }


        $boxesArr = json_decode(file_get_contents($pathTmp . DIRECTORY_SEPARATOR . 'data.json'), true);
        if (!is_array($boxesArr)){
            return 'Error: data.json';
        }

        $boxIdArr = [];
        if ($boxesArr['microtime'] ?? false) {
            if ($file) {
                $boxesArr['settings'][] = [
                    'setting_name' => 'from_file',
                    'setting_value' => $file,
                ];
            }
            $boxIdArr[] = Theme::blocksTreeImport($boxesArr, $params['theme_name'], $params['block_name'], $params['sort_order']);
        } else {
            foreach ($boxesArr as $blockName => $arr) {
                if ($file) {
                    $arr['settings'][] = [
                        'setting_name' => 'from_file',
                        'setting_value' => $file,
                    ];
                }
                if (is_int($blockName)) {
                    $sortOrder = $params['sortOrder'] ?? ($params['sort_order'] + $arr['sort_order']);
                    $blockBoxes = DesignBoxesTmp::find()->where([
                        'block_name' => $params['block_name'],
                        'theme_name' => $params['theme_name'],
                    ])->andWhere(['>=', 'sort_order', $sortOrder])->all();
                    foreach ($blockBoxes as $box) {
                        $box->sort_order = $box->sort_order + 1;
                        $box->save();
                    }
                    $boxIdArr[] = Theme::blocksTreeImport($arr, $params['theme_name'], $params['block_name'], $sortOrder);
                } else {
                    $boxIdArr[] = Theme::blocksTreeImport($arr, $params['theme_name'], $blockName);
                }
            }
        }


        if (is_file($pathTmp . DIRECTORY_SEPARATOR . 'files.json')) {
            $files = json_decode(file_get_contents($pathTmp . DIRECTORY_SEPARATOR . 'files.json'), true);
            if (is_array($files)){
                foreach ($files as $file) {
                    if (!preg_match('/^lib\//', $file)){
                        continue;
                    }

                    $settingValue = $file;
                    $fileFrom = $pathTmp . DIRECTORY_SEPARATOR . $settingValue;
                    if (!is_file($fileFrom)) {
                        continue;
                    }
                    $settingValue = preg_replace ('/^lib\//', 'lib/frontend/themes/' . $params['theme_name'] . '/', $settingValue);
                    if (is_file($settingValue)) {
                        continue;
                    }

                    $destinyFolder = substr($settingValue, 0, strrpos($settingValue, DIRECTORY_SEPARATOR));

                    FileHelper::createDirectory(DIR_FS_CATALOG . $destinyFolder, 0777);

                    copy($fileFrom, DIR_FS_CATALOG . $settingValue);
                    @chmod(DIR_FS_CATALOG . $settingValue, 0666);
                }
            }
        }
        FileHelper::removeDirectory($pathTmp);

        return [$boxesArr, $boxIdArr];
    }

    public static function getPlatformIds($themeName)
    {
        $platforms = \common\models\PlatformsToThemes::find()
            ->alias('p2t')
            ->select('p2t.platform_id')
            ->innerJoin(\common\models\Themes::tableName() . ' as t', 'p2t.theme_id = t.id')
            ->where(['t.theme_name' => $themeName])
            ->asArray()
            ->all();

        $ids = [];
        foreach ($platforms as $platform) {
            $ids[] = $platform['platform_id'];
        }

        return $ids;
    }

    public static function getThemeJson($theme_name)
    {
        $theme = [];

        $query = tep_db_query("select * from " . TABLE_DESIGN_BOXES . " where theme_name = '" . tep_db_input($theme_name) . "' and block_name not like 'block-%'");
        while ($item = tep_db_fetch_array($query)){
            $theme['blocks'][] = self::blocksTree($item['id'], true);
        }

        $query = tep_db_query("select * from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . tep_db_input($theme_name) . "'");
        while ($item = tep_db_fetch_array($query)){
            if ($item['setting_group'] == 'css' && $item['setting_name'] == 'css'){

                preg_match_all("/url\([\'\"]{0,1}([^\)\'\"]+)/", $item['setting_value'], $out, PREG_PATTERN_ORDER);

                $css_img_arr = [];
                foreach ($out[1] as $img){
                    if (substr($img, 0, 2) != '//' && substr($img, 0, 4) != 'http'){
                        if (!$css_img_arr[$img]){
                            $css_img_arr[$img] = Uploads::addArchiveImages('background_image', $img);
                        }
                    }
                }
                foreach ($css_img_arr as $path => $img){
                    $item['setting_value'] = str_replace($path, $img, $item['setting_value']);
                }
            }
            $item['setting_value'] = str_replace($theme_name, '<theme_name>', $item['setting_value']);
            $theme['settings'][] = [
                'setting_group' => $item['setting_group'],
                'setting_name' => $item['setting_name'],
                'setting_value' => $item['setting_value'],
            ];
        }

        $query = tep_db_query("select * from " . TABLE_THEMES_STYLES . " where theme_name = '" . tep_db_input($theme_name) . "'");
        while ($item = tep_db_fetch_array($query)){
            $item['value'] = Uploads::addArchiveImages($item['attribute'], $item['value']);

            $vArr = Style::vArr($item['visibility']);
            foreach ($vArr as $vKey => $vItem) {
                if ($vItem > 10) {
                    $vMedia = tep_db_fetch_array(tep_db_query("select setting_value from " . TABLE_THEMES_SETTINGS . " where id = '" . $vItem . "'"));
                    $vArr[$vKey] = $vMedia['setting_value'] ?? '';
                }
            }
            $item['visibility'] = Style::vStr($vArr, true);

            $theme['styles'][] = [
                'selector' => $item['selector'],
                'attribute' => $item['attribute'],
                'value' => $item['value'],
                'visibility' => $item['visibility'],
                'media' => $item['media'],
                'accessibility' => $item['accessibility'],
            ];
        }

        $theme['main_styles'] = ThemesStylesMain::find()->where(['theme_name' => $theme_name])->asArray()->all();

        return json_encode($theme);
    }

    public static function themeFiles($theme_name, $path = '', $tpl = false)
    {
        $theme_path = DIR_FS_CATALOG;
        if ($tpl) {
            $theme_path .= 'lib' . DIRECTORY_SEPARATOR . 'frontend' . DIRECTORY_SEPARATOR;
            $theme_path .= 'themes' . DIRECTORY_SEPARATOR . $theme_name . DIRECTORY_SEPARATOR;
        } else {
            $theme_path .= 'themes' . DIRECTORY_SEPARATOR . $theme_name . DIRECTORY_SEPARATOR;
        }

        $files_arr = array();
        $arr = file_exists($theme_path . $path) ? scandir($theme_path . $path) : array();
        foreach ($arr as $item) {
            if ($item != '.' && $item != '..' && $item != 'updates' && $item != 'cache') {
                if (is_dir($theme_path . $path . DIRECTORY_SEPARATOR . $item)) {
                    $files_arr = array_merge($files_arr, self::themeFiles($theme_name, $path . DIRECTORY_SEPARATOR . $item, $tpl));
                } else {
                    $files_arr[] = $path . ($path ? DIRECTORY_SEPARATOR : '') . $item;
                }
            }
        }
        return $files_arr;
    }

    public static $widgetTranslationKeysWidgetsNames = ['Html_box', 'ClosableBox', 'SendForm', 'Tabs'];
    public static $widgetTranslationKeysSettingsNames = ['text', 'tab_1', 'tab_2', 'tab_3', 'tab_4',
        'tab_5', 'tab_6', 'tab_7', 'tab_8', 'tab_9', 'tab_10', 'title', 'success', 'text'];

    public static function widgetTranslationKeys($widget)
    {
        $translations = [];
        if (!in_array($widget['widget_name'], self::$widgetTranslationKeysWidgetsNames)) {
            return $translations;
        }
        if (isset($widget['settings']) && is_array($widget['settings']))
        foreach ($widget['settings'] as $setting) {
            if (!isset($setting['setting_name']) || !in_array($setting['setting_name'], self::$widgetTranslationKeysSettingsNames)) {
                continue;
            }
            preg_match_all('/##([A-Z\_0-9]+)##/', $setting['setting_value'], $matches);
            foreach ($matches[1] as $key) {
                $translation = \common\models\Translation::find()
                    ->where(['translation_key' => $key])
                    ->asArray()->all();
                foreach ($translation as $t) {
                    if (substr($t['translation_entity'], 0, 5) == 'admin') continue;
                    if (!isset($translations[$t['translation_entity']])) {
                        $translations[$t['translation_entity']] = [];
                    }
                    if (!isset($translations[$t['translation_entity']][$t['translation_key']])) {
                        $translations[$t['translation_entity']][$t['translation_key']] = [];
                    }
                    $code = \common\helpers\Language::get_language_code($t['language_id'], false);
                    if (!isset($translations[$t['translation_entity']][$t['translation_key']][$code])) {
                        $translations[$t['translation_entity']][$t['translation_key']][$code] = $t['translation_value'];
                    }
                }
            }
        }
        return $translations;
    }

    public static function addTranslations($translations)
    {
        if (!isset($translations) || !is_array($translations) || !count($translations)) {
            return null;
        }

        foreach ($translations as $entity => $keys) {
            foreach ($keys as $key => $languageCodes) {
                foreach ($languageCodes as $code => $translat) {
                    $language = \common\helpers\Language::get_language_id($code);
                    if (!($language['languages_id']??null)) {
                        continue;
                    }
                    $languageId = $language['languages_id'];
                    $tr = \common\models\Translation::findOne([
                        'language_id' => $languageId,
                        'translation_key' => $key,
                        'translation_entity' => $entity]);
                    if (!$tr) {
                        $translation = new \common\models\Translation();
                        $translation->language_id = $languageId;
                        $translation->translation_key = $key;
                        $translation->translation_entity = $entity;
                        $translation->translation_value = $translat;
                        $translation->save();
                    }
                }
            }
        }
    }

    public static function addCss($css, $themeName, $widgetName, $rewrite = true)
    {
        if (!isset($css) || !is_array($css) || !count($css)) {
            return null;
        }

        foreach ($css as $className => $properties) {
            if ($className == 'current') {
                $className = self::widgetNameToCssClass($widgetName);
            }

            if ($rewrite) {
                ThemesStyles::deleteAll(['accessibility' => $className, 'theme_name' => $themeName]);
            } elseif (ThemesStyles::findOne(['accessibility' => $className, 'theme_name' => $themeName])) {
                continue;
            }

            foreach ($properties as $property) {
                $property['value'] = self::importFiles($property, 'css', $themeName);
                $themesStyles = new ThemesStyles();
                $themesStyles->theme_name = (string)$themeName;
                $themesStyles->selector = (string)$property['selector'];
                $themesStyles->attribute = (string)$property['attribute'];
                $themesStyles->value = (string)$property['value'];
                $themesStyles->visibility = (string)self::getIdStyleMediaSizes($property['visibility'], $themeName);
                $themesStyles->media = (string)$property['media'];
                $themesStyles->accessibility = (string)$className;
                $themesStyles->save();
            }

            \backend\design\Style::createCache($themeName, $className);
        }
    }

    public static function getStyleMediaSizes($sizeId, $themeName)
    {
        static $size = [];
        if (!($size[$themeName] ?? null)) {
            $size[$themeName] = [];
        }

        if ( isset($size[$themeName][$sizeId]) ) {
            return $size[$themeName][$sizeId];
        }

        $sizeVal = false;

        $themesSetting = \common\models\ThemesSettings::findOne(['id' => $sizeId, 'theme_name' => $themeName]);
        if ($themesSetting) {
            $sizeVal = $themesSetting->setting_value;
        }

        if ($sizeVal) {
            $size[$themeName][$sizeId] = $sizeVal;
            return $sizeVal;
        } else {
            $size[$themeName][$sizeId] = '';
            return '';
        }
    }

    public static function getIdStyleMediaSizes($styleMediaSize, $themeName)
    {
        if (strlen($styleMediaSize) < 2) {
            return $styleMediaSize;
        }
        static $size = [];
        if (!($size[$themeName]??null)) {
            $size[$themeName] = [];
        }

        if ( isset($size[$themeName][$styleMediaSize]) ) {
            return $size[$themeName][$styleMediaSize];
        }

        $sizeId = false;

        $themesSetting = ThemesSettings::findOne([
            'theme_name' => $themeName,
            'setting_group' => 'extend',
            'setting_name' => 'media_query',
            'setting_value' => $styleMediaSize,
        ]);
        if ($themesSetting) {
            $sizeId = $themesSetting->id;
        }

        if (!$sizeId) {
            $themesSetting = new ThemesSettings();
            $themesSetting->theme_name = $themeName;
            $themesSetting->setting_group = 'extend';
            $themesSetting->setting_name = 'media_query';
            $themesSetting->setting_value = $styleMediaSize;
            $themesSetting->save();
            $sizeId = $themesSetting->getPrimaryKey();
        }

        $size[$themeName][$styleMediaSize] = $sizeId;
        return $sizeId;
    }

    public static function widgetNameToCssClass($widgetName)
    {
        $class = preg_replace('/([A-Z])/', "-\$1", $widgetName);
        $class = str_replace('\\', '-', $class);
        $class = '.w-' . $class;
        $class = str_replace('--', '-', $class);
        $class = strtolower($class);

        return $class;
    }

    public static function getCssByClass($class, $themeName)
    {
        static $response = [];
        if (!($response[$themeName] ?? null)) {
            $response[$themeName] = [];
        }

        if ( isset($response[$themeName][$class]) ) {
            return $response[$themeName][$class];
        }

        $themesStyles = \common\models\ThemesStyles::find()
            ->where(['accessibility' => $class, 'theme_name' => $themeName])
            ->asArray()->all();

        if (!$themesStyles || !is_array($themesStyles)) {
            $response[$themeName][$class] = [];
            return [];
        }

        foreach ($themesStyles as $key => $style) {
            if ((int)$style['visibility'] > 10) {
                $themesStyles[$key]['visibility'] = self::getStyleMediaSizes($style['visibility'], $style['theme_name']);
            }
            $themesStyles[$key]['value'] = self::addFiles($style, 'css', $themeName);
        }

        $response[$themeName][$class] = $themesStyles;
        return $themesStyles;
    }

    public static function getWidgetClassCss($classes, $themeName)
    {
        $classArr = explode(' ', $classes);

        $response = [];
        foreach ($classArr as $class) {
            $class = trim($class);
            if (!$class) {
                continue;
            }

            $css = self::getCssByClass('.' . $classes, $themeName);
            if ($css) {
                $response['.' . $class] = $css;
            }
        }

        return $response;
    }

    public static function extensionWidgets($widgetName = '')
    {
        static $widgets = [];

        if ($widgetName) {
            $widgetPath = explode('\\', $widgetName);

            if (count($widgetPath) > 2 || (count($widgetPath) > 1 && ctype_upper(substr($widgetPath[0], 0, 1)))) {
                if (array_search($widgetName, array_column($widgets, 'name')) === false) {
                    $status = 'no';
                    $path = DIR_FS_CATALOG . 'common/extensions/' . $widgetName;
                    if (is_dir($path)) {
                        $status = (Modules::find()->where(['code' => $widgetPath[0]])->count() ? 'installed' : 'not-installed');
                    }

                    $widgets[] = [
                        'name' => $widgetName,
                        'extension' => $widgetPath[0],
                        'status' => $status
                    ];
                }
            }
        }

        return $widgets;
    }

    public static function blocksTree($id, $images = false)
    {
        $arr = array();

        $query = tep_db_fetch_array(tep_db_query("select widget_name, widget_params, sort_order, block_name, theme_name, microtime from " . TABLE_DESIGN_BOXES_TMP . " where id = '" . (int)$id . "'"));

        if (!$query) {
            return [];
        }

        $arr['microtime'] = $query['microtime'];
        $arr['block_name'] = $query['block_name'];
        $arr['widget_name'] = $query['widget_name'];
        $arr['widget_params'] = $query['widget_params'];
        $arr['sort_order'] = $query['sort_order'];
        if (!$images) {
            self::addFilesTpl($query['widget_name'], $query['theme_name']);
            $widgetCssClass = self::widgetNameToCssClass($arr['widget_name']);
            $css['current'] = self::getCssByClass($widgetCssClass, $query['theme_name']);
        }

        self::extensionWidgets($arr['widget_name']);

        $query2 = tep_db_query("
select dbs.setting_name, dbs.setting_value, dbs.visibility, dbs.microtime, l.code
from " . TABLE_DESIGN_BOXES_SETTINGS_TMP . " dbs left join " . TABLE_LANGUAGES . " l on dbs.language_id = l.languages_id
where dbs.box_id = '" . (int)$id . "'
");
        while ($item2 = tep_db_fetch_array($query2)) {
            if ($images){
                $item2['setting_value'] = Uploads::addArchiveImages($item2['setting_name'], $item2['setting_value']);
            } else {
                $item2['setting_value'] = self::addFiles($item2, 'box', $query['theme_name']);
            }
            $vArr = Style::vArr($item2['visibility']);
            foreach ($vArr as $vKey => $vItem) {
                if ($vItem > 10) {
                    $vMedia = tep_db_fetch_array(tep_db_query("select setting_value from " . TABLE_THEMES_SETTINGS . " where id = '" . $vItem . "'"));
                    if ($vMedia) {
                        $vArr[$vKey] = $vMedia['setting_value'];
                    }
                }
            }
            $item2['visibility'] = Style::vStr($vArr, true);

            $item2['setting_value'] = str_replace($query['theme_name'], '<theme_name>', $item2['setting_value']);
            $arr['settings'][] = array(
                'microtime' => $item2['microtime'],
                'setting_name' => $item2['setting_name'],
                'setting_value' => $item2['setting_value'],
                'language_id' => ($item2['code'] ? $item2['code'] : 0),
                'visibility' => $item2['visibility']
            );

            if (!$images && $item2['setting_name'] == 'style_class') {
                $css = array_merge($css, self::getWidgetClassCss($item2['setting_value'], $query['theme_name']));
            }
        }

        if (self::$exportImportType == 'block' && $arr['widget_name'] == 'Banner' && isset($arr['settings'])) {
            $arr['banner'] = self::addBanner($arr['settings'], $query['theme_name']);
        }

        if (!$images) {
            $arr['css'] = $css;
        }

        $arr['translation'] = self::widgetTranslationKeys($arr);

        foreach (\common\helpers\Hooks::getList('design/export-block') as $filename) {
            include($filename);
        }

        if ($query['widget_name'] == 'BatchSelectedProducts') {
            $arr['settings'][] = array(
                'setting_name' => 'cross_id',
                'setting_value' => $id,
                'language_id' => 0,
                'visibility' => ''
            );
        }

        if ($query['widget_name'] == 'BlockBox' || $query['widget_name'] == 'email\BlockBox' || $query['widget_name'] == 'invoice\Container' || $query['widget_name'] == 'cart\CartTabs' || $query['widget_name'] == 'ClosableBox'){

            $query = tep_db_query("select id from " . TABLE_DESIGN_BOXES_TMP . " where block_name = 'block-" . tep_db_input($id) . "'");
            if (tep_db_num_rows($query) > 0){
                while ($item = tep_db_fetch_array($query)){
                    $arr['sub_1'][] = self::blocksTree($item['id'], $images);
                }
            }
            $query = tep_db_query("select id from " . TABLE_DESIGN_BOXES_TMP . " where block_name = 'block-" . tep_db_input($id) . "-2'");
            if (tep_db_num_rows($query) > 0){
                while ($item = tep_db_fetch_array($query)){
                    $arr['sub_2'][] = self::blocksTree($item['id'], $images);
                }
            }
            $query = tep_db_query("select id from " . TABLE_DESIGN_BOXES_TMP . " where block_name = 'block-" . tep_db_input($id) . "-3'");
            if (tep_db_num_rows($query) > 0){
                while ($item = tep_db_fetch_array($query)){
                    $arr['sub_3'][] = self::blocksTree($item['id'], $images);
                }
            }
            $query = tep_db_query("select id from " . TABLE_DESIGN_BOXES_TMP . " where block_name = 'block-" . tep_db_input($id) . "-4'");
            if (tep_db_num_rows($query) > 0){
                while ($item = tep_db_fetch_array($query)){
                    $arr['sub_4'][] = self::blocksTree($item['id'], $images);
                }
            }
            $query = tep_db_query("select id from " . TABLE_DESIGN_BOXES_TMP . " where block_name = 'block-" . tep_db_input($id) . "-5'");
            if (tep_db_num_rows($query) > 0){
                while ($item = tep_db_fetch_array($query)){
                    $arr['sub_5'][] = self::blocksTree($item['id'], $images);
                }
            }
        } elseif ($query['widget_name'] == 'Tabs'){

            for($i = 1; $i < 11; $i++) {
                $query = tep_db_query("select id from " . TABLE_DESIGN_BOXES_TMP . " where block_name = 'block-" . tep_db_input($id) . "-" . $i . "'");
                if (tep_db_num_rows($query) > 0) {
                    while ($item = tep_db_fetch_array($query)) {
                        $arr['sub_' . $i][] = self::blocksTree($item['id'], $images);
                    }
                }
            }
        } elseif ($query['widget_name'] == 'WidgetsAria'){
            $aria = DesignBoxesSettingsTmp::find()->where([
                'box_id' => $id,
                'setting_name' => 'aria_name'
            ])->asArray()->one();

            if ($aria['setting_value'] ?? false) {
                $areaBoxes = DesignBoxesTmp::find()->where([
                    'theme_name' => $query['theme_name'],
                    'block_name' => $aria['setting_value']
                ])->asArray()->all();

                foreach ($areaBoxes as $areaBox) {
                    $arr['WidgetsAria'][] = self::blocksTree($areaBox['id'], $images);
                }
            }
        }

        return $arr;
    }

    public static function importTheme($arr, $theme_name)
    {
        \common\models\DesignBoxes::deleteAll(['theme_name' => $theme_name]);
        \common\models\DesignBoxesSettings::deleteAll(['theme_name' => $theme_name]);
        \common\models\DesignBoxesTmp::deleteAll(['theme_name' => $theme_name]);
        \common\models\DesignBoxesSettingsTmp::deleteAll(['theme_name' => $theme_name]);
        \common\models\ThemesStyles::deleteAll(['theme_name' => $theme_name]);
        \common\models\ThemesSettings::deleteAll(['theme_name' => $theme_name]);

        if (is_array($arr['settings'] ?? null)) foreach ($arr['settings'] as $item) {
            if ($item['setting_group'] == 'css' && $item['setting_name'] == 'css'){
                $item['setting_value'] = str_replace("$$", 'themes/' . $theme_name . '/img/', $item['setting_value']);
            }
            $item['setting_value'] = str_replace('<theme_name>', $theme_name, $item['setting_value']);
            $sql_data_array = array(
                'theme_name' => $theme_name,
                'setting_group' => $item['setting_group'],
                'setting_name' => $item['setting_name'],
                'setting_value' => $item['setting_value'],
            );
            tep_db_perform(TABLE_THEMES_SETTINGS, $sql_data_array);
        }

        if (is_array($arr['blocks'] ?? null)) foreach ($arr['blocks'] as $item){
            self::blocksTreeImport($item, $theme_name, '', '', false, false);
            \yii\caching\TagDependency::invalidate(\Yii::$app->getCache(), 'translation');
        }

        foreach (['', 'Tmp'] as $key) {
            $sesignBoxes = '\common\models\DesignBoxes' . $key;
            $sesignBoxesSettings = '\common\models\DesignBoxesSettings' . $key;
            $crossIds = $sesignBoxesSettings::find()
                ->where(['setting_name' => 'cross_id'])
                ->all();
            if (! $crossIds) continue;
            foreach ($crossIds as $crossId) {
                $crossItems = $sesignBoxesSettings::find()
                    ->alias('s')
                    ->select('s.*')
                    ->innerJoin($sesignBoxes::tableName() . ' b', 's.box_id = b.id')
                    ->where([
                        's.setting_name' => 'batchSelectedWidget',
                        's.setting_value' => $crossId->setting_value,
                        'b.theme_name' => $theme_name
                    ])
                    ->all();
                if (!$crossItems) continue;
                foreach ($crossItems as $crossItem) {
                    $crossItem->setting_value = $crossId->box_id;
                    $crossItem->save(false);
                }
                $crossId->delete();
            }
        }

        if (is_array($arr['styles'] ?? null)) foreach ($arr['styles'] as $item) {
            if (substr($item['value'], 0, 2) == '$$'){
                $item['value'] = 'themes/' . $theme_name . '/img/' . substr_replace( $item['value'], '', 0, 2);
            }
            if (strlen($item['visibility']) > 1){

                $vArr = Style::vArr($item['visibility'], true);
                foreach ($vArr as $vKey => $vItem) {
                    if (strlen($vItem) > 1) {
                        $vis_query = tep_db_fetch_array(tep_db_query("select id from " . TABLE_THEMES_SETTINGS . " where setting_value = '" . tep_db_input($vItem) . "' and setting_name = 'media_query' and theme_name = '" . tep_db_input($theme_name) . "'"));
                        $vArr[$vKey] = $vis_query['id'];
                    } else {
                        $vArr[$vKey] = $vItem;
                    }
                }
                $item['visibility'] = Style::vStr($vArr);
            }
            $sql_data_array = array(
                'theme_name' => $theme_name,
                'selector' => $item['selector'],
                'attribute' => $item['attribute'],
                'value' => $item['value'],
                'visibility' => $item['visibility'],
                'media' => $item['media'],
                'accessibility' => $item['accessibility'],
            );
            tep_db_perform(TABLE_THEMES_STYLES, $sql_data_array);
            //tep_db_perform(TABLE_THEMES_STYLES_TMP, $sql_data_array);
        }

        if (isset($arr['main_styles']) && is_array($arr['main_styles'])) {
            ThemesStylesMain::deleteAll(['theme_name' => $theme_name]);
            foreach ($arr['main_styles'] as $style) {
                $mainStyle = new ThemesStylesMain();
                $mainStyle->theme_name = $theme_name;
                $mainStyle->name = $style['name'];
                $mainStyle->value = $style['value'];
                $mainStyle->type = $style['type'];
                $mainStyle->sort_order = $style['sort_order'];
                $mainStyle->main_style = $style['main_style'] ?? 0;
                $mainStyle->save();
            }
        }

        self::elementsSave($theme_name);
    }

    public static function blocksTreeImport($arr, $theme_name, $block_name = '', $sort_order = '', $save = false, $newMicrotime = true)
    {
        $microtime =  $newMicrotime || !isset($arr['microtime']) ? microtime(true) . rand(0, 99) : $arr['microtime'];

        if (!($arr['block_name'] ?? false)) {
            return '';
        }
        $sql_data_array = array(
            'microtime' => $microtime,
            'theme_name' => $theme_name,
            'block_name' => ($block_name ? $block_name : $arr['block_name']),
            'widget_name' => $arr['widget_name'],
            'widget_params' => $arr['widget_params'],
            'sort_order' => ($sort_order ? $sort_order : $arr['sort_order']),
        );
        tep_db_perform(TABLE_DESIGN_BOXES_TMP, $sql_data_array);
        $box_id = tep_db_insert_id();
        if ($save) {
            $sql_data_array = array_merge($sql_data_array, ['id' => $box_id]);
            tep_db_perform(TABLE_DESIGN_BOXES, $sql_data_array);
        }

        self::extensionWidgets($arr['widget_name']);

        if (isset($arr['translation'])) {
            self::addTranslations($arr['translation']);
        }
        self::addCss($arr['css'] ?? null, $theme_name, $arr['widget_name']);

        foreach (\common\helpers\Hooks::getList('design/import-block') as $filename) {
            include($filename);
        }

        $bannerSettings = false;
        if ($arr['widget_name'] == 'Banner' && isset($arr['banner']) && is_array($arr['banner'])) {
            $bannerSettings = self::applyBanners($arr['banner'], $theme_name);
        }

        if (is_array($arr['settings'] ?? null) && count($arr['settings']))
            foreach ($arr['settings'] as $item){

                if ($bannerSettings && $item['setting_name'] == 'banners_group') {
                    $item['setting_value'] = $bannerSettings['groupId'];
                }
                if ($bannerSettings && $item['setting_name'] == 'ban_id') {
                    $item['setting_value'] = $bannerSettings['bannerId'];
                }

                $language_id = 0;
                $key = true;
                if ($item['language_id'] ?? false){
                    $lan_query = tep_db_fetch_array(tep_db_query("select languages_id from " . TABLE_LANGUAGES . " where code = '" . tep_db_input($item['language_id']) . "'"));
                    if ($lan_query['languages_id'] ?? false) {
                        $language_id = $lan_query['languages_id'];
                    } else {
                        $key = false;
                    }
                }
                $visibility = '';
                if (($item['visibility'] ?? false) && strlen($item['visibility']) > 1){

                    $vArr = Style::vArr($item['visibility'], true);
                    foreach ($vArr as $vKey => $vItem) {
                        if (strlen($vItem) > 1) {
                            $vis_query = tep_db_fetch_array(tep_db_query("select id from " . TABLE_THEMES_SETTINGS . " where setting_value = '" . tep_db_input($vItem) . "' and setting_name = 'media_query' and theme_name = '" . tep_db_input($theme_name) . "'"));
                            $vArr[$vKey] = $vis_query['id'] ?? null;
                        }
                    }
                    $visibility = Style::vStr($vArr);

                } else {
                    $visibility = $item['visibility'] ?? '';
                }
                if ($key) {
                    if (substr($item['setting_value'], 0, 2) == '$$'){
                        $item['setting_value'] = 'themes/' . $theme_name . '/img/' . substr_replace( $item['setting_value'], '', 0, 2);
                    }
                    $item['setting_value'] = self::importFiles($item, 'box', $theme_name);
                    $item['setting_value'] = str_replace('<theme_name>/', $theme_name . '/', $item['setting_value']);
                    $sql_data_array = array(
                        'box_id' => $box_id,
                        'microtime' => $microtime,
                        'theme_name' => $theme_name,
                        'setting_name' => $item['setting_name'],
                        'setting_value' => $item['setting_value'],
                        'language_id' => $language_id,
                        'visibility' => $visibility,
                    );
                    tep_db_perform(TABLE_DESIGN_BOXES_SETTINGS_TMP, $sql_data_array);
                    $set_id = tep_db_insert_id();
                    if ($save) {
                        $sql_data_array = array_merge($sql_data_array, ['id' => $set_id]);
                        tep_db_perform(TABLE_DESIGN_BOXES_SETTINGS, $sql_data_array);
                    }
                }
            }

        if ($arr['widget_name'] == 'BlockBox' || $arr['widget_name'] == 'email\BlockBox' || $arr['widget_name'] == 'invoice\Container' || $arr['widget_name'] == 'cart\CartTabs' || $arr['widget_name'] == 'ClosableBox'){

            if (is_array($arr['sub_1'] ?? null) && count($arr['sub_1']) > 0){
                foreach ($arr['sub_1'] as $item){
                    self::blocksTreeImport($item, $theme_name, 'block-' . $box_id, '', $save, $newMicrotime);
                }
            }
            if (is_array($arr['sub_2'] ?? null) && count($arr['sub_2']) > 0){
                foreach ($arr['sub_2'] as $item){
                    self::blocksTreeImport($item, $theme_name, 'block-' . $box_id . '-2', '', $save, $newMicrotime);
                }
            }
            if (is_array($arr['sub_3'] ?? null) && count($arr['sub_3']) > 0){
                foreach ($arr['sub_3'] as $item){
                    self::blocksTreeImport($item, $theme_name, 'block-' . $box_id . '-3', '', $save, $newMicrotime);
                }
            }
            if (is_array($arr['sub_4'] ?? null) && count($arr['sub_4']) > 0){
                foreach ($arr['sub_4'] as $item){
                    self::blocksTreeImport($item, $theme_name, 'block-' . $box_id . '-4', '', $save, $newMicrotime);
                }
            }
            if (is_array($arr['sub_5'] ?? null) && count($arr['sub_5']) > 0){
                foreach ($arr['sub_5'] as $item){
                    self::blocksTreeImport($item, $theme_name, 'block-' . $box_id . '-5', '', $save, $newMicrotime);
                }
            }
        } elseif ($arr['widget_name'] == 'Tabs'){

            for($i = 1; $i < 11; $i++) {
                if (is_array($arr['sub_' . $i] ?? null) && count($arr['sub_1']) > 0){
                    foreach ($arr['sub_' . $i] as $item){
                        self::blocksTreeImport($item, $theme_name, 'block-' . $box_id . '-' . $i, '', $save, $newMicrotime);
                    }
                }
            }
        } elseif ($arr['widget_name'] == 'WidgetsAria' && isset($arr['settings']) && is_array($arr['settings'])){
            $areaName = '';
            foreach ($arr['settings'] as $setting){
                if ($setting['setting_name'] == 'aria_name') {
                    $areaName = $setting['setting_value'];
                    break;
                }
            }

            if (is_array($arr['WidgetsAria'] ?? null) && count($arr['WidgetsAria']) > 0){
                $boxes = DesignBoxesTmp::find()->where(['theme_name' => $theme_name, 'block_name' => $areaName])
                    ->asArray()->all();
                foreach ($boxes as $box) {
                    Theme::deleteBlock($box['id']);
                }
                DesignBoxesTmp::deleteAll(['theme_name' => $theme_name, 'block_name' => $areaName]);
                foreach ($arr['WidgetsAria'] as $item){
                    self::blocksTreeImport($item, $theme_name, $areaName, '', $save, false);
                }
            }
        }

        return $box_id;
    }

    public static function saveThemeVersion($theme_name)
    {
        $themeVersion = \common\models\ThemesSettings::findOne([
            'theme_name' => $theme_name,
            'setting_group' => 'hide',
            'setting_name' => 'theme_version',
        ]);
        if (!$themeVersion) {
            $themeVersion = new \common\models\ThemesSettings();
            $themeVersion->theme_name = $theme_name;
            $themeVersion->setting_group = 'hide';
            $themeVersion->setting_name = 'theme_version';
            $themeVersion->setting_value = 0;
        }
        $themeVersion->setting_value += 1;
        $themeVersion->save();
    }

    public static function elementsSave($theme_name)
    {
        self::saveThemeVersion($theme_name);

        tep_db_query("delete from " . TABLE_DESIGN_BOXES_SETTINGS . " where box_id in (select id from " . TABLE_DESIGN_BOXES . " where theme_name = '" . tep_db_input($theme_name) . "')");
        //the order is important (empty settings first)
        tep_db_query("delete from " . TABLE_DESIGN_BOXES . " where theme_name = '" . tep_db_input($theme_name) . "'");

        tep_db_query("DELETE db FROM " . TABLE_DESIGN_BOXES . " db INNER JOIN " . TABLE_DESIGN_BOXES_TMP . " dbt ON dbt.id=db.id WHERE dbt.theme_name='" . tep_db_input($theme_name) . "';");
        tep_db_query("DELETE db FROM " . TABLE_DESIGN_BOXES_SETTINGS . " db INNER JOIN " . TABLE_DESIGN_BOXES_SETTINGS_TMP . " dbt ON dbt.id=db.id WHERE dbt.theme_name='" . tep_db_input($theme_name) . "';");

        tep_db_query("INSERT INTO " . TABLE_DESIGN_BOXES . " SELECT * FROM " . TABLE_DESIGN_BOXES_TMP . " WHERE theme_name = '" . tep_db_input($theme_name) . "'");
        tep_db_query("INSERT INTO " . TABLE_DESIGN_BOXES_SETTINGS . " SELECT * FROM " . TABLE_DESIGN_BOXES_SETTINGS_TMP . " WHERE theme_name = '" . tep_db_input($theme_name) . "'");

    }

    public static function copyFiles($theme_name)
    {
        $path = DIR_FS_CATALOG;

        $pathTpl = $path . 'lib' . DIRECTORY_SEPARATOR . 'frontend' . DIRECTORY_SEPARATOR;
        $pathTpl .= 'themes' . DIRECTORY_SEPARATOR . $theme_name;

        $pathFiles = $path . 'themes' . DIRECTORY_SEPARATOR . $theme_name;
        $pathDesktopTmp = $pathFiles . DIRECTORY_SEPARATOR . 'desktop';
        $pathMobileTmp = $pathFiles . DIRECTORY_SEPARATOR . 'mobile';

        if (is_dir($pathDesktopTmp . DIRECTORY_SEPARATOR . 'tpl')) {

            FileHelper::copyDirectory($pathDesktopTmp . DIRECTORY_SEPARATOR . 'tpl', $pathTpl);
            FileHelper::removeDirectory($pathDesktopTmp . DIRECTORY_SEPARATOR . 'tpl');

        } elseif (is_dir($pathFiles . DIRECTORY_SEPARATOR . 'tpl')) {// if old exported theme

            FileHelper::copyDirectory($pathFiles . DIRECTORY_SEPARATOR . 'tpl', $pathTpl);
            FileHelper::removeDirectory($pathFiles . DIRECTORY_SEPARATOR . 'tpl');

        }

        if (is_dir($pathMobileTmp . DIRECTORY_SEPARATOR . 'tpl')) {

            FileHelper::copyDirectory($pathMobileTmp . DIRECTORY_SEPARATOR . 'tpl', $pathTpl . '-mobile');
            FileHelper::removeDirectory($pathMobileTmp . DIRECTORY_SEPARATOR . 'tpl');

        }

        if (is_dir($pathDesktopTmp)) {
            FileHelper::copyDirectory($pathDesktopTmp, $pathFiles);
            FileHelper::removeDirectory($pathDesktopTmp);
        }

        if (is_dir($pathMobileTmp)) {
            FileHelper::copyDirectory($pathMobileTmp, $pathFiles . '-mobile');
            FileHelper::removeDirectory($pathMobileTmp);
        }
    }


    public static function copyTheme ($theme_name, $parent_theme, $parent_theme_files = '')
    {
        set_time_limit(0);
        $id_array = array();
        $visibility_array = array();
        $visibilityStyleArray = array();

        $themes_arr = array();
        $parents = array();
        $parents_query = tep_db_query("select theme_name, parent_theme from " . TABLE_THEMES);
        while ($item = tep_db_fetch_array($parents_query)) {
            $themes_arr[$item['theme_name']] = $item['parent_theme'];
        }
        $parent_theme = $parent_theme;
        $parents[] = $parent_theme;
        if (isset($themes_arr[$parent_theme]) && is_array($themes_arr[$parent_theme])) {
            while ($themes_arr[$parent_theme]) {
                $parents[] = $themes_arr[$parent_theme];
                $parent_theme = $themes_arr[$parent_theme];
            }
        }
        $parents = array_reverse($parents);

        if ($parent_theme_files == 'copy') {
            $query = tep_db_query("select * from " . TABLE_DESIGN_BOXES . " where theme_name = '" . tep_db_input($parent_theme) . "'");
            while ($item = tep_db_fetch_array($query)) {
                $sql_data_array = array(
                    'microtime' => $item['microtime'],
                    'theme_name' => $theme_name,
                    'block_name' => $item['block_name'],
                    'widget_name' => $item['widget_name'],
                    'widget_params' => $item['widget_params'],
                    'sort_order' => $item['sort_order'],
                );
                tep_db_perform(TABLE_DESIGN_BOXES, $sql_data_array);
                $new_row_id = tep_db_insert_id();
                $sql_data_array['id'] = $new_row_id;
                tep_db_perform(TABLE_DESIGN_BOXES_TMP, $sql_data_array);

                $query2 = tep_db_query("select * from " . TABLE_DESIGN_BOXES_SETTINGS . " where box_id = '" . (int)$item['id'] . "'");
                while ($item2 = tep_db_fetch_array($query2)) {
                    if ($parent_theme_files == 'copy' && ($item2['setting_name'] == 'background_image' || $item2['setting_name'] == 'logo')) {
                        foreach ($parents as $parentItem) {
                            $item2['setting_value'] = str_replace($parentItem, $theme_name, $item2['setting_value']);
                        }
                    }
                    $sql_data_array = array(
                        'microtime' => $item2['microtime'],
                        'theme_name' => $theme_name,
                        'box_id' => $new_row_id,
                        'setting_name' => $item2['setting_name'],
                        'setting_value' => $item2['setting_value'],
                        'language_id' => $item2['language_id'],
                        'visibility' => $item2['visibility'],
                    );
                    tep_db_perform(TABLE_DESIGN_BOXES_SETTINGS, $sql_data_array);
                    $new_row_id_2 = tep_db_insert_id();
                    $sql_data_array['id'] = $new_row_id_2;
                    tep_db_perform(TABLE_DESIGN_BOXES_SETTINGS_TMP, $sql_data_array);

                    foreach (Style::vArr($item2['visibility']) as $vItem) {
                        if ($vItem > 10) {
                            $visibility_array[$new_row_id_2] = $item2['visibility'];
                            break;
                        }
                    }
                }

                $id_array[$item['id']] = $new_row_id;
            }

            $query = tep_db_query("select id, block_name from " . TABLE_DESIGN_BOXES . " where theme_name = '" . tep_db_input($theme_name) . "'");
            while ($item = tep_db_fetch_array($query)) {
                preg_match('/[a-z]-([0-9]+)/', $item['block_name'], $matches);
                if ($matches[1] ?? null) {
                    $new_block_name = str_replace($matches[1], $id_array[$matches[1]], $item['block_name']);
                    $sql_data_array = array(
                        'block_name' => $new_block_name,
                    );
                    tep_db_perform(TABLE_DESIGN_BOXES, $sql_data_array, 'update', " id = '" . $item['id'] . "'");
                    tep_db_perform(TABLE_DESIGN_BOXES_TMP, $sql_data_array, 'update', " id = '" . $item['id'] . "'");
                }
            }
        }

        $query = tep_db_query("select * from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . tep_db_input($parent_theme) . "'");
        while ($item = tep_db_fetch_array($query)){
            if ($parent_theme_files == 'copy' && (
                    $item['setting_name'] == 'css' ||
                    $item['setting_name'] == 'javascript' ||
                    $item['setting_name'] == 'font_added'
            )) {

                foreach ($parents as $parentItem) {
                    $item['setting_value'] = str_replace($parentItem, $theme_name, $item['setting_value']);
                }
            }
            $sql_data_array = array(
                'theme_name' => $theme_name,
                'setting_group' => $item['setting_group'],
                'setting_name' => $item['setting_name'],
                'setting_value' => $item['setting_value'],
            );
            tep_db_perform(TABLE_THEMES_SETTINGS, $sql_data_array);
            $newMediaId = tep_db_insert_id();

            if ($item['setting_name'] == 'media_query' && $parent_theme_files == 'copy'){
                $visibilityStyleArray[$item['id']] = $newMediaId;
                foreach ($visibility_array as $settingsId => $oldMediaId) {
                    $vArr = Style::vArr($oldMediaId);

                    foreach ($vArr as $vKey => $omi) {
                        if ($omi > 10) {
                            if ($omi == $item['id']) {
                                $vArr[$vKey] = $newMediaId;
                                $sql_data_array = array();
                                $sql_data_array['visibility'] = Style::vStr($vArr);
                                tep_db_perform(TABLE_DESIGN_BOXES_SETTINGS, $sql_data_array, 'update', " id = '" . $settingsId . "'");
                                tep_db_perform(TABLE_DESIGN_BOXES_SETTINGS_TMP, $sql_data_array, 'update', " id = '" . $settingsId . "'");
                            }
                        }
                    }
                }
            }
        }

        if ($parent_theme_files == 'copy') {
            $query = tep_db_query("select * from " . TABLE_THEMES_STYLES . " where theme_name = '" . tep_db_input($parent_theme) . "'");
        } else {
            $query = tep_db_query("select * from " . TABLE_THEMES_STYLES . " where theme_name = '" . tep_db_input($parent_theme) . "' and accessibility = '.b-bottom'");
        }
        while ($item = tep_db_fetch_array($query)) {
            $visibilityArray = explode(',', $item['visibility']);
            $newVisibilityArray = [];
            foreach ($visibilityArray as $v) {
                if ($visibilityStyleArray[$v] ?? null) {
                    $newVisibilityArray[] = $visibilityStyleArray[$v];
                } else {
                    $newVisibilityArray[] = $v;
                }
            }
            $item['visibility'] = implode(',', $newVisibilityArray);
            if ($parent_theme_files == 'copy') {
                $item['value'] = str_replace($parent_theme, $theme_name, $item['value']);
            }
            $sql_data_array = array(
                'theme_name' => $theme_name,
                'selector' => $item['selector'],
                'attribute' => $item['attribute'],
                'value' => $item['value'],
                'visibility' => $item['visibility'],
                'media' => $item['media'],
                'accessibility' => $item['accessibility'],
            );
            tep_db_perform(TABLE_THEMES_STYLES, $sql_data_array);
        }

        $mainStyles = ThemesStylesMain::find()->where(['theme_name' => $parent_theme])->all();
        if (is_array($mainStyles)) {
            foreach ($mainStyles as $style) {
                $newStyle = new ThemesStylesMain();
                $newStyle->attributes = $style->attributes;
                $newStyle->theme_name = $theme_name;
                $newStyle->save();
            }
        }

        if ($parent_theme_files == 'copy') {

            foreach ($parents as $parentItem) {
                $path = \Yii::getAlias('@webroot');
                $path .= DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
                $parent_path = $path . 'themes' . DIRECTORY_SEPARATOR . $parentItem;
                $tpl = $path . 'lib' . DIRECTORY_SEPARATOR . 'frontend' . DIRECTORY_SEPARATOR;
                $tpl_parent = $tpl . 'themes' . DIRECTORY_SEPARATOR . $parentItem;
                $tpl .= 'themes' . DIRECTORY_SEPARATOR . $theme_name;
                $path .= 'themes' . DIRECTORY_SEPARATOR . $theme_name;

                if (is_dir($parent_path)) {
                    FileHelper::copyDirectory($parent_path, $path);
                }
                if (is_dir($tpl_parent)) {
                    FileHelper::copyDirectory($tpl_parent, $tpl);
                }
            }
        } else {
            $path = \Yii::getAlias('@webroot');
            $path .= DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
            $screenshot = $path . 'themes' . DIRECTORY_SEPARATOR . $parent_theme . DIRECTORY_SEPARATOR . 'screenshot.png';
            $path .= 'themes' . DIRECTORY_SEPARATOR . $theme_name;

            if (file_exists($screenshot)) {
                if (!file_exists($path)) mkdir($path);
                copy($screenshot, $path . DIRECTORY_SEPARATOR . 'screenshot.png');
            }

        }
    }

    public static function themeRemove ($theme_name, $removeFiles = true)
    {
        tep_db_query("delete from " . TABLE_DESIGN_BOXES . " where theme_name = '" . tep_db_input($theme_name) . "'");
        tep_db_query("delete from " . TABLE_DESIGN_BOXES_TMP . " where theme_name = '" . tep_db_input($theme_name) . "'");
        tep_db_query("delete from " . TABLE_DESIGN_BOXES_SETTINGS . " where theme_name = '" . tep_db_input($theme_name) . "'");
        tep_db_query("delete from " . TABLE_DESIGN_BOXES_SETTINGS_TMP . " where theme_name = '" . tep_db_input($theme_name) . "'");

        tep_db_query("delete from " . TABLE_THEMES . " where theme_name = '" . tep_db_input($theme_name) . "'");
        tep_db_query("delete from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . tep_db_input($theme_name) . "'");
        tep_db_query("delete from " . TABLE_THEMES_STYLES . " where theme_name = '" . tep_db_input($theme_name) . "'");
        tep_db_query("delete from " . TABLE_THEMES_STYLES_CACHE . " where theme_name = '" . tep_db_input($theme_name) . "'");
        tep_db_query("delete from " . TABLE_THEMES_STEPS . " where theme_name = '" . tep_db_input($theme_name) . "'");
        tep_db_query("delete from " . TABLE_THEMES_STYLES_CACHE . " where theme_name = '" . tep_db_input($theme_name) . "'");
        ThemesStylesMain::deleteAll(['theme_name' => $theme_name]);

        $themeBackups = DIR_FS_CATALOG . 'lib'
            . DIRECTORY_SEPARATOR . 'backend'
            . DIRECTORY_SEPARATOR . 'design'
            . DIRECTORY_SEPARATOR . 'backups'
            . DIRECTORY_SEPARATOR . $theme_name;
        FileHelper::removeDirectory($themeBackups);

        if ($removeFiles) {
            $count = tep_db_fetch_array(tep_db_query("select count(*) as total from " . TABLE_THEMES . " where parent_theme = '" . tep_db_input($theme_name) . "'"));
            if ($count['total'] == 0) {
                $path = DIR_FS_CATALOG;
                $pathLib = $path . 'lib' . DIRECTORY_SEPARATOR . 'frontend' . DIRECTORY_SEPARATOR;
                $pathLib .= 'themes' . DIRECTORY_SEPARATOR . $theme_name;
                $path .= 'themes' . DIRECTORY_SEPARATOR . $theme_name;
                FileHelper::removeDirectory($pathLib);
                FileHelper::removeDirectory($path);
            }
        }
    }

    public static function getThemeTitle ($theme_name)
    {
        static $themeTitle = [];

        if ( isset($themeTitle[$theme_name]) ) {
            return $themeTitle[$theme_name];
        }

        $mobile = false;
        if (strpos($theme_name, '-mobile')) {
            $theme_name = str_replace('-mobile', '', $theme_name);
            $mobile = true;
        }

        $query = tep_db_fetch_array(tep_db_query("select title from " . TABLE_THEMES . " where theme_name = '" . tep_db_input($theme_name) . "'"));

        $themeTitle[$theme_name] = $query['title'];

        if ($mobile && defined('TEXT_MOBILE')) {
            $themeTitle[$theme_name] .= ' (' . TEXT_MOBILE . ')';
        }

        return $themeTitle[$theme_name];
    }

    public static function useMobileTheme ($theme_name)
    {

        $theme = tep_db_fetch_array(tep_db_query("select setting_value from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . tep_db_input($theme_name) . "' and setting_name = 'use_mobile_theme'"));
        if ($theme['setting_value']) {
            return true;
        }

        return false;
    }

    public static function getThemeName ($platform_id)
    {
        static $_cache = [];

        if (isset($_cache[$platform_id])) {
            return $_cache[$platform_id];
        }

        if ($platform_id) {
            $platform_config = new \common\classes\platform_config($platform_id);
            $platform_config->constant_up();
            if ($platform_config->isVirtual() || $platform_config->isMarketplace()) {
                $theme = tep_db_fetch_array(tep_db_query("select t.theme_name from platforms_to_themes AS p2t INNER JOIN themes as t ON (p2t.theme_id=t.id) where p2t.is_default = 1 and p2t.platform_id = " . (int)\common\classes\platform::defaultId()));
            } else {
                $theme = tep_db_fetch_array(tep_db_query("select t.theme_name from " . TABLE_THEMES . " t, " . TABLE_PLATFORMS_TO_THEMES . " p2t where p2t.is_default = 1 and t.id = p2t.theme_id and p2t.platform_id='" . $platform_id . "'"));
            }
        } else {
            $theme = tep_db_fetch_array(tep_db_query("select theme_name from " . TABLE_THEMES));
        }

        $_cache[$platform_id] = ($theme['theme_name'] ?? '');

        return $_cache[$platform_id];
    }


    /**
     * copy image in theme image dir and save name in db, use only with save design/settings, it use POST data
     * @param string   $name image name in db, theme_settings.setting_name
     * @return string  path and filename saved in db, 'themes/themename/img/imagename.jpg'
     */
    public static function saveThemeImage ($name)
    {
        $post = Yii::$app->request->post();

        $imageMod = \common\models\ThemesSettings::findOne([
            'theme_name' => $post['theme_name'],
            'setting_group' => 'hide',
            'setting_name' => $name,
        ]);
        if (!$imageMod) {
            $imageMod = new \common\models\ThemesSettings();
        }

        $uploadedFile = \common\helpers\Image::prepareSavingImage(
            ($imageMod->setting_value ?? ''),
            $post[$name],
            $post[$name . '_upload'],
            'themes' . DIRECTORY_SEPARATOR . $post['theme_name'] . DIRECTORY_SEPARATOR . 'img',
            false, true
        );

        $imageMod->attributes = [
            'theme_name' => $post['theme_name'],
            'setting_group' => 'hide',
            'setting_name' => $name,
            'setting_value' => $uploadedFile,
        ];
        if (!$uploadedFile) {
            $imageMod->delete();
        } else {
            $imageMod->save();
        }

        return $uploadedFile;
    }

    public static function saveFavicon ()
    {
        $uploadedFile = self::saveThemeImage('favicon');

        if (!$uploadedFile) {
            return false;
        }
        $path = \Yii::getAlias('@webroot');
        $path .= DIRECTORY_SEPARATOR;
        $path .= '..';
        $path .= DIRECTORY_SEPARATOR;
        $path .= 'themes';
        $path .= DIRECTORY_SEPARATOR;
        $path .= $_GET['theme_name'];
        $path .= DIRECTORY_SEPARATOR;
        $theme = $path;
        $path .= 'icons';
        $path .= DIRECTORY_SEPARATOR;

        if (!file_exists($path)) {
            if (!file_exists($theme)) {
                mkdir($theme);
            }
            mkdir($path);
        }

        if (!is_file(DIR_FS_CATALOG . $uploadedFile)) {
            return false;
        }

        $info = getimagesize(DIR_FS_CATALOG . $uploadedFile);
        $mime = $info['mime'];

        if ($mime == 'image/jpeg'){
            $im = @imagecreatefromjpeg(DIR_FS_CATALOG . $uploadedFile);
        } elseif ($mime == 'image/png'){
            $im = @imagecreatefrompng(DIR_FS_CATALOG . $uploadedFile);
        } elseif ($mime == 'image/gif'){
            $im = @imagecreatefromgif(DIR_FS_CATALOG . $uploadedFile);
        }
        if (!$im) {
            return false;
        }
        $w = imagesx($im);
        $h = imagesy($im);

        $icons = [
            ['size' => 57, 'name' => 'apple-icon-57x57.png'],
            ['size' => 60, 'name' => 'apple-icon-60x60.png'],
            ['size' => 72, 'name' => 'apple-icon-72x72.png'],
            ['size' => 76, 'name' => 'apple-icon-76x76.png'],
            ['size' => 114, 'name' => 'apple-icon-114x114.png'],
            ['size' => 120, 'name' => 'apple-icon-120x120.png'],
            ['size' => 144, 'name' => 'apple-icon-144x144.png'],
            ['size' => 152, 'name' => 'apple-icon-152x152.png'],
            ['size' => 180, 'name' => 'apple-icon-180x180.png'],
            ['size' => 512, 'name' => 'apple-icon-512x512.png'],
            ['size' => 192, 'name' => 'android-icon-192x192.png'],
            ['size' => 32, 'name' => 'favicon-32x32.png'],
            ['size' => 96, 'name' => 'favicon-96x96.png'],
            ['size' => 16, 'name' => 'favicon-16x16.png'],
            ['size' => 16, 'name' => 'favicon.ico'],
            ['size' => 144, 'name' => 'ms-icon-144x144.png'],
            ['size' => 36, 'name' => 'android-icon-36x36.png'],
            ['size' => 48, 'name' => 'android-icon-48x48.png'],
            ['size' => 72, 'name' => 'android-icon-72x72.png'],
            ['size' => 96, 'name' => 'android-icon-96x96.png'],
            ['size' => 144, 'name' => 'android-icon-144x144.png'],
            ['size' => 192, 'name' => 'android-icon-192x192.png'],
            ['size' => 512, 'name' => 'android-icon-512x512.png'],
        ];

        foreach ($icons as $icon){
            $l = $icon['size'];
            if ($w > $h){
                $left = round(0 - (($l * ($w/$h)) - $l) / 2);
                $top = 0;
                $width = round($l * ($w/$h));
                $height = $l;
            } else {
                $left = 0;
                $top = round(0 - (($l * ($h/$w)) - $l) / 2);
                $width = $l;
                $height = round($l * ($h/$w));
            }
            $im1 = imagecreatetruecolor($l, $l);
            imagealphablending($im1, false);
            imagesavealpha($im1, true);
            imagecopyresampled($im1, $im, $left, $top, 0, 0, $width, $height, $w, $h);
            imagepng($im1, $path . $icon['name']);
            imagedestroy($im1);
        }

        imagedestroy($im);

        return true;
    }

    public static function savePageSettings($post)
    {
        $theme_name = tep_db_prepare_input($post['theme_name']);
        $page_name = tep_db_prepare_input($post['page_name']);

        if (is_array($post['added_page_settings'] ?? null)) {
            foreach ($post['added_page_settings'] as $setting => $value) {

                $count = tep_db_fetch_array(tep_db_query("select count(*) as total from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . tep_db_input($theme_name) . "' and setting_group = 'added_page_settings' and setting_name = '" . tep_db_input($page_name) . "' and (setting_value = '" . tep_db_input($setting) . "' or setting_value like '" . tep_db_input($setting) . ":%')"));
                if ($value) {
                    $sql_data_array = array(
                        'theme_name' => $theme_name,
                        'setting_group' => 'added_page_settings',
                        'setting_name' => $page_name,
                        'setting_value' => $value == 'on' ? $setting : $setting . ':' . $value
                    );
                    if ($count['total']) {
                        tep_db_perform(TABLE_THEMES_SETTINGS, $sql_data_array, 'update', "theme_name = '" . $theme_name . "' and 	setting_group = 'added_page_settings' and	setting_name='" . $page_name . "'");
                    } else {
                        tep_db_perform(TABLE_THEMES_SETTINGS, $sql_data_array);
                    }
                } elseif ($count['total'] > 0) {
                    tep_db_query("delete from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . tep_db_input($theme_name) . "' and setting_group = 'added_page_settings' and setting_name = '" . tep_db_input($page_name) . "' and (setting_value = '" . tep_db_input($setting) . "' or setting_value like '" . tep_db_input($setting) . ":%')");
                }
            }
        }
    }

    public static function getThemePages($page_name, $theme_name)
    {
        $settings = \common\models\ThemesSettings::find()->where([
            'theme_name' => $theme_name,
            'setting_group' => 'added_page',
            'setting_name' => $page_name,
        ])->asArray()->all();

        $pages = [];
        foreach ($settings as $setting) {
            $pages[] = $setting['setting_value'];
        }

        return $pages;
    }

    public static function getPageName($blockId)
    {
        $blockName = DesignBoxesTmp::findOne(['id' => $blockId])->block_name;
        if (substr($blockName, 0, 6) != 'block-') {
            return $blockName;
        }

        $block = explode('-', $blockName);

        return self::getPageName($block[1]);
    }

    public static function themesByGroup($groupId)
    {
        $themesArray = \common\models\Themes::find()->where(['themes_group_id' => $groupId])
            ->orderBy('sort_order')->asArray()->all();
        $themes = [];
        foreach ($themesArray as $item) {
            if (
                $item['theme_name'] == \common\classes\design::pageName(BACKEND_THEME_NAME) &&
                !\common\helpers\Acl::rule(['BOX_HEADING_DESIGN_CONTROLS', 'BOX_HEADING_THEMES', 'BOX_BACKEND_THEME_EDIT'])
            ) {
                continue;
            }

            $useMobileTheme = ThemesSettings::findOne(['theme_name' => $item['theme_name'],
                'setting_name' => 'use_mobile_theme'])->setting_value ?? null;
            $action = 'design/elements';
            if ($useMobileTheme) {
                $action = 'design/choose-view';
            }
            $item['link'] = Yii::$app->urlManager->createUrl([$action, 'theme_name' => $item['theme_name']]);

            $item['parent_theme_title'] = false;
            if ($item['parent_theme']) {
                $parent = \common\models\Themes::findOne(['theme_name' => $item['parent_theme']]);
                $item['parent_theme_title'] = $parent['title'];
            }
            $themes[] = $item;
        }

        return $themes;
    }

    public static function addFilesTpl($widgetName, $themeName)
    {
        $ds = DIRECTORY_SEPARATOR;
        $widgetPath = explode('\\', $widgetName);
        $last = count($widgetPath) - 1;
        $widgetPath[$last] = preg_replace('/([A-Z])/', "-\$1", $widgetPath[$last]);
        $widgetPath[$last] = strtolower($widgetPath[$last]);
        $widgetPath[$last] = trim($widgetPath[$last], '-');

        $widgetPath = array_merge(['lib', 'frontend', 'themes', $themeName, 'boxes'], $widgetPath);
        $widget = implode($ds, $widgetPath) . '.tpl';

        $jsPath = ['lib', 'frontend', 'themes', $themeName, 'js', 'boxes'];
        $js = implode($ds, $jsPath) . $ds . str_replace('\\', $ds, $widgetName) . '.js';

        if (is_file(DIR_FS_CATALOG . $ds . $widget)) {
            self::$themeFiles[$widget] = $widget;
        }
        if (is_file(DIR_FS_CATALOG . $ds . $js)) {
            self::$themeFiles[$js] = $js;
        }

        $widgetClass = '\\frontend\\design\\boxes\\' . $widgetName;
        if (!class_exists($widgetClass)) {
            $widgetClass = '\\common\\extensions\\' . $widgetName;
        }
        if (class_exists($widgetClass) && isset($widgetClass::$files) && is_array($widgetClass::$files)) {
            foreach ($widgetClass::$files as $file) {
                $filePath = implode($ds, ['lib', 'frontend', 'themes', $themeName, $file]);
                if (is_file(DIR_FS_CATALOG . $ds . $filePath)) {
                    self::$themeFiles[$filePath] = $filePath;
                }
            }
        }
    }

    public static function addFiles($settings, $type, $themeName)
    {
        if ($type == 'box') {
            $settingName = $settings['setting_name'];
            $settingValue = $settings['setting_value'];
        } else {
            $settingName = $settings['attribute'];
            $settingValue = $settings['value'];
        }

        if (in_array($settingName, ['background_image', 'background-image', 'logo', 'image', 'file', 'poster'])) {
            if (str_contains($settingValue, 'url(')){
                $settingValue = str_replace('url(', '', $settingValue);
                $settingValue = trim($settingValue, ') \' "');
            }

            if (is_file(DIR_FS_CATALOG . DIRECTORY_SEPARATOR . $settingValue)) {
                self::$themeFiles[$settingValue] = $settingValue;
            }

            $settingValue = str_replace('themes/' . $themeName . '/', 'theme/', $settingValue);
        }

        return $settingValue;
    }

    public static function importFiles($settings, $type, $themeName)
    {
        if ($type == 'box') {
            $settingName = $settings['setting_name'];
            $settingValue = $settings['setting_value'];
        } else {
            $settingName = $settings['attribute'];
            $settingValue = $settings['value'];
        }

        if (in_array($settingName, ['background_image', 'background-image', 'logo', 'image', 'file', 'poster'])) {

            $fileFrom = DIR_FS_CATALOG . implode(DIRECTORY_SEPARATOR, ['themes', $themeName, 'tmp', $settingValue]);
            if (is_file($fileFrom)) {
                $settingValue = preg_replace ('/^theme\//', 'themes/' . $themeName . '/', $settingValue);

                $foldersArr = explode(DIRECTORY_SEPARATOR, $settingValue);
                array_pop($foldersArr);

                $path2 = DIR_FS_CATALOG;
                foreach ($foldersArr as $item) {
                    if (!$item) continue;
                    $path2 .= $item . DIRECTORY_SEPARATOR;
                    if (!file_exists($path2)) {
                        mkdir($path2, 0777);
                        @chmod($path2,0777);
                    }
                }

                $i = 1;
                $dot_pos = strrpos($settingValue, '.');
                $end = substr($settingValue, $dot_pos);
                $tempName = $settingValue;
                while (is_file(DIR_FS_CATALOG . $tempName)) {
                    $tempName = substr($settingValue, 0, $dot_pos) . '-' . $i . $end;
                    $tempName = str_replace(' ', '_', $tempName);
                    $i++;
                }

                FileHelper::createDirectory(dirname(DIR_FS_CATALOG . $tempName), 0666);
                copy($fileFrom, DIR_FS_CATALOG . $tempName);
                @chmod(DIR_FS_CATALOG . $tempName, 0666);

                \common\classes\Images::createWebp($tempName, true, '');

                $settingValue = $tempName;
            }

        }

        return $settingValue;
    }


    public static function deleteBlock($id, $desktop = false)
    {
        $designBoxes = DesignBoxesTmp::find()
            ->where(['block_name' => 'block-' . $id])
            ->orWhere(['block_name' => 'block-' . $id . '-2'])
            ->orWhere(['block_name' => 'block-' . $id . '-3'])
            ->orWhere(['block_name' => 'block-' . $id . '-4'])
            ->orWhere(['block_name' => 'block-' . $id . '-5'])
            ->asArray()->all();
        foreach ($designBoxes as $designBox) {
            DesignBoxesTmp::deleteAll(['id' => $designBox['id']]);
            DesignBoxesSettingsTmp::deleteAll(['box_id' => $designBox['id']]);
            self::deleteBlock($designBox['id']);
        }
        if ($desktop) {
            $designBoxes = DesignBoxes::find()
                ->where(['block_name' => 'block-' . $id])
                ->orWhere(['block_name' => 'block-' . $id . '-2'])
                ->orWhere(['block_name' => 'block-' . $id . '-3'])
                ->orWhere(['block_name' => 'block-' . $id . '-4'])
                ->orWhere(['block_name' => 'block-' . $id . '-5'])
                ->asArray()->all();
            foreach ($designBoxes as $designBox) {
                DesignBoxes::deleteAll(['id' => $designBox['id']]);
                DesignBoxesSettings::deleteAll(['box_id' => $designBox['id']]);
                self::deleteBlock($designBox['id'], true);
            }
        }
    }

    public static function getWidgetsInPlaceholder($placeholder, $themeName)
    {
        $designBoxes = DesignBoxesTmp::find()
            ->where(['block_name' => $placeholder, 'theme_name' => $themeName])
            ->asArray()->all();

        $blocksArr = [];
        foreach ($designBoxes as $designBox) {
            $blocksTree = self::blocksTree($designBox['id']);
            $blocksArr[] = $blocksTree;
        }
        return self::getWidgetsFromTree($blocksArr);
    }

    public static function getWidgetsFromTree($blocksTree, $fields = [])
    {
        foreach ($blocksTree as $block) {
            if ($block['widget_name'] != 'BlockBox') {
                $fields[] = $block;
            } else {
                for ($i = 1; $i < 6; $i++) {
                    if (isset($block['sub_' . $i]) && is_array($block['sub_' . $i])) {
                        $fields = self::getWidgetsFromTree($block['sub_' . $i], $fields);
                    }
                }
            }
        }

        return $fields;
    }

    public static function addBanner($settings, $themeName = '')
    {
        $bannerGroup = $type = $bannerId = '';
        foreach ($settings as $setting) {
            if ($setting['setting_name'] == 'banners_group') {
                if (preg_match("/^[0-9]+$/", $setting['setting_value'])) {
                    $bannersGroups = BannersGroups::findOne($setting['setting_value']);
                    if ($bannersGroups) {
                        $bannerGroup = $bannersGroups->banners_group;
                    } else {
                        $bannerGroup = $setting['setting_value'];
                    }
                } else {
                    $bannerGroup = $setting['setting_value'];
                }
            }
        }

        $platforms = [];
        if ($themeName) {
            $platforms = self::getPlatformIds($themeName);
        }

        $banners = [];
        $groupData = \common\helpers\Banner::groupData($bannerGroup, $platforms, true);
        $groupImages = \common\helpers\Banner::groupImages($groupData, []);

        $banners['groupSettings'][$bannerGroup] = BannersGroupsSizes::find()->alias('bgs')
            ->innerJoin(\common\models\BannersGroups::tableName() . ' bg', 'bg.id = bgs.group_id')
            ->where(['banners_group' => $bannerGroup])
            ->asArray()->all();
        $banners[$bannerGroup] = $groupImages[1];
        $bannerImages = $groupImages[0];

        if (count($banners)) {
            foreach ($bannerImages as $imageName => $imagePath) {
                if (is_file(DIR_FS_CATALOG. 'images/' . $imagePath)) {
                    //$zip->addFile(DIR_FS_CATALOG_IMAGES . $imagePath, $imagePath);
                    self::$themeFiles['images/' . $imagePath] = 'images/' . $imagePath;
                }
            }
        }
        return $banners;
    }

    public static function applyBanners($banner, $themeName)
    {
        $pathTmp = implode(DIRECTORY_SEPARATOR, [DIR_FS_CATALOG, 'themes', $themeName, 'tmp', 'images']) . DIRECTORY_SEPARATOR;

        $platformIds = self::getPlatformIds($themeName);
        [$bannerId] = \common\helpers\Banner::setupBanners($banner, $pathTmp, $platformIds, true);

        $banner = Banners::findOne(['banners_id' => $bannerId]);
        return ['groupId' => ($banner ? $banner->group_id : 0), 'bannerId' => $bannerId];
    }
}
