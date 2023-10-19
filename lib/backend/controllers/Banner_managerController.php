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

use common\models\Banners;
use common\models\BannersGroupsImages;
use common\models\BannersGroupsSizes;
use common\models\BannersToPlatform;
use Yii;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use common\models\BannersLanguages;
use common\models\BannersGroups;
use common\classes\Images;
use common\helpers\Affiliate;
use common\classes\platform as Platform;
use common\helpers\Image;
use common\helpers\Language;

class Banner_managerController extends Sceleton
{

    public $acl = ['BOX_HEADING_MARKETING_TOOLS', 'BOX_TOOLS_BANNER_MANAGER'];
    public $banner_extension;
    public $dir_ok = false;

    private function banner_image_extension()
    {
        if (function_exists('imagetypes')) {
            if (imagetypes() & IMG_PNG) {
                return 'png';
            } elseif (imagetypes() & IMG_JPG) {
                return 'jpg';
            } elseif (imagetypes() & IMG_GIF) {
                return 'gif';
            }
        } elseif (function_exists('imagecreatefrompng') && function_exists('imagepng')) {
            return 'png';
        } elseif (function_exists('imagecreatefromjpeg') && function_exists('imagejpeg')) {
            return 'jpg';
        } elseif (function_exists('imagecreatefromgif') && function_exists('imagegif')) {
            return 'gif';
        }

        return false;
    }

    public function __construct($id, $module = null)
    {
        parent::__construct($id, $module);

        \common\helpers\Translation::init('admin/main');
        \common\helpers\Translation::init('admin/banner_manager');

        $this->banner_extension = $this->banner_image_extension();
        if (function_exists('imagecreate') && tep_not_null($this->banner_extension)) {
            if (is_dir(DIR_WS_IMAGES . 'graphs')) {
                if (is_writeable(DIR_WS_IMAGES . 'graphs')) {
                    $this->dir_ok = true;
                } else {
                    $this->view->errorMessage = ERROR_GRAPHS_DIRECTORY_NOT_WRITEABLE;
                    $this->view->errorMessageType = 'danger';
                }
            } else {
                $this->view->errorMessage = ERROR_GRAPHS_DIRECTORY_DOES_NOT_EXIST;
                $this->view->errorMessageType = 'danger';
            }
        }
    }

    public function actionIndex()
    {
        $this->selectedMenu = array('marketing', 'banner_manager');

        $this->topButtons[] = '<a href="' . Yii::$app->urlManager->createUrl('banner_manager/banneredit') . '" class="btn btn-primary btn-new-banner"><i class="icon-file-text"></i>' . IMAGE_NEW_BANNER . '</a>';
        $this->topButtons[] = '<a href="' . Yii::$app->urlManager->createUrl('banner_manager/banner-groups-edit') . '" class="btn btn-primary btn-new-group"><i class="icon-file-text"></i>' . NEW_BANNER_GROUP . '</a>';

        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('marketing/index'), 'title' => HEADING_TITLE);
        $this->view->headingTitle = HEADING_TITLE;

        $platform = Yii::$app->request->get('platform');
        $banners_group = Yii::$app->request->get('banners_group', '');
        $group_id = Yii::$app->request->get('group_id', 0);
        $row_id = Yii::$app->request->get('row_id', 0);
        $search_title = Yii::$app->request->get('search_title', '');
        $search_file = Yii::$app->request->get('search_file', '');
        $search_text = Yii::$app->request->get('search_text', '');
        $search_status = Yii::$app->request->get('search_status', '');
        if (Platform::isMulti()) {
            $platform_id = Yii::$app->request->get('platform_id', 0);
        } else {
            $platform_id = Platform::defaultId();
        }

        $tmp = array();

        $tmp[] = array(
            'title' => TAB_IMAGES,
            'not_important' => 0,
            'class' => 'image-heading-cell'
        );
        $tmp[] = array(
            'title' => TEXT_TITLE,
            'not_important' => 0,
            'class' => 'title-heading-cell'
        );
        $tmp[] = array(
            'title' => TABLE_HEADING_GROUPS,
            'not_important' => 0,
            'class' => 'group-heading-cell'
        );
        if (Platform::isMulti()) {
            $tmp[] = array(
                'title' => TABLE_HEAD_PLATFORM_NAME,
                'not_important' => 0,
                'class' => 'status-heading-cell'
            );
        }
        if (Platform::isMulti()) {
            $tmp[] = array(
                'title' => TABLE_HEAD_PLATFORM_BANNER_ASSIGN,
                'not_important' => 0,
                'class' => 'status-heading-cell'
            );
        } else {
            $tmp[] = array(
                'title' => TABLE_HEADING_STATUS,
                'not_important' => 0,
                'class' => 'status-heading-cell'
            );
        }

        $this->view->filters = new \stdClass();
        $this->view->filters->platform = array();
        if (isset($platform) && is_array($platform)) {
            foreach ($platform as $_platform_id)
                if ((int) $_platform_id > 0)
                    $this->view->filters->platform[] = (int) $_platform_id;
        }

        $bannersGroupsArr = BannersGroups::find(['id' => $group_id])->asArray()->one();
        $group_name = $bannersGroupsArr['banners_group'];

        $platform_name = '';
        $platforms = Platform::getList(false, true);
        foreach ($platforms as $platform) {
            if ($platform['id'] == $platform_id) {
                $platform_name = $platform['text'];
                break;
            }
        }

