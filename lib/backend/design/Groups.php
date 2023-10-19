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
use common\classes\Images as CommonImages;
use common\helpers\Html;
use common\models\DesignBoxesGroups;
use common\models\DesignBoxesGroupsCategory;
use common\models\DesignBoxesGroupsImages;
use common\models\DesignBoxesGroupsLanguages;
use backend\design\Theme;
use common\models\ThemesSettings;
use Yii;
use yii\helpers\FileHelper;
use yii\helpers\ArrayHelper;

class Groups
{
    public static function groupFilePath() {
        return DIR_FS_CATALOG . implode(DIRECTORY_SEPARATOR, ['lib', 'backend', 'design', 'groups']);
    }

    public static function synchronize()
    {
        $path = self::groupFilePath();

        FileHelper::createDirectory($path);
        chmod($path, 0755);

        $files = [];
        $filesPath = FileHelper::findFiles($path);
        if (is_array($filesPath)) {
            foreach ($filesPath as $filePath) {
                $filePathArr = explode(DIRECTORY_SEPARATOR, $filePath);
                $files[end($filePathArr)] = end($filePathArr);
            }
        }

        $groups = DesignBoxesGroups::find()->asArray()->all();
        foreach ($groups as $group) {
            if (isset($files[$group['file']]) && $files[$group['file']]) {
                unset($files[$group['file']]);
            } else {
                DesignBoxesGroups::deleteAll(['file' => $group['file']]);
            }
        }
        foreach ($files as $file) {
            if (!is_file($path . DIRECTORY_SEPARATOR . $file)) {
                continue;
            }

            $zip = new \ZipArchive();
            if (!$zip->open($path . DIRECTORY_SEPARATOR . $file, \ZipArchive::CREATE)) {
                continue;
            }

            $info = json_decode($zip->getFromName('info.json'), true);
            $images = json_decode($zip->getFromName('images.json'), true);
            if (is_array($images)) {
                foreach ($images as $key => $image) {
                    $images[$key] = 'images/' . $image;
                }
            }

            $name = explode('.', $file);
            $designBoxesGroups = new DesignBoxesGroups();
            $designBoxesGroups->file = $file;
            if (isset($info['name']) && $info['name']) {
                $designBoxesGroups->name = $info['name'];
            } else {
                $designBoxesGroups->name = $name[0];
            }
            if (isset($info['comment']) && $info['comment']) {
                $designBoxesGroups->comment = $info['comment'];
            }
            if (isset($info['page_type']) && $info['page_type']) {
                $designBoxesGroups->page_type = $info['page_type'];
            }
            if (isset($info['groupCategory']) && $info['groupCategory']) {
                $designBoxesGroups->category = $info['groupCategory'];
            }
            $designBoxesGroups->date_added = new \yii\db\Expression('now()');
            $designBoxesGroups->save();
            $groupId = $designBoxesGroups->getPrimaryKey();

            $imageFSPath = CommonImages::getFSCatalogImagesPath() . 'widget-groups' . DIRECTORY_SEPARATOR . $groupId . DIRECTORY_SEPARATOR;
            $zip->extractTo($imageFSPath, $images);
            if (is_dir($imageFSPath . 'images')){
                FileHelper::copyDirectory($imageFSPath . 'images', $imageFSPath);
                FileHelper::removeDirectory($imageFSPath . 'images');
            }

            $images = FileHelper::findFiles($imageFSPath);
            if (is_array($images) && $groupId) {
                foreach ($images as $image) {
                    $filePathArr = explode(DIRECTORY_SEPARATOR, $image);
                    $file = end($filePathArr);

                    $designBoxesGroupsImages = new DesignBoxesGroupsImages();
                    $designBoxesGroupsImages->boxes_group_id = $groupId;
                    $designBoxesGroupsImages->file = $file;
                    $designBoxesGroupsImages->save();
                }
            }

            $zip->close();
        }
    }

    public static function status()
    {
        $id = Yii::$app->request->post('id');
        $status = Yii::$app->request->post('status');

        $designBoxesGroup = DesignBoxesGroups::findOne($id);
        $designBoxesGroup->status = (int)$status;
        $designBoxesGroup->save();

        if ($designBoxesGroup->errors) {
            return var_dump($designBoxesGroup->errors);
        }
        return 'ok';
    }

    public static function save()
    {
        $id = Yii::$app->request->post('id');
        $name = Yii::$app->request->post('name');
        $page_type = Yii::$app->request->post('page_type');

        $designBoxesGroup = DesignBoxesGroups::findOne($id);
        $designBoxesGroup->name = $name;
        $designBoxesGroup->page_type = $page_type;
        $designBoxesGroup->save();

        if ($designBoxesGroup->errors) {
            return var_dump($designBoxesGroup->errors);
        }
        return 'ok';
    }

