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

namespace backend\controllers;

use backend\design\Groups;
use backend\design\Steps;
use backend\design\Style;
use backend\design\Theme;
use backend\models\Admin;
use common\classes\Images as CommonImages;
use common\models\DesignBoxes;
use common\models\DesignBoxesCache;
use common\models\DesignBoxesGroups;
use common\models\DesignBoxesGroupsCategory;
use common\models\DesignBoxesGroupsImages;
use common\models\DesignBoxesGroupsLanguages;
use common\models\DesignBoxesSettings;
use common\models\DesignBoxesSettingsTmp;
use common\models\DesignBoxesTmp;
use common\models\Themes;
use common\models\ThemesSettings;
use common\models\ThemesStyles;
use common\models\ThemesStylesMain;
use common\models\ThemesStylesCache;
use Yii;
use yii\helpers\ArrayHelper;
use backend\design\Uploads;
use backend\design\FrontendStructure;
use yii\helpers\FileHelper;

/**
 *
 */
class DesignGroupsController extends Sceleton {

    public $acl = ['BOX_HEADING_DESIGN_CONTROLS', 'BOX_HEADING_THEMES'];
    public $designerMode = '';
    public $designerModeTitle = '';

    function __construct($id,$module=null) {
        \common\helpers\Translation::init('admin/design');

        $admin = new Admin;
        $this->designerMode = $admin->getAdditionalData('designer_mode');
        switch ($this->designerMode) {
            case 'advanced': $this->designerModeTitle = EDIT_MODE . ': ' . ADVANCED_MODE; break;
            case 'expert': $this->designerModeTitle = EDIT_MODE . ': ' . EXPERT_MODE; break;
            default: $this->designerModeTitle = EDIT_MODE . ': ' . BASIC_MODE;
        }

        return parent::__construct($id,$module);
    }

    public function actionIndex()
    {
        $this->selectedMenu = array('design_controls', 'design/themes');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('design/groups'), 'title' => BOX_HEADING_THEMES);
        $this->view->headingTitle = BOX_HEADING_THEMES;

        $this->topButtons[] = '<span class="btn btn-primary btn-add-group">' . TEXT_IMPORT . '</span>';
        $this->topButtons[] = '<span class="btn btn-primary btn-add-group-category">' . TEXT_CREATE_NEW_CATEGORY . '</span>';
        $this->topButtons[] = '<span class="btn btn-primary btn-create-group">' . CREATE_GROUP . '</span>';
        $this->topButtons[] = '<span class="mode-title">' . $this->designerModeTitle . '</span>';

        $row = Yii::$app->request->get('row');
        $category = Yii::$app->request->get('category');

        Groups::synchronize();

        \backend\design\Data::addJsData([
            'widgetGroupsCategories' => Groups::getWidgetGroupsCategories(),
        ]);

