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

use backend\design\Data;
use backend\design\FileManager;
use backend\design\Groups;
use backend\models\Admin;
use common\helpers\Translation;
use common\models\DesignBoxes;
use common\models\DesignBoxesGroups;
use common\models\DesignBoxesSettingsTmp;
use common\models\DesignBoxesTmp;
use common\models\Platforms;
use common\models\ThemesStylesMain;
use frontend\design\Info;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use backend\design\Uploads;
use backend\design\Steps;
use backend\design\Style;
use common\classes\design;
use backend\design\Theme;
use backend\design\Backups;
use backend\design\FrontendStructure;
use common\models\ThemesStyles;
use common\models\ThemesSettings;
use common\models\DesignBoxesCache;
use common\helpers\Language;
use yii\helpers\Html;
/**
 *
 */
class DesignController extends Sceleton {

    public $acl = ['BOX_HEADING_DESIGN_CONTROLS', 'BOX_HEADING_THEMES'];
    public $designerMode = '';
    public $designerModeTitle = '';

    function __construct($id,$module=null) {
        \common\helpers\Translation::init('admin/design');

        if (Yii::$app->request->get('theme_name') == \common\classes\design::pageName(BACKEND_THEME_NAME)) {
            \common\helpers\Acl::checkAccess(['BOX_HEADING_DESIGN_CONTROLS', 'BOX_HEADING_THEMES', 'BOX_BACKEND_THEME_EDIT']);
        }

        $admin = new Admin;
        $this->designerMode = $admin->getAdditionalData('designer_mode');
        switch ($this->designerMode) {
            case 'advanced': $this->designerModeTitle = EDIT_MODE . ': ' . ADVANCED_MODE; break;
            case 'expert': $this->designerModeTitle = EDIT_MODE . ': ' . EXPERT_MODE; break;
            default: $this->designerModeTitle = EDIT_MODE . ': ' . BASIC_MODE;
        }

        return parent::__construct($id,$module);
    }
  /**
   *
   */
  public function actionIndex()
  {
      return Yii::$app->getResponse()->redirect(['design/themes']);
      $request = Yii::$app->request->get();

      if ($request['resource'] && $request['action']) {
          $params = json_decode(file_get_contents('php://input'), true);
          $resource = '\backend\design\data\\' . yii\helpers\Inflector::camelize($request['resource']);
          $action = yii\helpers\Inflector::variablize($request['action']);
          if (!class_exists($resource)) {
              return json_encode(['error' => 'Resource "' . $request['resource'] . '"' . " doesn't exist"]);
          }
          if (!method_exists($resource, $action)) {
              return json_encode(['error' => 'Action "' . $request['action'] . '"' . " doesn't exist"]);
          }
          $response = $resource::$action($params);
          return json_encode($response);
      }

      $this->selectedMenu = array('design_controls', 'design/themes');
      $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('design/themes'), 'title' => BOX_HEADING_THEMES);
      $this->view->headingTitle = BOX_HEADING_THEMES;


      Data::addJsData(['tr' => \common\helpers\Translation::translationsForJs([
          'TEXT_ADD_THEME'
      ], false)]);