    public static function delete()
    {
        $id = Yii::$app->request->post('id');

        $path = self::groupFilePath();
        $designBoxesGroup = DesignBoxesGroups::findOne($id);
        $designBoxesGroup->file;
        unlink($path . DIRECTORY_SEPARATOR . $designBoxesGroup->file);

        DesignBoxesGroups::deleteAll(['id' => $id]);

        if ($designBoxesGroup->errors) {
            return var_dump($designBoxesGroup->errors);
        }
        return 'ok';
    }

    public static function basename($param, $suffix=null,$charset = 'utf-8')
    {
        if ( $suffix ) {
            $tmpstr = ltrim(mb_substr($param, mb_strrpos($param, DIRECTORY_SEPARATOR, 0, $charset), null, $charset), DIRECTORY_SEPARATOR);
            if ( (mb_strpos($param, $suffix, null, $charset)+mb_strlen($suffix, $charset) )  ==  mb_strlen($param, $charset) ) {
                return str_ireplace( $suffix, '', $tmpstr);
            } else {
                return ltrim(mb_substr($param, mb_strrpos($param, DIRECTORY_SEPARATOR, 0, $charset), null, $charset), DIRECTORY_SEPARATOR);
            }
        } else {
            return ltrim(mb_substr($param, mb_strrpos($param, DIRECTORY_SEPARATOR, 0, $charset), null, $charset), DIRECTORY_SEPARATOR);
        }
    }

    public static function getWidgetGroups($type)
    {
        $widgets = [];
        $widgets[] = [
            'name' => "title",
            'title' => TEXT_WIDGET_GROUPS,
            'type' => "groups"
        ];

        $designBoxesGroups = DesignBoxesGroups::find()
            ->where(['page_type' => $type, 'status' => 1])
            ->orWhere(['page_type' => '', 'status' => 1])
            ->asArray()->all();

        if (is_array($designBoxesGroups))
        foreach ($designBoxesGroups as $group) {
            $widgets[] = [
                'name' => 'group-' . $group['id'],
                'title' => $group['name'],
                'type' => "groups",
                'description' => $group['comment']
            ];
        }

        return $widgets;
    }

    public static function getWidgetGroupsCategories()
    {
        $categories = [
            'header' => [
                'name' => 'header',
                'title' => TEXT_HEADER
            ],
            'footer' => [
                'name' => 'footer',
                'title' => TEXT_FOOTER
            ],
            'pages' => [
                'name' => 'pages',
                'title' => TEXT_PAGES
            ],
            'color' => [
                'name' => 'color',
                'title' => TEXT_COLOR_SCHEME
            ],
            'font' => [
                'name' => 'font',
                'title' => TEXT_FONTS
            ],
        ];

        $pageGroups = FrontendStructure::getPageGroups();

        $categories['pages']['children'] = $pageGroups;

        $pages = FrontendStructure::getPages();

        foreach ($pages as $page) {
            ArrayHelper::setValue($categories, ['pages', 'children', $page['group'], 'children', $page['name']], $page);
        }

        $groupsCategory = DesignBoxesGroupsCategory::find()->asArray()->all();

        foreach ($groupsCategory as $category) {
            $categoryArr = explode('/', $category['parent_category']);
            $categoryPath = [];

            foreach ($categoryArr as $categoryLevel) {
                if (!$categoryLevel) continue;
                $categoryPath[] = $categoryLevel;
                $categoryPath[] = 'children';
            }
            if (count($categoryPath)) {
                ArrayHelper::setValue($categories, $categoryPath, [$category['name'] => [
                    'name' => $category['name'],
                    'title' => $category['name'],
                    'category_id' => $category['boxes_group_category_id'],
                ]]);
            } else {
                $categories[$category['name']] = [
                    'name' => $category['name'],
                    'title' => $category['name'],
                    'category_id' => $category['boxes_group_category_id'],
                ];
            }
        }

        return $categories;
    }

    public static function widgetGroupsCategoriesDropdown($name, $selection = null, $options = [])
    {
        $categories = self::getWidgetGroupsCategories();

        $content = '<option name=""></option>';
        $content .= self::widgetGroupsCategoriesLevel($categories, $selection);

        $options['name'] = $name;

        return Html::tag('select', "\n" . $content . "\n", $options);
    }

    public static function widgetGroupsCategoriesLevel($categories, $selection, $indent = '')
    {
        $options = '';
        foreach ($categories as $category) {
            if (isset($category['name']) && $category['name']) {
                if ($category['name'] == 'home') {
                    $category['name'] = 'main';
                }
                $options .= '<option value="' . $category['name'] . '"' . ($category['name'] == $selection ? ' selected' : '') . '>' . $indent . $category['title'] . '</option>';
            }
            if (isset($category['children']) && $category['children']) {
                $options .= self::widgetGroupsCategoriesLevel($category['children'], $selection, $indent . '&nbsp;&nbsp;&nbsp;');
            }
        }

        return $options;
    }