        return $this->render('index.tpl', [
            'menu' => 'groups',
            'theme_name' => Yii::$app->request->get('theme_name'),
            'row' => $row,
            'category' => $category,
            'designer_mode' => $this->designerMode,
        ]);
    }

    public function actionList()
    {
        $languages_id = \Yii::$app->settings->get('languages_id');
        $draw = Yii::$app->request->get('draw', 1);
        $start = Yii::$app->request->get('start', 0);
        $length = Yii::$app->request->get('length', 10);
        $search = Yii::$app->request->get('search');
        if( $length == -1 ) $length = 10000;
        $keywords = '';
        if ($search) {
            $keywords = $search['value'];
        }

        $formFilter = Yii::$app->request->get('filter');
        parse_str($formFilter, $output);

        $category = $output['category'];

        $responseList = [];
        $categoriesList = Groups::getWidgetGroupsCategories();

        $currentCategory = '';
        if ($category) {
            $categories = explode('/', $category);
            foreach ($categories as $_category) {
                $categoriesList = ArrayHelper::getValue($categoriesList, [$_category, 'children'], []);
            }

            $currentCategory = end($categories);
        }

        if ($currentCategory == 'home') {
            $currentCategory = 'main';
        }

        $groups = DesignBoxesGroups::find()->alias('g')
            ->leftJoin(DesignBoxesGroupsLanguages::tableName() . ' gl',
                'gl.boxes_group_id = g.id and gl.language_id = ' . $languages_id)
            ->where(['category' => $currentCategory])
            ->asArray()->all();

        if (is_array($groups)) {
            foreach ($groups as $group) {
                if (!$group['name']) {
                    continue;
                }

                $img = DesignBoxesGroupsImages::find()->where(['boxes_group_id' => $group['id']])->asArray()->one();

                $imageFSPath = CommonImages::getFSCatalogImagesPath() . 'widget-groups' . DIRECTORY_SEPARATOR . $group['id'] . DIRECTORY_SEPARATOR;
                $imageWSPath = CommonImages::getWSCatalogImagesPath() . 'widget-groups' . DIRECTORY_SEPARATOR . $group['id'] . DIRECTORY_SEPARATOR;
                if (isset($img['file']) && is_file($imageFSPath . $img['file'])) {
                    $image = '<img src="' . $imageWSPath . $img['file'] . '">';
                } else {
                    $image = '<div class="no-image"></div>';
                }
                $responseList[] = [
                    '<div class="double-click image-cell" data-id="' . $group['id'] . '">
                        ' . $image . '
                     </div>',
                    '<div class="double-click name-cell group-name" data-id="' . $group['id'] . '">' . (isset($group['title']) && $group['title'] ? $group['title'] : $group['name']) . '</div>',
                    '<div class="double-click file-cell" data-id="' . $group['id'] . '">' . $group['file'] . '</div>',
                    '<div class="double-click type-cell" data-id="' . $group['id'] . '">' . $group['page_type'] . '</div>',
                    '<div class="double-click status-cell" data-id="' . $group['id'] . '">
                        <input type="checkbox" class="group-status" name="status[' . $group['id'] . ']" value="' . $group['id'] . '"' . ($group['status'] ? ' checked' : '') . '>
                     </div>',
                ];
            }
        }

        $response = array(
            'draw' => $draw,
            'recordsTotal' => count($responseList),
            'recordsFiltered' => count($responseList),
            'data' => $responseList
        );
        echo json_encode($response);
    }

    public function actionCategoriesList()
    {
        $languages_id = \Yii::$app->settings->get('languages_id');
        $request = Yii::$app->request->get();

        $category = $request['category'];

        $responseList = [];
        $categoriesList = Groups::getWidgetGroupsCategories();

        $currentCategory = '';
        if ($category) {
            $categories = explode('/', $category);
            foreach ($categories as $_category) {
                $categoriesList = ArrayHelper::getValue($categoriesList, [$_category, 'children'], []);
            }

            $currentCategory = end($categories);
        }

        if ($currentCategory == 'home') {
            $currentCategory = 'main';
        }

        $groupsCount = DesignBoxesGroups::find()->where(['category' => $currentCategory])->count();

        if (is_array($categoriesList)) {
            foreach ($categoriesList as $category) {
                if (!isset($category['name']) || !$category['name']) {
                    continue;
                }
                $count = Groups::countGroups($category);
                if (!$count && !isset($request['show_empty'])) {
                    continue;
                }
                if ($currentCategory == 'home' && $category['name'] == 'home') {
                    continue;
                }
                $responseList[] = [
                    'name' => $category['name'],
                    'title' => $category['title'],
                    'count' => $count,
                ];
            }
        }

        echo json_encode(['categories' => $responseList, 'groupsCount' => $groupsCount]);
    }

    public function actionUpload()
    {
        if (isset($_FILES['file'])) {
            $path = Groups::groupFilePath();

            $i = 1;
            $dot_pos = strrpos($_FILES['file']['name'], '.');
            $end = substr($_FILES['file']['name'], $dot_pos);
            $tempName = $_FILES['file']['name'];
            while (is_file($path . DIRECTORY_SEPARATOR . $tempName)) {
                $tempName = substr($_FILES['file']['name'], 0, $dot_pos) . '-' . $i . $end;
                $tempName = str_replace(' ', '_', $tempName);
                $i++;
            }
            $uploadFile = $path . DIRECTORY_SEPARATOR . $tempName;

            if ( !is_writeable(dirname($path)) ) {
                $response = ['status' => 'error', 'text'=> 'Directory "' . $path . '" not writeable'];
            } elseif (!is_uploaded_file($_FILES['file']['tmp_name']) || filesize($_FILES['file']['tmp_name'])==0) {
                $response = ['status' => 'error', 'text'=> 'File upload error'];
            } else {
                if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadFile)) {
                    $text = '';
                    $response = ['status' => 'ok', 'text' => $text];
                } else {
                    $response = ['status' => 'error'];
                }
            }

            \backend\design\Groups::synchronize();
        }
        echo json_encode($response);
    }

    public function actionEdit()
    {
        $groupId = Yii::$app->request->get('group_id', 0);
        $rowId = Yii::$app->request->get('row_id', 0);
        $category = Yii::$app->request->get('category', '');
        $languageId = Yii::$app->settings->get('languages_id');

        if (!$groupId) {
            $groupId = DesignBoxesGroups::find()->max('id') + 1;
            return $this->redirect(Yii::$app->urlManager->createUrl(['design-groups/edit', 'group_id' => $groupId, 'category' => $category, 'new_group' => 1]));
        }
        $group = Groups::getGroup($groupId);


        $this->selectedMenu = ['design_controls', 'design/themes'];
        $this->navigation[] = ['title' => 'Widget group: "' . ArrayHelper::getValue($group, ['languages', $languageId, 'title']) . '"'];

        $this->topButtons[] = '<span class="btn btn-confirm btn-save">' . IMAGE_SAVE . '</span>';
        $this->topButtons[] = '<a href="' . Yii::$app->urlManager->createUrl(['design-groups/view', 'row_id' => $rowId, 'category' => $category, 'group_id' => $groupId]) . '" class="btn btn-primary">' . IMAGE_VIEW . '</a>';
        $this->topButtons[] = '<a href="' . Yii::$app->urlManager->createUrl(['design-groups', 'row_id' => $rowId, 'category' => $category]) . '" class="btn">' . IMAGE_BACK . '</a>';

        $group['categoryDropdown'] = Groups::widgetGroupsCategoriesDropdown('group[category]', ($group['category']??''), ['class' => 'form-control']);

        $pageTypesArr = ['main' => 'main'];
        $pageTypes = FrontendStructure::getPageTypes();
        foreach ($pageTypes as $type => $cont) {
            if ($type == 'main') {
                $type = 'index';
            }
            $pageTypesArr[$type] = $type;
        }
        $group['typesDropdown'] = \common\helpers\Html::dropDownList('group[page_type]', ($group['page_type'] ?? ''), $pageTypesArr, ['class' => 'form-control']);

        $group['images'] = DesignBoxesGroupsImages::find()->select('file')
            ->where(['boxes_group_id' => $groupId])->asArray()->all();

        $themes = Themes::find()
            ->where(['not in', 'theme_name', \common\classes\design::pageName(BACKEND_THEME_NAME)])
            ->asArray()->all();

        return $this->render('edit.tpl', [
            'groupId' => $groupId,
            'group' => $group,
            'images' => [],
            'languages' => \common\helpers\Language::get_languages(),
            'new' => true,
            'backUrl' => Yii::$app->urlManager->createUrl(['design-groups', 'row_id' => $rowId, 'category' => $category]),
            'themes' => $themes,
            'new_group' => Yii::$app->request->get('new_group', 0),
        ]);
    }

    public function actionView()
    {
        $groupId = Yii::$app->request->get('group_id', 0);
        $rowId = Yii::$app->request->get('row_id', 0);
        $category = Yii::$app->request->get('category', 0);
        $languageId = Yii::$app->settings->get('languages_id');

        $group = Groups::getGroup($groupId);

        $this->selectedMenu = ['design_controls', 'design/themes'];
        $this->navigation[] = ['title' => 'Widget group: "' . ArrayHelper::getValue($group, ['languages', $languageId, 'title']) . '"'];

        $this->topButtons[] = '<a href="' . Yii::$app->urlManager->createUrl(['design-groups/edit', 'row_id' => $rowId, 'category' => $category, 'group_id' => $groupId]) . '" class="btn btn-primary">' . IMAGE_EDIT . '</a>';
        $this->topButtons[] = '<a href="' . Yii::$app->urlManager->createUrl(['design-groups', 'row_id' => $rowId, 'category' => $category]) . '" class="btn">' . IMAGE_BACK . '</a>';

        $group['images'] = DesignBoxesGroupsImages::find()->select('file')
            ->where(['boxes_group_id' => $groupId])->asArray()->all();

        return $this->render('view.tpl', [
            'groupId' => $groupId,
            'group' => $group,
            'imagesCount' => count($group['images']),
            'languages' => \common\helpers\Language::get_languages(),
            'new' => true,
            'languageId' => $languageId,
            'backUrl' => Yii::$app->urlManager->createUrl(['design-groups', 'row_id' => $rowId, 'category' => $category]),
            'themes' => \common\models\Themes::find()->asArray()->all()
        ]);
    }

    public function actionGroupBar()
    {
        $this->layout = false;
        $groupId = Yii::$app->request->get('group_id', 0);
        $rowId = Yii::$app->request->get('row_id', 0);
        $category = Yii::$app->request->get('category', 0);
        $categoryId = Yii::$app->request->get('category_id', 0);

        $this->topButtons[] = '<span class="mode-title">' . $this->designerModeTitle . '</span>';

        return $this->render('group-bar.tpl', [
            'groupId' => $groupId,
            'group' => Groups::getGroup($groupId),
            'rowId' => $rowId,
            'category' => $category,
            'categoryId' => $categoryId,
        ]);
    }

    public function actionGroupCategoryBar()
    {
        $this->layout = false;
        $groupId = Yii::$app->request->get('group_id', 0);
        $rowId = Yii::$app->request->get('row_id', 0);
        $category = Yii::$app->request->get('name', 0);
        $title = Yii::$app->request->get('title', 0);
        $categoryId = Yii::$app->request->get('category_id', 0);

        $this->topButtons[] = '<span class="mode-title">' . $this->designerModeTitle . '</span>';

        return $this->render('group-category-bar.tpl', [
            'groupId' => $groupId,
            'group' => Groups::getGroup($groupId),
            'rowId' => $rowId,
            'category' => $category,
            'title' => $title,
            'categoryId' => $categoryId,
        ]);
    }

    public function actionSave()
    {
        $this->layout = false;
        $groupId = Yii::$app->request->post('group_id', 0);
        $group = Yii::$app->request->post('group', []);
        $languageId = \Yii::$app->settings->get('languages_id');
        $filePath = Groups::groupFilePath() . DIRECTORY_SEPARATOR;
        $imagePath = CommonImages::getFSCatalogImagesPath() . 'widget-groups' . DIRECTORY_SEPARATOR . $groupId . DIRECTORY_SEPARATOR;
        $newGroup = false;

        $infoContent = [];

        $designBoxesGroups = DesignBoxesGroups::findOne(['id' => $groupId]);
        if (!$designBoxesGroups) {
            $designBoxesGroups = new DesignBoxesGroups();
            if ($groupId) {
                $designBoxesGroups->id = $groupId;
            }
            $designBoxesGroups->date_added = new \yii\db\Expression('NOW()');

            $error = Groups::createGroup($group);
            if ($error) {
                return json_encode($error);
            }
            $newGroup = true;
        }

        $name = ArrayHelper::getValue($group, ['languages', $languageId, 'title'], false);
        if ($name) {
            $designBoxesGroups->name = $name;
            $infoContent['name'] = $name;
        }
        $comment = ArrayHelper::getValue($group, ['languages', $languageId, 'description'], false);
        if ($comment) {
            $designBoxesGroups->comment = $comment;
            $infoContent['comment'] = $comment;
        }
        if ($designBoxesGroups->file != $group['file']) {
            if (substr($group['file'], -4) != '.zip'){
                $group['file'] = $group['file'] . '.zip';
            }
            if (is_file($filePath . $group['file']) && !$newGroup) {
                return json_encode([
                    'error' => sprintf('File "%s" already exists, please enter other name', $group['file']),
                    'focus' => 'group[file]'
                ]);
            }
            if (is_file($filePath . $designBoxesGroups->file)) {
                rename($filePath . $designBoxesGroups->file, $filePath . $group['file']);
            }
            $designBoxesGroups->file = $group['file'];
        }
        $designBoxesGroups->page_type = $group['page_type'];
        $infoContent['page_type'] = $group['page_type'];
        $designBoxesGroups->status = $group['status'] ? $group['status'] : 0;
        $designBoxesGroups->category = $group['category'];
        $infoContent['groupCategory'] = $group['category'];
        $designBoxesGroups->save();
        $groupId = $designBoxesGroups->getPrimaryKey();

        $infoContent['languages'] = [];
        $languages = \common\helpers\Language::get_languages();
        foreach ($languages as $language) {
            $designBoxesGroupsLanguages = DesignBoxesGroupsLanguages::findOne([
                'boxes_group_id' => $groupId,
                'language_id' => $language['id'],
            ]);
            if (!$designBoxesGroupsLanguages) {
                $designBoxesGroupsLanguages = new DesignBoxesGroupsLanguages();
                $designBoxesGroupsLanguages->boxes_group_id = $groupId;
                $designBoxesGroupsLanguages->language_id = $language['id'];
            }
            $designBoxesGroupsLanguages->title = ArrayHelper::getValue($group, ['languages', $language['id'], 'title'], '');
            $designBoxesGroupsLanguages->description = ArrayHelper::getValue($group, ['languages', $language['id'], 'description'], '');
            $designBoxesGroupsLanguages->save();
            if ($designBoxesGroupsLanguages->title || $designBoxesGroupsLanguages->description) {
                $infoContent['languages'][$language['code']] = [
                    'title' => $designBoxesGroupsLanguages->title,
                    'description' => $designBoxesGroupsLanguages->description,
                ];
            }
        }


        $infoContent['images'] = [];
        $oldImages = DesignBoxesGroupsImages::find()
            ->where(['boxes_group_id' => $groupId])->asArray()->all();

        if (is_array($oldImages)) {
            foreach ($oldImages as $key => $oldImage) {
                $key = array_search($oldImage['file'], $group['images']);
                if ($key === false) {
                    $file = $imagePath . $oldImage['file'];
                    if (is_file($file)) {
                        unlink($file);
                    }
                    DesignBoxesGroupsImages::deleteAll(['boxes_group_image_id' => $oldImage['boxes_group_image_id']]);
                } else {
                    $infoContent['images'][] = $group['images'][$key];
                    unset($group['images'][$key]);
                }
            }
        }

        if (isset($group['images']) && is_array($group['images'])) {
            foreach ($group['images'] as $image) {
                $designBoxesGroupsImages = new DesignBoxesGroupsImages();
                $designBoxesGroupsImages->file = $image;
                $designBoxesGroupsImages->save();
                $infoContent['images'][] = $image;
            }
        }

        if (isset($group['image_upload']) && is_array($group['image_upload'])) {
            foreach ($group['image_upload'] as $image) {
                if ($imageName = Uploads::move($image, DIR_WS_IMAGES . 'widget-groups' . DIRECTORY_SEPARATOR . $groupId . DIRECTORY_SEPARATOR, false)) {
                    $designBoxesGroupsImages = new DesignBoxesGroupsImages();
                    $designBoxesGroupsImages->file = $imageName;
                    $designBoxesGroupsImages->boxes_group_id = $groupId;
                    $designBoxesGroupsImages->save();
                    $infoContent['images'][] = $imageName;
                }
            }
        }


        chmod($filePath . $group['file'], 0755);
        $zip = new \ZipArchive();
        if ($zip->open($filePath . $group['file'], \ZipArchive::CREATE)) {
            $zip->deleteName('images/');
            $zip->deleteName('info.json');
            $zip->addFromString ('info.json', json_encode($infoContent));

            foreach ($infoContent['images'] as $image) {
                if (is_file($imagePath . $image)) {
                    $zip->addFile($imagePath . $image, 'images/' . $image);
                }
            }
            $zip->close();
        }


        $successMessage = 'Saved';
        return json_encode(['text' => $successMessage, 'html' => $this->actionEdit()]);
    }

    public function actionDeleteGroup()
    {
        $groupId = Yii::$app->request->post('groupId');

        $group = DesignBoxesGroups::findOne(['id' => $groupId]);

        if (!$group) {
            return json_encode(['error' => GROUP_NOT_FOUND]);
        }

        $filePath = Groups::groupFilePath();

        if (is_file($filePath . DIRECTORY_SEPARATOR . $group->file)) {
            unlink($filePath . DIRECTORY_SEPARATOR . $group->file);
        }

        Groups::synchronize();

        return json_encode(['text' => TEXT_REMOVED]);
    }

    public function actionDeleteCategory()
    {
        $categoryId = Yii::$app->request->post('categoryId');

        DesignBoxesGroupsCategory::deleteAll(['boxes_group_category_id' => $categoryId]);

        return json_encode(['text' => TEXT_REMOVED]);
    }

    public function actionSwitchStatus()
    {
        $this->layout = false;
        $groupId = Yii::$app->request->post('groupId');
        $status = Yii::$app->request->post('status');

        $group = DesignBoxesGroups::findOne(['id' => $groupId]);

        if (!$group) {
            return json_encode(['error' => GROUP_NOT_FOUND]);
        }

        $group->status = $status;
        $group->save();

        return json_encode(['text' => MESSAGE_SAVED]);
    }

    public function actionWizard()
    {
        $this->selectedMenu = array('design_controls', 'design/themes');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('design/groups'), 'title' => 'Create theme');
        $this->view->headingTitle = 'Create theme';

        $themeName = Yii::$app->request->get('theme_name', '');
        $languageId = Yii::$app->settings->get('languages_id');

        $imageFSPath = CommonImages::getFSCatalogImagesPath() . 'widget-groups' . DIRECTORY_SEPARATOR;
        $imageWSPath = CommonImages::getWSCatalogImagesPath() . 'widget-groups' . DIRECTORY_SEPARATOR;

        $groupLists = [
            ['title' => TEXT_HEADER, 'category' => 'header', 'multiSelect' => false],
            ['title' => TEXT_FOOTER, 'category' => 'footer', 'multiSelect' => true],
            ['title' => TEXT_HOME, 'category' => 'main', 'multiSelect' => true],
            ['title' => TEXT_PRODUCT, 'category' => 'product', 'multiSelect' => true],
        ];

        $groupsCategories = DesignBoxesGroupsCategory::find()->asArray()->all();
        foreach ($groupsCategories as$groupsCategory) {
            $groupLists[] = [
                'title' => $groupsCategory['name'],
                'category' => $groupsCategory['name'],
                'multiSelect' => false
            ];
        }

        $groupLists[] = [
            'title' => TEXT_COLOR_SCHEME,
            'category' => 'color',
            'multiSelect' => false
        ];
        $groupLists[] = [
            'title' => TEXT_FONTS,
            'category' => 'font',
            'multiSelect' => false
        ];
        $filePath = Groups::groupFilePath() . DIRECTORY_SEPARATOR;

        foreach ($groupLists as $categoryKey => $category) {
            $list = DesignBoxesGroups::find()->alias('g')
                ->leftJoin(DesignBoxesGroupsLanguages::tableName() . ' gl', 'g.id = gl.boxes_group_id and gl.language_id = ' . $languageId)
                ->where(['category' => $category['category']])
                ->asArray()->all();

            if ($list && is_array($list)) {
                foreach ($list as $itemKey => $item) {
                    $images = DesignBoxesGroupsImages::find()->where(['boxes_group_id' => $item['id']])->asArray()->all();
                    foreach ($images as $imageKey => $image) {
                        if (is_file($imageFSPath . $item['id'] . DIRECTORY_SEPARATOR . $image['file'])) {
                            $images[$imageKey]['image'] = $imageWSPath . $item['id'] . DIRECTORY_SEPARATOR . $image['file'];
                        } else {
                            unset($images[$imageKey]);
                        }
                    }
                    $list[$itemKey]['images'] = $images;
                }

                if ($category['category'] == 'color') {
                    foreach ($list as $itemKey => $item) {

                        $colors = [];
                        $zip = new \ZipArchive();
                        if ($zip->open($filePath . $item['file'], \ZipArchive::CREATE)) {
                            $json = $zip->getFromName('data.json');
                            $groupData = json_decode($json, true);
                            $zip->close();

                            if (!is_array($groupData)) continue;

                            foreach ($groupData as $color) {
                                if ($color['main_style'] ?? false) {
                                    $colors[$color['value']][] = $color['name'];
                                }
                            }
                        }

                        $list[$itemKey]['colors'] = $colors;
                    }
                }

                $groupLists[$categoryKey]['list'] = $list;

                $filesQuery = DesignBoxesTmp::find()->alias('b')
                    ->select(['bs.setting_value'])->distinct()
                    ->leftJoin(DesignBoxesSettingsTmp::tableName() . ' bs',
                        "b.id = bs.box_id")
                    ->where([
                        'b.theme_name' => $themeName,
                        'b.block_name' => $category['category'],
                        'bs.setting_name' => 'from_file'
                    ])
                    ->asArray()->all();
                $files = [];
                foreach ($filesQuery as $file) {
                    $files[] = $file['setting_value'];
                }
                $groupLists[$categoryKey]['files'] = $files;

            } else {
                unset($groupLists[$categoryKey]);
            }
        }

        $themeTitle = '';
        if ($themeName) {
            $theme = Themes::findOne(['theme_name' => $themeName]);
            if ($theme) {
                $themeTitle = $theme->title;
            }
        }

        return $this->render('wizard.tpl', [
            'themeName' => $themeName,
            'theme_name' => $themeName,
            'themeTitle' => $themeTitle,
            'group_id' => Yii::$app->request->get('group_id', 0),
            'groupLists' => $groupLists,
            'designer_mode' => $this->designerMode,
            'menu' => 'wizard',
        ]);
    }

    public function actionCreateTheme()
    {
        $title = Yii::$app->request->post('title');
        $groupId = Yii::$app->request->post('group_id', 0);

        $themeName = \common\classes\design::pageName($title);
        if (in_array($themeName, ['new_theme', 'origin'])) {
            return json_encode(['error' => 'This name reserved for the system']);
        }

        if (Themes::findOne(['theme_name' => $themeName])) {
            return json_encode(['error' => THEME_ALREADY_EXISTS]);
        }

        $ts = ThemesSettings::find()
            ->where(['theme_name' => 'origin', 'setting_name' => 'block_copy_theme'])
            ->asArray()->one();
        if ($ts['setting_value'] ?? false){
            if ($ts['setting_value'] + 600 > time()) {
                return json_encode(['error' => SYSTEM_NOT_READY]);
            } else {
                ThemesSettings::deleteAll(['setting_name' => 'block_copy_theme']);
            }
        }

        $themes = new Themes();
        $themes->theme_name = $themeName;
        $themes->title = $title;
        $themes->description = '';
        $themes->install = 1;
        $themes->is_default = 0;
        $themes->sort_order = Themes::find()->max('sort_order') + 1;
        $themes->parent_theme = '0';
        $themes->themes_group_id = $groupId;
        $themes->save();

        if ($themes->errors) {
            return json_encode(['error' => $themes->errors]);
        }

        $themeSetting = new ThemesSettings();
        $themeSetting->theme_name = 'origin';
        $themeSetting->setting_group = 'hide';
        $themeSetting->setting_name = 'block_copy_theme';
        $themeSetting->setting_value = (string)time();
        $themeSetting->save();

        DesignBoxes::updateAll(['theme_name' => $themeName], ['theme_name' => 'new_theme']);
        DesignBoxesTmp::updateAll(['theme_name' => $themeName], ['theme_name' => 'new_theme']);
        DesignBoxesSettings::updateAll(['theme_name' => $themeName], ['theme_name' => 'new_theme']);
        DesignBoxesSettingsTmp::updateAll(['theme_name' => $themeName], ['theme_name' => 'new_theme']);
        ThemesSettings::updateAll(['theme_name' => $themeName], ['theme_name' => 'new_theme']);
        ThemesStyles::updateAll(['theme_name' => $themeName], ['theme_name' => 'new_theme']);
        ThemesStylesMain::updateAll(['theme_name' => $themeName], ['theme_name' => 'new_theme']);
        ThemesStylesCache::updateAll(['theme_name' => $themeName], ['theme_name' => 'new_theme']);

        $bottomCss = '';
        $ds = DIRECTORY_SEPARATOR;
        $bottomFile = DIR_FS_CATALOG . 'themes' . $ds . 'basic' . $ds . 'css' . $ds . 'bottom.css';
        if (file_exists($bottomFile)) {
            $bottomCss = file_get_contents($bottomFile);
        }
        $bottomCss .= \backend\design\Style::getCss($themeName, array('.b-bottom'));
        $bottomCss = \frontend\design\Info::minifyCss($bottomCss);
        $filePath = DIR_FS_CATALOG . 'themes' . $ds . $themeName . $ds . 'css' . $ds;
        FileHelper::createDirectory($filePath);
        file_put_contents($filePath . 'style.css', $bottomCss);

        return json_encode(['text' => THEME_ADDED, 'theme_name' => $themeName]);
    }

    public function actionCopyNewTheme()
    {
        if (!DesignBoxesTmp::findOne(['theme_name' => 'new_theme'])) {
            Theme::copyTheme('new_theme', 'origin', 'copy');
            Style::createCache('new_theme');

            ThemesSettings::deleteAll(['setting_name' => 'block_copy_theme']);
            return 'copied';
        }

        return 'exist';
    }

    public function actionSetGroup()
    {
        $themeName = Yii::$app->request->post('theme_name');
        $groupIds = Yii::$app->request->post('group_id', 0);
        $category = Yii::$app->request->post('category', 0);
        $path = DIR_FS_CATALOG . implode(DIRECTORY_SEPARATOR, ['lib', 'backend', 'design', 'groups']);
        $groupData = [];
        $oddData = [];
        $newData = [];

        $boxes = DesignBoxesTmp::find()->where(['theme_name' => $themeName, 'block_name' => $category])->asArray()->all();
        foreach ($boxes as $box) {
            $oddData[$category][] = Theme::blocksTree($box['id']);
            Theme::deleteBlock($box['id']);
        }
        DesignBoxesTmp::deleteAll(['theme_name' => $themeName, 'block_name' => $category]);

        $errors = [];
        $sortOrder = 1;

        foreach ($groupIds as $groupId) {
            $group = DesignBoxesGroups::findOne($groupId);
            if (!$group) {
                $errors[] = 'Group not found: ' . $groupId;
                continue;
            }
            $file = $group->file;

            if (!is_file($path . DIRECTORY_SEPARATOR . $file)) {
                $errors[] = 'Group file not found: ' . $file;
                continue;
            }

            $zip = new \ZipArchive();
            if ($zip->open($path . DIRECTORY_SEPARATOR . $file, \ZipArchive::CREATE) === TRUE) {
                $json = $zip->getFromName('data.json');
                $groupData = json_decode($json, true);
                foreach ($groupData as $blockName => $data) {
                    if (!is_int($blockName)) {
                        $newData[$blockName][] = $data;
                        $boxes = DesignBoxesTmp::find()->where(['theme_name' => $themeName, 'block_name' => $blockName])
                            ->asArray()->all();
                        foreach ($boxes as $box) {
                            $oddData[$blockName][] = Theme::blocksTree($box['id']);
                            Theme::deleteBlock($box['id']);
                        }
                        DesignBoxesTmp::deleteAll(['theme_name' => $themeName, 'block_name' => $blockName]);
                    } else {
                        $newData[$category][] = $data;
                    }
                }
                $addedPagesJson = $zip->getFromName('addedPages.json');
                if ($addedPagesJson) {
                    $addedPages = json_decode($addedPagesJson, true);
                    if (is_array($addedPages)) {
                        foreach ($addedPages as $addedPage) {
                            $newThemePge = new ThemesSettings();
                            $newThemePge->theme_name = $themeName;
                            $newThemePge->setting_group = 'added_page';
                            $newThemePge->setting_name = $addedPage['setting_name'];
                            $newThemePge->setting_value = $addedPage['setting_value'];
                            $newThemePge->save(false);
                        }
                    }
                }
                $zip->close();
            }

            $params = [];
            $params['theme_name'] = $themeName;
            $params['block_name'] = $category;
            $params['sort_order'] = $sortOrder;
            $sortOrder = $sortOrder+10;

            $importBlock = Theme::importBlock($path . DIRECTORY_SEPARATOR . $file, $params, $file);
            if (!is_array($importBlock)) {
                $errors[] = $importBlock;
            }
        }

        if (count($errors)) {
            return json_encode(['error' => $errors]);
        }

        Theme::elementsSave($themeName);
        DesignBoxesCache::deleteAll(['theme_name' => $themeName]);

        $log = [
            'old' => $oddData,
            'new' => $newData,
            'theme_name' => $themeName,
        ];
        if (Theme::extensionWidgets()) {
            $log['extensionWidgets'] = Theme::extensionWidgets();
        }
        Steps::setGroup($log);

        return json_encode(['text' => GROUP_APPLIED, 'widgets' => Theme::extensionWidgets()]);
    }

    public function actionSetStyles()
    {
        $themeName = Yii::$app->request->post('theme_name');
        $groupIds = Yii::$app->request->post('group_id', 0);
        $category = Yii::$app->request->post('category', 0);
        $colors = Yii::$app->request->post('colors', []);
        $path = DIR_FS_CATALOG . implode(DIRECTORY_SEPARATOR, ['lib', 'backend', 'design', 'groups']);
        $groupId = $groupIds[0];

        $newColors = [];
        if ($category == 'color' && count($colors)) {
            foreach ($colors as $color) {
                $newColors[$color['old']] = $color['new'];
            }
        }

        $group = DesignBoxesGroups::findOne($groupId);
        if (!$group) {
            return json_encode(['error' => GROUP_NOT_FOUND . ': ' . $groupId]);
        }
        $file = $group->file;

        if (!is_file($path . DIRECTORY_SEPARATOR . $file)) {
            return json_encode(['error' => GROUP_FILE_NOT_FOUND . ': ' . $file]);
        }

        $zip = new \ZipArchive();
        if ($zip->open($path . DIRECTORY_SEPARATOR . $file, \ZipArchive::CREATE) !== TRUE) {
            return json_encode(['error' => 'ZipArchive error']);
        }
        $json = $zip->getFromName('data.json');
        $styles = json_decode($json, true);
        $newStyles = [];

        $oldStyles = ThemesStylesMain::find()->where(['theme_name' => $themeName, 'type' => $category])->asArray()->all();
        ThemesStylesMain::deleteAll(['theme_name' => $themeName, 'type' => $category]);
        foreach ($styles as $style) {
            $themesStyles = new ThemesStylesMain();
            $themesStyles->theme_name = $themeName;
            $themesStyles->name = $style['name'];
            $themesStyles->value = $newColors[$style['value']] ?? $style['value'];
            $themesStyles->type = $style['type'];
            $themesStyles->sort_order = $style['sort_order'];
            $themesStyles->main_style = $style['main_style'];
            $themesStyles->save();
            $newStyles[] = array_merge($style, ['theme_name' => $themeName]);

            if ($style['type'] == 'font' && isset($style['font_settings'])) {
                $fontSettings = str_replace('themes/<theme_name>', 'themes/' . $themeName, $style['font_settings']);

                $themeSetting = ThemesSettings::find()->where([
                    'theme_name' => $themeName,
                    'setting_group' => 'extend',
                    'setting_name' => 'font_added'
                ])->andWhere(['like', 'setting_value', $style['value']])->one();

                if (!$themeSetting || !preg_match("/font-family: [\'\"]{0,1}" . $style['value'] . "[\'\"]{0,1};/", $themeSetting->setting_value)) {

                    $files = [];
                    preg_match_all("/url\([\'\"]{0,1}(themes\/" . $themeName . "[^'^\"^\)]+)\?[^'^\"^)]+[\'\"]{0,1}\)/", $fontSettings, $files);
                    if (isset($files[1]) && is_array($files[1])) {
                        FileHelper::createDirectory(DIR_FS_CATALOG . 'themes/' . $themeName . '/fonts/');
                        foreach ($files[1] as $file) {
                            $filePath = explode('/', $file);
                            $filePath = explode('\\', end($filePath));
                            $fileName = end($filePath);

                            $zip->extractTo(DIR_FS_CATALOG . 'themes/' . $themeName . '/fonts/', $fileName);
                        }
                    }

                    $themeSetting = new ThemesSettings();
                    $themeSetting->theme_name = $themeName;
                    $themeSetting->setting_group = 'extend';
                    $themeSetting->setting_name = 'font_added';
                    $themeSetting->setting_value = $fontSettings;
                    $themeSetting->save();
                }
            }
        }
        $zip->close();

        DesignBoxesCache::deleteAll(['theme_name' => $themeName]);
        Style::createCache($themeName);
        $themesPath = DIR_FS_CATALOG .implode(DIRECTORY_SEPARATOR, ['themes', $themeName, 'cache']);
        if (file_exists($themesPath)) {
            FileHelper::removeDirectory($themesPath);
        }

        Steps::setStyles([
            'old' => $oldStyles,
            'new' => $newStyles,
            'type' => $category,
            'theme_name' => $themeName,
        ]);

        return json_encode(['text' => TEXT_APPLIED]);
    }

    public function actionAddCategory()
    {
        $parentCategory = Yii::$app->request->post('category', '');
        $name = Yii::$app->request->post('name');

        if (DesignBoxesGroupsCategory::findOne(['parent_category' => $parentCategory, 'name' => $name])) {
            return json_encode(['error' => sprintf(CATEGORY_ALREADY_EXISTS, $parentCategory)]);
        }

        $category = new DesignBoxesGroupsCategory();
        $category->name = $name;
        $category->parent_category = $parentCategory;
        $category->save();

        if ($category->errors) {
            return json_encode(['error' => $category->errors]);
        }

        return json_encode(['text' => TEXT_APPLIED]);
    }

    public function actionGetPages()
    {
        $themeName = Yii::$app->request->get('theme_name', '');

        $pageGroups = FrontendStructure::getPageGroups();

        $categories = [];
        foreach ($pageGroups as $pageGroup) {
            $categories[$pageGroup['name']] = [
                'title' => ($pageGroup['title'] ? $pageGroup['title'] : $pageGroup['name']),
                'key' => $pageGroup['name'],
                "folder" => true,
                'checkbox' => false
            ];
        }

        $pages = FrontendStructure::getPages();

        foreach ($pages as $page) {
            if (!($page['group'] ?? false)) {
                continue;
            }
            if (!isset($categories[$page['group']])) {
                $categories[$page['group']] = [
                    'title' => $page['group'],
                    'children' => [],
                    "folder" => true,
                ];
            }
            if (!isset($categories[$page['group']]['children'])) {
                $categories[$page['group']]['children'] = [];
            }
            $categories[$page['group']]['children'][] = [
                'title' => $page['title'],
                'key' => $page['page_name'],
                'checkbox' => true
            ];
        }

        $pagesTree = [];
        foreach ($categories as $category) {
            if ($category['children'] ?? false) {
                $pagesTree[] = $category;
            }
        }

        return json_encode($pagesTree);
    }

    public function actionGetGroups()
    {
        $languageId = Yii::$app->settings->get('languages_id');
        $names = Yii::$app->request->get('names', []);
        $themeName = Yii::$app->request->get('theme_name', '');

        $imageFSPath = CommonImages::getFSCatalogImagesPath() . 'widget-groups' . DIRECTORY_SEPARATOR;
        $imageWSPath = CommonImages::getWSCatalogImagesPath() . 'widget-groups' . DIRECTORY_SEPARATOR;
        $groupLists = [];

        $groups = [
            'header' => ['title' => TEXT_HEADER, 'multiSelect' => false],
            'footer' => ['title' => TEXT_FOOTER, 'multiSelect' => true],
            'main' => ['title' => TEXT_HOME, 'multiSelect' => true],
            'product' => ['title' => TEXT_PRODUCT, 'multiSelect' => true],
        ];

        foreach ($names as $category) {
            $list = DesignBoxesGroups::find()->alias('g')
                ->leftJoin(DesignBoxesGroupsLanguages::tableName() . ' gl', 'g.id = gl.boxes_group_id and gl.language_id = ' . $languageId)
                ->where(['category' => $category])
                ->asArray()->all();

            if ($list && is_array($list)) {
                if ($groups[$category]) {
                    $groupLists[$category] = $groups[$category];
                } else {
                    $groupLists[$category] = ['multiSelect' => true];
                }

                foreach ($list as $itemKey => $item) {
                    $images = DesignBoxesGroupsImages::find()->where(['boxes_group_id' => $item['id']])->asArray()->all();
                    foreach ($images as $imageKey => $image) {
                        if (is_file($imageFSPath . $item['id'] . DIRECTORY_SEPARATOR . $image['file'])) {
                            $images[$imageKey]['image'] = $imageWSPath . $item['id'] . DIRECTORY_SEPARATOR . $image['file'];
                        } else {
                            unset($images[$imageKey]);
                        }
                    }
                    $list[$itemKey]['images'] = $images;
                }
                $groupLists[$category]['list'] = $list;
            }

            $filesQuery = DesignBoxesTmp::find()->alias('b')
                ->select(['bs.setting_value'])->distinct()
                ->leftJoin(DesignBoxesSettingsTmp::tableName() . ' bs',
                    "b.id = bs.box_id")
                ->where([
                    'b.theme_name' => $themeName,
                    'b.block_name' => $category,
                    'bs.setting_name' => 'from_file'
                ])
                ->asArray()->all();

            $files = [];
            foreach ($filesQuery as $file) {
                $files[] = $file['setting_value'];
            }

            $groupLists[$category]['files'] = $files;
        }

        return json_encode($groupLists);
    }
}