      $this->layout = false;
      return $this->render('designer.tpl');
  }

    public function actionThemes()
    {
        $groupId = $request = Yii::$app->request->get('group_id', 0);

        //$this->topButtons[] = '<a href="' . Yii::$app->urlManager->createUrl(['design/theme-add', 'group_id' => $groupId]) . '" class="btn btn-primary btn-add-theme">' . TEXT_ADD_THEME . '</a>';
        $this->topButtons[] = '<a href="' . Yii::$app->urlManager->createUrl(['design-groups/wizard', 'group_id' => $groupId]) . '" class="btn btn-primary">' . CREATE_THEME . '</a>';

        $this->topButtons[] = '<a href="' . Yii::$app->urlManager->createUrl(['design/theme-import', 'group_id' => $groupId]) . '" class="btn btn-primary btn-import-theme">' . IMPORT_THEME . '</a>';
        if ($groupId) {
            $this->topButtons[] = '<a href="' . Yii::$app->urlManager->createUrl('design/themes') . '" class="btn">' . BACK_TO_ROOT . '</a>';
        } else {
            $this->topButtons[] = '<a href="' . Yii::$app->urlManager->createUrl('design/add-group') . '" class="btn create-group">' . ADD_THEME_GROUP . '</a>';
        }

        $this->selectedMenu = array('design_controls', 'design/themes');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('design/themes'), 'title' => BOX_HEADING_THEMES);
        $this->view->headingTitle = BOX_HEADING_THEMES;

        $themes = Theme::themesByGroup($groupId);
        foreach ($themes as $key => $theme) {
            $themeImage = ThemesSettings::findOne([
                'theme_name' => $theme['theme_name'],
                'setting_group' => 'hide',
                'setting_name' => 'theme_image',
            ])->setting_value ?? null;
            if ($themeImage) {
                $themes[$key]['theme_image'] = $themeImage;
            }

            $themes[$key]['platforms'] = \common\models\PlatformsToThemes::find()->alias('p2t')
                ->select(['p.platform_name', 'p.ssl_enabled', 'p.platform_url'])
                ->leftJoin(Platforms::tableName() . ' p', 'p.platform_id = p2t.platform_id')
                ->where(['p2t.theme_id' => $theme['id']])
                ->asArray()->all();

        }

        if ($groupId) {
            return $this->render('themes.tpl', [
                'themes' => $themes,
                'group_id' => $groupId,
                'designer_mode' => $this->designerMode,
            ]);
        }

        $themesGroups = \common\models\ThemesGroups::find()->asArray()->all();

        foreach ($themesGroups as $group) {
            $group['link'] = Yii::$app->urlManager->createUrl(['design/themes', 'group_id' => $group['themes_group_id']]);
            $group['themes'] = Theme::themesByGroup($group['themes_group_id']);
            $themes[] = $group;
        }

        usort($themes, function($a, $b){
            return $a['sort_order'] <=> $b['sort_order'];
        });

        return $this->render('themes.tpl', [
            'themes' => $themes,
            'group_id' => 0,
            'designer_mode' => $this->designerMode,
        ]);
    }

    public function actionSaveAdminData()
    {
        $post = Yii::$app->request->post();
        $admin = new Admin;
        $admin->saveAdditionalData($post);
    }

    public function actionThemeAdd()
    {
        $themes = array();
        $query = tep_db_query("select id, theme_name, title from " . TABLE_THEMES . " where install = '1' order by sort_order");
        while ($theme = tep_db_fetch_array($query)){
            $themes[] = $theme;
        }
        $group_id = Yii::$app->request->get('group_id');

        $this->layout = 'popup.tpl';
        return $this->render('theme-add.tpl', ['themes' => $themes, 'group_id' => $group_id, 'action' => Yii::$app->urlManager->createUrl('design/theme-add-action')]);
    }

    public function actionThemeCopy()
    {
        $group_id = Yii::$app->request->get('group_id');
        $theme_name = Yii::$app->request->get('theme_name');

        $this->layout = 'popup.tpl';
        return $this->render('theme-copy.tpl', ['group_id' => $group_id, 'theme_name' => $theme_name, 'action' => Yii::$app->urlManager->createUrl('design/theme-add-action')]);
    }

    public function actionThemeImport()
    {
        $group_id = Yii::$app->request->get('group_id');

        $this->layout = 'popup.tpl';
        return $this->render('theme-import.tpl', ['group_id' => $group_id, 'action' => Yii::$app->urlManager->createUrl('design/theme-add-action')]);
    }

    public function actionThemeAddAction()
    {
        $params = Yii::$app->request->get();
        $this->layout = false;

        if (!$params['title']) {
            return json_encode(['code' => 1, 'text' => THEME_TITLE_REQUIRED]);
        }

        if (!$params['theme_name']) {
            $params['theme_name'] = \common\classes\design::pageName($params['title']);
        }
        if (!preg_match("/^[a-z0-9_\-]+$/", $params['theme_name'])) {
            return json_encode(['code' => 1, 'text' => 'Enter only lowercase letters and numbers for theme name']);
        }

        $theme = tep_db_query("select id from " . TABLE_THEMES . " where theme_name = '" . tep_db_input($params['theme_name']) . "'");
        if (tep_db_num_rows($theme) > 0){
            return json_encode(['code' => 1, 'text' => 'Theme with this name already exist']);
        }

        $query = tep_db_query("select id, sort_order from " . TABLE_THEMES . " where install = '1'");
        while ($theme = tep_db_fetch_array($query)){
            $sql_data_array = array(
                'sort_order' => $theme['sort_order'] + 1,
            );
            tep_db_perform(TABLE_THEMES, $sql_data_array, 'update', " id = '" . $theme['id'] . "'");
        }

        $sql_data_array = array(
            'theme_name' => $params['theme_name'],
            'title' => $params['title'],
            'install' => 1,
            'is_default' => 0,
            'sort_order' => 0,
            'themes_group_id' => $params['group_id'],
            'parent_theme' => (isset($params['parent_theme']) && $params['parent_theme'] && $params['theme_source'] == 'theme' && $params['parent_theme_files'] == 'link' ? $params['parent_theme'] : 0)
        );
        tep_db_perform(TABLE_THEMES, $sql_data_array);


        if (isset($params['parent_theme']) && $params['parent_theme'] && $params['theme_source'] == 'theme'){

            Theme::copyTheme($params['theme_name'], $params['parent_theme'], $params['parent_theme_files']);
            Theme::copyTheme($params['theme_name'] . '-mobile', $params['parent_theme'] . '-mobile', $params['parent_theme_files']);

        }

        if ($params['theme_source'] == 'url' || $params['theme_source'] == 'computer') {

            $path = \Yii::getAlias('@webroot');
            $path .= DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
            $path .= 'themes' . DIRECTORY_SEPARATOR . $params['theme_name'] . DIRECTORY_SEPARATOR;

            if ($params['theme_source'] == 'url') {
                $themeFile = $params['theme_source_url'];
            } else {
                $themeFile = \Yii::getAlias('@webroot');
                $themeFile .= DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $params['theme_source_computer'];
            }
            if ( !\backend\design\Theme::import($params['theme_name'], $themeFile) ) {
                return json_encode(['code' => 1, 'text' => 'Wron theme file']);
            }

        }

        Style::createCache($params['theme_name']);
        Style::createCache($params['theme_name'] . '-mobile');

        return json_encode(['code' => 2, 'text' => 'Theme added']);
    }

  public function actionThemeRemove(){

    $params = Yii::$app->request->get();

    Theme::themeRemove($params['theme_name']);
    Theme::themeRemove($params['theme_name'] . '-mobile');

    return Yii::$app->getResponse()->redirect(array('design/themes'));
  }


  public function actionThemeEdit()
  {
    $languages_id = \Yii::$app->settings->get('languages_id');

    $params = Yii::$app->request->get();

    $language_query = tep_db_fetch_array(tep_db_query("select code from " . TABLE_LANGUAGES . " where languages_id = '" . $languages_id . "' order by sort_order"));
    $language_code = $language_query['code'];

    $this->topButtons[] = '<span class="redo-buttons"></span>';

    $this->topButtons[] = '<span class="mode-title">' . $this->designerModeTitle . '</span>';

    $this->selectedMenu = array('design_controls', 'design/themes');
    $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('design/elements'), 'title' => BOX_HEADING_MAIN_STYLES . ' "' . Theme::getThemeTitle($params['theme_name']) . '"');
    $this->view->headingTitle = BOX_HEADING_MAIN_STYLES . ' "' . Theme::getThemeTitle($params['theme_name']) . '"';

    $css = tep_db_fetch_array(tep_db_query("select setting_value from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . tep_db_input($params['theme_name']) . "' and setting_group = 'css' and setting_name = 'css'"));
    $javascript = tep_db_fetch_array(tep_db_query("select setting_value from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . tep_db_input($params['theme_name']) . "' and setting_group = 'javascript' and setting_name = 'javascript'"));

    return $this->render('theme-edit.tpl', [
      'menu' => 'theme-edit',
      'theme_name' => ($params['theme_name'] ? $params['theme_name'] : 'theme-1'),
      'clear_url' => ($params['theme_name'] ? true : false),
      'css' => $css['setting_value'] ?? null,
      'javascript' => $javascript['setting_value'] ?? null,
      'language_code' => $language_code,
        'designer_mode' => $this->designerMode,
    ]);
  }

    public function actionCss()
    {
        $params = Yii::$app->request->get();

        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('design/css'), 'title' => 'CSS "' . Theme::getThemeTitle($params['theme_name']) . '"');
        $this->selectedMenu = array('design_controls', 'design/themes');

        $this->topButtons[] = '<span class="btn btn-confirm btn-save-css btn-elements ">' . IMAGE_SAVE . '</span><span class="redo-buttons"></span>';

        $this->topButtons[] = '<span class="mode-title">' . $this->designerModeTitle . '</span>';

        Style::changeCssAttributes($params['theme_name']);

        $style = Style::getCss($params['theme_name']);

        $css = tep_db_fetch_array(tep_db_query("select setting_value from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . tep_db_input($params['theme_name']) . "' and setting_group = 'css' and setting_name = 'css'"));
        if ($css['setting_value'] ?? null) {
            $style .= $css['setting_value'];
        }

        $setting = tep_db_fetch_array(tep_db_query("select setting_value from " . TABLE_THEMES_SETTINGS . " where setting_name = 'development_mode' and setting_group = 'hide' and theme_name = '" . tep_db_input($params['theme_name']) . "'"));
        $css_status = 0;
        if ($setting['setting_value'] ?? null) {
            $css_status = 1;
        }

        $mainStyles = ThemesStylesMain::find()->where(['theme_name' => $params['theme_name']])->orderBy('sort_order')->asArray()->all();

        return $this->render('css.tpl', [
            'menu' => 'css',
            'theme_name' => ($params['theme_name'] ? $params['theme_name'] : 'theme-1'),
            'css' => $style,
            'css_status' => $css_status,
            'widgets_list' => Style::getCssWidgetsList($params['theme_name']),
            'designer_mode' => $this->designerMode,
            'mainStyles' => $mainStyles,
        ]);
    }

    public function actionGetCss()
    {
        $get = Yii::$app->request->get();

        if ($get['widget'] == 'all'){
            $widget = [];
        } elseif ($get['widget'] == 'main') {
            $widget = [''];
        } else {
            $widget = [$get['widget']];
        }

        $css = Style::getCss($get['theme_name'], $widget);

        if ($get['widget'] != 'all' && $get['widget'] != 'main' && $get['widget'] != 'block_box') {
            $css = str_replace(($get['widget'] ? $get['widget'] . ' ' : ''), '', $css);
        }

        return $css;
    }

  public function actionJs()
  {
    $params = Yii::$app->request->get();

      $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('design/js'), 'title' => 'JS "' . Theme::getThemeTitle($params['theme_name']) . '"');
      $this->selectedMenu = array('design_controls', 'design/themes');

    $this->topButtons[] = '<span class="btn btn-confirm btn-save-javascript btn-elements ">' . IMAGE_SAVE . '</span>';

      $this->topButtons[] = '<span class="mode-title">' . $this->designerModeTitle . '</span>';

    $javascript = tep_db_fetch_array(tep_db_query("select setting_value from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . tep_db_input($params['theme_name']) . "' and setting_group = 'javascript' and setting_name = 'javascript'"));

    return $this->render('js.tpl', [
      'menu' => 'js',
      'theme_name' => ($params['theme_name'] ? $params['theme_name'] : 'theme-1'),
      'javascript' => $javascript['setting_value'] ?? null,
        'designer_mode' => $this->designerMode,
    ]);
  }

  public function actionCssSave()
  {
      $params = Yii::$app->request->post();

      $devPath = DIR_FS_CATALOG . 'themes/' . $params['theme_name'] . '/css/';

      if ($params['widget'] == 'all') {
          \yii\helpers\FileHelper::createDirectory($devPath);
          file_put_contents($devPath . 'develop.css', $params['css']);
      }
      Theme::saveThemeVersion($params['theme_name']);
      /*$develop = fopen($devPath . 'develop.css', "w");
      fwrite($develop, $params['css']);
      fclose($develop);*/
      $cssSave = Style::cssSave($params);

      $this->actionBackupAuto($params['theme_name'], $cssSave);
  }

  public function actionJavascriptSave()
  {
    $params = Yii::$app->request->post();

    $total = tep_db_fetch_array(tep_db_query("select count(*) as total from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . tep_db_input($params['theme_name']) . "' and setting_group = 'javascript' and setting_group = 'javascript'"));

    $query = tep_db_query("select * from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . tep_db_input($params['theme_name']) . "' and setting_group = 'javascript' and setting_group = 'javascript'");
    $javascript_old = tep_db_fetch_array($query);
    $javascript_old = $javascript_old['setting_value'] ?? null;

    if (tep_db_num_rows($query) == 0) {
      $sql_data_array = array(
        'theme_name' => $params['theme_name'],
        'setting_group' => 'javascript',
        'setting_name' => 'javascript',
        'setting_value' => $params['javascript']
      );
      tep_db_perform(TABLE_THEMES_SETTINGS, $sql_data_array);
    } else {
      $sql_data_array = array(
        'setting_value' => $params['javascript']
      );
      tep_db_perform(TABLE_THEMES_SETTINGS, $sql_data_array, 'update', " theme_name = '" . tep_db_input($params['theme_name']) . "' and setting_group = 'javascript' and setting_name = 'javascript'");
    }
      Theme::saveThemeVersion($params['theme_name']);

    $data = [
      'theme_name' => $params['theme_name'],
      'javascript_old' => $javascript_old,
      'javascript' => $params['javascript'],
    ];
    Steps::javascriptSave($data);

    return '';

  }

    public function actionElements()
    {
        $languages_id = \Yii::$app->settings->get('languages_id');
        $this->selectedMenu = array('design', 'elements');
        $params = Yii::$app->request->get();

        if (!isset($params['theme_name'])) {
            return Yii::$app->getResponse()->redirect(['design/themes']);
        }

        $language_query = tep_db_fetch_array(tep_db_query("select code from " . TABLE_LANGUAGES . " where languages_id = '" . $languages_id . "' order by sort_order"));
        $language_code = $language_query['code'];

        \backend\design\Data::addJsData([
            'languageCode' => $language_code,
            'languages' => Language::get_languages(),
        ]);

        $this->topButtons[] = '<span data-href="' . Yii::$app->urlManager->createUrl(['design/elements-save']) . '" class="btn btn-confirm btn-save-boxes">' . IMAGE_SAVE . '</span>';
        $this->topButtons[] = '<span class="btn btn-preview-2 btn-primary">' . IMAGE_PREVIEW_POPUP . '</span>';
        $this->topButtons[] = '<span class="btn btn-preview btn-primary" title="Alt + P">' . IMAGE_PREVIEW . '</span>';
        $this->topButtons[] = '<span class="btn btn-edit btn-primary" style="display: none" title="Alt + P">' . IMAGE_EDIT . '</span>';
        if ($this->designerMode) {
            $this->topButtons[] = '<span class="redo-buttons"></span>';
        }

        $this->topButtons[] = '<span class="mode-title">' . $this->designerModeTitle . '</span>';

        $this->selectedMenu = array('design_controls', 'design/themes');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('design/elements'), 'title' => BOX_HEADING_ELEMENTS . ' "' . Theme::getThemeTitle($params['theme_name']) . '"');
        $this->view->headingTitle = BOX_HEADING_ELEMENTS . ' "' . Theme::getThemeTitle($params['theme_name']) . '"';

        \backend\design\Data::addJsData([
            'tr' => Translation::translationsForJs([
                'TEXT_SELECT_PREVIEW_PLATFORM', 'IMAGE_SAVE', 'IMAGE_CANCEL', 'TEXT_REMOVE', 'TEXT_PAGES',
                'TEXT_EDIT_SETTINGS', 'TEXT_COPY_PAGE', 'TEXT_ADD_PAGE', 'COPY_PAGE_CONTENT_FROM', 'COPY_PAGE_CONTENT',
                'TEXT_CHOOSE_PAGE', 'TEXT_SEARCH_PAGE', 'TEXT_PAGE_SETTINGS', 'TEXT_REMOVE_THIS_PAGE', 'TEXT_PAGE_NAME',
                'TEXT_PAGE_TYPE', 'GO_TO_PAGE_BY_URL', 'TEXT_WIDGETS', 'TEXT_EXPORT', 'TEXT_NAME_THIS_BLOCK',
                'SAVE_TO_WIDGET_GROUPS', 'WIDGET_GROUP_CATEGORY', 'NO_CATEGORIZED', 'DOWNLOAD_ON_MY_COMPUTER',
                'TEXT_COMMENTS', 'EDIT_WIDGETS', 'EDIT_TEXTS', 'ICON_WARNING', 'DATA_FROM_NETWORK_CHANGED',
                'BLOCK_CONTAINS_EXTENSION_WIDGETS', 'ADD_WIDGET', 'EXPORT_BLOCK', 'EDIT_BLOCK', 'MOVE_BLOCK',
                'EDIT_WIDGET', 'EDIT_WIDGET_STYLES', 'TEXT_CHANGE', 'EXTENSIONS_YOU_DONT_HAVE',
                'WIDGETS_NOT_INSTALLED_EXTENSIONS',
            ], false),
            'pages' => FrontendStructure::getPages(),
            'groups' => FrontendStructure::getPageGroups(),
            'unitedTypes' => FrontendStructure::getUnitedTypesGroup(),
            'groupCategories' => FrontendStructure::getGroupCategories(),
            'platformSelect' => FrontendStructure::getThemePlatforms(),
            'platformsList' => \common\classes\platform::getList(false),
            'theme_name' => ($params['theme_name'] ? $params['theme_name'] : 'theme-1'),
            'theme_title' => Theme::getThemeTitle($params['theme_name']),
            'designer_mode' => $this->designerMode,
        ]);

        return $this->render('elements.tpl', [
            'menu' => 'elements',
            'link_save' => Yii::$app->urlManager->createUrl(['design/elements-save']),
            'link_cancel' => Yii::$app->urlManager->createUrl(['design/elements-cancel']),
            'theme_name' => ($params['theme_name'] ? $params['theme_name'] : 'theme-1'),
            'landing' => \frontend\design\Info::themeSetting('landing', 'hide', $params['theme_name']) ? 1 : 0,
            'designer_mode' => $this->designerMode,
        ]);
    }

    public function actionElementsSave()
    {
        $get = tep_db_prepare_input(Yii::$app->request->get());

        Theme::elementsSave($get['theme_name']);

        Steps::elementsSave($get['theme_name']);

        DesignBoxesCache::deleteAll(['theme_name' => $get['theme_name']]);

        return '<div class="popup-heading">' . TEXT_NOTIFIC . '</div><div class="popup-content pop-mess-cont">'.MESSAGE_SAVED.'</div>';
    }

    public function actionElementsCancel()
    {
        $themeName = tep_db_prepare_input(Yii::$app->request->get('theme_name'));

        Steps::elementsCancel($themeName);

        tep_db_query("delete from " . TABLE_DESIGN_BOXES_SETTINGS_TMP . " where box_id in (select id from " . TABLE_DESIGN_BOXES . " where theme_name = '" . tep_db_input($themeName) . "')");
        tep_db_query("delete from " . TABLE_DESIGN_BOXES_TMP . " where theme_name = '" . tep_db_input($themeName) . "'");

        tep_db_query("INSERT INTO " . TABLE_DESIGN_BOXES_TMP . " SELECT * FROM " . TABLE_DESIGN_BOXES . " WHERE theme_name = '" . tep_db_input($themeName) . "'");
        tep_db_query("INSERT INTO " . TABLE_DESIGN_BOXES_SETTINGS_TMP . " SELECT dbs.* FROM " . TABLE_DESIGN_BOXES_SETTINGS . " dbs, " . TABLE_DESIGN_BOXES_TMP . " db WHERE db.theme_name = '" . tep_db_input($themeName) . "' and dbs.box_id = db.id");

        return '<div class="popup-heading">' . TEXT_NOTIFIC . '</div><div class="popup-content pop-mess-cont">Canceled</div>';
    }

    public function actionBlocksMove()
    {
        $params = Yii::$app->request->post();

        $firstBoxId = substr($params['id'][0], 4);
        $themeName = \common\models\DesignBoxesTmp::findOne(['id' => $firstBoxId])->theme_name;
        if ($themeName != $params['theme_name']) {
            return json_encode('');
        }


        $i = 1;
        $positions = array();
        $positions_old = array();
        if (is_array($params['id']))
            foreach ($params['id'] as $item){
                $id = substr($item, 4);
                $microtime = DesignBoxesTmp::findOne(['id' => $id])->microtime;
                $sql_data_array = array(
                    'block_name' => tep_db_prepare_input($params['name']),
                    'sort_order' => $i,
                );
                $i++;
                $positions[] = array_merge(['id' => $id, 'microtime' => $microtime], $sql_data_array);
                $positions_old[] = tep_db_fetch_array(tep_db_query("select id, block_name, sort_order, microtime from " . TABLE_DESIGN_BOXES_TMP . " where id='" . (int)$id . "'"));
                tep_db_perform(TABLE_DESIGN_BOXES_TMP, $sql_data_array, 'update', "id = '" . (int)$id . "'");
            }

        $data = [
            'positions' => $positions,
            'positions_old' => $positions_old,
            'theme_name' => $params['theme_name'],
        ];
        Steps::blocksMove($data);

        $this->actionBackupAuto($params['theme_name'], json_encode(''));
    }

  public static function deleteBlock($id) {
    $query = tep_db_query("select id from " . TABLE_DESIGN_BOXES_TMP . " where block_name = 'block-" . tep_db_input($id) . "' or block_name = 'block-" . tep_db_input($id) . "-2' or block_name = 'block-" . tep_db_input($id) . "-3' or block_name = 'block-" . tep_db_input($id) . "-4' or block_name = 'block-" . tep_db_input($id) . "-5'");
    while ($item = tep_db_fetch_array($query)){
      tep_db_query("delete from " . TABLE_DESIGN_BOXES_TMP . " where id = '" . (int)$item['id'] . "'");
      tep_db_query("delete from " . TABLE_DESIGN_BOXES_SETTINGS_TMP . " where box_id = '" . $item['id'] . "'");
      self::deleteBlock($item['id']);
    }
  }

  public function actionBoxDelete()
  {
    $params = tep_db_prepare_input(Yii::$app->request->post());

    $id = substr($params['id'], 4);

    Steps::boxDelete([
      'theme_name' => $params['theme_name'],
      'id' => $id
    ]);

    tep_db_query("delete from " . TABLE_DESIGN_BOXES_TMP . " where id = '" . (int)$id . "'");
    tep_db_query("delete from " . TABLE_DESIGN_BOXES_SETTINGS_TMP . " where box_id = '" . (int)$id . "'");

    self::deleteBlock($id);

      $this->actionBackupAuto($params['theme_name'], json_encode(['text' => 'removed']));
  }

  public function actionWidgetsList()
  {
    $type = Yii::$app->request->get('type');

    $widgets = \backend\design\WidgetsList::get($type);

    return json_encode($widgets);
  }


    public function actionBoxAdd()
    {
        $params = tep_db_prepare_input(Yii::$app->request->post());

        $params['sort_order'] = DesignBoxesTmp::find()->where([
                'block_name' => $params['block'],
                'theme_name' => $params['theme_name']
            ])->max('sort_order') + 1;

        if (substr($params['box'], 0, 6) == 'group-') {

            $id = substr($params['box'], 6);
            $file = \common\models\DesignBoxesGroups::findOne($id)->file;
            $path = DIR_FS_CATALOG . implode(DIRECTORY_SEPARATOR, ['lib', 'backend', 'design', 'groups']);

            $params['block_name'] = $params['block'];

            $importBlock = Theme::importBlock($path . DIRECTORY_SEPARATOR . $file, $params);
            if (is_array($importBlock)) {
                [$arr, $boxId] = $importBlock;
            } else {
                return $importBlock;
            }

            $data = [
                'idArr' => $boxId,
                'theme_name' => $params['theme_name'],
            ];
            Steps::importBlock($data);

        } else {
            $designBoxes = new DesignBoxesTmp();
            $designBoxes->microtime = microtime(true);
            $designBoxes->theme_name = $params['theme_name'];
            $designBoxes->block_name = $params['block'];
            $designBoxes->widget_name = $params['box'];
            $designBoxes->sort_order = $params['sort_order'];
            $designBoxes->save();
            $designBoxes->refresh();

            Steps::boxAdd($designBoxes->getAttributes());
        }

        $this->actionBackupAuto($params['theme_name'], json_encode($params));
    }


    public function actionBoxAddSort()
    {
        $params = tep_db_prepare_input(Yii::$app->request->post());

        if (substr($params['box'], 0, 6) == 'group-') {

            $id = substr($params['box'], 6);
            $file = \common\models\DesignBoxesGroups::findOne($id)->file;
            $path = DIR_FS_CATALOG . implode(DIRECTORY_SEPARATOR, ['lib', 'backend', 'design', 'groups']);

            $params['sort_order'] = $params['order'];
            $params['block_name'] = $params['block'];

            $importBlock = Theme::importBlock($path . DIRECTORY_SEPARATOR . $file, $params);
            if (is_array($importBlock)) {
                [$arr, $boxId] = $importBlock;
            } else {
                return $importBlock;
            }

            $data = [
                'idArr' => $boxId,
                'theme_name' => $params['theme_name'],
            ];
            Steps::importBlock($data);

        } else {
            $designBoxes = new DesignBoxesTmp();
            $designBoxes->setAttributes([
                'microtime' => microtime(true),
                'theme_name' => $params['theme_name'],
                'block_name' => $params['block'] ?? null,
                'widget_name' => $params['box'],
                'sort_order' => $params['order'],
            ]);
            $designBoxes->save();
            $designBoxes->refresh();
            $boxId = $designBoxes->id;

            $i = 1;
            $sort_arr = [];
            $sort_arr_old = [];
            foreach ($params['id'] as $item) {
                if ($item == 'new') {
                    $id = $boxId;
                } else {
                    $id = (int)substr($item, 4);
                }

                $designBoxesSibling = DesignBoxesTmp::findOne(['id' => $id, 'theme_name' => $params['theme_name']]);

                if ($designBoxesSibling){
                    $sort_arr[$designBoxesSibling->microtime] = $i;
                    $sort_arr_old[$designBoxesSibling->microtime] = $designBoxesSibling->sort_order;

                    $designBoxesSibling->sort_order = $i;
                    $designBoxesSibling->save();
                }
                $i++;
            }
            Steps::boxAdd($designBoxes->getAttributes() + ['sort_arr' => $sort_arr, 'sort_arr_old' => $sort_arr_old]);
        }
        $this->actionBackupAuto($params['theme_name'], $params['order']);
    }

    public function actionCopyPage()
    {
        $theme_name = Yii::$app->request->post('theme_name');
        $page_to = Yii::$app->request->post('page_to');
        $page_from = Yii::$app->request->post('page_from');

        if (!$theme_name || !$page_to || !$page_from) {
            return '';
        }

        $aldBoxes = \common\models\DesignBoxes::find()->where([
            'theme_name' => $theme_name,
            'block_name' => $page_to,
        ])->asArray()->all();

        $contentOld = [];

        foreach ($aldBoxes as $box) {
            $tree = \backend\design\Theme::blocksTree($box['id']);
            $contentOld[] = $tree;
            tep_db_query("delete from " . TABLE_DESIGN_BOXES_TMP . " where id = '" . (int)$box['id'] . "'");
            tep_db_query("delete from " . TABLE_DESIGN_BOXES_SETTINGS_TMP . " where box_id = '" . (int)$box['id'] . "'");
            self::deleteBlock($box['id']);
        }

        $content = [];
        $boxes = DesignBoxes::find()->where([
            'block_name' => $page_from,
            'theme_name' => $theme_name,
        ])->asArray()->all();

        foreach ($boxes as $box) {
            $tree = \backend\design\Theme::blocksTree($box['id']);
            $content[] = $tree;
            Theme::blocksTreeImport($tree, $theme_name, $page_to);
        }

        $stepData = [
            'theme_name' => $theme_name,
            'page_to' => $page_to,
            'page_from' => $page_from,
            'content' => $content,
            'content_old' => $contentOld,
        ];
        Steps::copyPage($stepData);

        return '';
    }

    public function actionAddPageAction()
    {
        $params = Yii::$app->request->get();

        $theme_name = tep_db_prepare_input($params['theme_name']);
        $page_name = tep_db_prepare_input($params['page_name']);
        $page_type = tep_db_prepare_input($params['page_type']);

        if (!$theme_name) {
            return json_encode(['code' => 1, 'text' => THEME_UNKNOWN]);
        }
        if (!$page_name) {
            return json_encode(['code' => 1, 'text' => ENTER_PAGE_NAME]);
        }

        $sqlDataArray = [
            'theme_name' => $theme_name,
            'setting_group' => 'added_page',
            'setting_name' => $page_type,
            'setting_value' => $page_name,
        ];

        $count = ThemesSettings::find()->where($sqlDataArray)->count();

        if ($count > 0) {
            return json_encode(['code' => 1, 'text' => THIS_PAGE_ALREADY_EXIST]);
        }

        $themesSettings = new ThemesSettings();
        $themesSettings->theme_name = $theme_name;
        $themesSettings->setting_group = 'added_page';
        $themesSettings->setting_name = $page_type;
        $themesSettings->setting_value = $page_name;
        $themesSettings->save();

        \backend\design\Theme::savePageSettings($params);

        $boxes = DesignBoxes::find()->where([
            'block_name' => ($page_type == 'inform' ? 'info' : $page_type),
            'theme_name' => $theme_name,
        ])->asArray()->all();

        $content = [];
        foreach ($boxes as $box) {
            $tree = \backend\design\Theme::blocksTree($box['id']);
            $content[] = $tree;
            Theme::blocksTreeImport($tree, $theme_name, \common\classes\design::pageName($page_name));
        }

        $sqlDataArray['content'] = $content;
        Steps::addPage($sqlDataArray);

        return json_encode(['code' => 2, 'text' => PAGE_ADDED]);
    }

    public function actionRemovePageTemplate()
    {
        $params = Yii::$app->request->get();

        $theme_name = tep_db_prepare_input($params['theme_name']);
        $page_title = tep_db_prepare_input($params['page_name']);
        $page_name = \common\classes\design::pageName($page_title);

        if ($theme_name && $page_name) {

            $count = tep_db_fetch_array(tep_db_query("select count(*) as total from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . tep_db_input($theme_name) . "' and setting_group = 'added_page' and setting_value = '" . tep_db_input($page_title) . "'"));
            if ($count['total'] == 1) {

                Steps::removePageTemplate([
                    'theme_name' => $theme_name,
                    'page_title' => $page_title
                ]);

                tep_db_query("
                        delete 
                        from " . TABLE_THEMES_SETTINGS . " 
                        where 
                            theme_name = '" . tep_db_input($theme_name) . "' and 
                            ((setting_group = 'added_page' and setting_value = '" . tep_db_input($page_title) . "') or
                             (setting_group = 'added_page_settings' and setting_name = '" . tep_db_input($page_title) . "'))
                ");

                $query = tep_db_query("select id from " . TABLE_DESIGN_BOXES_TMP . " where block_name = '" . tep_db_input($page_name) . "'");
                while ($item = tep_db_fetch_array($query)){
                    tep_db_query("delete from " . TABLE_DESIGN_BOXES_TMP . " where id = '" . (int)$item['id'] . "'");
                    tep_db_query("delete from " . TABLE_DESIGN_BOXES_SETTINGS_TMP . " where box_id = '" . $item['id'] . "'");
                    self::deleteBlock($item['id']);
                }

                $this->actionBackupAuto($params['theme_name'], json_encode(['code' => 2, 'text' => '']));
            }
        }
    }

    public function actionAddPageSettings()
    {
        $get = Yii::$app->request->get();

        $query = tep_db_query("select setting_value from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . tep_db_input($get['theme_name']) . "' and setting_group = 'added_page_settings' and setting_name = '" . tep_db_input($get['page_name']) . "'");

        $added_page_settings = array();
        while ($item = tep_db_fetch_array($query)){
            if (strpos($item['setting_value'], ':')){
                $setArr = explode(':', $item['setting_value']);
                $added_page_settings[$setArr[0]] = $setArr[1];
            } else {
                $added_page_settings[$item['setting_value']] = true;
            }
        }

        $this->layout = 'popup.tpl';
        return $this->render('add-page-settings.tpl', [
            'short' => $get['short'] ?? null,
            'theme_name' => $get['theme_name'],
            'page_name' => $get['page_name'],
            'page_type' => $get['page_type'],
            'added_page_settings' => $added_page_settings,
            'action' => Yii::$app->urlManager->createUrl('design/add-page-settings-action')
        ]);
    }

  public function actionAddPageSettingsAction()
  {
    $post = Yii::$app->request->post();

    $theme_name = tep_db_prepare_input($post['theme_name']);
    $page_name = tep_db_prepare_input($post['page_name']);

    $settings_old = array();
    $settings = array();
    $query_settings = tep_db_query("select * from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . tep_db_input($theme_name) . "' and setting_group = 'added_page_settings' and setting_name = '" . tep_db_input($page_name) . "'");
    while ($item = tep_db_fetch_array($query_settings)){
      $settings_old[] = $item;
    }

    \backend\design\Theme::savePageSettings($post);

    $query_settings = tep_db_query("select * from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . tep_db_input($theme_name) . "' and setting_group = 'added_page_settings' and setting_name = '" . tep_db_input($page_name) . "'");
    while ($item = tep_db_fetch_array($query_settings)){
      $settings[] = $item;
    }

    Steps::addPageSettings([
      'theme_name' => $theme_name,
      'page_name' => $page_name,
      'settings_old' => $settings_old,
      'settings' => $settings
    ]);

    return json_encode(['code' => 1, 'text' => '']);
  }

  public function actionBoxEdit()
  {
    $params = tep_db_prepare_input(Yii::$app->request->get());
    $id = substr($params['id'], 4);

    $settings = array();
    $items_query = tep_db_query("select id, widget_name, widget_params, theme_name from " . TABLE_DESIGN_BOXES_TMP . " where id = '" . (int)$id . "'");
    $widget_params = [];
    if ($item = tep_db_fetch_array($items_query)) {
      $widget_params = $item['widget_params'];

      $media_query = array();
      $media_query_arr = tep_db_query("select * from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . tep_db_input($item['theme_name']) . "' and setting_name = 'media_query'");
      while ($item1 = tep_db_fetch_array($media_query_arr)){
          $width = explode('w', $item1['setting_value']);
          $item1['title'] = ($width[0] ? $width[0] : '0') . ' - ' . ($width[1] ? $width[1] : '<span style="font-size: 1.8em; line-height: 0">&#8734;</span>');
        $media_query[] = $item1;
      }
        usort($media_query, function($a, $b){
            return ((int)str_replace('w', '', $a['setting_value']) < (int)str_replace('w', '', $b['setting_value'])) ? -1 : 1;
        });
      $settings['media_query'] = $media_query;
      $settings['theme_name'] = $item['theme_name'];
    }



    $visibility = array();
    $settings_query = tep_db_query("select * from " . TABLE_DESIGN_BOXES_SETTINGS_TMP . " where box_id = '" . (int)$id . "'");
    while ($set = tep_db_fetch_array($settings_query)) {
      if (!$set['visibility']){
        $settings[$set['language_id']][$set['setting_name']] = $set['setting_value'];
      } else {
          if (count(Style::vArr($set['visibility'])) == 1) {
              $visibility[$set['language_id']][$set['visibility']][$set['setting_name']] = $set['setting_value'];
          }
      }
    }

    $font_added = array();
    $font_added_arr = tep_db_query("select * from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . tep_db_input($item['theme_name']) . "' and setting_name = 'font_added'");
    while ($item1 = tep_db_fetch_array($font_added_arr)){
      preg_match('/font-family:[ \'"]+([^\'^"^;^}]+)/', $item1['setting_value'], $val);
      $font_added[] = $val[1];
    }
    $settings['font_added'] = $font_added;
    $settings['theme_name'] = $item['theme_name'];

      $settings['designer_mode'] = $this->designerMode;

    if (is_file(Yii::getAlias('@app') . DIRECTORY_SEPARATOR . 'design' . DIRECTORY_SEPARATOR . 'boxes' . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $params['name']) . '.php')){
      $widget_name = 'backend\design\boxes\\' .str_replace('\\\\', '\\', $params['name']);
      return $widget_name::widget(['id' => $id, 'params' => $widget_params, 'settings' => $settings, 'visibility' => $visibility]);
	} elseif($ext = \common\helpers\Acl::checkExtension($params['name'], 'showTabSettings', true)){
      $widget_name = 'backend\design\boxes\Def';
      $settings['tabs'] = ['class'=> $ext, 'method' => 'showTabSettings'];
      return $widget_name::widget(['id' => $id, 'params' => $widget_params, 'settings' => $settings, 'visibility' => $visibility, 'block_type' => $params['block_type']]);
    } elseif($ext = \common\helpers\Acl::checkExtension($params['name'], 'showSettings', true)){
        $widget_name = 'backend\design\boxes\Def';
        $settings['class'] = $ext;
        $settings['method'] = 'showSettings';
        return $widget_name::widget(['id' => $id, 'params' => $widget_params, 'settings' => $settings, 'visibility' => $visibility, 'block_type' => $params['block_type']]);
    }else {
      $widget_name = 'backend\design\boxes\Def';
      return $widget_name::widget(['id' => $id, 'params' => $widget_params, 'settings' => $settings, 'visibility' => $visibility, 'block_type' => $params['block_type']]);
    }
  }

  public function saveBoxSettings($id, $language, $key, $val, $visibility = '', $settings = [])
  {
      if (($val == '' || $val == 'off') && !in_array($key, ['background_image', 'logo', 'poster', 'video', 'image'])) {
          DesignBoxesSettingsTmp::deleteAll([
              'box_id' => $id,
              'setting_name' => $key,
              'language_id' => $language,
              'visibility' => $visibility
          ]);
          return null;
      }

      if (in_array($key, ['background_image_upload', 'logo_upload', 'poster_upload', 'video_upload', 'image_upload'])){
          return null;
      }

      $themeRow = DesignBoxesTmp::find()->select('theme_name, microtime')->where(['id' => $id])->asArray()->one();
      if (!$themeRow) {
          return null;
      }
      $themeName = $themeRow['theme_name'];

      $settingRow = DesignBoxesSettingsTmp::findOne([
          'box_id' => $id,
          'setting_name' => $key,
          'language_id' => $language,
          'visibility' => $visibility
      ]);

      if (!$settingRow) {
          $settingRow = new DesignBoxesSettingsTmp();
      }

      if (in_array($key, ['background_image', 'logo', 'poster', 'video', 'image'])){
          $val = \common\helpers\Image::prepareSavingImage(
              ($settingRow->setting_value ?? ''),
              $val,
              $settings[$key . '_upload'],
              'themes' . DIRECTORY_SEPARATOR . $themeName . DIRECTORY_SEPARATOR . 'img',
              false, true
          );
          if (!$val) {
              DesignBoxesSettingsTmp::deleteAll([
                  'box_id' => $id,
                  'setting_name' => $key,
                  'language_id' => $language,
                  'visibility' => $visibility
              ]);
              return null;
          }
      }

      $settingRow->box_id = $id;
      $settingRow->microtime = $themeRow['microtime'];
      $settingRow->theme_name = $themeName;
      $settingRow->setting_name = $key;
      $settingRow->setting_value = (string)$val;
      $settingRow->language_id = $language;
      $settingRow->visibility = $visibility;
      $settingRow->save(false);
  }

  public function actionBoxSave()
  {
    $values = Yii::$app->request->post('values');

    $params = Style::paramsFromOneInput($values);
    //$params = tep_db_prepare_input($params);

    if (isset($params['product_types']) && is_array($params['product_types'])) {
      $tmp = 0;
      //2do jquery.edit-[box|theme].js pass checkbox value/remove from params if unchecked VL
      foreach ($params['product_types'] as $v => $foo) {
        if (!empty($foo)) {
          $tmp |= $v;
        }
      }
      $params['setting'][0]['product_types'] = $tmp;
    }

    $p = tep_db_fetch_array(tep_db_query("select theme_name, microtime from " . TABLE_DESIGN_BOXES_TMP . " where id = '" . (int)$params['id'] . "'"));

    $box_settings_old = array();
    $query = tep_db_query("select setting_name, setting_value, language_id, visibility from " . TABLE_DESIGN_BOXES_SETTINGS_TMP . " where box_id = '" . (int)$params['id'] . "'");
    while ($item = tep_db_fetch_array($query)){
      $box_settings_old[] = $item;
    }

    if (ArrayHelper::getValue($params, 'setting') || ArrayHelper::getValue($params, 'visibility')) {
      for ($i=0; $i<17; $i++){
        if ($params['setting'][0]['sort_hide_' . $i] ?? null) {
          $params['setting'][0]['sort_hide_' . $i] = 0;
        } elseif (isset($params['setting'][0]['sort_hide_' . $i])) {
          $params['setting'][0]['sort_hide_' . $i] = 1;
        }
      }

      if (ArrayHelper::getValue($params, ['setting',0,'font_size_dimension']) && !ArrayHelper::getValue($params, ['setting',0,'font-size'])) {
          $params['setting'][0]['font_size_dimension'] = '';
      }

        $convertSettings = [
            // visibility widgets on various pages
            'visibility_home', 'visibility_first_view', 'visibility_more_view', 'visibility_logged', 'visibility_not_logged', 'visibility_product', 'visibility_catalog', 'visibility_info', 'visibility_cart', 'visibility_checkout', 'visibility_success', 'visibility_account', 'visibility_login', 'visibility_other',

            //items on listing product
            'show_name', 'show_image', 'show_stock', 'show_description', 'show_model', 'show_properties', 'show_rating', 'show_rating_counts', 'show_price', 'show_buy_button', 'show_qty_input', 'show_view_button', 'show_wishlist_button', 'show_compare', 'show_bonus_points', 'show_attributes', 'show_paypal_button', 'show_amazon_button',

            'show_name_rows', 'show_image_rows', 'show_stock_rows', 'show_description_rows', 'show_model_rows', 'show_properties_rows', 'show_rating_rows', 'show_rating_counts_rows', 'show_price_rows', 'show_buy_button_rows', 'show_qty_input_rows', 'show_view_button_rows', 'show_wishlist_button_rows', 'show_compare_rows', 'show_bonus_points_rows', 'show_attributes_rows', 'show_paypal_button_rows', 'show_amazon_button_rows',

            'show_name_b2b', 'show_image_b2b', 'show_stock_b2b', 'show_description_b2b', 'show_model_b2b', 'show_properties_b2b', 'show_rating_b2b', 'show_rating_counts_b2b', 'show_price_b2b', 'show_buy_button_b2b', 'show_qty_input_b2b', 'show_view_button_b2b', 'show_wishlist_button_b2b', 'show_compare_b2b', 'show_bonus_points_b2b', 'show_attributes_b2b', 'show_paypal_button_b2b', 'show_amazon_button_b2b',
        ];

        foreach ($convertSettings as $setting) {
            if (isset($params['setting'][0][$setting]) && !$params['setting'][0][$setting]) {
                $params['setting'][0][$setting] = 1;
            } elseif (ArrayHelper::getValue($params, ['setting',0,$setting]) == 1) {
                $params['setting'][0][$setting] = '';
            }
        }
        if (is_array($params['setting'] ?? null)) {
            foreach ($params['setting'] as $language => $set) {
                foreach ($set as $key => $val) {
                    if (is_array($val)){
                        $val = implode(',', $val);
                    }
                    $this->saveBoxSettings($params['id'], $language, $key, $val, '', $set);
                }
            }
        }

      if (is_array($params['visibility'] ?? null)) {
          foreach ($params['visibility'] as $language => $set) {
              foreach ($set as $visibility => $set2) {
                  foreach ($set2 as $key => $val) {
                      if (is_array($val)){
                          $val = implode(',', $val);
                      }
                      $this->saveBoxSettings($params['id'], $language, $key, $val, $visibility, $set2);
                  }
              }
          }
      }
    }

    $oldParams = '';
    $box = DesignBoxesTmp::findOne(['id' => $params['id']]);
    if ($box) {
        $oldParams = $box->widget_params;
    }

      $widget_params = $params['params'] ?? '';
      $sql_data_array = array(
        'widget_params' => tep_db_prepare_input($params['params'] ?? null)
      );
      tep_db_perform(TABLE_DESIGN_BOXES_TMP, $sql_data_array, 'update', "id = '" . (int)$params['id'] . "'");

    $box_settings = array();
      $query = tep_db_query("select setting_name, setting_value, language_id, visibility, microtime, theme_name from " . TABLE_DESIGN_BOXES_SETTINGS_TMP . " where box_id = '" . (int)$params['id'] . "'");
    while ($item = tep_db_fetch_array($query)){
      $box_settings[] = $item;
    }

      Style::createCache($params['theme_name'] ?? null);

      Steps::boxSave([
          'box_id' => $params['id'],
          'microtime' => $p['microtime'],
          'theme_name' => $p['theme_name'],
          'box_settings' => $box_settings,
          'box_settings_old' => $box_settings_old,
          'widget_params' => $widget_params,
          'widget_params_old' => $oldParams,
      ]);


      $this->actionBackupAuto($p['theme_name'], json_encode( ''));
  }


  public function actionStyleEdit()
  {
    $params = tep_db_prepare_input(Yii::$app->request->get());

    $settings = array();
    $styles_query = tep_db_query("select * from " . TABLE_THEMES_STYLES . " where theme_name = '" . tep_db_input($params['theme_name']) . "' and selector = '" . tep_db_input($params['data_class']) . "'");
    $visibility = array();
    while ($styles_arr = tep_db_fetch_array($styles_query)){
      if (!$styles_arr['visibility']){
        $settings[0][$styles_arr['attribute']] = $styles_arr['value'];
      } else {
        $visibility[0][$styles_arr['visibility']][$styles_arr['attribute']] = $styles_arr['value'];
      }
    }
    $this->layout = 'popup.tpl';



    $media_query = array();
    $media_query_arr = tep_db_query("select * from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . tep_db_input($params['theme_name']) . "' and setting_name = 'media_query'");
    while ($item1 = tep_db_fetch_array($media_query_arr)){
      $media_query[] = $item1;
    }
    $settings['media_query'] = $media_query;


    $font_added = array();
    $font_added_arr = tep_db_query("select * from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . tep_db_input($params['theme_name']) . "' and setting_name = 'font_added'");
    while ($item1 = tep_db_fetch_array($font_added_arr)){
      preg_match('/font-family:[ \'"]+([^\'^"^;^}]+)/', $item1['setting_value'], $val);
      $font_added[] = $val[1];
    }
    $settings['font_added'] = $font_added;
    $settings['data_class'] = $params['data_class'];
    $settings['theme_name'] = $params['theme_name'];
    $widget_name = 'backend\design\boxes\StyleEdit';
      $this->actionBackupAuto($params['theme_name'], $widget_name::widget(['id' => 0, 'params' => '', 'settings' => $settings, 'visibility' => $visibility, 'block_type' => '']));

    /*return $this->render('style-edit.tpl', [
      'data_class' => $params['data_class'],
      'theme_name' => $params['theme_name'],
      'settings' => $styles
    ]);*/
  }

    public function styleSave($styles, $params, $visibility = '')
    {
        if (is_array($styles)) foreach ($styles as $key => $val) {

            $accessibility = '';
            if (preg_match('/^(\.w-[0-9a-zA-Z\-\_]+)/', $key, $matches)) {
                $accessibility = $matches[1];
            }

            $total = tep_db_fetch_array(tep_db_query("select count(*) as total from " . TABLE_THEMES_STYLES . " where theme_name = '" . tep_db_input($params['theme_name']) . "' and selector = '" . tep_db_input($params['data_class']) . "' and attribute = '" . tep_db_input($key) . "' and visibility='" . tep_db_input($visibility) . "' and media = ''"));

            if ($val !== '') {

                if ($key == 'background_image') {
                    $setting_value = tep_db_fetch_array(tep_db_query("select ts.value from " . TABLE_THEMES_STYLES . " ts where ts.theme_name = '" . tep_db_input($params['theme_name']) . "' and ts.selector = '" . tep_db_input($params['data_class']) . "' and ts.attribute = '" . tep_db_input($key) . "' and visibility='" . tep_db_input($visibility) . "' and media = ''"));

                    if ($setting_value['value'] != $val) {
                        $val_tmp = Uploads::move($val, 'themes/' . $params['theme_name'] . '/img');
                        if ($val_tmp) $val = $val_tmp;
                    }
                }

                if ($total['total'] == 0) {
                    $sql_data_array = array(
                        'theme_name' => $params['theme_name'],
                        'selector' => $params['data_class'],
                        'attribute' => $key,
                        'value' => $val,
                        'visibility' => $visibility,
                        'media' => '',
                        'accessibility' => $accessibility,
                    );
                    tep_db_perform(TABLE_THEMES_STYLES, $sql_data_array);
                } else {
                    $sql_data_array = array(
                        'value' => $val,
                    );
                    tep_db_perform(TABLE_THEMES_STYLES, $sql_data_array, 'update', "theme_name = '" . tep_db_input($params['theme_name']) . "' and selector = '" . tep_db_input($params['data_class']) . "' and attribute = '" . tep_db_input($key) . "' and visibility='" . tep_db_input($visibility) . "' and media = ''");
                }

            } else {
                if ($total['total'] > 0) {
                    tep_db_query("delete from " . TABLE_THEMES_STYLES . " where theme_name = '" . tep_db_input($params['theme_name']) . "' and selector = '" . tep_db_input($params['data_class']) . "' and attribute = '" . tep_db_input($key) . "' and visibility='" . tep_db_input($visibility) . "' and media = ''");
                }
            }
        }
    }

    public function actionStyleMainSave()
    {
        $styles = Yii::$app->request->post('styles');
        $theme_name = Yii::$app->request->post('theme_name');

        $oldStyles = ThemesStylesMain::find()->where(['theme_name' => $theme_name])->asArray()->all();

        ThemesStylesMain::deleteAll(['theme_name' => $theme_name]);
        if (is_array($styles)) {
            $sortOrder = 0;
            foreach ($styles as $key => $style) {
                $themesStylesMain = new ThemesStylesMain();
                $themesStylesMain->theme_name = $theme_name;
                $themesStylesMain->name = $style['name'];
                $themesStylesMain->value = $style['value'];
                $themesStylesMain->type = $style['type'];
                $themesStylesMain->sort_order = $sortOrder;
                $themesStylesMain->main_style = $style['main_style'] ?? 0;
                $themesStylesMain->save();
                $styles[$key]['sort_order'] = $sortOrder;
                $sortOrder++;
            }
        }

        $data = [
            'theme_name' => $theme_name,
            'old_styles' => $oldStyles,
            'new_styles' => $styles,
        ];
        Steps::styleSave($data);

        return json_encode(['text' => MESSAGE_SAVED]);
    }

    public function actionStylesData()
    {
        $action = Yii::$app->request->get('action');
        $theme_name = Yii::$app->request->get('theme_name');
        $name = Yii::$app->request->get('name');

        switch ($action) {
            case 'count':
                $count = ThemesStyles::find()->where(['theme_name' => $theme_name, 'value' => '$' . $name])->count();
                $count += DesignBoxesSettingsTmp::find()->where(['theme_name' => $theme_name, 'setting_value' => '$' . $name])->count();

                return json_encode(['text' => sprintf(THIS_STYLE_PLACED_IN, $count), 'count' => $count]);
        }
    }

    public function actionStyleSave()
    {
        $values = Yii::$app->request->post('values');
        $post = Yii::$app->request->post();

        $params = Style::paramsFromOneInput($values);

        $params = tep_db_prepare_input($params);
        $params['data_class'] = $params['data_class'] ?? null;
        $params['theme_name'] = $params['theme_name'] ?? null;
        
        $query = tep_db_query("select * from " . TABLE_THEMES_STYLES . " where selector='" . tep_db_input($params['data_class']) . "' and theme_name='" . tep_db_input($params['theme_name']) . "'");
        $styles_old = [];
        while($item = tep_db_fetch_array($query)){
            $styles_old[] = $item;
        }

        if (is_array($params['visibility'][0] ?? null)) {
            foreach ($params['visibility'][0] as $key => $item) {
                $this->styleSave($item, $post, $key);
            }
        }
        $this->styleSave($params['setting'][0] ?? null, $post);

        $query = tep_db_query("select * from " . TABLE_THEMES_STYLES . " where selector='" . tep_db_input($params['data_class']) . "' and theme_name='" . tep_db_input($params['theme_name']) . "'");
        $styles = [];
        while($item = tep_db_fetch_array($query)){
            $styles[] = $item;
        }

        $attributesChanged = array();
        $attributesDelete = array();
        $attributesNew = array();

        foreach ($styles_old as $item) {

            $find = false;
            foreach ($styles as $i => $attr) {
                if (
                    $attr['selector'] == $item['selector'] &&
                    $attr['attribute'] == $item['attribute'] &&
                    $attr['visibility'] == $item['visibility'] &&
                    $attr['media'] == $item['media'] &&
                    $attr['accessibility'] == $item['accessibility']
                ) {
                    if ($attr['value'] != $item['value']) {
                        $attributesChanged[] = [
                            'selector' => $attr['selector'],
                            'attribute' => $attr['attribute'],
                            'value_old' => $item['value'],
                            'value' => $attr['value'],
                            'visibility' => $attr['visibility'],
                            'media' => $attr['media'],
                            'accessibility' => $attr['accessibility']
                        ];
                    }
                    unset($styles[$i]);
                    $find = true;
                }
            }
            if (!$find) {
                $attributesDelete[] = [
                    'selector' => $item['selector'],
                    'attribute' => $item['attribute'],
                    'value' => $item['value'],
                    'visibility' => $item['visibility'],
                    'media' => $item['media'],
                    'accessibility' => $item['accessibility']
                ];
            }
        }

        foreach ($styles as $attr) {
            $attributesNew[] = [
                'theme_name' => $post['theme_name'],
                'selector' => $attr['selector'],
                'attribute' => $attr['attribute'],
                'value' => $attr['value'],
                'visibility' => $attr['visibility'],
                'media' => $attr['media'],
                'accessibility' => $attr['accessibility']
            ];
        }

        Style::createCache($post['theme_name']);

        $data = [
            'theme_name' => $post['theme_name'],
            'attributes_changed' => $attributesChanged,
            'attributes_delete' => $attributesDelete,
            'attributes_new' => $attributesNew,
        ];
        Steps::cssSave($data);

        return '';
    }

    public function actionStyleAdd()
    {
        $theme_name = Yii::$app->request->post('theme_name');
        $name = Yii::$app->request->post('name');
        $value = Yii::$app->request->post('value');
        $type = Yii::$app->request->post('type');
        $main_style = Yii::$app->request->post('main_style');

        if (!$name) {
            return json_encode(['error' => 'Name is empty']);
        }
        if (!$value) {
            return json_encode(['error' => 'Value is empty']);
        }
        if (!$type) {
            return json_encode(['error' => 'Type is empty']);
        }
        if (!$theme_name) {
            return json_encode(['error' => 'theme_name is empty']);
        }
        $style = ThemesStylesMain::findOne(['theme_name' => $theme_name, 'name' => $name]);
        if ($style) {
            return json_encode(['error' => THIS_STYLE_NAME_EXISTS]);
        }

        $oldStyles = ThemesStylesMain::find()->where(['theme_name' => $theme_name])->asArray()->all();

        $style = new ThemesStylesMain();
        $style->theme_name = $theme_name;
        $style->name = $name;
        $style->value = $value;
        $style->type = $type;
        $style->main_style = $main_style;
        $style->save();

        $newStyles = array_merge($oldStyles, [
            'theme_name' => $theme_name,
            'name' => $name,
            'value' => $value,
            'type' => $type,
        ]);

        if (isset($style->errors) && count($style->errors)) {
            return json_encode(['error' => 'db error']);
        }

        $data = [
            'theme_name' => $theme_name,
            'old_styles' => $oldStyles,
            'new_styles' => $newStyles,
        ];
        Steps::styleSave($data);

        return json_encode(['text' => MESSAGE_ADDED]);
    }

  public function actionBackups()
  {
    $params = tep_db_prepare_input(Yii::$app->request->get());

    $this->selectedMenu = array('design_controls', 'design/themes');
    $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('design/themes'), 'title' => TEXT_BACKUPS . ' "' . Theme::getThemeTitle($params['theme_name']) . '"');

    $this->topButtons[] = '<a href="' . Yii::$app->urlManager->createUrl(['design/backup-add', 'theme_name' => $params['theme_name']]) . '" class="create_item">' . NEW_NEW_BACKUP . '</a>';

      $this->topButtons[] = '<span class="mode-title">' . $this->designerModeTitle . '</span>';

    $this->view->headingTitle = TEXT_BACKUPS;

      \backend\design\Data::addJsData([
          'tr' => [
              'IMAGE_SAVE' => IMAGE_SAVE,
              'IMAGE_CANCEL' => IMAGE_CANCEL,
              'TEXT_EXPORT' => TEXT_EXPORT,
          ],
          'platformSelect' => FrontendStructure::getThemePlatforms(),
          'theme_name' => ($params['theme_name'] ? $params['theme_name'] : 'theme-1'),
          'theme_title' => Theme::getThemeTitle($params['theme_name'] ?? null),
      ]);

    return $this->render('backups.tpl', [
      'menu' => 'backups',
      'theme_name' => $params['theme_name'],
      'messages' => [],
        'designer_mode' => $this->designerMode,
    ]);
  }

    public function actionBackupsList ()
    {

        $draw = Yii::$app->request->get('draw', 1);
        $start = Yii::$app->request->get('start', 0);
        $length = Yii::$app->request->get('length', 10);
        $theme_name = tep_db_prepare_input(Yii::$app->request->get('theme_name', 10));

        if ($length == -1)
            $length = 10000;

        $responseList = [];

        if (isset($_GET['order'][0]['column']) && $_GET['order'][0]['dir']) {
            switch ($_GET['order'][0]['column']) {
                case 0:
                    $orderBy = "date_added " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir']));
                    break;
                case 1:
                    $orderBy = "comments " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir']));
                    break;
                default:
                    $orderBy = "date_added";
                    break;
            }
        } else {
            $orderBy = "date_added";
        }

        $orders_status_query_raw = "select * from " . TABLE_DESIGN_BACKUPS . " where theme_name = '" . tep_db_input($theme_name) . "' order by " . $orderBy . " limit " . (int)$_GET['start'] . ", " . (int)$length;
        $count = tep_db_num_rows(tep_db_query("select * from " . TABLE_DESIGN_BACKUPS . " where theme_name = '" . tep_db_input($theme_name) . "' order by " . $orderBy));
        $orders_status_query = tep_db_query($orders_status_query_raw);

        $path = DIR_FS_CATALOG
            . DIRECTORY_SEPARATOR . 'lib'
            . DIRECTORY_SEPARATOR . 'backend'
            . DIRECTORY_SEPARATOR . 'design'
            . DIRECTORY_SEPARATOR . 'backups'
            . DIRECTORY_SEPARATOR . $theme_name
            . DIRECTORY_SEPARATOR;

        while ($orders_status = tep_db_fetch_array($orders_status_query)) {
            if (!is_file($path . $orders_status['backup_id'] . '.zip')) {
                \common\models\DesignBackups::findOne(['backup_id' => $orders_status['backup_id']])->delete();
                continue;
            }

            $short_desc = $orders_status['comments'];
            $short_desc = preg_replace("/<.*?>/", " ", $short_desc);
            if (strlen($short_desc) > 128) {
                $short_desc = substr($short_desc, 0, 122) . '...';
            }

            $responseList[] = array(
                \common\helpers\Date::date_long($orders_status['date_added'], "%d %b %Y / %H:%M:%S"),
                $short_desc . '<input type="hidden" class="backup_id" name="backup_id" value="' . $orders_status['backup_id'] . '">',
            );
        }

        $response = [
            'draw' => $draw,
            'recordsTotal' => $count,
            'recordsFiltered' => $count,
            'data' => $responseList
        ];
        echo json_encode($response);
    }

    public function actionBackupsActions()
    {
        $this->layout = false;
        $backupId = (int)Yii::$app->request->post('backup_id');
        if (!$backupId) {
            return '';
        }
        $comments = \common\models\DesignBackups::findOne(['backup_id' => $backupId])->comments;

        echo '<br>
<div style="font-size: 12px">' . str_replace("\n", '<br>', $comments) . '</div>
<div class="btn-toolbar btn-toolbar-order">
    <button class="btn btn-no-margin" onclick="backupRestore(\'' . $backupId . '\')">' . IMAGE_RESTORE . '</button><button class="btn btn-delete" onclick="translateDelete(\'' . $backupId . '\')">' . IMAGE_DELETE . '</button>
</div>';
    }

    public function actionBackupAdd() {
        $params = Yii::$app->request->get();

        $this->layout = false;
        return $this->render('add.tpl', [
            'theme_name' => $params['theme_name'],
        ]);
    }

    public function actionBackupAuto($theme_name, $return = '')
    {
        ignore_user_abort(true);
        set_time_limit(0);
        ob_start();
        echo $return;
        header('Connection: close');
        header('Content-Length: '.ob_get_length());
        ob_end_flush();
        ob_flush();
        flush();

        $backupDate = \frontend\design\Info::themeSetting('backup_date', 'hide', $theme_name);
        $backupHours = \frontend\design\Info::themeSetting('backup_hours', 'main', $theme_name);
        $backupCount = \frontend\design\Info::themeSetting('backup_count', 'main', $theme_name);

        if (!$backupHours) $backupHours = 1;
        if (!$backupCount) $backupCount = 10;

        $designBackups = \common\models\DesignBackups::find()
            ->where([
                'theme_name' => $theme_name,
                'comments' => 'Auto saved',
            ])
            ->orderBy(['backup_id' => SORT_DESC])
            ->offset($backupCount-1)
            ->asArray()
            ->all();
        if ($designBackups) {
            foreach ($designBackups as $designBackup){
                Backups::delete($designBackup['backup_id']);
            }
        }

        if ($backupDate && (int)$backupDate > 1580000000 && $backupDate + 3600 * $backupHours > time()) {
            return false;
        }

        if ($backupDate){
            $themesSettings = \common\models\ThemesSettings::findOne([
                'theme_name' => $theme_name,
                'setting_group' => 'hide',
                'setting_name' => 'backup_date',
            ]);
        } else {
            $themesSettings = new \common\models\ThemesSettings();
        }


        $themesSettings->setting_value = strval(time());
        $themesSettings->save();

        $this->actionBackupSubmit($theme_name, 'Auto saved');
    }

    public function actionBackupSubmit($themeName = '', $comments = '')
    {
        $themeName = Yii::$app->request->post('theme_name', $themeName);
        $comments = Yii::$app->request->post('comments', $comments);

        $backup = new \common\models\DesignBackups();
        $backup->attributes = [
            'date_added' => new \yii\db\Expression('NOW()'),
            'theme_name' => $themeName,
            'comments' => $comments,
        ];
        $backup->save();
        $backupId = $backup->getPrimaryKey();

        Steps::backupSubmit([
            'theme_name' => $themeName,
            'backup_id' => $backupId,
            'comments' => $comments
        ]);

        Backups::create($themeName, $backupId);

        return json_encode('');
    }

    public function actionExportPopup()
    {
        \common\helpers\Translation::init('admin/banner_manager');
        $theme_name = Yii::$app->request->get('theme_name');

        $menus = \common\models\DesignBoxes::find()
            ->select(['name' => 'widget_params'])->distinct()
            ->where(['widget_name' => 'Menu'])
            ->andWhere(['theme_name' => [$theme_name, $theme_name . '-mobile']])
            ->asArray()->all();

        $banners = \common\models\DesignBoxesSettings::find()
            ->select(['group' => 'setting_value'])->distinct()
            ->where(['setting_name' => 'banners_group', 'theme_name' => [$theme_name, $theme_name . '-mobile']])
            ->asArray()->all();

        $infoPages = \common\models\DesignBoxesSettings::find()
            ->select(['name' => 'setting_value'])->distinct()
            ->where(['setting_name' => 'info_page', 'theme_name' => [$theme_name, $theme_name . '-mobile']])
            ->asArray()->all();

        if (!$menus && !$banners && !$infoPages) {
            return 'no-additionals';
        }
        //return 'no-additionals';

        $this->layout = false;
        return $this->render('export-popup.tpl', [
            'theme_name' => $theme_name,
            'menus' => $menus,
            'banners' => $banners,
            'infoPages' => $infoPages,
        ]);
    }

    public function actionExport()
    {
        $theme_name = Yii::$app->request->get('theme_name');
        if (Yii::$app->request->post()) {
            $_SESSION['exportItems'] = Yii::$app->request->post();
            return 'ok';
        }

        return \backend\design\Theme::export($theme_name);
    }

    public function actionExportData()
    {
        if ($_SESSION['exportItems']['menus']) {
            $menus = [];

            foreach ($_SESSION['exportItems']['menus'] as $menu => $checked) {
                if (!$checked) continue;

                $menus[$menu] = \common\helpers\MenuHelper::menuTree($menu);

            }
        }
    }

    public function actionExportBlock()
    {
        $params = Yii::$app->request->get();
        if (!isset($params['id']) || !$params['id']) {
            $params = Yii::$app->request->post();
        }
        if (!isset($params['id']) || !$params['id']) {
            return json_encode(['error' => 'Error']);
        }

        if (substr($params['id'], 0, 4) == 'box-') {
            $id = intval(substr($params['id'], 4));
            $type = 'id';
        } else {
            $type = 'block_name';
            $id = $params['id'];
        }
        return \backend\design\Theme::exportBlock($id, $type, $params);
    }

    public function actionDownloadBlock()
    {
        $filename = Yii::$app->request->get('filename');

        $fsCatalog = DIR_FS_CATALOG . implode(DIRECTORY_SEPARATOR, ['lib', 'backend', 'design', 'groups']) . DIRECTORY_SEPARATOR;

        header('Cache-Control: none');
        header('Pragma: none');
        header('Content-type: application/x-octet-stream');
        header('Content-disposition: attachment; filename=' . $filename);
        readfile($fsCatalog . $filename);

        if (Yii::$app->request->get('delete')) {
            unlink($fsCatalog . $filename);
        }

        return json_encode(['']);
    }

    public function actionImport()
    {
        $params = Yii::$app->request->get();
        if (isset($_FILES['file']) && isset($_FILES['file']['error']) && isset($_FILES['file']['tmp_name']) && $_FILES['file']['error'] == UPLOAD_ERR_OK  && is_uploaded_file($_FILES['file']['tmp_name'])) {
            if ( \backend\design\Theme::import($params['theme_name'],$_FILES['file']['tmp_name']) ) {
                Theme::saveThemeVersion($params['theme_name']);
                return 'OK';
            }
        }
        return 'error';
    }

    public function actionImportBlock() {
        $params = Yii::$app->request->get();
        if ($_FILES['file']['error'] != UPLOAD_ERR_OK  || !is_uploaded_file($_FILES['file']['tmp_name'])) {
            return 'Error: no file';
        }
        $params['box_id'] = substr($params['box_id'], 4);

        $params['sort_order'] = DesignBoxesTmp::findOne(['id' => (int)$params['box_id']])->sort_order;

        $importBlock = Theme::importBlock($_FILES['file']['tmp_name'], $params);
        if (is_array($importBlock)) {
            [$_arr, $boxId] = $importBlock;
        } else {
            return $importBlock;
        }

        DesignBoxesTmp::deleteAll(['id' => (int)$params['box_id']]);
        DesignBoxesSettingsTmp::deleteAll(['box_id' => (int)$params['box_id']]);

        $data = [
            'id_old' => $params['box_id'],
            'idArr' => $boxId,
            'theme_name' => $params['theme_name'],
        ];
        Steps::importBlock($data);

        return 'Added';
    }

    public function actionBackupRestore()
    {
        $backupId = (int)Yii::$app->request->post('backup_id');

        $backup = \common\models\DesignBackups::find()
            ->select('theme_name')
            ->where(['backup_id' => $backupId])
            ->asArray()->one();

        Backups::backupRestore($backupId, $backup['theme_name']);

        Steps::backupRestore([
            'theme_name' => $backup['theme_name'],
            'backup_id' => $backupId
        ]);
    }

    public function actionBackupDelete() {
        Backups::delete((int)Yii::$app->request->post('backup_id'));
    }

    public function actionGallery()
    {
        $directory = Yii::$app->request->get('directory', ['main']);
        $fileTypes = Yii::$app->request->get('fileTypes', []);
        return json_encode(FileManager::getFiles($directory, $fileTypes));
    }

    public function actionGalleryThumbnail()
    {
        $file = Yii::$app->request->get('file');
        return json_encode(FileManager::createThumbnails($file));
    }

  public function actionSettings() {
    \common\helpers\Translation::init('admin/js');
    $params = tep_db_prepare_input(Yii::$app->request->get());
    $post = tep_db_prepare_input(Yii::$app->request->post(),false);

    $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('design/settings'), 'title' => THEME_SETTINGS . ' "' . Theme::getThemeTitle($params['theme_name']??null) . '"');
    $this->selectedMenu = array('design_controls', 'design/themes');

    $this->topButtons[] = '<span class="redo-buttons"></span>';

      $this->topButtons[] = '<span class="mode-title">' . $this->designerModeTitle . '</span>';

    if (count($post) > 0){

      foreach ($post['setting'] as $key => $val) {
          if ($key == 'background_image_upload') {
              continue;
          }

          $stylesRow = ThemesStyles::findOne([
              'theme_name' => $params['theme_name'],
              'selector' => 'body',
              'attribute' => $key,
              'visibility' => ''
          ]);
          if (!$stylesRow) {
              $stylesRow = new ThemesStyles();
          }

          if (in_array($key, ['background_image'])){
              $val = \common\helpers\Image::prepareSavingImage(
                  ($stylesRow->value ?? ''),
                  $val,
                  $post['setting']['background_image_upload'],
                  'themes' . DIRECTORY_SEPARATOR . $params['theme_name'] . DIRECTORY_SEPARATOR . 'img',
                  false, true
              );
              if (!$val) {
                  ThemesStyles::deleteAll([
                      'theme_name' => $params['theme_name'],
                      'selector' => 'body',
                      'attribute' => $key,
                      'visibility' => ''
                  ]);
                  continue;
              }
          }

          $stylesRow->theme_name = $params['theme_name'];
          $stylesRow->selector = 'body';
          $stylesRow->attribute = $key;
          $stylesRow->value = $val;
          $stylesRow->save();
      }


      $them_settings_old = [];
      $query_s = tep_db_query("select * from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . tep_db_input($params['theme_name']) . "' and (setting_group = 'main' or setting_group = 'extend' or setting_group = 'hide')");
      while ($item = tep_db_fetch_array($query_s)){
        $them_settings_old[] = $item;
      }
      /*echo '<pre>';
      var_dump($them_settings_old);
      echo '</pre>';
      echo json_encode($them_settings_old);die;*/

      foreach ($post['settings'] as $setting_name => $setting_value){

        $sql_data_array = array(
          'theme_name' => $params['theme_name'],
          'setting_group' => 'main',
          'setting_name' => $setting_name,
          'setting_value' => $setting_value,
        );

        $query = tep_db_fetch_array(tep_db_query("select count(*) as total from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . tep_db_input($params['theme_name']) . "' and setting_group = 'main' and setting_name = '" . tep_db_input($setting_name) . "'"));
        if ($query['total'] > 0){
          tep_db_perform(TABLE_THEMES_SETTINGS, $sql_data_array, 'update', " theme_name = '" . tep_db_input($params['theme_name']) . "' and setting_group = 'main' and setting_name = '" . tep_db_input($setting_name) . "'");
        } else {
          tep_db_perform(TABLE_THEMES_SETTINGS, $sql_data_array);
        }

      }


      if (is_array($post['extend']??null)) {
        foreach ($post['extend'] as $setting_name => $val) {
          foreach ($val as $id => $setting_value) {

            $sql_data_array = array(
                'setting_value' => $setting_value,
            );
            $query = tep_db_fetch_array(tep_db_query("select count(*) as total from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . tep_db_input($params['theme_name']) . "' and setting_group = 'extend' and setting_name = '" . tep_db_input($setting_name) . "' and id = '" . (int)$id . "'"));
            if ($query['total'] > 0) {
              tep_db_perform(TABLE_THEMES_SETTINGS, $sql_data_array, 'update', " theme_name = '" . tep_db_input($params['theme_name']) . "' and setting_group = 'extend' and setting_name = '" . tep_db_input($setting_name) . "' and id = '" . (int)$id . "'");
            }
          }
        }
      }

      Theme::saveFavicon();
      Theme::saveThemeImage('logo');
      Theme::saveThemeImage('na_category');
      Theme::saveThemeImage('na_product');

      $them_settings = [];
      $query_s = tep_db_query("select * from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . tep_db_input($params['theme_name']) . "' and (setting_group = 'main' or setting_group = 'extend' or setting_group = 'hide')");
      while ($item = tep_db_fetch_array($query_s)){
        $them_settings[] = $item;
      }

      $data = [
        'theme_name' => $params['theme_name'],
        'them_settings_old' => $them_settings_old,
        'them_settings' => $them_settings,
      ];
      Steps::settings($data);

    }

    $query = tep_db_query("select setting_name, setting_value from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . tep_db_input($params['theme_name']) . "'");

    $settings = array();
    while ($item = tep_db_fetch_array($query)){
      $settings[$item['setting_name']] = $item['setting_value'];
    }

    $styles = array();
    $styles_query = tep_db_query("select * from " . TABLE_THEMES_STYLES . " where theme_name = '" . tep_db_input($params['theme_name']) . "' and selector = 'body' and visibility=''");
    while ($styles_arr = tep_db_fetch_array($styles_query)){
      $styles[$styles_arr['attribute']] = $styles_arr['value'];
    }

    $path = \Yii::getAlias('@webroot');
    $path .= DIRECTORY_SEPARATOR;
    $path .= '..';
    $path .= DIRECTORY_SEPARATOR;
    $path .= 'themes';
    $path .= DIRECTORY_SEPARATOR;
    $path .= $_GET['theme_name'];
    $path .= DIRECTORY_SEPARATOR;
    $path .= 'icons';
    $path .= DIRECTORY_SEPARATOR;
    if (is_file($path . 'favicon-16x16.png')){
      $favicon = '../themes/' . $_GET['theme_name'] . '/icons/favicon-16x16.png';
    } else {
      $favicon = '../themes/basic/icons/favicon-16x16.png';
    }

      $this->actionBackupAuto($params['theme_name'], $this->render('settings.tpl', [
          'favicon' => $favicon,
          'menu' => 'settings',
          'settings' => $settings,
          'setting' => $styles,
          'theme_name' => $params['theme_name'],
          'action' => Yii::$app->urlManager->createUrl(['design/settings', 'theme_name' => $params['theme_name']]),
          'is_mobile' => strpos($_GET['theme_name'], '-mobile') ? true : false,
          'designer_mode' => $this->designerMode,
      ]));
  }

  public function actionExtend() {
    $get = tep_db_prepare_input(Yii::$app->request->get());

    if ($get['remove'] ?? null){
        //Steps::extendRemove(['theme_name' => $get['theme_name'], 'id' => (int)$get['remove']]);

        $data = [
            'theme_name' => $get['theme_name'],
            'them_settings_old' => ThemesSettings::find()->where(['id' => (int)$get['remove']])->asArray()->all(),
            'them_settings' => [],
        ];
        Steps::settings($data);

      tep_db_query("delete from " . TABLE_THEMES_SETTINGS . " where id = '" . (int)$get['remove'] . "'");
      tep_db_query("delete from " . TABLE_DESIGN_BOXES_SETTINGS . " where visibility = '" . (int)$get['remove'] . "'");
      tep_db_query("delete from " . TABLE_DESIGN_BOXES_SETTINGS_TMP . " where visibility = '" . (int)$get['remove'] . "'");
      tep_db_query("delete from " . TABLE_THEMES_STYLES . " where visibility = '" . (int)$get['remove'] . "'");
      //tep_db_query("delete from " . TABLE_THEMES_STYLES_TMP . " where visibility = '" . (int)$get['remove'] . "'");
    }

    if ($get['add'] ?? null){
      $sql_data_array = array(
        'theme_name' =>$get['theme_name'],
        'setting_group' => 'extend',
        'setting_name' => $get['setting_name'],
        'setting_value' => '',
      );
      tep_db_perform(TABLE_THEMES_SETTINGS, $sql_data_array);
      $added_id = tep_db_insert_id();

      $sql_data_array['id'] = $added_id;
      //Steps::extendAdd(['theme_name' => $get['theme_name'], 'data' => $sql_data_array]);
    }

    $query = tep_db_query("select id, setting_name, setting_value from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . tep_db_input($get['theme_name']) . "' and setting_group = 'extend' and setting_name = '" . tep_db_input($get['setting_name']) . "'");
    $arr = array();
    while ($item = tep_db_fetch_array($query)){
      $arr[] = $item;
    }
    return json_encode($arr);
  }


  public function actionDemoStyles() {
    $post = tep_db_prepare_input(Yii::$app->request->post());
    $class = str_replace('\\', '', $post['data_class'] ?? null);
    $style = $class . '{' . \frontend\design\Block::styles($post['setting'] ?? null).'}';

    $key_arr = explode(',', $class);
    for ($i = 1; $i < 5; $i++) {
      $add = '';
      switch ($i) {
        case 1: $add = ':hover'; break;
        case 2: $add = '.active'; break;
        case 3: $add = ':before'; break;
        case 4: $add = ':after'; break;
      }
      $selector_arr = array();
      foreach ($key_arr as $item) {
        $selector_arr[] = trim($item) . $add;
      }
      $selector = implode(', ', $selector_arr);
      $params[0] = $post['visibility'][0][$i] ?? null;
      $style .= $selector . '{' . \frontend\design\Block::styles($params) . '}';
    }

    return $style;
  }

    public function actionLog()
    {
        $get = tep_db_prepare_input(Yii::$app->request->get());
        $get['from'] = $get['from'] ?? null;
        $get['to'] = $get['to'] ?? null;
        $this->topButtons[] = '<span class="redo-buttons"></span>';

        $this->topButtons[] = '<span class="mode-title">' . $this->designerModeTitle . '</span>';

        $this->selectedMenu = array('design_controls', 'design/themes');
        $this->view->headingTitle = LOG_TEXT . ' "' . Theme::getThemeTitle($get['theme_name']) . '"';
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('design/settings'), 'title' => 'Log "' . Theme::getThemeTitle($get['theme_name']) . '"');

        $admins = array();
        $query = tep_db_query("select admin_id, admin_firstname, admin_lastname, admin_email_address from " . TABLE_ADMIN . "");
        while ($item = tep_db_fetch_array($query)){
            $admins[$item['admin_id']] = $item;
        }

        $date = [];
        $date['from'] = empty($get['from']) ? null: $get['from'];
        $date['to'] = empty($get['to']) ? null: $get['to'];

        if (Yii::$app->request->isAjax) {
            $this->layout = 'popup.tpl';
        }

        $updates = Style::getNewUpdates($get['parent_theme'] ?? null);

        return $this->render('log.tpl', [
            'tree' => Steps::log($get['theme_name'], $date),
            'admins' => $admins,
            'theme_name' => $get['theme_name'],
            'menu' => 'log',
            'from' => $get['from'],
            'to' => $get['to'],
            'apple_update' => count($updates) > 0 ? false : true,
            'update_buttons' => \backend\components\Information::showHidePage(),
            'designer_mode' => $this->designerMode,
        ]);
    }

    public function actionLogDetails()
    {
        $get = tep_db_prepare_input(Yii::$app->request->get());

        if (Yii::$app->request->isAjax) {
            $this->layout = 'popup.tpl';
        }

        return $this->render('log-details.tpl', [
            'details' => Steps::logDetails($get['id']),
        ]);
    }

  public function actionUndo() {
    $get = tep_db_prepare_input(Yii::$app->request->get());
    Steps::undo($get['theme_name']);
  }

  public function actionRedo() {
    $get = tep_db_prepare_input(Yii::$app->request->get());
    Steps::redo($get['theme_name'], $get['steps_id']);
  }

  public function actionRedoButtons() {
    $get = tep_db_prepare_input(Yii::$app->request->get());

    $redo_query = tep_db_query("select sr.steps_id, sr.event, sr.date_added, sr.admin_id from " . TABLE_THEMES_STEPS . " sr left join " . TABLE_THEMES_STEPS . " sa on sr.parent_id = sa.steps_id where sa.active='1' and sr.theme_name='" . tep_db_input($get['theme_name']) . "'");
    $redo = '';
    while ($item = tep_db_fetch_array($redo_query)){
      $redo .= '<span class="btn btn-redo btn-elements" data-id="' . $item['steps_id'] . '" data-event="' . $item['event'] . '" title="' . Steps::logNames($item['event']) . ' (' . \common\helpers\Date::date_long($item['date_added'], "%d %b %Y / %H:%M:%S") . ')">' . LOG_REDO . '</span>';
    }

    $undo = tep_db_fetch_array(tep_db_query("select steps_id, event, date_added, admin_id from " . TABLE_THEMES_STEPS . " where active='1' and parent_id!='0' and theme_name='" . tep_db_input($get['theme_name']) . "'"));

    if ($undo['steps_id'] ?? null) {
      $redo .= '<span class="btn btn-undo btn-elements" data-event="' . $undo['event'] . '" title="' . Steps::logNames($undo['event']) . ' (' . \common\helpers\Date::date_long($undo['date_added'], "%d %b %Y / %H:%M:%S") . ')">' . LOG_UNDO . '</span>';
    }

    echo $redo;
  }

  public  function actionStepRestore()
  {
    $get = tep_db_prepare_input(Yii::$app->request->get());
    $text = Steps::restore($get['id']);
    if ($text){
      $text = '
<div class="popup-box-wrap pop-mess">
    <div class="around-pop-up"></div>
    <div class="popup-box">
        <div class="pop-up-close pop-up-close-alert"></div>
        <div class="pop-up-content">
            <div class="popup-content pop-mess-cont pop-mess-cont-error">
                ' . $text . '
            </div>
        </div>
            <div class="noti-btn">
                    <div></div>
                    <div><span class="btn btn-primary">' . TEXT_BTN_OK . '</span></div>
                </div>
    </div>
<script>
    $(\'body\').scrollTop(0);
    $(\'.pop-mess .pop-up-close-alert, .noti-btn .btn\').click(function () {
        $(this).parents(\'.pop-mess\').remove();
    });
</script>
</div>
';
    }
    return $text;
  }

  public  function actionFindSelector()
  {
    $get = tep_db_prepare_input(Yii::$app->request->get());

    $selectors_query = tep_db_query("
      select DISTINCT selector
      from " . TABLE_THEMES_STYLES . "
      where theme_name = '" . tep_db_input($get['theme_name']) . "' and
        selector LIKE '%" . tep_db_input($get['selector']) . "%'
");

    $html = '';
    while ($item = tep_db_fetch_array($selectors_query)) {
      $html .= '<div class="item">' . $item['selector'] . '</div>';
    }

    if ($html == '') {
      $html = '<div class="no-selector">Not found selectors.</div>';
    }

    return $html;

  }

  public  function actionStyles()
  {
    $get = tep_db_prepare_input(Yii::$app->request->get());

      /*$this->topButtons[] = '<span class="redo-buttons"></span>';*/

    /*$this->topButtons[] = '<span data-href="' . Yii::$app->urlManager->createUrl(['design/theme-save', 'theme_name' => $get['theme_name']]) . '" class="btn btn-confirm btn-save-boxes btn-elements">'.IMAGE_SAVE.'</span> <span class="redo-buttons"></span>';*/


      $this->topButtons[] = '<span class="mode-title">' . $this->designerModeTitle . '</span>';
    $this->selectedMenu = array('design_controls', 'design/themes');
    $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('design/elements'), 'title' => BOX_HEADING_MAIN_STYLES . ' "' . Theme::getThemeTitle($get['theme_name']) . '"');
    $this->view->headingTitle = BOX_HEADING_MAIN_STYLES . ' "' . Theme::getThemeTitle($get['theme_name']) . '"';

    $path = \Yii::getAlias('@webroot');
    $path .= DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
    $path .= 'lib' . DIRECTORY_SEPARATOR . 'frontend' . DIRECTORY_SEPARATOR;
    $path .= 'themes' . DIRECTORY_SEPARATOR . 'basic' . DIRECTORY_SEPARATOR;
    $path .= 'index' . DIRECTORY_SEPARATOR . 'design';
    $files = scandir($path);
    $sf = array();
    foreach ($files as $item) {
      if ($item != '.' && $item != '..') {
        $content = file_get_contents($path  . DIRECTORY_SEPARATOR . $item);
        preg_match_all("/Info\:\:dataClass\([\'\"]([^}]+)[\'\"]/", $content, $arr);
        $sf = array_merge($sf, $arr[1]);
      }
    }


    $fontColors = array();
    $query = tep_db_query("select value from " . TABLE_THEMES_STYLES . " where theme_name = '" .tep_db_input($get['theme_name']) . "' and attribute = 'color'");
    while ($item = tep_db_fetch_array($query)) {
      if ($fontColors[$item['value']] ?? null){
        $fontColors[$item['value']]++;
      } else {
        $fontColors[$item['value']] = 1;
      }
    }
    $query = tep_db_query("select bs.setting_value from " . TABLE_DESIGN_BOXES_TMP . " b left join " . TABLE_DESIGN_BOXES_SETTINGS_TMP . " bs on b.id = bs.box_id where b.theme_name = '" .tep_db_input($get['theme_name']) . "' and bs.setting_name = 'color'");
    while ($item = tep_db_fetch_array($query)) {
      if ($fontColors[$item['setting_value']] ?? null){
        $fontColors[$item['setting_value']]++;
      } else {
        $fontColors[$item['setting_value']] = 1;
      }
    }

    $backgroundColors = array();
    $query = tep_db_query("select value from " . TABLE_THEMES_STYLES . " where theme_name = '" .tep_db_input($get['theme_name']) . "' and attribute = 'background-color'");
    while ($item = tep_db_fetch_array($query)) {
      if ($backgroundColors[$item['value']] ?? null){
        $backgroundColors[$item['value']]++;
      } else {
        $backgroundColors[$item['value']] = 1;
      }
    }
    $query = tep_db_query("select bs.setting_value from " . TABLE_DESIGN_BOXES_TMP . " b left join " . TABLE_DESIGN_BOXES_SETTINGS_TMP . " bs on b.id = bs.box_id where b.theme_name = '" .tep_db_input($get['theme_name']) . "' and bs.setting_name = 'background-color'");
    while ($item = tep_db_fetch_array($query)) {
      if ($backgroundColors[$item['setting_value']] ?? null){
        $backgroundColors[$item['setting_value']]++;
      } else {
        $backgroundColors[$item['setting_value']] = 1;
      }
    }

    $borderColors = array();
    $query = tep_db_query("select value from " . TABLE_THEMES_STYLES . " where theme_name = '" .tep_db_input($get['theme_name']) . "' and attribute in ('border-top-color', 'border-left-color', 'border-right-color', 'border-bottom-color', 'border-color')");
    while ($item = tep_db_fetch_array($query)) {
      if ($borderColors[$item['value']] ?? null){
        $borderColors[$item['value']]++;
      } else {
        $borderColors[$item['value']] = 1;
      }
    }
    $query = tep_db_query("select bs.setting_value from " . TABLE_DESIGN_BOXES_TMP . " b left join " . TABLE_DESIGN_BOXES_SETTINGS_TMP . " bs on b.id = bs.box_id where b.theme_name = '" .tep_db_input($get['theme_name']) . "' and bs.setting_name in ('border-top-color', 'border-left-color', 'border-right-color', 'border-bottom-color', 'border-color')");
    while ($item = tep_db_fetch_array($query)) {
      if ($borderColors[$item['setting_value']] ?? null){
        $borderColors[$item['setting_value']]++;
      } else {
        $borderColors[$item['setting_value']] = 1;
      }
    }

    $fontFamily = array();
    $query = tep_db_query("select value from " . TABLE_THEMES_STYLES . " where theme_name = '" .tep_db_input($get['theme_name']) . "' and attribute = 'font-family'");
    while ($item = tep_db_fetch_array($query)) {
      if ($item['value'] != 'FontAwesome' && $item['value'] != 'trueloaded') {
        if ($fontFamily[$item['value']] ?? null) {
          $fontFamily[$item['value']]++;
        } else {
          $fontFamily[$item['value']] = 1;
        }
      }
    }
    $query = tep_db_query("select bs.setting_value from " . TABLE_DESIGN_BOXES_TMP . " b left join " . TABLE_DESIGN_BOXES_SETTINGS_TMP . " bs on b.id = bs.box_id where b.theme_name = '" .tep_db_input($get['theme_name']) . "' and bs.setting_name = 'font-family'");
    while ($item = tep_db_fetch_array($query)) {
      if ($item['setting_value'] != 'FontAwesome' && $item['setting_value'] != 'trueloaded') {
        if ($fontFamily[$item['setting_value']] ?? null) {
          $fontFamily[$item['setting_value']]++;
        } else {
          $fontFamily[$item['setting_value']] = 1;
        }
      }
    }

    $fontAdded = array();
    $fontAddedArr = tep_db_query("select * from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . tep_db_input($get['theme_name']) . "' and setting_name = 'font_added'");
    while ($item1 = tep_db_fetch_array($fontAddedArr)){
      preg_match('/font-family:[ \'"]+([^\'^"^;^}]+)/', $item1['setting_value'], $val);
      $fontAdded[] = $val[1];
    }

    $tpl = 'styles-new.tpl';
    if (Yii::$app->request->get('old')) {
        $tpl = 'styles.tpl';
    }

      $mainStyles = ThemesStylesMain::find()->where(['theme_name' => $get['theme_name']])
          ->orderBy('sort_order')->asArray()->all();

      foreach ($mainStyles as $key => $style) {
          $count = DesignBoxesSettingsTmp::find()->where([
              'theme_name' => $get['theme_name'],
              'setting_value' => '$' . $style['name']
          ])->count();
          $count = $count + ThemesStyles::find()->where([
              'theme_name' => $get['theme_name'],
              'value' => '$' . $style['name']
          ])->count();
          $mainStyles[$key]['count'] = $count;
      }

    return $this->render($tpl, [
        'theme_name' => $get['theme_name'],
        'fontColors' => $fontColors,
        'backgroundColors' => $backgroundColors,
        'borderColors' => $borderColors,
        'fontFamily' => $fontFamily,
        'fontAdded' => $fontAdded,
        'designer_mode' => $this->designerMode,
        'mainStyles' => $mainStyles,
        'menu' => 'styles',
    ]);
  }

    public  function actionStylesChange()
    {
        $get = tep_db_prepare_input(Yii::$app->request->get());

        Steps::stylesChange([
            'from' => $get['from'],
            'to' => $get['to'],
            'style' => $get['style'],
            'theme_name' => $get['theme_name']
        ]);


        if ($get['style'] == 'border-color') {
            $attribute = " and attribute in ('border-top-color', 'border-left-color', 'border-right-color', 'border-bottom-color', 'border-color')";
        } else {
            $attribute = " and attribute = '" . tep_db_input($get['style']) . "'";
        }
        tep_db_perform(
            TABLE_THEMES_STYLES,
            array('value' => $get['to']),
            'update',
            " theme_name = '" . tep_db_input($get['theme_name']) . "'" . $attribute . " and value = '" . tep_db_input($get['from']) . "'"
        );

        if ($get['style'] == 'border-color') {
            $setting_name = " and bs.setting_name in ('border-top-color', 'border-left-color', 'border-right-color', 'border-bottom-color', 'border-color')";
        } else {
            $setting_name = " and bs.setting_name = '" . tep_db_input($get['style']) . "'";
        }
        $query = tep_db_query("select bs.id from " . TABLE_DESIGN_BOXES_TMP . " b left join " . TABLE_DESIGN_BOXES_SETTINGS_TMP . " bs on b.id = bs.box_id where b.theme_name = '" . tep_db_input($get['theme_name']) . "' " . $setting_name . " and bs.setting_value = '" . tep_db_input($get['from']) . "'");
        while ($item = tep_db_fetch_array($query)) {
            tep_db_perform(TABLE_DESIGN_BOXES_SETTINGS_TMP, array('setting_value' => $get['to']), 'update', " id = '" . $item['id'] . "'");
        }

        Style::createCache($get['theme_name']);

        return '<div style="padding: 30px;">Changed</div><script type="text/javascript">setTimeout(function(){location.reload()}, 500);</script>';
    }

    public  function actionRemoveClass()
    {
        $themeName = Yii::$app->request->get('theme_name');
        $cssClass = Yii::$app->request->get('class');
        if (!$themeName || !$cssClass) return 'Error';

        Steps::removeClass([
            'class' => $cssClass,
            'theme_name' => $themeName
        ]);

        $attributesDelete = ThemesStyles::find()->where([
            'theme_name' => $themeName,
            'selector' => $cssClass,
        ])->asArray()->all();

        $data = [
            'theme_name' => $themeName,
            'attributes_changed' => [],
            'attributes_delete' => $attributesDelete,
            'attributes_new' => [],
        ];
        Steps::cssSave($data);

        ThemesStyles::deleteAll([
            'theme_name' => $themeName,
            'selector' => $cssClass,
        ]);

        Style::createCache($themeName);

        return 'Ok';
    }


    public function actionRemoveHiddenBoxes() {
        $theme_query = tep_db_query("select theme_name from " . TABLE_THEMES . " where 1");
        while ($theme = tep_db_fetch_array($theme_query)) {
            $query = tep_db_query("select bs.box_id from " . TABLE_DESIGN_BOXES_SETTINGS . " bs left join " . TABLE_DESIGN_BOXES . " b on b.id = bs.box_id where bs.setting_name = 'display_none' and bs.visibility = '' and b.theme_name = '" . tep_db_input($theme['theme_name']) . "'");
            $removed = '';
            while ($item = tep_db_fetch_array($query)) {
                $id = $item['box_id'];
                $removed .= $id . '<br>';
                /*Steps::boxDelete([
                    'theme_name' => $theme['theme_name'],
                    'id' => $id
                ]);*/
                //$this->actionBackupAuto($theme['theme_name']);
                //tep_db_query("delete from " . TABLE_DESIGN_BOXES_TMP . " where id = '" . (int) $id . "'");
                //tep_db_query("delete from " . TABLE_DESIGN_BOXES_SETTINGS_TMP . " where box_id = '" . (int) $id . "'");
                self::deleteBlock($id);
            }
            tep_db_query("DELETE FROM " . TABLE_THEMES_STYLES . " WHERE visibility > 10 AND visibility NOT IN (SELECT id FROM " . TABLE_THEMES_SETTINGS . " WHERE `setting_name` LIKE 'media_query' )");
        }
        return 'Removed:<br>' . $removed;
    }


    public function actionCreateUpdate() {
        $post = tep_db_prepare_input(Yii::$app->request->post());

        if (!isset($post['theme_name'])) {
            return 'error';
        }

        if (!isset($post['steps']) || !is_array($post['steps'])) {
            return 'error';
        }

        $migration = Steps::createMigration($post['theme_name'], $post['steps']);

        header('Content-Type: application/json');
        header("Content-Transfer-Encoding: utf-8");
        header('Content-disposition: attachment; filename="migration-' . $post['theme_name'] . '.json"');
        return json_encode($migration);

        /*$idArr = [];
        foreach ($post['id_array'] as $item) {
            $idArr[] = (int)$item;
        }

        $query = tep_db_query("
            select * 
            from " . TABLE_THEMES_STEPS . " 
            where
                theme_name = '" . tep_db_input($post['theme_name']) . "' and
                event = 'cssSave' and
                steps_id in('" . implode("','", $idArr) . "')
            order by date_added asc");

        $themeSteps = [];

        while ($item = tep_db_fetch_array($query)) {
            $themeSteps[] = json_decode($item['data'], true);
        }

        $attributes = Style::mergeSteps($themeSteps);

        $attributes['attributes_new'] = Style::changeVisibilityFromIdToWidth($attributes['attributes_new']);
        $attributes['attributes_changed'] = Style::changeVisibilityFromIdToWidth($attributes['attributes_changed']);
        $attributes['attributes_delete'] = Style::changeVisibilityFromIdToWidth($attributes['attributes_delete']);


        $filePath = DIR_FS_CATALOG . 'themes'
            . DIRECTORY_SEPARATOR . $post['theme_name']
            . DIRECTORY_SEPARATOR . 'updates'
            . DIRECTORY_SEPARATOR;
        \yii\helpers\FileHelper::createDirectory($filePath);
        $date = date("U");
        $fileLength = file_put_contents($filePath . $date . '.json', json_encode($attributes));

        if ($fileLength) {
            Style::saveUpdateDate($post['theme_name'], $date);

            return '<div style="padding: 20px; text-align: center">Update created</div>';
        }

        return '<div style="padding: 20px; text-align: center">Error: Update not created</div>';*/

    }

    public function actionApplyMigration()
    {
        $get = Yii::$app->request->get();
        if ($_FILES['file']['error'] == UPLOAD_ERR_OK  && is_uploaded_file($_FILES['file']['tmp_name'])) {
            $migration = json_decode(file_get_contents($_FILES['file']['tmp_name']), true);
            if ( $result = Steps::applyMigration($get['theme_name'], $migration) ) {
                Theme::elementsSave($get['theme_name']);
                DesignBoxesCache::deleteAll(['theme_name' => $get['theme_name']]);
                Theme::saveThemeVersion($get['theme_name']);
                return $result;
            }
        }
        return 'error';
    }

    public function actionApplyUpdate() //deprecated
    {
        $get = tep_db_prepare_input(Yii::$app->request->get());

        $updates = Style::getNewUpdates($get['theme_name']);

        $update = Style::mergeSteps($updates);

        $update = Style::changeVisibilityFromWidthToId($update, $get['theme_name']);

        $update = Style::addExistValueFromCurrentTheme($update, $get['theme_name']);// and add local_id

        $update = Style::changeSelectorsByVisibility($update);

        $attributesByMedia = Style::addToArraySortedByMediaAndSelector($update, $get['theme_name']);

        if (Yii::$app->request->isAjax) {
            $this->layout = 'popup.tpl';
        }

        return $this->render('apply-update.tpl', [
            'attributes' => $attributesByMedia,
            'theme_name' => $get['theme_name'],
        ]);

    }

    public function actionApplyUpdateSubmit() //deprecated
    {
        $get = tep_db_prepare_input(Yii::$app->request->get());
        $post = tep_db_prepare_input(Yii::$app->request->post());

        $updates = Style::getNewUpdates($get['theme_name']);

        $update = Style::mergeSteps($updates);

        $update = Style::changeVisibilityFromWidthToId($update, $get['theme_name']);

        $update = Style::addExistValueFromCurrentTheme($update, $get['theme_name']);// and add local_id

        Style::saveUpdate($post, $update, $get['theme_name']);

        $sql_data_array = array(
            'theme_name' => $get['theme_name'],
            'setting_group' => 'hide',
            'setting_name' => 'theme_update',
            'setting_value' => date("U"),
        );
        tep_db_perform(TABLE_THEMES_SETTINGS, $sql_data_array);

        return Yii::$app->getResponse()->redirect(['design/log', 'theme_name' => $get['theme_name']]);

    }

    public function actionCssStatus()
    {
        $get = tep_db_prepare_input(Yii::$app->request->get());

        $devPath = DIR_FS_CATALOG . 'themes/' . $get['theme_name'] . '/development/';
        if (!is_file($devPath)) {
            \yii\helpers\FileHelper::createDirectory($devPath);
        }

        $development_mode = tep_db_fetch_array(tep_db_query("select setting_value from " . TABLE_THEMES_SETTINGS . " where setting_name = 'development_mode' and setting_group = 'hide' and theme_name = '" . tep_db_input($get['theme_name']) . "'"));
        tep_db_query("delete from " . TABLE_THEMES_SETTINGS . " where setting_name = 'development_mode' and setting_group = 'hide' and theme_name = '" . tep_db_input($get['theme_name']) . "'");

        $query = tep_db_query("select * from " . TABLE_THEMES_STYLES_CACHE . " where theme_name = '" . tep_db_input($get['theme_name']) . "'");
        while ($item = tep_db_fetch_array($query)) {
            if (!$item['accessibility']) {
                $item['accessibility'] = 'main';
            }

            if ($get['status']) {

                file_put_contents($devPath . 'style' . $item['accessibility'] . '.css', $item['css']);

            } elseif (filemtime($devPath . 'style' . $item['accessibility'] . '.css') > $development_mode['setting_value']) {
                $css = file_get_contents($devPath . 'style' . $item['accessibility'] . '.css');

                if ($item['accessibility'] != 'main') {
                    $css = str_replace($item['accessibility'], '', $css);
                }

                $params = [
                    'css' => $css,
                    'theme_name' => $get['theme_name'],
                    'widget' => $item['accessibility'],
                ];
                Style::cssSave($params);
            }
        }

        if ($get['status']) {
            tep_db_perform(TABLE_THEMES_SETTINGS, [
                'theme_name' => $get['theme_name'],
                'setting_name' => 'development_mode',
                'setting_group' => 'hide',
                'setting_value' => date("U"),
            ]);
        }

        return 'ok';

    }

    public function actionStyleTab()
    {
        $get = tep_db_prepare_input(Yii::$app->request->get());

        if ($get['box_id']) {
            $query = tep_db_query("
                select setting_name, setting_value
                from " . TABLE_DESIGN_BOXES_SETTINGS_TMP . " 
                where 
                    box_id = '" . (int)$get['box_id'] . "' and 
                    visibility = '" . tep_db_input($get['visibility'] ? $get['visibility'] : '') . "' and
                    language_id = '0'
            ");
        } elseif ($get['data_class']) {
            $query = tep_db_query("
                select attribute as setting_name, value as setting_value
                from " . TABLE_THEMES_STYLES . " 
                where theme_name = '" .  tep_db_input($get['theme_name']) . "' and
                selector = '" . tep_db_input($get['data_class']) . "' and 
                visibility = '" . tep_db_input($get['visibility'] ? $get['visibility'] : '') . "'
            ");
        }

        $value = [];
        while ($item = tep_db_fetch_array($query)) {
            $value[$item['setting_name']] = $item['setting_value'];
        }

        $this->layout = 'popup.tpl';

        $font_added = array();
        $font_added_arr = tep_db_query("select * from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . tep_db_input($get['theme_name']) . "' and setting_name = 'font_added'");
        while ($item1 = tep_db_fetch_array($font_added_arr)){
            preg_match('/font-family:[ \'"]+([^\'^"^;^}]+)/', $item1['setting_value'], $val);
            $font_added[] = $val[1];
        }

        return $this->render('/../design/boxes/views/include/style_tab.tpl', [
            'id' => $get['id'],
            'name' => $get['name'],
            'theme_name' => $get['theme_name'],
            'value' => $value,
            'responsive' => ($get['visibility'] > 10 ? '1' : ''),
            'responsive_settings' => json_decode($get['responsive_settings'], true),
            'block_view' => $get['block_view'],
            'font_added' => $font_added,
            'designer_mode' => $this->designerMode,
            'styleHide' => (isset($get['data_class']) && $get['data_class'] ? Style::hide($get['data_class']) : []),
        ]);

    }

    public function actionChooseView()
    {
        $get = tep_db_prepare_input(Yii::$app->request->get());

        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('design/choose-view'), 'title' => 'Choose View "' . Theme::getThemeTitle($get['theme_name']) . '"');
        $this->selectedMenu = array('design_controls', 'design/themes');

        return $this->render('choose-view.tpl', [
            'theme_name' => $get['theme_name'],
            'theme_name_mobile' => $get['theme_name'] . '-mobile',
            'designer_mode' => $this->designerMode,
        ]);
    }

    public function actionCreateMobileTheme()
    {

        $theme_name = Yii::$app->request->get('theme_name');

        if (substr($theme_name, -7) !== '-mobile') {
            return WRONG_THEME_NAME;
        }

        $desktop_theme_name = substr($theme_name, 0, -7);

        $theme = tep_db_fetch_array(tep_db_query("select id from " . TABLE_THEMES . " where theme_name = '" . tep_db_input($desktop_theme_name) . "'"));
        if (!$theme['id']) {
            return WRONG_THEME_NAME;
        }
        Theme::themeRemove($theme_name, false);
        Theme::copyTheme($theme_name, $desktop_theme_name, 'copy'); //'link', 'copy'
        Style::createCache($theme_name);

        return TEXT_CREATED;
    }

    public function actionGetComponentHtml()
    {
        $getRequest = \Yii::$app->request->get();
        if (!$getRequest['name']) {
            return '';
        }

        $platformsToThemes = \common\models\PlatformsToThemes::findOne((int)$getRequest['platform_id']);
        $themes = \common\models\Themes::findOne($platformsToThemes['theme_id']);
        $theme_name = $themes->theme_name;

        $getRequest['theme_name'] = $theme_name;
        if ($getRequest['option'] && $getRequest['option_val']) {
            $getRequest[$getRequest['option']] = $getRequest['option_val'];
        }

        define('THEME_NAME', $theme_name);

        $block = \frontend\design\Block::widget([
            'name' => \common\classes\design::pageName($getRequest['name']),
            'params' => [
                'params' => $getRequest,

            ]
        ]);

        $css = file_get_contents(Info::themeFile('/css/base_3.css', 'fs'));

        $widgets = \frontend\design\Info::getWidgetsNames();
        $areaArr[] = '';
        foreach ($widgets as $widget) {
            $areaArr[] = tep_db_input($widget);
        }
        $area = "'" . implode("','", $areaArr) . "'";
        $query = tep_db_query("select css from " . TABLE_THEMES_STYLES_CACHE . " where theme_name = '" . tep_db_input($theme_name) . "' and accessibility in(" . $area . ")");

        while ($item = tep_db_fetch_array($query)) {
            $css .= $item['css'];
        }
        $css .= \frontend\design\Block::getStyles();
        $css = \frontend\design\Info::minifyCss($css);

        $css = '<style type="text/css">' . $css . '</style>';

        return $block . $css;

    }

    public function actionWebp ()
    {
        $this->selectedMenu = array('design_controls', 'design/themes');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('design/themes'), 'title' => 'Create webp images');
        $this->view->headingTitle = TITLE_CREATE_WEBP_IMAGES;

        $buttonSettings = \common\helpers\Acl::getExtensionCreateImagesSettings();

        return $this->render('webp.tpl', [
            'imagewebp' => function_exists('imagewebp'),
            'buttonSettings' => $buttonSettings,
            'designer_mode' => $this->designerMode,
        ]);
    }

    public function actionCreateWebp ()
    {
        $type = Yii::$app->request->get('type', false);
        $iteration = (int)\Yii::$app->request->get('iteration', 0);

        return \common\classes\Images::createAllWebpImages($type, $iteration);
    }

    public function actionCreatePdfFont() {
        $fontPath = Yii::$app->request->post('font_path');
        if (substr($fontPath, 0, 4) == 'http'){
            return \TCPDF_FONTS::addTTFfont($fontPath);
        } else {
            if (is_file(DIR_FS_CATALOG . $fontPath)) {
                return \TCPDF_FONTS::addTTFfont(DIR_FS_CATALOG . $fontPath);
            } else {
                return false;
            }
        }
    }

    public function actionThemeTitle()
    {
        $theme_name = Yii::$app->request->post('theme_name');
        $group_id = Yii::$app->request->post('group_id');
        $title = Yii::$app->request->post('title');
        $image = Yii::$app->request->post('image', false);
        $image_upload = Yii::$app->request->post('image_upload');
        $image_delete = Yii::$app->request->post('image_delete');

        if ($theme_name && !\common\models\Themes::findOne(['theme_name' => $theme_name])) {
            return json_encode(['error' => THEME_NAME_DOESNT_EXIST]);
        }
        if (!$title) {
            return json_encode(['error' => TITLE_CANT_BE_BLANK]);
        }
        $responseImg = '';

        if (!$theme_name && $group_id && !\common\models\ThemesGroups::findOne(['themes_group_id' => $group_id])) {
            return json_encode(['error' => THEME_NAME_DOESNT_EXIST]);
        } elseif (!$theme_name && $group_id) {
            $theme = \common\models\ThemesGroups::findOne(['themes_group_id' => $group_id]);

            $theme->image = \common\helpers\Image::prepareSavingImage(
                $theme->image ?? '',
                $image,
                $image_upload,
                '',
                false, true
            );
            $responseImg = $theme->image;
        } else {
            $theme = \common\models\Themes::findOne(['theme_name' => $theme_name]);
            if ($theme) {
                $themeSetting = ThemesSettings::findOne([
                    'theme_name' => $theme_name,
                    'setting_group' => 'hide',
                    'setting_name' => 'theme_image',
                ]);
                $themeImage = '';
                if ($themeSetting) {
                    $themeImage = $themeSetting->setting_value;
                }
                $themeImage = \common\helpers\Image::prepareSavingImage(
                    $themeImage,
                    $image,
                    $image_upload,
                    'themes' . DIRECTORY_SEPARATOR . $theme_name . DIRECTORY_SEPARATOR . 'img',
                    false, true
                );
                if (!$themeImage) {
                    ThemesSettings::deleteAll([
                        'theme_name' => $theme_name,
                        'setting_group' => 'hide',
                        'setting_name' => 'theme_image'
                    ]);
                } elseif ($themeSetting) {
                    $themeSetting->setting_value = $themeImage;
                    $themeSetting->save();
                } else {
                    $themeSetting = new ThemesSettings();
                    $themeSetting->theme_name = $theme_name;
                    $themeSetting->setting_group = 'hide';
                    $themeSetting->setting_name = 'theme_image';
                    $themeSetting->setting_value = $themeImage;
                    $themeSetting->save();

                }
                $responseImg = $themeImage;
            }
        }
        if (!$theme) {
            return json_encode(['error' => 'db error']);
        }
        $theme->title = $title;
        $theme->save();

        return json_encode(['title' => $theme->title, 'image' => $responseImg]);
    }

    public function actionAddGroup()
    {
        $title = Yii::$app->request->post('title');

        if (!$title) {
            $this->layout = 'popup.tpl';
            return $this->render('add-group.tpl', []);
        }

        $group = new \common\models\ThemesGroups();
        $group->title = $title;
        $group->save();

        return json_encode(['text' => TEXT_GROUP_ADDED]);
    }

    public function actionThemeMove()
    {
        $title = Yii::$app->request->post('title');
        $group_id = Yii::$app->request->post('group_id', '');

        if (!$title && $group_id === '') {
            $groups = \common\models\ThemesGroups::find()->asArray()->all();

            $this->layout = 'popup.tpl';
            return $this->render('theme-move.tpl', [
                'groups' => $groups,
                'theme_name' => Yii::$app->request->get('theme_name')
            ]);
        }

        if ($group_id == 'add') {
            if (!$title) {
                return json_encode(['error' => TITLE_CANT_BE_BLANK]);
            }

            $group = new \common\models\ThemesGroups();
            $group->title = $title;
            $group->save();
            $group_id = $group->getPrimaryKey();
        }
        if (!$group_id && $group_id !== 0 && $group_id !== '0') {
            return json_encode(['error' => 'Group error']);
        }

        $theme_name = Yii::$app->request->post('theme_name');
        $themes = \common\models\Themes::findOne(['theme_name' => $theme_name]);

        if (!$themes) {
            return json_encode(['error' => 'Theme not found']);
        }

        $themes->themes_group_id = (int)$group_id;
        $themes->save(false);

        return json_encode(['text' => TEXT_THEME_MOVED]);
    }

    public function actionGroupRemove()
    {
        $group_id = Yii::$app->request->get('group_id', 0);

        if ($group_id) {
            $themes = \common\models\Themes::find()->where(['themes_group_id' => $group_id])->asArray()->all();
            $groupTitle = \common\models\ThemesGroups::findOne(['themes_group_id' => $group_id])->title;

            $this->layout = 'popup.tpl';
            return $this->render('group-remove.tpl', [
                'themes' => $themes,
                'groupTitle' => $groupTitle,
                'group_id' => $group_id,
            ]);
        }

        $group_id = Yii::$app->request->post('group_id', 0);

        if (!$group_id) {
            return json_encode(['error' => 'Error']);
        }

        $themes = \common\models\Themes::find()->where(['themes_group_id' => $group_id])->asArray()->all();

        foreach ($themes as $theme) {
            Theme::themeRemove($theme['theme_name']);
            Theme::themeRemove($theme['theme_name'] . '-mobile');
        }

        \common\models\ThemesGroups::deleteAll(['themes_group_id' => $group_id]);

        return json_encode(['text' => TEXT_GROUP_REMOVED]);
    }

    public function actionThemeSort()
    {
        $sort = Yii::$app->request->post('sort', 0);

        if (!$sort || !is_array($sort)) {
            return json_encode(['error' => 'Error: no sort array']);
        }

        foreach ($sort as $key => $item) {
            if ($item['theme_name']) {
                $theme = \common\models\Themes::findOne(['theme_name' => $item['theme_name']]);
                $theme->sort_order = $key + 1;
                $theme->save(false);
            } elseif ($item['group_id']) {
                $group = \common\models\ThemesGroups::findOne(['themes_group_id' => $item['group_id']]);
                $group->sort_order = $key + 1;
                $group->save(false);
            }
        }

        return json_encode(['text' => 'Sorted']);
    }

    public function actionContentWidget()
    {
        $params = tep_db_prepare_input(Yii::$app->request->get());
        $id = 0;
        $settings = [];
        $widgetParams = ['main_content' => true];
        $content = '';

        if (is_file(Yii::getAlias('@app') . DIRECTORY_SEPARATOR . 'design' . DIRECTORY_SEPARATOR . 'boxes' . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $params['name']) . '.php')){
            $widgetName = 'backend\design\boxes\\' .str_replace('\\\\', '\\', $params['name']);
            $content = $widgetName::widget(['id' => $id, 'params' => $widgetParams, 'settings' => $settings]);
        } elseif($ext = \common\helpers\Acl::checkExtension($params['name'], 'showTabSettings', true)){
            $widgetName = 'backend\design\boxes\Def';
            $settings['tabs'] = ['class'=> $ext, 'method' => 'showTabSettings'];
            $content = $widgetName::widget(['id' => $id, 'params' => $widgetParams, 'settings' => $settings]);
        } elseif($ext = \common\helpers\Acl::checkExtension($params['name'], 'showSettings', true)){
            $widgetName = 'backend\design\boxes\Def';
            $settings['class'] = $ext;
            $settings['method'] = 'showSettings';
            $content = $widgetName::widget(['id' => $id, 'params' => $widgetParams, 'settings' => $settings]);
        }
        $this->layout = false;

        return $this->render('content-widget.tpl', [
            'content' => $content,
            'widgetName' => $params['name']
        ]);
    }

    public function actionSetThemeSetting()
    {
        $theme_name = Yii::$app->request->post('theme_name', false);
        $setting_group = Yii::$app->request->post('setting_group', false);
        $setting_name = Yii::$app->request->post('setting_name', false);
        $setting_value = Yii::$app->request->post('setting_value', 2);

        if (!$theme_name || !$setting_group || !$setting_name) {
            return json_encode(['error' => 'Error']);
        }

        $themeSetting = ThemesSettings::findOne([
            'theme_name' => $theme_name,
            'setting_group' => $setting_group,
            'setting_name' => $setting_name,
        ]);
        if (!$themeSetting) {
            $themeSetting = new ThemesSettings();
            $themeSetting->theme_name = $theme_name;
            $themeSetting->setting_group = $setting_group;
            $themeSetting->setting_name = $setting_name;
        }
        $themeSetting->setting_value = $setting_value;
        $themeSetting->save();

        if ($themeSetting->errors ?? false) {
            return json_encode(['error' => 'Error']);
        }

        return json_encode(['text' => MESSAGE_SAVED]);
    }

    public function actionExportStyles()
    {
        $themeName = Yii::$app->request->post('theme_name', false);
        $type = Yii::$app->request->post('type', false);
        $saveToGroups = Yii::$app->request->post('save-to-groups', false);
        $name = Yii::$app->request->post('name', 2);
        $comment = Yii::$app->request->post('comment', 2);

        if (!$name) {
            return json_encode(['error' => PLEASE_ENTER_NAME]);
        }
        if (!$themeName || !$type) {
            return json_encode(['error' => 'Error']);
        }

        $fsCatalog = DIR_FS_CATALOG . implode(DIRECTORY_SEPARATOR, ['lib', 'backend', 'design', 'groups']) . DIRECTORY_SEPARATOR;

        $themeArchive = design::pageName($name);

        FileHelper::createDirectory($fsCatalog);
        chmod($fsCatalog, 0755);

        $_name = $themeArchive;
        for ($i = 1; $i < 100 && file_exists($fsCatalog . $themeArchive . '.zip'); $i++) {
            $themeArchive = $_name . '-' . $i;
        }

        $zip = new \ZipArchive();
        if ($zip->open($fsCatalog . $themeArchive . '.zip', \ZipArchive::CREATE) !== TRUE) {
            return 'Error';
        }

        $styles = ThemesStylesMain::find()->where(['theme_name' => $themeName, 'type' => $type])->asArray()->all();

        if ($type == 'font' && is_array($styles)) {
            foreach ($styles as $key => $style) {
                $fontSetting = ThemesSettings::find()->where([
                    'theme_name' => $themeName,
                    'setting_group' => 'extend',
                    'setting_name' => 'font_added'
                ])->andWhere(['like', 'setting_value', $style['value']])->asArray()->one();
                $styles[$key]['font_settings'] = preg_replace('/themes[\/\\\]' . $themeName . '/', 'themes/<theme_name>', $fontSetting['setting_value']);

                $files = [];
                preg_match_all("/url\([\'\"]{0,1}(themes[\/\\\]' . $themeName . '[^'^\"^)]+)\?[^'^\"^)]+[\'\"]{0,1}\)/", $fontSetting['setting_value'], $files);
                if (isset($files[1]) && is_array($files[1])) {
                    foreach ($files[1] as $file) {
                        if (is_file(DIR_FS_CATALOG . $file)) {
                            $filePath = explode('/', $file);
                            $filePath = explode('\\', end($filePath));
                            $fileName = end($filePath);
                            $zip->addFile(DIR_FS_CATALOG . $file, $fileName);
                        }
                    }
                }
            }
        }

        $json = json_encode($styles);
        $zip->addFromString('data.json', $json);

        $info = [
            'name' => $name,
            'name_title' => $name,
            'groupCategory' => $type,
            'comment' => $comment,
            'page_type' => $type,
        ];
        $zip->addFromString('info.json', json_encode($info));
        $zip->addFromString('images.json', json_encode([]));

        $zip->close();
        $themeArchive .= '.zip';

        Groups::synchronize();

        if ($saveToGroups) {
            $message = sprintf(SAVED_TO_GROUPS, $name);
        } else {
            $message = sprintf(COMMON_CREATED, $name);
        }

        return json_encode([
            'text' => $message,
            'filename' => $themeArchive
        ]);
    }

    public function actionCheckOriginTheme()
    {
        $this->layout = false;
        $themeFile = Groups::groupFilePath() . DIRECTORY_SEPARATOR . 'origin.zip';

        if (!is_file($themeFile)) {
            return '';
        }

        foreach (['origin', 'new_theme'] as $themeName) {
            if (DesignBoxes::findOne(['theme_name' => $themeName])) {
                continue;
            }

            Theme::import($themeName, $themeFile);
            Style::createCache($themeName);
        }

        return '';
    }
}