    public static function getGroup($groupId)
    {
        $languageId = Yii::$app->settings->get('languages_id');
        if (!$groupId) {
            return [];
        }

        $group = DesignBoxesGroups::find()->where(['id' => $groupId])->asArray()->one();

        if (!$group) {
            return [];
        }

        $group['images'] = DesignBoxesGroupsImages::find()->where(['boxes_group_id' => $groupId])->asArray()->one();

        $languages = [];
        $designBoxesGroupsLanguages = DesignBoxesGroupsLanguages::find()
            ->where(['boxes_group_id' => $groupId])
            ->asArray()->all();
        if (is_array($designBoxesGroupsLanguages)) {
            foreach ($designBoxesGroupsLanguages as $language) {
                $languages[$language['language_id']] = $language;
            }
        }
        if (!isset($languages[$languageId]) || !$languages[$languageId]) {
            $languages[$languageId] = [];
        }
        if ((!isset($languages[$languageId]['title']) || !$languages[$languageId]['title']) && $group['name']) {
            $languages[$languageId]['title'] = $group['name'];
        }
        if ((!isset($languages[$languageId]['description']) || !$languages[$languageId]['description']) && $group['comment']) {
            $languages[$languageId]['description'] = $group['comment'];
        }

        $group['languages'] = $languages;

        foreach (['title', 'description'] as $field) {
            $group[$field] = '';
            if (isset($languages[$languageId][$field]) && $languages[$languageId][$field]) {
                $group[$field] = $languages[$languageId][$field];
                continue;
            }

            if (!is_array($languages)) {
                continue;
            }
            foreach ($languages as $language) {
                if (isset($language[$field])) {
                    $group[$field] = $language[$field];
                    break;
                }
            }
        }

        return $group;
    }

    public static function countGroups($category)
    {
        if (!isset($category['name'])) {
            return 0;
        }

        if ($category['name'] == 'home') {
            $category['name'] = 'main';
        }
        $count = DesignBoxesGroups::find()->where(['category' => $category['name']])->count();

        if (isset($category['children']) && is_array($category['children'])) {
            foreach ($category['children'] as $subCategory) {
                if ($category['name'] == 'main' && $subCategory['name'] = 'home') {
                    continue;
                }
                $count += self::countGroups($subCategory);
            }
        }

        return $count;
    }

    public static function createGroup($group)
    {
        $fsCatalog = DIR_FS_CATALOG . implode(DIRECTORY_SEPARATOR, ['lib', 'backend', 'design', 'groups']) . DIRECTORY_SEPARATOR;

        $themeName = $group['theme_name'];

        if (substr($group['file'], -4) != '.zip'){
            $group['file'] = $group['file'] . '.zip';
        }
        if (is_file($fsCatalog . $group['file'])) {
            return json_encode([
                'error' => sprintf(FILE_ALREADY_EXISTS, $group['file']),
                'focus' => 'group[file]'
            ]);
        }

        FileHelper::createDirectory($fsCatalog);
        chmod($fsCatalog, 0755);

        $zip = new \ZipArchive();
        if ($zip->open($fsCatalog . $group['file'], \ZipArchive::CREATE) !== TRUE) {
            return json_encode(['error' => 'Error']);
        }

        if (!isset($group['pages']) || !is_array($group['pages'])) {
            return json_encode(['error' => CHOOSE_PAGES]);
        }

        $addedPages = [];
        $boxes = [];
        foreach ($group['pages'] as $page) {
            $designBoxes = \common\models\DesignBoxesTmp::find()->where([
                'block_name' => $page,
                'theme_name' => $themeName
            ])->orderBy('sort_order')->asArray()->all();

            foreach ($designBoxes as $key => $box) {
                $boxTree = Theme::blocksTree($box['id']);
                $boxTree['sort_order'] = $key;
                $boxes[$page] =$boxTree;
            }

            $themeAddedPages = ThemesSettings::find()
                ->where(['theme_name' => $themeName, 'setting_group' => 'added_page'])
                ->asArray()->all();

            foreach ($themeAddedPages as $addedPage) {
                if (\common\classes\design::pageName($addedPage['setting_value']) == $page) {
                    $addedPages[] = [
                        'setting_name' => $addedPage['setting_name'],
                        'setting_value' => $addedPage['setting_value']
                    ];
                }
            }
        }
        $json = json_encode($boxes);
        $files = [];

        $zip->addFromString ('data.json', $json);

        foreach (Theme::$themeFiles as $file){
            $path = str_replace('frontend/themes/' . $themeName . '/', '', $file);
            $path = str_replace('themes/' . $themeName . '/', 'theme/', $path);
            $zip->addFile(DIR_FS_CATALOG . $file, $path);
            $files[] = $path;
        }

        $zip->addFromString ('files.json', json_encode($files));
        $zip->addFromString ('addedPages.json', json_encode($addedPages));

        $zip->close();

        return false;
    }
}