        $this->view->bannerTable = $tmp;
        return $this->render('index', array(
            'isMultiPlatforms' => Platform::isMulti(),
            'platforms' => $platforms,
            'platform_id' => $platform_id,
            'group_id' => $group_id,
            'row_id' => $row_id,
            'group_name' => ($group_id == '-1' || $group_id == -1 ? BANNERS_WITHOUT_GROUP : $group_name),
            'platform_name' => ($platform_id == '-1' || $platform_id == -1 ? BANNERS_WITHOUT_PLATFORM : $platform_name),
            'search_title' => $search_title,
            'search_file' => $search_file,
            'search_text' => $search_text,
            'search_status' => $search_status,
        ));
    }

    public function actionGetimage($banner_id)
    {
        $languages_id = \Yii::$app->settings->get('languages_id');

        $banner = BannersLanguages::find()->select(['banners_image', 'banners_title'])->where([
            'banners_id' => (int)$banner_id,
            'language_id' => (int)$languages_id
        ])->asArray()->one();

        $image = $this->getImage($banner);
        if ($image) {
            return $image;
        }

        $banners = BannersLanguages::find()->select(['banners_title', 'banners_title'])->where([
            'banners_id' => (int)$banner_id
        ])->asArray()->all();

        if (is_array($banners)) {
            foreach ($banners as $banner) {
                $image = $this->getImage($banner);
                if ($image) {
                    return $image;
                }
            }
        }

        return '';
    }

    public function getImage($banner){
        if (!isset($banner) || !isset($banner['banners_image'])) {
            return false;
        }

        if (isset($banner['banners_image']) && is_file(Images::getFSCatalogImagesPath() . $banner['banners_image'])) {

            $type = explode('/', mime_content_type(Images::getFSCatalogImagesPath() . $banner['banners_image']));
            if ($type[0] == 'image') {
                return tep_image(HTTP_CATALOG_SERVER . DIR_WS_CATALOG_IMAGES . $banner['banners_image'], $banner['banners_title']);
            } else {
                return '<span class="ico-video"></span>';
            }
        } elseif (isset($banner['banners_image']) && is_file(DIR_FS_CATALOG . $banner['banners_image'])) {

            $type = explode('/', mime_content_type(DIR_FS_CATALOG . $banner['banners_image']));
            if ($type[0] == 'image') {
                return tep_image(HTTP_CATALOG_SERVER . DIR_WS_CATALOG_IMAGES . $banner['banners_image'], $banner['banners_title']);
            } else {
                return '<span class="ico-video"></span>';
            }

        }

        return false;
    }

    public function actionList()
    {
        $languages_id = \Yii::$app->settings->get('languages_id');
        $draw = Yii::$app->request->get('draw', 1);
        $start = Yii::$app->request->get('start', 0);
        $length = Yii::$app->request->get('length', 10);
        $_search = Yii::$app->request->get('search', 10);
        $search = ArrayHelper::getValue($_search, ['value'], '');

        $responseList = [];
        $formFilter = Yii::$app->request->get('filter');
        parse_str($formFilter, $output);

        $group_id = $output['group_id'];
        if (Platform::isMulti()) {
            $platform_id = $output['platform_id'];
        } else {
            $platform_id = Platform::defaultId();
        }
        $search_title = $output['search_title'] ?? null;
        $search_file = $output['search_file'] ?? null;
        $search_text = $output['search_text'] ?? null;
        $search_status = $output['search_status'] ?? null;
        $empty_groups = $output['empty_groups'] ?? null;

        if ($group_id || $search_title || $search_file || $search_text || $search) {
            if ($group_id == '-1' || $group_id == -1) $group_id = 0;

            $bannersQuery = Banners::find()->alias('b')
                ->select(['b.banners_id', 'b.status', 'b.sort_order', 'bl.banners_title',
                    'bl.banners_image', 'bg.banners_group', 'bl.banners_html_text', 'bl.banners_image'])
                ->leftJoin(BannersGroups::tableName(). ' bg', 'b.group_id = bg.id');

            if ($platform_id != '-1' && $platform_id != -1){
                $bannersQuery->leftJoin(BannersToPlatform::tableName(). ' b2p', 'b.banners_id = b2p.banners_id');
            }

            $bannersQuery->leftJoin(BannersLanguages::tableName(). ' bl',
                'b.banners_id = bl.banners_id and bl.language_id = ' . (int) $languages_id);

            if ($group_id || $group_id === 0) {
                $bannersQuery->where(['b.group_id' => $group_id]);
            }

            if ($search) {
                $bannersQuery->andWhere(['or',
                    ['like', 'bl.banners_title', $search],
                    ['like', 'bl.banners_image', $search],
                    ['like', 'bl.banners_html_text', $search],
                ]);
            }

            if ($search_title) {
                $bannersQuery->andWhere(['like', 'bl.banners_title', $search_title]);
            }
            if ($search_file) {
                $bannersQuery->andWhere(['like', 'bl.banners_image', $search_file]);
            }
            if ($search_text) {
                $bannersQuery->andWhere(['like', 'bl.banners_html_text', $search_text]);
            }
            if ($search_status) {
                $bannersQuery->andWhere(['b.status' => ($search_status == 'on' ? 1 : 0)]);
            }

            if ($platform_id == '-1' || $platform_id == -1){
                $bannersQuery->andWhere('NOT EXISTS(SELECT 1 FROM ' . BannersToPlatform::tableName() . ' b2p WHERE b2p.banners_id=b.banners_id)');
            } elseif ($platform_id) {
                $bannersQuery->andWhere(['b2p.platform_id' => $platform_id]);
            }

            $bannersQuery->orderBy('b.sort_order, bl.banners_title');

            $allBannersCount = $bannersQuery->count();
            $bannersArr = $bannersQuery
                ->limit($length)
                ->offset($start)
                ->asArray()->all();

            foreach ($bannersArr as $banners) {
                $tmp = [];

                $tmp['id'] = $banners['banners_id'];
                $tmp['name'] = 'banners_id';

                $tmp['image'] = $this->actionGetimage($banners['banners_id']);

                if ($search_title) {
                    $tmp['title'] = str_ireplace(
                        $search_title,
                        '<span class="keywords">' . $search_title . '</span>',
                        strip_tags($banners['banners_title'])
                    );
                } else {
                    $tmp['title'] = $banners['banners_title'];
                }

                if ($search_file) {
                    $tmp['file'] = str_ireplace(
                        $search_file,
                        '<span class="keywords">' . $search_file . '</span>',
                        strip_tags($banners['banners_image'])
                    );
                } else {
                    $tmp['file'] = $banners['banners_image'];
                }

                if ($search_text) {
                    $tmp['text'] = str_ireplace(
                        $search_text,
                        '<span class="keywords">' . $search_text . '</span>',
                        strip_tags($banners['banners_html_text'])
                    );
                } else {
                    $tmp['text'] = $banners['banners_html_text'];
                }

                $tmp['group'] = $banners['banners_group'];

                if (Platform::isMulti()) {
                    $platforms = '';
                    $public_checkbox = '';

                    $bannersToPlatform = BannersToPlatform::find()
                        ->where(['banners_id' => $banners['banners_id']])
                        ->asArray()->all();
                    $banner_statuses = [];
                    foreach ($bannersToPlatform as $status) {
                        $sub_row_key = $status['banners_id'] . '^' . $status['platform_id'];
                        $banner_statuses[$sub_row_key] = 1;
                    }

                    foreach (Platform::getList(false, true) as $platform_variant) {
                        $sub_row_key = $banners['banners_id'] . '^' . $platform_variant['id'];
                        $sub_row_disabled = !isset($banner_statuses[$sub_row_key]);

                        $_row_key = $banners['banners_id'] . '-' . $platform_variant['id'];
                        if ($platform_variant['is_marketplace'] == 0) {
                            $platforms .= '<div id="banner-' . $_row_key . '"' . ($sub_row_disabled ? ' class="platform-disable"' : '') . '>' . $platform_variant['text'] . '</div>';

                            $public_checkbox .= '<div>' .
                                Html::checkbox('platform[' . $banners['banners_id'] . '][' . $platform_variant['id'] . ']', !$sub_row_disabled, ['value' => $_row_key, 'class' => 'check_on_off']) . '</div>';
                        }
                    }

                    $tmp['platform-name'] = $platforms;
                    $tmp['platform-status'] = $public_checkbox;
                }

                $tmp['status'] = '<input type="checkbox" value=' . $banners['banners_id'] . ' name="status" class="check_on_off"' . ($banners['status'] == '1' ? ' checked="checked"' : '') . '>';

                $responseList[] = $this->bannerRow($tmp);
            }

        } elseif (!$platform_id && Platform::isMulti()) {

            foreach (Platform::getList(false, true) as $platform) {
                $tmp = [];
                $tmp['id'] = $platform['id'];
                $tmp['name'] = 'platform_id';
                $tmp['platform-name'] = '<div class="platform-name">' . $platform['text'] . '</div>';

                $tmp['count'] = BannersToPlatform::find()->where(['platform_id' => $platform['id']])->count();

                $responseList[] = $this->bannerRow($tmp);
            }
            $tmp = [];
            $tmp['platform-name'] = '<div class="platform-name">' . BANNERS_WITHOUT_PLATFORM . '</div>';
            $tmp['id'] = '-1';
            $tmp['name'] = 'platform_id';

            $tmp['count'] = Banners::find()->alias('b')
                ->where('NOT EXISTS(SELECT 1 FROM ' . BannersToPlatform::tableName() . ' b2p WHERE b2p.banners_id=b.banners_id)')->count();

            $responseList[] = $this->bannerRow($tmp);

        } else {
            $bannersGroups = BannersGroups::find()->asArray()->all();

            $responseListTmp = [];
            foreach ($bannersGroups as $bannersGroup) {
                $tmp = [];
                $tmp['group'] = $bannersGroup['banners_group'];
                $tmp['id'] = $bannersGroup['id'];
                $tmp['name'] = 'group_id';

                if ($platform_id == '-1') {
                    $tmp['count'] = Banners::find()->alias('b')
                        ->where(['group_id' => $bannersGroup['id']])
                        ->andWhere('NOT EXISTS(SELECT 1 FROM ' . BannersToPlatform::tableName() . ' b2p WHERE b2p.banners_id=b.banners_id)')->count();
                } else {
                    $tmp['count'] = Banners::find()->alias('b')
                        ->innerJoin(BannersToPlatform::tableName() . ' b2p',
                            'b.banners_id = b2p.banners_id and b2p.platform_id = ' . $platform_id)
                        ->where(['b.group_id' => $bannersGroup['id']])->count();

                }

                if (!$empty_groups && !$tmp['count']) {
                    continue;
                }

                $responseListTmp[] = $tmp;
            }
            usort($responseListTmp, function($a, $b){
                return ($a['count'] > $b['count']) ? -1 : 1;
            });
            foreach ($responseListTmp as $item) {
                $responseList[] = $this->bannerRow($item);
            }
            $tmp = [];
            $tmp['group'] = BANNERS_WITHOUT_GROUP;
            $tmp['id'] = '-1';
            $tmp['name'] = 'group_id';
            if ($platform_id == '-1') {
                $tmp['count'] = Banners::find()->alias('b')
                    ->where(['group_id' => 0])
                    ->andWhere('NOT EXISTS(SELECT 1 FROM ' . BannersToPlatform::tableName() . ' b2p WHERE b2p.banners_id=b.banners_id)')->count();
            } else {
                $tmp['count'] = Banners::find()->alias('b')
                    ->innerJoin(BannersToPlatform::tableName() . ' b2p',
                        'b.banners_id = b2p.banners_id and b2p.platform_id = ' . $platform_id)
                    ->where(['group_id' => 0])->count();
            }

            if ($empty_groups && !$tmp['count']) {
                $responseList[] = $this->bannerRow($tmp);
            }
        }
        if (!isset($allBannersCount)) {
            $allBannersCount = count($responseList);
        }
        $response = array(
            'draw' => $draw,
            'recordsTotal' => $allBannersCount,
            'recordsFiltered' => $allBannersCount,
            'data' => $responseList,
        );
        echo json_encode($response);
    }

    function bannerRow($data) {
        $row = [];
        $row[] = '<div class="batch-cell"><input type="checkbox" name="' . $data['name'] . '" value="' . $data['id'] . '"/></div>';
        $row[] = '<div class="sort-cell" data-id="' . $data['id'] . '" data-name="' . $data['name'] . '"></div>';
        $row[] = '<div class="image-cell double-click" data-id="' . $data['id'] . '" data-name="' . $data['name'] . '">' . ($data['image'] ?? '') . '</div>';
        $row[] = '<div class="title-cell double-click" data-id="' . $data['id'] . '" data-name="' . $data['name'] . '">' . ($data['title'] ?? '') . '</div>';
        $row[] = '<div class="group-cell double-click" data-id="' . $data['id'] . '" data-name="' . $data['name'] . '">' . ($data['group'] ?? '') . '</div>';
        $row[] = '<div class="file-cell double-click" data-id="' . $data['id'] . '" data-name="' . $data['name'] . '">' . ($data['file'] ?? '') . '</div>';
        $row[] = '<div class="text-cell double-click" data-id="' . $data['id'] . '" data-name="' . $data['name'] . '">' . ($data['text'] ?? '') . '</div>';
        $row[] = '<div class="platform-cell double-click" data-id="' . $data['id'] . '" data-name="' . $data['name'] . '"><div class="platforms-cell">' . ($data['platform-name'] ?? '') . '</div></div>';
        $row[] = '<div class="platform-cell"><div class="platforms-cell-checkbox">' . ($data['platform-status'] ?? '') . '</div></div>';
        $row[] = '<div class="status-cell">' . ($data['status'] ?? '') . '</div>';
        $row[] = '<div class="count-cell double-click" data-id="' . $data['id'] . '" data-name="' . $data['name'] . '">' . ($data['count'] ?? '') . '</div>';
        return $row;
    }

    function getBanner($bID)
    {
        $languages_id = \Yii::$app->settings->get('languages_id');

        return Banners::find()->alias('b')
            ->select(['b.banners_id', 'b.status', 'b.sort_order', 'bl.banners_title', 'b.date_added',
                'b.date_scheduled', 'b.expires_date', 'b.expires_impressions', 'b.date_status_change',
                'bl.banners_image', 'bg.banners_group', 'bl.banners_html_text', 'bl.banners_image'])
            ->leftJoin(BannersGroups::tableName(). ' bg', 'b.group_id = bg.id')
            ->leftJoin(BannersLanguages::tableName(). ' bl',
                'b.banners_id = bl.banners_id and language_id = ' . (int) $languages_id)
            ->where(['b.banners_id' => $bID])
            ->asArray()->one();
    }

    public function actionView()
    {
        $id = Yii::$app->request->get('id');
        $name = Yii::$app->request->get('name');
        $platform_id = Yii::$app->request->get('platform_id');
        $row_id = Yii::$app->request->get('platform_id');
        $this->layout = false;

        if (!$id || !$name) {
            return '';
        }

        switch ($name) {
            case 'banners_id':
                $bInfo = $this->getBanner($id);

                $b_platform = '';
                $banners_platform = tep_db_query("select platform_name from " . TABLE_PLATFORMS . " p left join " . TABLE_BANNERS_TO_PLATFORM . " bt on p.platform_id = bt.platform_id where bt.banners_id ='" . $id . "' ");
                if (tep_db_num_rows($banners_platform) > 0) {
                    while ($banners_platform_result = tep_db_fetch_array($banners_platform)) {
                        $b_platform .= '<div class="platform_res">' . $banners_platform_result['platform_name'] . '</div>';
                    }
                }
                return $this->render('bar-banner', [
                    'b_platform' => $b_platform,
                    'bInfo' => $bInfo,
                    'image' => $this->actionGetimage($id)
                ]);
            case 'group_id':

                $title = BannersGroups::findOne(['id' => $id])->banners_group;
                $count = 0;

                if ($id == '-1') {
                    $id = 0;
                }
                if ($platform_id == '-1') {
                    $count = Banners::find()->alias('b')
                        ->where(['b.group_id' => $id])
                        ->andWhere('NOT EXISTS(SELECT 1 FROM ' . BannersToPlatform::tableName() . ' b2p WHERE b2p.banners_id=b.banners_id)')->count();
                } else {
                    $count = Banners::find()->alias('b')
                        ->innerJoin(BannersToPlatform::tableName() . ' b2p',
                            'b.banners_id = b2p.banners_id and b2p.platform_id = ' . $platform_id)
                        ->where(['group_id' => $id])->count();
                }

                return $this->render('bar-group', [
                    'name' => ($id == '-1' ? BANNERS_WITHOUT_GROUP : $title ),
                    'count' => $count,
                    'platform_id' => $platform_id,
                    'group_id' => $id,
                    'row_id' => $row_id,
                ]);
            case 'platform_id':

                if ($id == '-1') {
                    $count = Banners::find()->alias('b')
                        ->where('NOT EXISTS(SELECT 1 FROM ' . BannersToPlatform::tableName() . ' b2p WHERE b2p.banners_id=b.banners_id)')->count();
                } else {
                    $count = BannersToPlatform::find()->where(['platform_id' => $id])->count();
                }

                return $this->render('bar-platform', [
                    'name' => ($id == '-1' ? BANNERS_WITHOUT_PLATFORM : Platform::name($id) ),
                    'count' => $count
                ]);
        }
    }

    public function actionBannerDuplicate()
    {
        $id = (int)\Yii::$app->request->post('banners_id', 0);
        $ret = false;
        if (!$id) {
            return json_encode(['error' => 'no ID']);
        }

        $fromBanner = \common\models\Banners::findOne($id);
        if (!$fromBanner) {
            return json_encode(['error' => 'no banner with banner_id = ' . $id]);
        }

        $newBanner = new \common\models\Banners();
        try {
            $newBanner->attributes = $fromBanner->attributes;
            $newBanner->banners_id = null;
            $newBanner->isNewRecord = true;

            $newBanner->status = 0;
            $newBanner->group_id = $fromBanner->group_id;
            $newBanner->date_added = new Expression("NOW()");

            $newBanner->save(false);
            $banners_id = $newBanner->banners_id;

        } catch (\Exception $ex) {
            \Yii::warning($ex->getMessage() . " #### " .print_r($newBanner, 1), 'TLDEBU_banner_save_error');
            return json_encode(['error' => BANNER_NOT_COPIED]);
        }

        if (!$banners_id) {
            return json_encode(['error' => BANNER_NOT_COPIED]);
        }

        $fromBLs = BannersLanguages::findall(['banners_id' => $fromBanner->banners_id]);
        if (!empty($fromBLs) && is_array($fromBLs)) {
            foreach ($fromBLs as $fromBL) {
                $toBL = new BannersLanguages();
                try {
                    $toBL->attributes = $fromBL->attributes;
                    $toBL->blang_id = null;
                    $toBL->isNewRecord = true;
                    $toBL->banners_image = Images::moveImage($fromBL->banners_image, 'banners' . DIRECTORY_SEPARATOR . $banners_id);
                    $toBL->banners_id =$banners_id;
                    $toBL->save(false);

                    Images::createWebp($toBL->banners_image );

                } catch (\Exception $ex) {
                    \Yii::warning($ex->getMessage() . " #### " .print_r($toBL, 1), 'TLDEBU_banner_lang_save_error');

                }

            }
        }

        $fromBLs = BannersGroupsImages::findall(['banners_id' => $fromBanner->banners_id]);
        if (!empty($fromBLs) && is_array($fromBLs)) {
            foreach ($fromBLs as $fromBL) {
                $toBL = new BannersGroupsImages();
                try {
                    $toBL->attributes = $fromBL->attributes;
                    $toBL->id = null;
                    $toBL->isNewRecord = true;

                    $toBL->image = Images::moveImage($fromBL->image, 'banners' . DIRECTORY_SEPARATOR . $banners_id);
                    $toBL->banners_id =$banners_id;
                    $toBL->save(false);

                    Images::createWebp($toBL->image );

                } catch (\Exception $ex) {
                    \Yii::warning($ex->getMessage() . " #### " .print_r($toBL, 1), 'TLDEBU_banner_lang_save_error');

                }

            }
        }

        $fromBLs = BannersToPlatform::findall(['banners_id' => $fromBanner->banners_id]);
        if (!empty($fromBLs) && is_array($fromBLs)) {
            foreach ($fromBLs as $fromBL) {
                $toBL = new BannersToPlatform();
                try {
                    $toBL->banners_id = $banners_id;
                    $toBL->platform_id = $fromBL->platform_id;
                    $toBL->save(false);

                } catch (\Exception $ex) {
                    \Yii::warning($ex->getMessage() . " #### " .print_r($toBL, 1), 'TLDEBU_banner_platform_save_error');
                }
            }
        }

        return json_encode(['success' => BANNER_COPIED, 'banners_id' => $banners_id]);
    }

    public function actionSubmit()
    {
        global $login_id;
        $request = Yii::$app->request->post();

        \common\helpers\Translation::init('admin/banner_manager');

        $banners_id = (int)Yii::$app->request->post('banners_id', 0);

        $platforms = Platform::getList(false, true);

        $sql_data_array = array();

        $expires_date = 'null';
        if (!empty(\Yii::$app->request->post('expires_date'))) {
            $expires_date = \common\helpers\Date::prepareInputDate(\Yii::$app->request->post('expires_date'), true);
        }

        $sql_data_array['expires_date'] = $expires_date;

        $expires_impressions = tep_db_prepare_input(\Yii::$app->request->post('expires_impressions'));
        if (tep_not_null($expires_impressions)) {
            $sql_data_array['expires_impressions'] = $expires_impressions;
        }

        $date_scheduled = 'null';
        if (!empty(\Yii::$app->request->post('date_scheduled'))) {
            $date_scheduled = \common\helpers\Date::prepareInputDate(\Yii::$app->request->post('date_scheduled'), true);
        }
        $sql_data_array['date_scheduled'] = $date_scheduled;
        $sql_data_array['status'] = (isset($request['status']) ? 1 : 0);

        if (Affiliate::isLogged()) {
            $sql_data_array['affiliate_id'] = $login_id;
        }

        $sql_data_array['sort_order'] = tep_db_prepare_input($request['sort_order']);
        $sql_data_array['nofollow'] = (isset($request['nofollow']) && $request['nofollow'] ? 1 : 0);
        $sql_data_array['group_id'] = tep_db_prepare_input($request['group_id']);

        if ($banners_id == 0) {

            tep_db_perform(Banners::tableName(), array_merge($sql_data_array, ['date_added' => 'now()']));
            $banners_id = tep_db_insert_id();
            Yii::$app->request->setBodyParams(['banners_id' => $banners_id]);
            $successMessage = defined('SUCCESS_BANNER_INSERTED') ? SUCCESS_BANNER_INSERTED : 'Inserted';

        } else {

            $sql_data_array['banners_id'] = $banners_id;
            $check = tep_db_fetch_array(tep_db_query(
                            "SELECT COUNT(*) AS c FROM " . Banners::tableName() . " WHERE banners_id='" . (int) $banners_id . "'"
            ));
            if ($check['c'] == 0) {
                tep_db_perform(Banners::tableName(), array_merge($sql_data_array, ['date_added' => 'now()']));
            } else {
                tep_db_perform(Banners::tableName(), array_merge($sql_data_array, ['date_status_change' => 'now()']), 'update', "banners_id = '" . (int) $banners_id . "'");
            }

            $successMessage = defined('SUCCESS_BANNER_UPDATED') ? SUCCESS_BANNER_UPDATED : 'Updated';
        }

        foreach ($platforms as $_platform_info) {
            if (isset($request['platform_status'][$_platform_info['id']])) {
                tep_db_query("REPLACE INTO " . TABLE_BANNERS_TO_PLATFORM . " (banners_id, platform_id) VALUES('" . (int) $banners_id . "', '" . (int) $_platform_info['id'] . "')");
            } else {
                tep_db_query("DELETE FROM  " . TABLE_BANNERS_TO_PLATFORM . " WHERE banners_id='" . (int) $banners_id . "' AND platform_id='" . (int) $_platform_info['id'] . "'");
            }
        }

        $languages = \common\helpers\Language::get_languages();
        $oldImage = [];
        $deleteOldImage = [];

        foreach ($languages as $language) {
            $language_id = $language['id'];

            $bannerLanguage = BannersLanguages::findOne(['banners_id' => $banners_id, 'language_id' => $language_id]);
            if (!$bannerLanguage) {
                $bannerLanguage = new BannersLanguages();
                $bannerLanguage->banners_id = $banners_id;
                $bannerLanguage->language_id = $language_id;
            }
            $oldImage[$language_id] = $bannerLanguage->banners_image;

            $bannerLanguage->banners_title = $request['banners_title'][$language_id] ?? '';
            $bannerLanguage->banners_url = $request['banners_url'][$language_id] ?? '';
            $bannerLanguage->target = $request['target'][$language_id] ?? 0;
            $bannerLanguage->banner_display = $request['banner_display'][$language_id] ?? 0;
            $bannerLanguage->text_position = $request['text_position'][$language_id] ?? 0;
            $bannerLanguage->banners_html_text = $request['banners_html_text'][$language_id] ?? 0;

            $bannerLanguage->banners_image = Image::prepareSavingImage(
                $bannerLanguage->banners_image,
                $request['banners_image'][$language_id] ?? '',
                $request['banners_image_upload'][$language_id] ?? '',
                'banners' . DIRECTORY_SEPARATOR . $banners_id,
                $request['banners_image_delete'][$language_id] ?? ''
            );

            $deleteOldImage[$language_id] = false;

            if (isset($request['banners_image_delete'][$language_id]) &&
                $request['banners_image_delete'][$language_id] == 1 &&
                $bannerLanguage->banners_image
            ) {
                $deleteOldImage[$language_id] = true;
            }

            if ($bannerLanguage->banners_title || $bannerLanguage->banners_url || $bannerLanguage->banners_html_text || $bannerLanguage->banners_image) {
                $bannerLanguage->save();
            }

            if ($bannerLanguage->errors && count($bannerLanguage->errors) > 0) {
                return json_encode(['error' => 'db error']);
            }
        }


        $wrong = BannersToPlatform::find()->alias('b2p')->where('NOT EXISTS(SELECT 1 FROM ' . Banners::tableName() . ' b WHERE b2p.banners_id=b.banners_id)')->asArray()->all();
        foreach ($wrong as $row) {
            BannersToPlatform::deleteAll(['banners_id' => $row['banners_id']]);
        }

        self::saveGroupImages($banners_id, $oldImage, $deleteOldImage);

        foreach (\common\helpers\Hooks::getList('banner_manager/submit') as $filename) {
            include($filename);
        }

        return json_encode(['text' => $successMessage, 'html' => $this->actionBanneredit()]);
    }

    public function actionDeleteConfirm()
    {
        $ids = Yii::$app->request->get('bID', []);
        $this->layout = false;

        $bInfo = [];
        foreach ($ids as $id) {
            $bInfo[] = $this->getBanner($id);
        }

        return $this->render('bar-banner-delete', array(
            'bInfo' => $bInfo,
            'ids' => $ids,
        ));
    }

    public function actionDelete()
    {
        $this->layout = false;
        $banners_ids = Yii::$app->request->post('bID', []);
        $delete_image = Yii::$app->request->post('delete_image', false);

        $this->deleteBanners($banners_ids, $delete_image);

        return json_encode(['success' => SUCCESS_BANNER_REMOVED]);
    }

    public function deleteBanners ($bannersIds, $deleteImages)
    {
        foreach ($bannersIds as $banners_id) {
            if ($deleteImages) {
                try {
                    FileHelper::removeDirectory(DIR_FS_CATALOG_IMAGES . 'banners/' . $banners_id);
                } catch (\Exception $ex) {
                    \Yii::warning($ex->getMessage() . " #### banner image delete error. id = " . $banners_id);
                }
            }

            tep_db_query("delete from " . TABLE_BANNERS_HISTORY . " where banners_id = '" . (int)$banners_id . "'");

            Banners::deleteAll(['banners_id' => (int)$banners_id]);
            BannersLanguages::deleteAll(['banners_id' => (int)$banners_id]);
            BannersToPlatform::deleteAll(['banners_id' => (int)$banners_id]);

            if (function_exists('imagecreate') && tep_not_null($this->banner_extension)) {
                if (is_file(DIR_WS_IMAGES . 'graphs/banner_infobox-' . $banners_id . '.' . $this->banner_extension)) {
                    if (is_writeable(DIR_WS_IMAGES . 'graphs/banner_infobox-' . $banners_id . '.' . $this->banner_extension)) {
                        unlink(DIR_WS_IMAGES . 'graphs/banner_infobox-' . $banners_id . '.' . $this->banner_extension);
                    }
                }

                if (is_file(DIR_WS_IMAGES . 'graphs/banner_yearly-' . $banners_id . '.' . $this->banner_extension)) {
                    if (is_writeable(DIR_WS_IMAGES . 'graphs/banner_yearly-' . $banners_id . '.' . $this->banner_extension)) {
                        unlink(DIR_WS_IMAGES . 'graphs/banner_yearly-' . $banners_id . '.' . $this->banner_extension);
                    }
                }

                if (is_file(DIR_WS_IMAGES . 'graphs/banner_monthly-' . $banners_id . '.' . $this->banner_extension)) {
                    if (is_writeable(DIR_WS_IMAGES . 'graphs/banner_monthly-' . $banners_id . '.' . $this->banner_extension)) {
                        unlink(DIR_WS_IMAGES . 'graphs/banner_monthly-' . $banners_id . '.' . $this->banner_extension);
                    }
                }

                if (is_file(DIR_WS_IMAGES . 'graphs/banner_daily-' . $banners_id . '.' . $this->banner_extension)) {
                    if (is_writeable(DIR_WS_IMAGES . 'graphs/banner_daily-' . $banners_id . '.' . $this->banner_extension)) {
                        unlink(DIR_WS_IMAGES . 'graphs/banner_daily-' . $banners_id . '.' . $this->banner_extension);
                    }
                }
            }

            self::deleteBannerGroupImages($banners_id);
        }
    }

    public function actionSwitchStatus()
    {
        $ids = Yii::$app->request->post('ids');
        $status = Yii::$app->request->post('status');

        foreach ($ids as $id) {
            $banner = Banners::findOne(['banners_id' => (int) $id]);
            $banner->status = ($status == 'true' ? 1 : 0);
            $banner->date_status_change = new Expression("NOW()");
            $banner->save(false);
        }
    }

    public function actionSwitchStatusPlatform()
    {
        $ids = Yii::$app->request->post('ids', []);
        $status = Yii::$app->request->post('status');
        foreach ($ids as $id) {
            if (strpos($id, '-') !== false) {
                list($bid, $pid) = explode('-', $id, 2);
                if ($status == 'true') {
                    tep_db_query("REPLACE INTO " . TABLE_BANNERS_TO_PLATFORM . " (banners_id, platform_id) VALUES('" . (int)$bid . "', '" . (int)$pid . "')");
                } else {
                    tep_db_query("DELETE FROM  " . TABLE_BANNERS_TO_PLATFORM . " WHERE banners_id='" . (int)$bid . "' AND platform_id='" . (int)$pid . "'");
                }
            } else {
                if ($status == 'true') {
                    tep_db_query("REPLACE INTO " . TABLE_BANNERS_TO_PLATFORM . " (banners_id, platform_id) VALUES('" . (int)$id . "', '" . (int)Platform::firstId() . "')");
                } else {
                    tep_db_query("DELETE FROM  " . TABLE_BANNERS_TO_PLATFORM . " WHERE banners_id='" . (int)$id . "' AND platform_id='" . (int)Platform::firstId() . "'");
                }
            }
        }
    }

    public function actionBanneredit()
    {
        if (Yii::$app->request->get('popup')) {
            $this->layout = false;
        }

        if (Yii::$app->request->isPost) {
            $banners_id = (int) Yii::$app->request->getBodyParam('banners_id');
        } else {
            $banners_id = (int) Yii::$app->request->get('banners_id');
        }

        $platform_id = (int) Yii::$app->request->get('platform_id', 0);
        $group_id = (int) Yii::$app->request->get('group_id', 0);
        $row_id = (int) Yii::$app->request->get('row_id', 0);
        $popup = (int) Yii::$app->request->get('popup', false);

        $this->topButtons[] = '<span class="btn btn-confirm">' . IMAGE_SAVE . '</span>';

        if (!$banners_id) {
            $banner_query = tep_db_fetch_array(tep_db_query("select MAX(banners_id) as max_id from " . Banners::tableName() ));
            $banners_id = $banner_query['max_id'] + 1;

            if (!$popup) {
                return $this->redirect(Yii::$app->urlManager->createUrl(['banner_manager/banneredit', 'banners_id' => $banners_id, 'platform_id' => $platform_id, 'group_id' => $group_id, 'row_id' => $row_id]));
            }
        }

        if ($banners_id > 0) {
            $banner_query = tep_db_query("select * from " . Banners::tableName() . " where banners_id = " . $banners_id);
            $banner = tep_db_fetch_array($banner_query);
        }
        $cInfo = new \objectInfo($banner);

        $groups_array = [['id' => '', 'text' => '']];
        $groups = BannersGroups::find()->asArray()->all();
        foreach ($groups as $group) {
            $groups_array[] = ['id' => $group['id'], 'text' => $group['banners_group']];
        }

        $banner_statuses = array();
        $platform_statuses = array();
        $get_statuses_r = tep_db_query("SELECT banners_id, platform_id FROM " . TABLE_BANNERS_TO_PLATFORM . " WHERE banners_id='" . (int) $banners_id . "'");
        while ($get_status = tep_db_fetch_array($get_statuses_r)) {
            $sub_row_key = $get_status['platform_id'];
            $banner_statuses[$sub_row_key] = 1;
        }
        $banners_data = array();

        $cDescription = [];
        $mainDesc = [];
        $languages = \common\helpers\Language::get_languages();
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
            $languages[$i]['logo'] = $languages[$i]['image'];
            $cDescription[$i]['code'] = $languages[$i]['code'];

            $banner_description_query = tep_db_query(
                    "select * from " . Banners::tableName() . " b " .
                    " left join " . TABLE_BANNERS_LANGUAGES . " bl on b.banners_id = bl.banners_id and bl.language_id = '" . (int) $languages[$i]['id'] . "' " .
                    "where   b.banners_id = '" . $banners_id . "'  " .
                    " and b.affiliate_id=0 "
            );
            if (tep_db_num_rows($banner_description_query) > 0) {
                $banner_data = tep_db_fetch_array($banner_description_query);
            }
            \common\helpers\Php8::nullProps($banner_data, ['banners_title', 'banners_url', 'banner_display', 'target', 'svg', 'banners_html_text', 'banners_image', 'text_position', 'banners_group', 'banner_type', 'date_scheduled', 'expires_date', 'sort_order', 'nofollow']);
            $cDescription[$i]['language_id'] = $languages[$i]['id'];
            $cDescription[$i]['banners_title'] = tep_draw_input_field('banners_title[' . $languages[$i]['id'] . ']', $banner_data['banners_title'], 'class="form-control banner-title"');
            $cDescription[$i]['banners_url'] = tep_draw_input_field('banners_url[' . $languages[$i]['id'] . ']', $banner_data['banners_url'], 'class="form-control"');
            $cDescription[$i]['bannerUrl'] = 'banners_url[' . $languages[$i]['id'] . ']';

            $cDescription[$i]['target'] = tep_draw_checkbox_field('target[' . $languages[$i]['id'] . ']', 0, $banner_data['target'] == 1, '', 'class="form-control"');
            
            $cDescription[$i]['banner_display'] = $banner_data['banner_display'];
            $cDescription[$i]['banner_display_name'] = 'banner_display[' . $languages[$i]['id'] . ']';

            $cDescription[$i]['svg'] = $banner_data['svg'];
            $cDescription[$i]['svg_url'] = Yii::$app->urlManager->createUrl([
                'banner_manager/banner-editor',
                'language_id' => $languages[$i]['id'],
                'banners_id' => $banners_id
            ]);

            $cDescription[$i]['banners_html_text'] = tep_draw_textarea_field('banners_html_text[' . $languages[$i]['id'] . ']', 'soft', '40', '15', $banner_data['banners_html_text'], 'class="form-control ck-editor"');
            $cDescription[$i]['banners_image'] = '<div class="banner_image">' .
                    '<div class="upload" data-name="banners_image[' . $languages[$i]['id'] . ']" data-value="' . \common\helpers\Output::output_string($banner_data['banners_image']) . '"></div>' .
                    '</div>';
            
            $cDescription[$i]['name'] = 'banners_image[' . $languages[$i]['id'] . ']';
            //$cDescription[$i]['value'] = $banner_data['banners_image'];
            $cDescription[$i]['upload'] = 'banners_image_upload[' . $languages[$i]['id'] . ']';
            $cDescription[$i]['delete'] = 'banners_image_delete[' . $languages[$i]['id'] . ']';

            $cDescription[$i]['name_video'] = 'banners_video[' . $languages[$i]['id'] . ']';
            //$cDescription[$i]['value_video'] = $banner_data['banners_image'];
            $cDescription[$i]['upload_video'] = 'banners_video_upload[' . $languages[$i]['id'] . ']';
            $cDescription[$i]['delete_video'] = 'banners_video_delete[' . $languages[$i]['id'] . ']';

            if ($banner_data['banner_display'] == 4) {
                $cDescription[$i]['value'] = '';
                $cDescription[$i]['value_video'] = $banner_data['banners_image'];
            } else {
                $cDescription[$i]['value'] = $banner_data['banners_image'];
                $cDescription[$i]['value_video'] = '';
            }

            if (is_file(Images::getFSCatalogImagesPath() . $cDescription[$i]['value'])) {
                $type = explode('/', mime_content_type(Images::getFSCatalogImagesPath() . $cDescription[$i]['value']));
                $cDescription[$i]['type'] = $type[0];
            } elseif (is_file(DIR_FS_CATALOG . $cDescription[$i]['value'])) {
                $type = explode('/', mime_content_type(DIR_FS_CATALOG . $cDescription[$i]['value']));
                $cDescription[$i]['type'] = $type[0];
            }
            
            $cDescription[$i]['text_position'] = $banner_data['text_position'];
            $cDescription[$i]['text_position_name'] = 'text_position[' . $languages[$i]['id'] . ']';

            $mainDesc['banners_group'] = tep_draw_pull_down_menu('group_id', $groups_array, $group_id ? : $banner_data['group_id'], 'class="form-control"');
            $mainDesc['date_scheduled'] = '<input type="text" name="date_scheduled" value="' . \common\helpers\Date::formatDateTimeJS($banner_data && $banner_data['date_scheduled'] > 0 ? $banner_data['date_scheduled'] : '') . '" class="form-control datepicker">';
            $mainDesc['expires_date'] = '<input type="text" name="expires_date" value="' . \common\helpers\Date::formatDateTimeJS($banner_data && $banner_data['expires_date'] > 0 ? $banner_data['expires_date'] : '') . '" class="form-control datepicker">';

            $mainDesc['status'] = tep_draw_checkbox_field('status', '1', (isset($banner_data['status']) && $banner_data['status'] ? true : false), '', 'class="check_on_off"');

            $mainDesc['sort_order'] = tep_draw_input_field('sort_order', ($banner_data ? $banner_data['sort_order'] : ''), 'class="form-control"');
            $mainDesc['nofollow'] = tep_draw_checkbox_field('nofollow', '', ($banner_data ? $banner_data['nofollow'] : ''), 'class="form-control"');

            $banners_data = $mainDesc;
        }
        $banners_data['lang'] = $cDescription;

        if (Platform::isMulti()) {
            foreach (Platform::getList(false, true) as $_platform_info) {
                $status = (isset($banner_statuses[$_platform_info['id']]) ? true : false);
                if ($_platform_info['id'] == $platform_id) {
                    $status = true;
                }
                $platform_statuses[$_platform_info['id']] = tep_draw_checkbox_field('platform_status[' . $_platform_info['id'] . ']', '1', $status, '', 'class="check_on_off platform-status" data-platform-id="' . $_platform_info['id'] . '"');
            }
        } else {
            $platform_statuses = Html::hiddenInput('platform_status[' . Platform::firstId() . ']', 1);
        }
        $banners_data['platform_statuses'] = $platform_statuses;

        $this->selectedMenu = array('marketing', 'banner_manager');

        if (Yii::$app->request->isAjax) {
            $this->layout = false;
        }
        $text_new_or_edit = ($banners_id == 0) ? TEXT_BANNER_INSERT : TEXT_BANNER_EDIT;
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('banner_manager/index'), 'title' => $text_new_or_edit);

        foreach (\common\helpers\Hooks::getList('banner_manager/banneredit') as $filename) {
            include($filename);
        }

        $render_data = [
            'banners_id' => $banners_id,
            'cInfo' => $cInfo,
            'languages' => $languages,
            //'cDescription' => $cDescription,
            //'mainDesc'=>$mainDesc,
            'banners_data' => $banners_data,
            'platforms' => Platform::getList(false, true),
            'first_platform_id' => Platform::firstId(),
            'isMultiPlatforms' => Platform::isMulti(),
            'tr' => \common\helpers\Translation::translationsForJs(['IMAGE_SAVE', 'IMAGE_CANCEL', 'NOT_SAVE',
                'CHANGED_DATA_ON_PAGE', 'GO_TO_BANNER_EDITOR']),
            'setLanguage' => (int) Yii::$app->request->get('language_id', false),
            'backUrl' => Yii::$app->urlManager->createUrl(['banner_manager',
                'platform_id' => $platform_id, 'group_id' => $group_id, 'row_id' => $row_id
            ]),
            'platform_id' => $platform_id,
            'group_id' => $group_id,
            'row_id' => $row_id,
            'popup' => $popup
        ];
        return $this->render('banneredit.tpl', $render_data);
    }

    public function actionGallery()
    {
        $htm = '';

        $files = scandir(DIR_FS_CATALOG . 'images/banners/thumbnails');

        foreach ($files as $item){
            $s = strtolower(substr($item, -3));
            if ($s == 'gif' || $s == 'png' || $s == 'jpg' || $s == 'peg'){
                $htm .= '<div class="item item-general" data-src="' . DIR_WS_CATALOG . 'images/banners/' . $item . '"><div class="image"><img src="' . DIR_WS_CATALOG . 'images/banners/thumbnails/' . $item . '" title="' . $item . '" alt="' . $item . '"></div><div class="name" data-path="images/">' . $item . '</div></div>';
            }
        }
        return $htm;
    }

    public function actionUpload()
    {
        if (isset($_FILES['file'])) {
            $path = DIR_FS_CATALOG . 'images' . DIRECTORY_SEPARATOR . 'banners';
            if (!file_exists($path)) {
                mkdir($path, 0777);
                @chmod($path, 0777);
            }
            $path_thumbnails = DIR_FS_CATALOG . 'images' . DIRECTORY_SEPARATOR . 'banners' . DIRECTORY_SEPARATOR . 'thumbnails';
            if (!file_exists($path_thumbnails)) {
                mkdir($path_thumbnails, 0777);
                @chmod($path_thumbnails, 0777);
            }

            $i = 0;
            $response = [];

            while ($_FILES['file']['name'][$i]) {
                $file_name = $_FILES['file']['name'][$i];

                $copy_file = $file_name;
                $j = 1;
                $dot_pos = strrpos($copy_file, '.');
                $end = substr($copy_file, $dot_pos);
                $temp_name = $copy_file;
                while (is_file($path . DIRECTORY_SEPARATOR . $temp_name)) {
                    $temp_name = substr($copy_file, 0, $dot_pos) . '-' . $j . $end;
                    $temp_name = str_replace(' ', '_', $temp_name);
                    $j++;
                }

                $uploadfile = $path . DIRECTORY_SEPARATOR . $temp_name;
                $thumbnail = $path_thumbnails . DIRECTORY_SEPARATOR . $temp_name;

                if (!is_writeable(dirname($uploadfile))) {

                    $response[] = ['status' => 'error', 'text' => sprintf(ERROR_DATA_DIRECTORY_NOT_WRITEABLE, self::basename(\Yii::getAlias('@webroot')) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR), 'file' => $_FILES['file']['name'][$i]];

                } elseif (!is_uploaded_file($_FILES['file']['tmp_name'][$i]) || filesize($_FILES['file']['tmp_name'][$i]) == 0) {

                    $response[] = ['status' => 'error', 'text' => WARNING_NO_FILE_UPLOADED, 'file' => $_FILES['file']['name'][$i]];

                } elseif (is_file($uploadfile)) {

                    $response[] = ['status' => 'error', 'text' => FILE_ALREADY_EXIST, 'file' => $_FILES['file']['name'][$i]];

                } elseif ( move_uploaded_file($_FILES['file']['tmp_name'][$i], $uploadfile)) {

                    Images::tep_image_resize($uploadfile, $thumbnail, 200, 200);
                    $response[] = [
                        'status' => 'ok',
                        'text' => TEXT_MESSEAGE_SUCCESS_ADDED,
                        'file' => $temp_name,
                        'src' => DIR_WS_CATALOG . 'images/banners/' . $temp_name
                    ];

                } else {
                    $response[] = ['status' => 'error', 'text'=> 'error', 'file' => $_FILES['file']['name'][$i]];
                }


                $i++;
            }
        }
        return json_encode($response);
    }

    public function actionProductImages()
    {
        $productsId = (int)Yii::$app->request->get('id');

        $images = Images::getImageList($productsId);

        return json_encode($images);
    }


    public function actionBannerGroups()
    {
        $this->selectedMenu = array('marketing', 'banner_manager');
        $this->topButtons[] = '<a href="' . Yii::$app->urlManager->createUrl('banner_manager/banner-groups-edit') . '" class="btn btn-confirm new-group">' . NEW_GROUP . '</a>';
        $this->navigation[] = [
            'link' => Yii::$app->urlManager->createUrl('banner_manager/banner-groups'),
            'title' => BOX_BANNER_GROUPS
        ];

        $this->view->headingTitle = BOX_BANNER_GROUPS;

        return $this->render('banner-groups.tpl', []);
    }

    public function actionBannerGroupsList()
    {
        $draw = Yii::$app->request->get('draw', 1);
        $length = Yii::$app->request->get('length', 25);
        $search = Yii::$app->request->get('search');
        $start = Yii::$app->request->get('start', 0);
        $order = Yii::$app->request->get('order', 0);

        if ($length == -1) {
            $length = 10000;
        }
        if (!$search['value']) {
            $search['value'] = '';
        }

        $groups = BannersGroups::find()
            ->select('banners_group')
            ->distinct()
            ->where(['like', 'banners_group', $search['value']])
            ->limit($length)
            ->offset($start)
            ->orderBy(['banners_group' => $order[0]['dir'] == 'asc' ? SORT_ASC : SORT_DESC ])
            ->all();

        $responseList = [];
        foreach ($groups as $group) {

            $responseList[] = [
                '<div class="group" data-group-name="'. $group->banners_group . '">'. $group->banners_group . '</div>',
            ];
        }

        $countGroups = BannersGroups::find()
            ->select('banners_group')
            ->distinct()
            ->count();

        $response = array(
            'draw'            => $draw,
            'recordsTotal'    => $countGroups,
            'recordsFiltered' => $countGroups,
            'data'            => $responseList
        );
        echo json_encode( $response );
    }

    public function actionBannerGroupsEdit()
    {
        $groupId = Yii::$app->request->get('group_id', 0);
        $row_id = Yii::$app->request->get('row_id', 0);
        $platform_id = Yii::$app->request->get('platform_id', 0);

        if (!$groupId) {
            $maxId = BannersGroups::find()->max('id');
            $groupId = $maxId + 1;

            return $this->redirect(Yii::$app->urlManager->createUrl(['banner_manager/banner-groups-edit', 'platform_id' => $platform_id, 'group_id' => $groupId, 'row_id' => $row_id]));
        }

        $bannersGroups = BannersGroups::findOne(['id' => $groupId]);
        if ($bannersGroups) {
            $groupName = $bannersGroups->banners_group;
        } else {
            $groupName = '';
        }

        $this->selectedMenu = array('marketing', 'banner_manager');
        $this->topButtons[] = '<span class="btn btn-confirm save-group">' . IMAGE_SAVE . '</span>';
        $this->navigation[] = [
            'link' => Yii::$app->urlManager->createUrl('banner_manager/banner-groups'),
            'title' => BOX_BANNER_GROUPS . ': ' . $groupName
        ];
        $this->view->headingTitle = 'Banner group: ' . $groupName;

        $groupSizes = [];
        if ($groupId) {
            $groupSizes = BannersGroupsSizes::find()
                ->where(['group_id' => $groupId])
                ->asArray()
                ->all();
        }

        if (Yii::$app->request->isAjax) {
            $this->layout = false;
        }

        return $this->render('banner-groups-edit.tpl', [
            'groupName' => $groupName,
            'groupSizes' => $groupSizes,
            'group_id' => $groupId,
            'row_id' => $row_id,
            'platform_id' => $platform_id
        ]);
    }

    public function actionBannerGroupSettings()
    {
        $groupId = Yii::$app->request->get('group_id', 0);

        $groupSettings = [];
        if ($groupId) {
            $groupSettings = BannersGroupsSizes::find()
                ->where(['group_id' => $groupId])
                ->asArray()
                ->all();
        }

        return json_encode($groupSettings);
    }

    public function actionBannerGroupsSave()
    {
        $post = Yii::$app->request->post();

        $ids = [];
        if ($post['group_id']) {
            $bannersGroups = BannersGroups::findOne($post['group_id']);
            if (!$bannersGroups) {
                $bannersGroups = new BannersGroups();
            }
            $bannersGroups->banners_group = $post['banners_group'] ?? '';
            $bannersGroups->save();

            $groupSizes = BannersGroupsSizes::find()
                ->select('id')
                ->where(['group_id' => $post['group_id']])
                ->asArray()
                ->all();
            foreach ($groupSizes as $item) {
                $ids[$item['id']] = $item['id'];
            }
        }

        $count = 0;
        if (!empty($post['id'])) {
            foreach ($post['id'] as $id) {

                $saveData = [
                    'group_id' => $post['group_id'],
                    'width_from' => (int)$post['width_from'][$count] ? $post['width_from'][$count] : 0,
                    'width_to' => (int)$post['width_to'][$count]  ? $post['width_to'][$count] : 0,
                    'image_width' => (int)$post['image_width'][$count] ? $post['image_width'][$count] : 0,
                    'image_height' => (int)$post['image_height'][$count] ? $post['image_height'][$count] : 0,
                ];

                if ($ids[$id] ?? null) {
                    unset($ids[$id]);

                    $bannersGroupsSizes = BannersGroupsSizes::findOne($id);
                    if ($bannersGroupsSizes) {
                        $bannersGroupsSizes->attributes = $saveData;
                        $bannersGroupsSizes->save();
                    }
                } else {
                    $bannersGroupsSizes = new BannersGroupsSizes();
                    $bannersGroupsSizes->attributes = $saveData;
                    $bannersGroupsSizes->save();
                }

                $count++;
            }

            if (is_array($ids) && count($ids) > 0) {
                foreach ($ids as $id) {
                    if ($id != 0) {
                        $bannersGroupsSizes = BannersGroupsSizes::findOne($id);
                        $bannersGroupsSizes->delete();
                    }
                }
            }
        }
        return json_encode(['text' => MESSAGE_SAVED]);
    }

    public function actionBannerGroupsDeleteConfirm()
    {
        $this->layout = false;
        $group_id = \Yii::$app->request->get('group_id', 0);
        $languageId = \Yii::$app->settings->get('languages_id');

        $banners = Banners::find()->alias('b')
            ->select(['b.*'])->addSelect(['bl.banners_title'])
            ->leftJoin(BannersLanguages::tableName() . ' bl', 'b.banners_id = bl.banners_id and bl.language_id = '. $languageId)
            ->andWhere('b.group_id = "' . $group_id . '"')
            ->orderBy('bl.banners_title')
            ->asArray()->all();

        $banners_group = BannersGroups::findOne(['id' => $group_id])->banners_group;

        return $this->render('bar-group-delete', [
            'group_id' => $group_id,
            'banners' => $banners,
            'banners_group' => $banners_group
        ]);
    }

    public function actionBannerGroupsDelete()
    {
        $group_id = Yii::$app->request->post('group_id');
        $delete_image = Yii::$app->request->post('delete_image');

        BannersGroups::deleteAll(['id' => $group_id]);
        BannersGroupsSizes::deleteAll(['group_id' => $group_id]);

        $banners = Banners::find()->where(['group_id' => $group_id])->asArray()->all();

        $bannersIds = [];
        foreach ($banners as $banner) {
            $bannersIds[] = $banner['banners_id'];
        }
        $this->deleteBanners($bannersIds, $delete_image);

        $response = [
            'status' => 'ok',
            'success'=> TEXT_REMOVED,
        ];

        return json_encode($response);
    }

    public function actionBannerGroupImages()
    {
        $this->layout = false;
        $group_id = Yii::$app->request->get('group_id');
        $banners_id = Yii::$app->request->get('banners_id');

        $groupSizes = BannersGroupsSizes::find()
            ->where(['group_id' => $group_id])
            ->asArray()
            ->all();

        $groupImages = BannersGroupsImages::find()
            ->where(['banners_id' => $banners_id])
            ->asArray()
            ->all();

        $groupImagesLang = [];
        foreach ($groupImages as $langImages) {
            $groupImagesLang[$langImages['language_id']][$langImages['image_width']] = $langImages;
        }

        $response = [];

        $languages = \common\helpers\Language::get_languages();
        foreach ($languages as $language) {

            $sizeImages = [];
            foreach ($groupSizes as $size) {
                $type = 'image';
                $image = '';
                if ($image = ArrayHelper::getValue($groupImagesLang, [$language['id'], $size['image_width'], 'image'])) {

                    if (is_file(Images::getFSCatalogImagesPath() . $image)) {
                        $type = explode('/', mime_content_type(Images::getFSCatalogImagesPath() . $image));
                        $type = $type[0];
                    } elseif (is_file(DIR_FS_CATALOG . $image)) {
                        $type = explode('/', mime_content_type(DIR_FS_CATALOG . $image));
                        $type = $type[0];
                    }
                }

                $sizeImages[$size['image_width']] = [
                    'width_from' => $size['width_from'],
                    'width_to' => $size['width_to'],
                    'image_width' => $size['image_width'],
                    'image_height' => $size['image_height'],
                    'image' => ($image ? DIR_WS_IMAGES . $image : ''),
                    'type' => $type,
                    'svg' => $groupImagesLang[$language['id']][$size['image_width']]['svg'] ?? null,
                    'fit' => $groupImagesLang[$language['id']][$size['image_width']]['fit'] ?? null,
                    'position' => $groupImagesLang[$language['id']][$size['image_width']]['position'] ?? null,
                    'svg_url' => Yii::$app->urlManager->createUrl([
                            'banner_manager/banner-editor',
                        'language_id' => $language['id'],
                        'banners_id' => $banners_id,
                        'banner_group' => $size['image_width'],
                    ])
                ];
            }

            $response[$language['id']] = [
                'img' => $this->render('banner-group-images.tpl', [
                    'group_id' => $group_id,
                    'sizeImages' => $sizeImages,
                    'language_id' => $language['id'],
                ]),
                'svg' => $this->render('banner-group-svg.tpl', [
                    'group_id' => $group_id,
                    'sizeImages' => $sizeImages,
                    'language_id' => $language['id'],
                ])
            ];
        }

        return json_encode($response);
    }

    public static function saveGroupImages ($bannersId, $oldImage = '', $deleteOldImage = [])
    {
        $groupImage = Yii::$app->request->post('group_image', []);
        $groupImageUpload = Yii::$app->request->post('group_image_upload', []);
        $groupImageDelete = Yii::$app->request->post('group_image_delete', []);
        $groupId = Yii::$app->request->post('group_id', 0);
        $positions = Yii::$app->request->post('position', []);
        $fits = Yii::$app->request->post('fit', []);

        $languages = Language::get_languages();
        $groupSizes = BannersGroupsSizes::find()->where(['group_id' => $groupId])->asArray()->all();

        foreach ($languages as $language) {
            $mainImage = BannersLanguages::find()->select('banners_image')
                ->where(['banners_id' => $bannersId, 'language_id' => $language['id']])
                ->asArray()->one();

            foreach ($groupSizes as $groupSize) {
                if (!isset($groupImage[$language['id']])) {
                    continue;
                }
                $image = str_replace(DIR_WS_IMAGES, '', ($groupImage[$language['id']][$groupSize['image_width']] ?? ''));
                $imageUpload = $groupImageUpload[$language['id']][$groupSize['image_width']] ?? '';
                $imageDelete = (boolean)$groupImageDelete[$language['id']][$groupSize['image_width']] ?? false;
                $position = $positions[$language['id']][$groupSize['image_width']] ?? '';
                $fit = $fits[$language['id']][$groupSize['image_width']] ?? '';

                $bannersGroupsImages = BannersGroupsImages::findOne([
                    'banners_id' => $bannersId,
                    'language_id' => $language['id'],
                    'image_width' => $groupSize['image_width'],
                ]);

                $newImg = Image::prepareSavingImage(
                    $bannersGroupsImages->image ?? '',
                    $image,
                    $imageUpload,
                    'banners' . DIRECTORY_SEPARATOR . $bannersId,
                    $imageDelete,
                    false,
                    [
                        'width' => $groupSize['image_width'],
                        'height' => $groupSize['image_height'],
                        'fit' => $fit,
                        'parentImage' => $mainImage['banners_image'] ?? '',
                        'parentOldImage' => $oldImage[$language['id']]
                    ]
                );

                if (!$bannersGroupsImages) {
                    $bannersGroupsImages = new BannersGroupsImages();
                }
                $bannersGroupsImages->attributes = [
                    'banners_id' => (int)$bannersId,
                    'language_id' => (int)$language['id'],
                    'image_width' => (int)$groupSize['image_width'],
                    'image' => $newImg ? $newImg : '',
                    'fit' => $fit,
                    'position' => $position,
                ];
                $bannersGroupsImages->save();
            }
        }
    }

    public static function deleteBannerGroupImages ($banners_id)
    {
        $removeImages = BannersGroupsImages::find()
            ->where(['banners_id' => $banners_id])
            ->asArray()
            ->all();

        BannersGroupsImages::deleteAll(['banners_id' => $banners_id]);

        foreach ($removeImages as $removeImage) {

            $count = BannersGroupsImages::find()
                ->andWhere(['image' => $removeImage['image']])
                ->count();

            if ($count == 0 && is_file(DIR_FS_CATALOG_IMAGES . $removeImage['image'])) {
                unlink(DIR_FS_CATALOG_IMAGES . $removeImage['image']);
            }

        }

    }

    public function actionGroupBanners ()
    {
        $languageId = \Yii::$app->settings->get('languages_id');
        $bannersGroup = \Yii::$app->request->get('banners_group');

        $banners = Banners::find()
            ->alias('b')
            ->select(['b.*'])
            ->addSelect(['bl.banners_title'])
            ->leftJoin(BannersLanguages::tableName() . ' bl', 'b.banners_id = bl.banners_id and bl.language_id = '. $languageId)
            ->leftJoin(BannersGroups::tableName() . ' bg', 'bg.id = b.group_id')
            ->andWhere(['or', ['bg.banners_group' => $bannersGroup], ['b.group_id' => $bannersGroup]])
            ->orderBy('bl.banners_title')
            ->asArray()->all();

        $responseList = [];
        foreach ($banners as $banner) {
            $platforms = BannersToPlatform::find()->alias('b2p')->select(['p.platform_name'])
                ->leftJoin(\common\models\Platforms::tableName() . ' p', 'p.platform_id = b2p.platform_id')
                ->where(['b2p.banners_id' => $banner['banners_id']])->asArray()->all();

            $responseList[] = [
                'url' => \Yii::$app->urlManager->createUrl(['banner_manager/banneredit', 'banners_id' => $banner['banners_id']]),
                'banners_id' => $banner['banners_id'],
                'image' => $this->actionGetimage($banner['banners_id']),
                'banners_title' => $banner['banners_title'],
                'status' => $banner['status'],
                'platforms' => $platforms
            ];
        }

        echo json_encode($responseList);
    }

    public function actionSort ()
    {
        $ids = \Yii::$app->request->post('ids', []);

        $count = 0;
        foreach ($ids as $id) {
            $banner = Banners::findOne(['banners_id' => $id]);
            $banner->sort_order = $count;
            $banner->save(false);
            $count++;
        }
    }

}
