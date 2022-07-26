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

use common\models\BannersGroupsImages;
use Yii;
use yii\helpers\Html;
use yii\helpers\Url;
use common\models\BannersLanguages;
use common\models\BannersGroups;
use common\classes\Images;
use common\helpers\Affiliate;

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
        $this->topButtons[] = '<a href="' . Yii::$app->urlManager->createUrl('banner_manager/banneredit') . '" class="btn btn-primary"><i class="icon-file-text"></i>' . IMAGE_NEW_BANNER . '</a>';
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('marketing/index'), 'title' => HEADING_TITLE);
        $this->view->headingTitle = HEADING_TITLE;
        $tmp = array();

        $tmp[] = array(
            'title' => TABLE_HEADING_BANNERS,
            'not_important' => 0
        );
        $tmp[] = array(
            'title' => TABLE_HEADING_GROUPS,
            'not_important' => 0
        );
        if (\common\classes\platform::isMulti()) {
            $tmp[] = array(
                'title' => TABLE_HEAD_PLATFORM_NAME,
                'not_important' => 0
            );
        }
        if (\common\classes\platform::isMulti()) {
            $tmp[] = array(
                'title' => TABLE_HEAD_PLATFORM_BANNER_ASSIGN,
                'not_important' => 0
            );
        } else {
            $tmp[] = array(
                'title' => TABLE_HEADING_STATUS,
                'not_important' => 0
            );
        }

        $this->view->filters = new \stdClass();
        $this->view->filters->platform = array();
        if (isset($_GET['platform']) && is_array($_GET['platform'])) {
            foreach ($_GET['platform'] as $_platform_id)
                if ((int) $_platform_id > 0)
                    $this->view->filters->platform[] = (int) $_platform_id;
        }

        $banners_group = array();
        $banners_query = tep_db_query("select distinct banners_group from " . TABLE_BANNERS_NEW . "");
        if (tep_db_num_rows($banners_query) > 0) {
            $banners_group[] = array('id' => '', 'text' => TEXT_BANNER_FILTER_BY);
            while ($banners_gr = tep_db_fetch_array($banners_query)) {
                $banners_group[] = array('id' => $banners_gr['banners_group'], 'text' => $banners_gr['banners_group']);
            }
        }

        $this->view->bannerTable = $tmp;
        return $this->render('index', array(
            'isMultiPlatforms' => \common\classes\platform::isMulti(),
            'platforms' => \common\classes\platform::getList(false, true),
            'groups' => tep_draw_pull_down_menu('filter_by', $banners_group, ( isset($_GET['banners_group']) && tep_not_null($_GET['banners_group']) ? $_GET['banners_group'] : ''), ' class="form-control"')
        ));
    }

    public function actionGetimage($banner_id)
    {
        $languages_id = \Yii::$app->settings->get('languages_id');
        $banners_id = (int)Yii::$app->request->get('banner', 0);

        $banner_query = tep_db_query("select banners_title, banners_image from " . TABLE_BANNERS_LANGUAGES . " where banners_id = '" . (int) $banner_id . "' and language_id = '" . $languages_id . "'");
        $banner = tep_db_fetch_array($banner_query);

        $page_title = $banner['banners_title'];
        if ($banner['banners_image']) {
            $image_source = tep_image(HTTP_CATALOG_SERVER . DIR_WS_CATALOG_IMAGES . $banner['banners_image'], $page_title, '100');
            return $image_source;
        } else {
            return '';
        }
    }

    public function actionList()
    {
        $languages_id = \Yii::$app->settings->get('languages_id');
        $draw = Yii::$app->request->get('draw', 1);
        $start = Yii::$app->request->get('start', 0);
        $length = Yii::$app->request->get('length', 10);

        $responseList = array();
        $formFilter = Yii::$app->request->get('filter');
        parse_str($formFilter, $output);

        $current_page_number = ( $start / $length ) + 1;

        $filter_by_platform = array();
        if (isset($output['platform']) && is_array($output['platform'])) {
            foreach ($output['platform'] as $_platform_id)
                if ((int) $_platform_id > 0)
                    $filter_by_platform[] = (int) $_platform_id;
        }
        $search_condition = '';
        if (count($filter_by_platform) > 0) {
            $search_condition = ' and b.banners_id IN (SELECT banners_id FROM ' . TABLE_BANNERS_TO_PLATFORM . ' WHERE platform_id IN(\'' . implode("','", $filter_by_platform) . '\'))  ';
        }

        if (isset($_GET['search']['value']) && tep_not_null($_GET['search']['value'])) {
            $keywords = tep_db_input(tep_db_prepare_input($_GET['search']['value']));
            $search_condition .= " and (b.banners_group like '%" . $keywords . "%' or bl.banners_title like '%" . $keywords . "%') ";
        }

        if (tep_not_null($output['filter_by'])) {
            $search_condition .= " and (b.banners_group = '" . tep_db_input($output['filter_by']) . "') ";
        }

        if (isset($_GET['order'][0]['column']) && $_GET['order'][0]['dir']) {
            switch ($_GET['order'][0]['column']) {
                case 0:
                    $orderBy = "bl.banners_title " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir']));
                    break;
                case 1:
                    $orderBy = "b.banners_group " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir'])) . ", bl.banners_title";
                    break;
                case 3:
                    $orderBy = "b.status " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir'])) . ", bl.banners_title";
                    break;
                default:
                    $orderBy = "bl.banners_title, b.banners_group";
                    break;
            }
        } else {
            $orderBy = "bl.banners_title, b.banners_group";
        }

        $banners_query_raw = "select * from " . TABLE_BANNERS_NEW . " b, " . TABLE_BANNERS_LANGUAGES . " bl where bl.banners_id = b.banners_id and bl.language_id = " . (int) $languages_id . " " . $search_condition . " order by " . $orderBy;
        $banners_split = new \splitPageResults($current_page_number, $length, $banners_query_raw, $banners_query_numrows);

        $banners_query = tep_db_query($banners_query_raw);
        while ($banners = tep_db_fetch_array($banners_query)) {
            $tmp = array();
            $info_query = tep_db_query("select sum(banners_shown) as banners_shown, sum(banners_clicked) as banners_clicked from " . TABLE_BANNERS_HISTORY . " where banners_id = '" . (int) $banners['banners_id'] . "'");
            $info = tep_db_fetch_array($info_query);
            $banners_shown = ($info['banners_shown'] != '') ? $info['banners_shown'] : '0';
            $banners_clicked = ($info['banners_clicked'] != '') ? $info['banners_clicked'] : '0';

            $tmp[] = '<div class="click_double imgcenter" data-click-double="' . \Yii::$app->urlManager->createUrl(['banner_manager/banneredit', 'banners_id' => $banners['banners_id']]) . '">' . $this->actionGetimage($banners['banners_id']) . '<span>' . $banners['banners_title'] .
                    '<input class="cell_identify" type="hidden" value="' . $banners['banners_id'] . '"></span></div>';


            if (\common\classes\platform::isMulti()) {
                $platforms = '';
                $public_checkbox = '';

                $banner_statuses = array();
                $get_statuses_r = tep_db_query("SELECT banners_id, platform_id FROM " . TABLE_BANNERS_TO_PLATFORM . " WHERE banners_id='" . $banners['banners_id'] . "'");
                while ($get_status = tep_db_fetch_array($get_statuses_r)) {
                    $sub_row_key = $get_status['banners_id'] . '^' . $get_status['platform_id'];
                    $banner_statuses[$sub_row_key] = 1;
                }

                foreach (\common\classes\platform::getList(false, true) as $platform_variant) {

                    $sub_row_key = $banners['banners_id'] . '^' . $platform_variant['id'];
                    $sub_row_disabled = !isset($banner_statuses[$sub_row_key]);

                    $_row_key = $banners['banners_id'] . '-' . $platform_variant['id'];
                    if ($platform_variant['is_marketplace'] == 0) {
                        $platforms .= '<div id="banner-' . $_row_key . '"' . ($sub_row_disabled ? ' class="platform-disable"' : '') . '>' . $platform_variant['text'] . '</div>';

                        $public_checkbox .= '<div>' .
                                (( isset($banner_statuses[$sub_row_key]) ) ?
                                '<input type="checkbox" value="' . $_row_key . '" name="status[' . $banners['banners_id'] . '][' . $platform_variant['id'] . ']" class="check_on_off" checked="checked" data-id="banner-' . $_row_key . '">' :
                                '<input type="checkbox" value="' . $_row_key . '" name="status[' . $banners['banners_id'] . '][' . $platform_variant['id'] . ']" class="check_on_off" data-id="banner-' . $_row_key . '">'
                                ) . '</div>';
                    }
                }

                $tmp[] = '<div class="click_double" data-click-double="' . \Yii::$app->urlManager->createUrl(['banner_manager/banneredit', 'banners_id' => $banners['banners_id']]) . '">' . $banners['banners_group'] . '</div>';

                $tmp[] = '<div class="platforms-cell">' . $platforms . '</div>';
                $tmp[] = '<div class="platforms-cell-checkbox">' . $public_checkbox . '</div>';
            } else {
                $get_status = tep_db_fetch_array(tep_db_query("SELECT COUNT(*) AS status FROM " . TABLE_BANNERS_TO_PLATFORM . " WHERE banners_id='" . $banners['banners_id'] . "' AND platform_id='" . \common\classes\platform::firstId() . "'"));
                $banners['status'] = $get_status['status'];
                $tmp[] = '<div class="click_double" data-click-double="' . \Yii::$app->urlManager->createUrl(['banner_manager/banneredit', 'banners_id' => $banners['banners_id']]) . '"><a class="popupN" href="' . \Yii::$app->urlManager->createUrl('banner_manager/bannertype?group=' . $banners['banners_group']) . '"><i class="icon-pencil icon"></i>' . $banners['banners_group'] . '</a></div>';
                $tmp[] = '<input type="checkbox" value=' . $banners['banners_id'] . ' name="status" class="check_on_off"' . ($banners['status'] == '1' ? ' checked="checked"' : '') . '>';
            }
            $responseList[] = $tmp;
        }

        $response = array(
            'draw' => $draw,
            'recordsTotal' => $banners_query_numrows,
            'recordsFiltered' => $banners_query_numrows,
            'data' => $responseList,
        );
        echo json_encode($response);
    }

    function getBanner($bID)
    {
        $languages_id = \Yii::$app->settings->get('languages_id');
        $banners_query = tep_db_query("select * from " . TABLE_BANNERS_NEW . " b, " . TABLE_BANNERS_LANGUAGES . " bl where bl.banners_id = b.banners_id and bl.language_id = " . (int) $languages_id . " and b.affiliate_id = 0 and b.banners_id = '" . (int) $bID . "'");
        return (tep_db_num_rows($banners_query) ? (object) tep_db_fetch_array($banners_query) : false);
    }

    public function actionView()
    {
        $bID = Yii::$app->request->get('bID', 0);

        if ($bID) {
            $bInfo = $this->getBanner($bID);
        }
        $b_platform = '';
        $banners_platform = tep_db_query("select platform_name from " . TABLE_PLATFORMS . " p left join " . TABLE_BANNERS_TO_PLATFORM . " bt on p.platform_id = bt.platform_id where bt.banners_id ='" . $bID . "' ");
        if (tep_db_num_rows($banners_platform) > 0) {
            while ($banners_platform_result = tep_db_fetch_array($banners_platform)) {
                $b_platform .= '<div class="platform_res">' . $banners_platform_result['platform_name'] . '</div>';
            }
        }
        if (is_object($bInfo)) {
            echo '<div class="or_box_head">' . $bInfo->banners_title . '</div>';

            echo '<div class="row_or_wrapp">';
            echo '<div class="row_or"><div>' . TEXT_BANNERS_DATE_ADDED . '</div><div>' . \common\helpers\Date::date_format($bInfo->date_added, DATE_FORMAT_SHORT) . '</div></div>';
            echo '</div>';

            if ((function_exists('imagecreate')) && ($this->dir_ok) && ($this->banner_extension)) {
                $banner_id = $bInfo->banners_id;
                $banner_extension = $this->banner_extension;
                $days = '3';
                include(DIR_WS_INCLUDES . 'graphs/banner_infobox.php');
                echo '<div class="graph b_imgcenter">' . $this->actionGetimage($bID) . '</div>';
            }
            echo '<div class="pad_bottom b_right"><strong>' . TEXT_GROUP . '</strong><span>' . $bInfo->banners_group . '</span></div>';
            echo '<div class="b_right"><strong>' . BOX_PLATFORMS . ':</strong><span>' . $b_platform . '</span></div>';

            if ($bInfo->date_scheduled) {
                echo '<div class="pad_bottom">' . sprintf(TEXT_BANNERS_SCHEDULED_AT_DATE, \common\helpers\Date::datetime_short($bInfo->date_scheduled, DATE_FORMAT_SHORT)) . '</div>';
            }
            if ($bInfo->expires_date) {
                echo '<div class="pad_bottom">' . sprintf(TEXT_BANNERS_EXPIRES_AT_DATE, \common\helpers\Date::datetime_short($bInfo->expires_date, DATE_FORMAT_SHORT)) . '</div>';
            } elseif ($bInfo->expires_impressions) {
                echo '<div class="pad_bottom">' . sprintf(TEXT_BANNERS_EXPIRES_AT_IMPRESSIONS, $bInfo->expires_impressions) . '</div>';
            }
            if ($bInfo->date_status_change) {
                //echo '<div class="pad_bottom">' . sprintf(TEXT_BANNERS_STATUS_CHANGE, \common\helpers\Date::date_format($bInfo->date_status_change, DATE_FORMAT_SHORT)) . '</div>';
            }
            //echo '<div class="btn-toolbar btn-toolbar-order"><button class="btn btn-edit btn-no-margin"  onClick="return editBanner(' . $bInfo->banners_id . ');">' . IMAGE_EDIT . '</button><button class="btn btn-delete"  onClick="return deleteItemConfirm(' . $bInfo->banners_id . ');">' . IMAGE_DELETE . '</button></div>';
            echo '<div class="btn-toolbar btn-toolbar-order"><a class="btn btn-no-margin btn-edit" href="' . tep_href_link('banner_manager/banneredit', 'banners_id=' . $bInfo->banners_id) . '">' . IMAGE_EDIT . '</a><button class="btn btn-delete"  onClick="return deleteItemConfirm(' . $bInfo->banners_id . ');">' . IMAGE_DELETE . '</button><a class="btn btn-no-margin btn-edit" href="' . tep_href_link('banner_manager/bannerduplicate', 'banners_id=' . $bInfo->banners_id) . '">' . IMAGE_COPY . '</a></div>';
        }
    }

    public function actionBannerduplicate()
    {
      $id = (int)\Yii::$app->request->get('banners_id', 0);
      $ret = false;
      if ($id > 0) {
        $fromBanner = \common\models\Banners::findOne($id);
        if ($fromBanner) {

          $newBanner = new \common\models\Banners();
          try {
            $newBanner->attributes = $fromBanner->attributes;
            $newBanner->banners_id = null;
            $newBanner->isNewRecord = true;

            $newBanner->status = 0; /// rudiment ^(
            $newBanner->save(false);
            $banners_id = $newBanner->banners_id;
            
          } catch (\Exception $ex) {
            \Yii::warning($ex->getMessage() . " #### " .print_r($newBanner, 1), 'TLDEBU_banner_save_error');
          }


          if ($banners_id>0) {
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
            self::saveGroupImages($banners_id);
          }
        
          $ret = $this->redirect(Url::toRoute(['banner_manager/banneredit', 'banners_id' => $banners_id]));
        }
      }
      // any error
      //2do add message
      if (!$ret) {
        $ret = $this->redirect(Url::toRoute('banner_manager/'));
      }
      
      return $ret;
    }

    public function actionEdit()
    {
        $form_action = 'insert';

        $parameters = array('expires_date' => '',
            'date_scheduled' => '',
            'banner_type' => '',
            'sort_order' => '',
            'banners_title' => '',
            'banners_url' => '',
            'banners_group' => '',
            'banners_image' => '',
            'banners_html_text' => '',
            'expires_impressions' => '');

        $bInfo = new \objectInfo($parameters);

        if (isset($_GET['bID'])) {
            $form_action = 'update';

            $bID = tep_db_prepare_input($_GET['bID']);
            $bInfo = $this->getBanner($bID);
        } elseif (tep_not_null($_POST)) {
            $bInfo = new \objectInfo(tep_db_prepare_input($_POST));
        }

        $groups_array = array();
        $groups_query = tep_db_query("select distinct banners_group from " . TABLE_BANNERS_NEW . " order by banners_group");
        while ($groups = tep_db_fetch_array($groups_query)) {
            $groups_array[] = array('id' => $groups['banners_group'], 'text' => $groups['banners_group']);
        }
        $banner_type[0] = array('id' => 'banner', 'text' => 'banner');
        $banner_type[1] = array('id' => 'carousel', 'text' => 'carousel');
        $banner_type[2] = array('id' => 'slider', 'text' => 'slider');
        $languages = \common\helpers\Language::get_languages();
        ob_start();
        echo tep_draw_form('new_banner', FILENAME_BANNER_MANAGER . '/' . $form_action, '', 'post', 'enctype="multipart/form-data"');
        if ($form_action == 'update')
            echo tep_draw_hidden_field('banners_id', $bID);
        ?>
            <?php $count = 0; ?>
        <div class="banner_page">
                <?php
                echo '<ul class="nav nav-tabs">';
                foreach ($languages as $lang) {
                    echo '<li' . ($count == 0 ? ' class="active"' : '') . '><a href="#tab_' . $lang['code'] . '" data-toggle="tab">' . $lang['image'] . '<span>' . $lang['name'] . '</span></a></li>';
                    $count++;
                }
                echo '</ul>';
                ?>
            <div class="tab-content">
        <?php $counter = 0; ?>
        <?php foreach ($languages as $lang) { ?>    
                    <div class="tab-pane<?php echo ($counter == 0 ? ' active' : ''); ?>" id="tab_<?php echo $lang['code']; ?>">
                        <table border="0" cellspacing="0" cellpadding="2" height="100%">
                            <tr>
                                <td class="label_name"><?php echo TEXT_BANNERS_TITLE; ?></td>
                                <td class="label_value"><?php echo tep_draw_input_field('banners_title', $bInfo->banners_title, '', true); ?></td>
                            </tr>
                            <tr>
                                <td class="label_name"><?php echo TEXT_BANNERS_URL; ?></td>
                                <td class="label_value"><?php echo tep_draw_input_field('banners_url', $bInfo->banners_url); ?></td>
                            </tr>
                            <tr>
                                <td class="label_name" valign="top"><?php echo TEXT_BANNERS_GROUP; ?></td>
                                <td class="label_value"><?php echo tep_draw_pull_down_menu('banners_group', $groups_array, $bInfo->banners_group) . (!Affiliate::isLogged() ? TEXT_BANNERS_NEW_GROUP . '<br>' . tep_draw_input_field('new_banners_group', '', '', ((sizeof($groups_array) > 0) ? false : true)) : ''); ?></td>
                            </tr>
                            <tr>
                                <td class="label_name" valign="top"><?php echo TEXT_BANNERS_TYPE; ?></td>
                                <td class="label_value"><?php echo tep_draw_pull_down_menu('banner_type', $banner_type, $bInfo->banner_type); ?></td>
                            </tr>
                            <tr>
                                <td class="label_name" valign="top"><?php echo TEXT_BANNERS_IMAGE; ?></td>
                                <td class="label_value"><?php echo tep_draw_file_field('banners_image') . (!Affiliate::isLogged() ? ' ' . TEXT_BANNERS_IMAGE_LOCAL . '<br>' . DIR_FS_CATALOG_IMAGES . tep_draw_input_field('banners_image_local', (isset($bInfo->banners_image) ? $bInfo->banners_image : '')) : ''); ?></td>
                            </tr>
                            <tr>
                                <td class="label_name"><?php echo TEXT_BANNERS_IMAGE_TARGET; ?></td>
                                <td class="label_value"><?php echo DIR_FS_CATALOG_IMAGES . (Affiliate::isLogged() ? 'banners/' . $login_id . '/' : '') . tep_draw_input_field('banners_image_target'); ?></td>
                            </tr>
                            <tr>
                                <td valign="top" class="label_name"><?php echo TEXT_BANNERS_HTML_TEXT; ?></td>
                                <td class="label_value"><?php echo tep_draw_textarea_field('banners_html_text', 'soft', '60', '10', $bInfo->banners_html_text); ?></td>
                            </tr>
                            <tr>
                                <td class="label_name"><?php echo TEXT_BANNERS_SCHEDULED_AT; ?><br><small>(<?php echo strtolower(DATE_FORMAT_SPIFFYCAL); ?>)</small></td>
                                <td valign="top" class="label_value"><?php echo tep_draw_calendar_jquery('date_scheduled', $bInfo->date_scheduled); ?></td>
                            </tr>
                            <tr>
                                <td valign="top" class="label_name"><?php echo TEXT_BANNERS_EXPIRES_ON; ?><br><small>(<?php echo strtolower(DATE_FORMAT_SPIFFYCAL); ?>)</small></td>
                                <td class="label_value"><?php echo tep_draw_calendar_jquery('expires_date', $bInfo->expires_date); ?><?php echo TEXT_BANNERS_OR_AT . '<br>' . tep_draw_input_field('expires_impressions', $bInfo->expires_impressions, 'maxlength="7" size="7"') . ' ' . TEXT_BANNERS_IMPRESSIONS; ?></td>
                            </tr>
                            <tr>
                                <td valign="top" class="label_name"><?php echo TEXT_BANNER_STATUS; ?></td>
                                <td class="label_value"><?php echo tep_draw_checkbox_field('status', '', ($bInfo->status ? true : false), '', 'class="check_on_off"'); ?></td>
                            </tr>
                            <tr>
                                <td class="label_name"><?php echo TEXT_BANNER_SORT_ORDER; ?></td>
                                <td class="label_value"><?php echo tep_draw_input_field('sort_order', $bInfo->sort_order); ?></td>
                            </tr>
                        </table>
                    </div>
            <?php
            $counter++;
        }
        ?>
            </div>
            <div class="btn-bar">
                <div class="btn-left"><?php echo (($form_action == 'insert') ? '<input type="submit" value="' . IMAGE_INSERT . '" class="btn btn-primary">' : '<input type="submit" value="' . IMAGE_UPDATE . '" class="btn btn-primary">'); ?></div>
                <div class="btn-right"><?php echo '<button class="btn btn-cancel" onclick="return resetStatement();">' . IMAGE_CANCEL . '</button>'; ?></div>
            </div>
        </div>
        </form>
        <?php
        $page = ob_get_clean();
        echo $page;
    }

    public function actionSubmit()
    {
        global $login_id;

        \common\helpers\Translation::init('admin/banner_manager');

        $banners_id = $_POST['banners_id'];
        $this->view->errorMessageType = 'success';
        $this->view->errorMessage = '';
        //	die($banners_id);
        if ($banners_id > 0) {
            $action = 'update';
        } else {
            $action = 'insert';
        }

        $banner_params = array();

        $platforms = \common\classes\platform::getList(false, true);

        $sql_data_array = array();
        $new_banners_group = tep_db_prepare_input(\Yii::$app->request->post('new_banners_group'));
        if (Affiliate::isLogged()) {
            $banners_group = tep_db_prepare_input(\Yii::$app->request->post('banners_group'));
        } else {
            $banners_group = (empty($new_banners_group)) ? tep_db_prepare_input(\Yii::$app->request->post('banners_group')) : $new_banners_group;
        }

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

        //$banner_type = ($_POST['banner_type'] ? tep_db_prepare_input($_POST['banner_type']) : '');
        $sort_order = tep_db_prepare_input(\Yii::$app->request->post('sort_order'));
        $status = 0;
        if (is_array(\Yii::$app->request->post('status'))) {
            $status = isset($_POST['status'][\common\classes\platform::firstId()]) ? 1 : 0;
        } else {
            $status = isset($_POST['status']) ? 1 : 0;
        }

        if (($status == '0') || ($status == '1')) {
            $sql_data_array['status'] = $status;
        }
        if (Affiliate::isLogged()) {
            $sql_data_array['affiliate_id'] = $login_id;
        }

        //$sql_data_array['banner_type'] = tep_db_prepare_input($_POST['banner_type']);
        $sql_data_array['sort_order'] = tep_db_prepare_input($_POST['sort_order']);
        $sql_data_array['nofollow'] = $_POST['nofollow'] ? 1 : 0;
        $sql_data_array['banners_group'] = tep_db_prepare_input($_POST['banners_group']);

        if (!empty($sql_data_array['banners_group']) && !isset($banner_params[$sql_data_array['banners_group']])) {
            $get_banner_params_r = tep_db_query(
                    "SELECT banner_type FROM " . TABLE_BANNERS_NEW . " WHERE '" . tep_db_input($sql_data_array['banners_group']) . "' and banner_type!='' LIMIT 1"
            );
            if (tep_db_num_rows($get_banner_params_r) > 0) {
                $get_banner_param = tep_db_fetch_array($get_banner_params_r);
                $banner_params[$sql_data_array['banners_group']] = $get_banner_param['banner_type'];
            }
        }

        if ($action == 'insert' || $banners_id == 0) {
            $insert_sql_data['date_added'] = 'now()';
            $insert_sql_data['banner_type'] = $banner_params[$sql_data_array['banners_group']];

            tep_db_perform(TABLE_BANNERS_NEW, array_merge($sql_data_array, $insert_sql_data));
            $banners_id = tep_db_insert_id();
            Yii::$app->request->setBodyParams(['banners_id' => $banners_id]);
            $this->view->errorMessage = defined('SUCCESS_BANNER_INSERTED') ? SUCCESS_BANNER_INSERTED : 'Inserted';
            $action = 'update';
        } elseif ($action == 'update') {
            $sql_data_array['banners_id'] = $banners_id;
            $check = tep_db_fetch_array(tep_db_query(
                            "SELECT COUNT(*) AS c FROM " . TABLE_BANNERS_NEW . " WHERE banners_id='" . (int) $banners_id . "'"
            ));
            if ($check['c'] == 0) {
                $insert_sql_data['date_added'] = 'now()';
                $insert_sql_data['banner_type'] = $banner_params[$sql_data_array['banners_group']];

                tep_db_perform(TABLE_BANNERS_NEW, array_merge($sql_data_array, $insert_sql_data));
            } else {
                $update_sql_data['date_status_change'] = 'now()';

                tep_db_perform(TABLE_BANNERS_NEW, array_merge($sql_data_array, $update_sql_data), 'update', "banners_id = '" . (int) $banners_id . "'");
            }

            $this->view->errorMessage = defined('SUCCESS_BANNER_UPDATED') ? SUCCESS_BANNER_UPDATED : 'Updated';
        }
        if (\common\classes\platform::isMulti()) {
            foreach ($platforms as $_platform_info) {
                if (isset($_POST['status'][$_platform_info['id']])) {
                    tep_db_query("REPLACE INTO " . TABLE_BANNERS_TO_PLATFORM . " (banners_id, platform_id) VALUES('" . (int) $banners_id . "', '" . (int) $_platform_info['id'] . "')");
                } else {
                    tep_db_query("DELETE FROM  " . TABLE_BANNERS_TO_PLATFORM . " WHERE banners_id='" . (int) $banners_id . "' AND platform_id='" . (int) $_platform_info['id'] . "'");
                }
            }
        } else {
            if ($status) {
                tep_db_query("REPLACE INTO " . TABLE_BANNERS_TO_PLATFORM . " (banners_id, platform_id) VALUES('" . (int) $banners_id . "', '" . (int) \common\classes\platform::firstId() . "')");
            } else {
                tep_db_query("DELETE FROM  " . TABLE_BANNERS_TO_PLATFORM . " WHERE banners_id='" . (int) $banners_id . "' AND platform_id='" . (int) \common\classes\platform::firstId() . "'");
            }
        }

        $languages = \common\helpers\Language::get_languages();


        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
            $language_id = $languages[$i]['id'];
            $banners_title = tep_db_prepare_input($_POST['banners_title'][$language_id]);


            $banners_url = tep_db_prepare_input($_POST['banners_url'][$language_id] ?? null);
            $target = tep_db_prepare_input($_POST['target'][$language_id] ?? null);
            $banner_display = tep_db_prepare_input($_POST['banner_display'][$language_id] ?? null);
            $text_position = tep_db_prepare_input($_POST['text_position'][$language_id] ?? null);


            $banners_html_text = tep_db_prepare_input($_POST['banners_html_text'][$language_id] ?? null);

            $banner_error = false;
            
        
            $sql_data_array = [
                'banners_title' => $banners_title,
                'banners_url' => $banners_url,
                'target' => ($target == 'on' ? 1 : 0),
                'banner_display' => $banner_display,
                'banners_html_text' => $banners_html_text,
                'language_id' => $language_id,
                'text_position' => $text_position
            ];

            $imgPath = DIR_WS_IMAGES . 'banners' . DIRECTORY_SEPARATOR . $banners_id;
            if ($banner_display == 4) {

                $sql_data_array['banners_image'] = str_replace(DIR_WS_IMAGES, '', $_POST['banners_video'][$language_id]);
                if ($_POST['banners_video_upload'][$language_id] != '') {
                    $val = \backend\design\Uploads::move($_POST['banners_video_upload'][$language_id], $imgPath);
                    $sql_data_array['banners_image'] = str_replace(DIR_WS_IMAGES, '', $val);
                } else {
                    $sql_data_array['banners_image'] = Images::moveImage($sql_data_array['banners_image'], 'banners' . DIRECTORY_SEPARATOR . $banners_id);
                }

            } else {

                $sql_data_array['banners_image'] = str_replace(DIR_WS_IMAGES, '', $_POST['banners_image'][$language_id]);
                if ($_POST['banners_image_upload'][$language_id] != '') {
                    $val = \backend\design\Uploads::move($_POST['banners_image_upload'][$language_id], $imgPath);
                    $sql_data_array['banners_image'] = str_replace(DIR_WS_IMAGES, '', $val);
                } else {
                    $sql_data_array['banners_image'] = Images::moveImage($sql_data_array['banners_image'], 'banners' . DIRECTORY_SEPARATOR . $banners_id);
                }
                Images::createWebp($sql_data_array['banners_image']);

            }

            $check_banner = tep_db_query("select * from " . TABLE_BANNERS_LANGUAGES . " where banners_id = '" . $banners_id . "' and language_id = '" . $languages[$i]['id'] . "' ");
            if (tep_db_num_rows($check_banner) == 0) {
                if ($sql_data_array['banners_title'] || $sql_data_array['banners_url'] || $sql_data_array['banners_html_text'] || $sql_data_array['banners_image']) {
                    $insert_sql_data = [
                        'banners_id' => $banners_id
                    ];
                    $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

                    tep_db_perform(TABLE_BANNERS_LANGUAGES, $sql_data_array);
                    $this->view->errorMessage = SUCCESS_BANNER_INSERTED;
                    $messageType = 'success';
                } else {
                    if ($messageType != 'success') {
                        $this->view->errorMessage = ERROR_WARNING;
                        $messageType = 'warning';
                    }
                }
            } else {

                tep_db_perform(TABLE_BANNERS_LANGUAGES, $sql_data_array, 'update', "banners_id = '" . (int) $banners_id . "' and language_id ='" . (int) $language_id . "'");
                $this->view->errorMessage = SUCCESS_BANNER_UPDATED;
                $messageType = 'success';
            }
        }

        if ($banner_display != 4) {
            self::saveGroupImages($banners_id);
        }

        if ($this->view->errorMessage) {
            ?>
            <div class="popup-box-wrap pop-mess">
                <div class="around-pop-up"></div>
                <div class="popup-box">
                    <div class="pop-up-close pop-up-close-alert"></div>
                    <div class="pop-up-content">
                        <div class="popup-heading"><?php echo TEXT_NOTIFIC; ?></div>
                        <div class="popup-content pop-mess-cont pop-mess-cont-<?php echo $messageType; ?>">
            <?php echo $this->view->errorMessage; ?>
                        </div>  
                    </div>   
                    <div class="noti-btn">
                        <div></div>
                        <div><span class="btn btn-primary"><?php echo TEXT_BTN_OK; ?></span></div>
                    </div>
                </div>  
                <script>
                    $('body').scrollTop(0);
                    $('.pop-mess .pop-up-close-alert, .noti-btn .btn').click(function () {
                        $(this).parents('.pop-mess').remove();
                    });
                </script>
            </div>

            <?php
        }

        /* else {
          $action = 'new';
          } */

        //return $this->redirect(Url::toRoute('banner_manager/'));
        return $this->actionBanneredit();
    }

    public function actionDeleteconfirm()
    {
        $bInfo = $this->getBanner(Yii::$app->request->get('bID', 0));
        if ($bInfo) {
            echo '<div class="or_box_head">' . $bInfo->banners_title . '</div>';
            echo tep_draw_form('banners', 'banner_manager/delete', 'bID=' . $bInfo->banners_id, 'post');
            echo '<div class="pad_bottom">' . TEXT_INFO_DELETE_INTRO . '</div>';
            if ($bInfo->banners_image) {
                echo '<div class="pad_bottom">' . tep_draw_checkbox_field('delete_image', 'on', true, '', 'class="uniform"') . '<span>' . TEXT_INFO_DELETE_IMAGE . '</span></div>';
            }
            echo '<div class="btn-toolbar btn-toolbar-order"><button class="btn btn-delete btn-no-margin">' . IMAGE_DELETE . '</button><button class="btn btn-cancel"  onClick="return resetStatement();">' . IMAGE_CANCEL . '</button></div>';
            echo '</form>';
        }
    }

    public function actionDelete()
    {
        $messageStack = \Yii::$container->get('message_stack');
        $banners_id = Yii::$app->request->get('bID', 0);

        if (isset($_POST['delete_image']) && ($_POST['delete_image'] == 'on')) {
            $banner_query = tep_db_query("select banners_image from " . TABLE_BANNERS_LANGUAGES . " where banners_id = '" . (int) $banners_id . "'");
            $banner = tep_db_fetch_array($banner_query);

            if (is_file(DIR_FS_CATALOG_IMAGES . $banner['banners_image'])) {
                if (is_writeable(DIR_FS_CATALOG_IMAGES . $banner['banners_image'])) {
                    unlink(DIR_FS_CATALOG_IMAGES . $banner['banners_image']);
                } else {
                    $messageStack->add_session(ERROR_IMAGE_IS_NOT_WRITEABLE);
                }
            } else {
                $messageStack->add_session(ERROR_IMAGE_DOES_NOT_EXIST);
            }
        }

        tep_db_query("delete from " . TABLE_BANNERS_NEW . " where banners_id = '" . (int) $banners_id . "'");
        tep_db_query("delete from " . TABLE_BANNERS_LANGUAGES . " where banners_id = '" . (int) $banners_id . "'");
        tep_db_query("delete from " . TABLE_BANNERS_HISTORY . " where banners_id = '" . (int) $banners_id . "'");

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

        $messageStack->add_session(SUCCESS_BANNER_REMOVED, 'header', 'success');

        return $this->redirect(Url::toRoute('banner_manager/'));
    }

    public function actionSwitchStatus()
    {
        $id = Yii::$app->request->post('id');
        $status = Yii::$app->request->post('status');
        if (strpos($id, '-') !== false) {
            list($bid, $pid) = explode('-', $id, 2);
            if ($status == 'true') {
                tep_db_query("REPLACE INTO " . TABLE_BANNERS_TO_PLATFORM . " (banners_id, platform_id) VALUES('" . (int) $bid . "', '" . (int) $pid . "')");
            } else {
                tep_db_query("DELETE FROM  " . TABLE_BANNERS_TO_PLATFORM . " WHERE banners_id='" . (int) $bid . "' AND platform_id='" . (int) $pid . "'");
            }
        } else {
            tep_db_query("update " . TABLE_BANNERS_NEW . " set status = '" . ($status == 'true' ? 1 : 0) . "' where banners_id = '" . (int) $id . "'");
            if ($status == 'true') {
                tep_db_query("REPLACE INTO " . TABLE_BANNERS_TO_PLATFORM . " (banners_id, platform_id) VALUES('" . (int) $id . "', '" . (int) \common\classes\platform::firstId() . "')");
            } else {
                tep_db_query("DELETE FROM  " . TABLE_BANNERS_TO_PLATFORM . " WHERE banners_id='" . (int) $id . "' AND platform_id='" . (int) \common\classes\platform::firstId() . "'");
            }
        }
    }

    public function actionBanneredit()
    {

        if (Yii::$app->request->isPost) {
            $banners_id = (int) Yii::$app->request->getBodyParam('banners_id');
        } else {
            $banners_id = (int) Yii::$app->request->get('banners_id');
        }

        $this->topButtons[] = '<span class="btn btn-confirm" onclick="return saveBanner();">' . IMAGE_SAVE . '</span>';

        if (!$banners_id) {
            $banner_query = tep_db_fetch_array(tep_db_query("select MAX(banners_id) as max_id from " . TABLE_BANNERS_NEW ));
            $banners_id = $banner_query['max_id'] + 1;

            return $this->redirect(Yii::$app->urlManager->createUrl(['banner_manager/banneredit', 'banners_id' => $banners_id]));
        }

        if ($banners_id > 0) {
            $banner_query = tep_db_query("select * from " . TABLE_BANNERS_NEW . " where banners_id = " . $banners_id);
            $banner = tep_db_fetch_array($banner_query);
        }
        $uniqueElements = [];
        $cInfo = new \objectInfo($banner);
        $groups_array = array();
        $groups_query = tep_db_query("select distinct banners_group from " . TABLE_BANNERS_NEW . " order by banners_group");
        while ($groups = tep_db_fetch_array($groups_query)) {
            if ($uniqueElements[$groups['banners_group']] ?? null) continue;
            $groups_array[] = array('id' => $groups['banners_group'], 'text' => $groups['banners_group']);
            $uniqueElements[$groups['banners_group']] = $groups['banners_group'];
        }
        $banner_type[0] = array('id' => 'banner', 'text' => 'banner');
        $banner_type[1] = array('id' => 'carousel', 'text' => 'carousel');
        $banner_type[2] = array('id' => 'slider', 'text' => 'slider');

        $groups = BannersGroups::find()
            ->select('banners_group')
            ->distinct()
            ->asArray()
            ->all();
        $groupsSet = [];
        foreach ($groups as $group) {
            if ($uniqueElements[$group['banners_group']] ?? null) continue;
            $groupsSet[] = ['id' => $group['banners_group'], 'text' => $group['banners_group']];
            $uniqueElements[$group['banners_group']] = $group['banners_group'];
        }

        $groups_array = array_merge($groups_array, $groupsSet);

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
                    "select * from " . TABLE_BANNERS_NEW . " b " .
                    " left join " . TABLE_BANNERS_LANGUAGES . " bl on b.banners_id = bl.banners_id and bl.language_id = '" . (int) $languages[$i]['id'] . "' " .
                    "where   b.banners_id = '" . $banners_id . "'  " .
                    " and b.affiliate_id=0 "
            );
            if (tep_db_num_rows($banner_description_query) > 0) {
                $banner_data = tep_db_fetch_array($banner_description_query);
            }
            \common\helpers\Php8::nullProps($banner_data, ['banners_title', 'banners_url', 'banner_display', 'target', 'svg', 'banners_html_text', 'banners_image', 'text_position', 'banners_group', 'banner_type', 'date_scheduled', 'expires_date', 'sort_order', 'nofollow']);
            $cDescription[$i]['language_id'] = $languages[$i]['id'];
            $cDescription[$i]['banners_title'] = tep_draw_input_field('banners_title[' . $languages[$i]['id'] . ']', $banner_data['banners_title'], 'class="form-control"');
            $cDescription[$i]['banners_url'] = tep_draw_input_field('banners_url[' . $languages[$i]['id'] . ']', $banner_data['banners_url'], 'class="form-control"');
            $cDescription[$i]['bannerUrl'] = 'banners_url[' . $languages[$i]['id'] . ']';

            $cDescription[$i]['target'] = tep_draw_checkbox_field('target[' . $languages[$i]['id'] . ']', 0, $banner_data['target'] == 1, '', 'class="uniform"');
            
            $cDescription[$i]['banner_display'] = $banner_data['banner_display'];
            $cDescription[$i]['banner_display_name'] = 'banner_display[' . $languages[$i]['id'] . ']';

            $cDescription[$i]['svg'] = $banner_data['svg'];
            $cDescription[$i]['svg_url'] = Yii::$app->urlManager->createUrl([
                'banner_manager/banner-editor',
                'language_id' => $languages[$i]['id'],
                'banners_id' => $banners_id
            ]);

            $cDescription[$i]['banners_html_text'] = tep_draw_textarea_field('banners_html_text[' . $languages[$i]['id'] . ']', 'soft', '70', '15', $banner_data['banners_html_text'], 'form-control"');
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
            
            $cDescription[$i]['text_position'] = $banner_data['text_position'];
            $cDescription[$i]['text_position_name'] = 'text_position[' . $languages[$i]['id'] . ']';


            $mainDesc['banners_group'] = tep_draw_pull_down_menu('banners_group', $groups_array, $banner_data['banners_group'], 'class="form-control"') . (!Affiliate::isLogged() ? tep_draw_hidden_field('new_banners_group', '', 'class="form-control new_ban_field"', ((sizeof($groups_array) > 0) ? false : true)) : '');
            $mainDesc['banner_type'] = tep_draw_pull_down_menu('banner_type', $banner_type, $banner_data['banner_type'], 'class="form-control"');
            $mainDesc['date_scheduled'] = '<input type="text" name="date_scheduled" value="' . \common\helpers\Date::formatDateTimeJS($banner_data && $banner_data['date_scheduled'] > 0 ? $banner_data['date_scheduled'] : '') . '" class="form-control datepicker">';
            $mainDesc['expires_date'] = '<input type="text" name="expires_date" value="' . \common\helpers\Date::formatDateTimeJS($banner_data && $banner_data['expires_date'] > 0 ? $banner_data['expires_date'] : '') . '" class="form-control datepicker">';

            //$mainDesc[$platform['id']]['expires_impressions'] =  tep_draw_input_field('expires_impressions', $cInfo->expires_impressions, 'maxlength="7" size="7"');

            if (\common\classes\platform::isMulti()) {
                foreach (\common\classes\platform::getList(false, true) as $_platform_info) {
                    $platform_statuses[$_platform_info['id']] = tep_draw_checkbox_field('status[' . $_platform_info['id'] . ']', '1', (isset($banner_statuses[$_platform_info['id']]) ? true : false), '', 'class="check_on_off"');
                }
            }
            $mainDesc['status'] = tep_draw_checkbox_field('status', '1', ((isset($banner_statuses[\common\classes\platform::firstId()])) ? true : false), '', 'class="check_on_off"');

            $mainDesc['sort_order'] = tep_draw_input_field('sort_order', ($banner_data ? $banner_data['sort_order'] : ''), 'class="form-control"');
            $mainDesc['nofollow'] = tep_draw_checkbox_field('nofollow', '', ($banner_data ? $banner_data['nofollow'] : ''), 'class="form-control"');

            $banners_data = $mainDesc;
        }
        $banners_data['lang'] = $cDescription;
        $banners_data['platform_statuses'] = $platform_statuses;

        $this->selectedMenu = array('marketing', 'banner_manager');

        if (Yii::$app->request->isAjax) {
            $this->layout = false;
        }
        $text_new_or_edit = ($banners_id == 0) ? TEXT_BANNER_INSERT : TEXT_BANNER_EDIT;
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('banner_manager/index'), 'title' => $text_new_or_edit);

        $render_data = [
            'banners_id' => $banners_id,
            'cInfo' => $cInfo,
            'languages' => $languages,
            //'cDescription' => $cDescription,
            //'mainDesc'=>$mainDesc,
            'banners_data' => $banners_data,
            'platforms' => \common\classes\platform::getList(false, true),
            'first_platform_id' => \common\classes\platform::firstId(),
            'isMultiPlatforms' => \common\classes\platform::isMulti(),
            'tr' => \common\helpers\Translation::translationsForJs(['IMAGE_SAVE', 'IMAGE_CANCEL', 'NOT_SAVE',
                'CHANGED_DATA_ON_PAGE', 'GO_TO_BANNER_EDITOR']),
            'setLanguage' => (int) Yii::$app->request->get('language_id', false),
        ];
        return $this->render('banneredit.tpl', $render_data);
    }

    function actionNewgroup()
    {
        if (Yii::$app->request->isPost) {
            $banners_id = (int) Yii::$app->request->getBodyParam('banners_id');
        } else {
            $banners_id = (int) Yii::$app->request->get('banners_id');
        }

        $this->layout = false;
        $this->view->usePopupMode = true;

        $groups_array = array();
        $groups_query = tep_db_query("select distinct banners_group from " . TABLE_BANNERS_NEW . " order by banners_group");
        while ($groups = tep_db_fetch_array($groups_query)) {
            $groups_array[] = array('id' => $groups['banners_group'], 'text' => $groups['banners_group']);
        }
        $html = '<div id="bannerpopup">';
        $html .= '<table cellspacing="0" cellpadding="0" width="100%">
									<tr>
											<td class="dataTableContent">' . TEXT_BANNER_NEW_GROUP . '</td>
											<td class="dataTableContent">' . tep_draw_input_field('new_ban_group_popup', '', 'class="form-control"', ((sizeof($groups_array) > 0) ? false : true)) . '</td>
									</tr>
							</table>
							<div class="btn-bar">
									<div class="btn-left"><button class="btn btn-cancel" onclick="return closePopup();">' . IMAGE_CANCEL . '</button></div>
									<div class="btn-right"><button class="btn btn-primary" onclick="return saveGroupnew();">' . IMAGE_INSERT . '</button></div>
							</div></div>';

        return $html;
    }

    function actionBannertype()
    {
        if (Yii::$app->request->isPost) {
            $group = Yii::$app->request->getBodyParam('group');
        } else {
            $group = Yii::$app->request->get('group');
        }
        $this->layout = false;
        $this->view->usePopupMode = true;
        $banner_effect = array();
        $banner_effect[0] = array('id' => 'sliceDown', 'text' => 'sliceDown');
        $banner_effect[1] = array('id' => 'sliceDownLeft', 'text' => 'sliceDownLeft');
        $banner_effect[2] = array('id' => 'sliceUp', 'text' => 'sliceUp');
        $banner_effect[3] = array('id' => 'sliceUpLeft', 'text' => 'sliceUpLeft');
        $banner_effect[4] = array('id' => 'sliceUpDown', 'text' => 'sliceUpDown');
        $banner_effect[5] = array('id' => 'sliceUpDownLeft', 'text' => 'sliceUpDownLeft');
        $banner_effect[6] = array('id' => 'fold', 'text' => 'fold');
        $banner_effect[7] = array('id' => 'fade', 'text' => 'fade');
        $banner_effect[8] = array('id' => 'random', 'text' => 'random');
        $banner_effect[9] = array('id' => 'slideInRight', 'text' => 'slideInRight');
        $banner_effect[10] = array('id' => 'slideInLeft', 'text' => 'slideInLeft');
        $banner_effect[11] = array('id' => 'boxRandom', 'text' => 'boxRandom');
        $banner_effect[12] = array('id' => 'boxRain', 'text' => 'boxRain');
        $banner_effect[13] = array('id' => 'boxRainReverse', 'text' => 'boxRainReverse');
        $banner_effect[14] = array('id' => 'boxRainGrow', 'text' => 'boxRainGrow');
        $banner_effect[15] = array('id' => 'boxRainGrowReverse', 'text' => 'boxRainGrowReverse');

        $groups_query = tep_db_query("select distinct banners_group, banner_type from " . TABLE_BANNERS_NEW . " where banners_group = '" . tep_db_input($group) . "'");
        while ($groups = tep_db_fetch_array($groups_query)) {
            $banner_type = $groups['banner_type'];
        }
        $banner_type_array = explode(';', $banner_type);
        $html = '<form name="save_banner_type" onSubmit="return saveBannertype();" id="save_banner_type"><div class="banner_type">
					<input name="banner_group" type="hidden" value="' . $group . '">
						<div class="after">
							<div class="type_title"><strong>' . TEXT_BANNERS_GROUP . '</strong></div>
							<div class="type_value group_val"><strong>' . $group . '</strong></div>
						</div>
						<div class="after">
							<div class="type_title">' . TEXT_BANNERS_TYPE . '</div>
							<div class="type_value">
								<select name="banner_type" class="form-control">
									<option value="banner"' . ($banner_type_array[0] == 'banner' ? ' selected' : '') . '>banner</option>
									<option value="carousel"' . ($banner_type_array[0] == 'carousel' ? ' selected' : '') . '>carousel</option>
									<option value="slider"' . ($banner_type_array[0] == 'slider' ? ' selected' : '') . '>slider</option>
								</select>
							</div>
						</div>
						<div class="after slider_effect"' . ($banner_type_array[0] == 'slider' ? '' : ' style="display:none;"') . '>
							<div class="type_title">' . TEXT_BANNER_EFFECT . '</div>
							<div class="type_value"> ' . tep_draw_pull_down_menu('banner_effect', $banner_effect, ($banner_type_array[1] ?? ''), 'class="form-control"') . '</div>
						</div>
						<div class="after speed"' . ($banner_type_array[0] == 'banner' || $banner_type_array[0] == '' ? ' style="display:none;"' : '') . '>
							<div class="type_title">' . TEXT_ANIMATED_SPEED . '</div>
							<div class="type_value">' . tep_draw_input_field('animated_speed', ($banner_type_array[2] ?? ''), 'class="form-control"') . '</div>
						</div>
						<div class="btn-bar">
									<div class="btn-left"><button class="btn btn-cancel" onclick="return closePopup();">' . IMAGE_CANCEL . '</button></div>
									<div class="btn-right"><button class="btn btn-primary">' . IMAGE_SAVE . '</button></div>
							</div>
						</div></form>';
        $html .= '<script type="text/javascript">
						$(document).ready(function(){
							$("select[name=banner_type]").on("change", function() {
								if($(this).val() == "slider"){
									$(".slider_effect, .speed").show();
								}else if($(this).val() == "carousel"){
									$(".slider_effect").hide();
									$(".speed").show();
								}else{
									$(".slider_effect, .speed").hide();
								}
							});
						})
						</script>';

        return $html;
    }

    function actionSavetype()
    {
        $banner_group = tep_db_prepare_input($_POST['banner_group']);
        $banner_type = $_POST['banner_type'] ? tep_db_prepare_input($_POST['banner_type']) : '';
        $banner_effect = $_POST['banner_effect'] ? tep_db_prepare_input($_POST['banner_effect']) : '';
        $animated_speed = $_POST['animated_speed'] ? tep_db_prepare_input($_POST['animated_speed']) : '';
        $sql_data_array = array('banner_type' => $banner_type . ';' . $banner_effect . ';' . $animated_speed);
        tep_db_perform(TABLE_BANNERS_NEW, $sql_data_array, 'update', "banners_group = '" . tep_db_input($banner_group) . "'");
    }

    function actionBannerEditor()
    {
        $this->selectedMenu = array('marketing', 'banner_manager');
        $this->topButtons[] = '
            <span class="btn btn-confirm btn-save-boxes btn-elements">' . IMAGE_SAVE . '</span>
            <span class="btn btn-cancel btm-back">' . IMAGE_BACK . '</span>';
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('marketing/index'), 'title' => HEADING_TITLE);
        $this->view->headingTitle = HEADING_TITLE;

        return $this->render('banner-editor.tpl', [
            'svgEditorUrl' => Yii::$app->urlManager->createUrl('banner_manager/svg-editor'),
            'banners_id' => Yii::$app->request->get('banners_id'),
            'language_id' => Yii::$app->request->get('language_id'),
            'banner_group' => Yii::$app->request->get('banner_group', 0),
            'tr' => \common\helpers\Translation::translationsForJs(['IMAGE_SAVE', 'IMAGE_CANCEL', 'NOT_SAVE',
                'YOU_CHANGED_BANNER', 'GO_TO_BANNER_PAGE'])
        ]);
    }

    function actionSvgEditor()
    {
        $languages_id = \Yii::$app->settings->get('languages_id');
        $this->layout = false;
        \common\helpers\Translation::init('admin/design');

        $languages_code = \common\helpers\Language::get_language_code($languages_id);

        return $this->render('svg-editor.tpl', [
            'setLangKey' => $languages_code['code'],
            'tr' => \common\helpers\Translation::translationsForJs(['TEXT_GALLERY_IMAGE', 'IMAGE_CANCEL', 'IMAGE_SAVE',
                'TEXT_WIDTH', 'TEXT_HEIGHT'])
        ]);
    }

    function actionGetSvg()
    {
        $banners_id = Yii::$app->request->get('banners_id');
        $language_id = Yii::$app->request->get('language_id');
        $banner_group = Yii::$app->request->get('banner_group', false);

        if ($banner_group) {
            $banner = BannersGroupsImages::find()->select(['svg', 'banners_image' => 'image'])->where([
                'banners_id' => $banners_id,
                'language_id' => $language_id,
                'image_width' => $banner_group,
            ])->asArray()->one();
        } else {
            $banner = BannersLanguages::find()->select(['svg', 'banners_image'])->where([
                'banners_id' => $banners_id,
                'language_id' => $language_id,
            ])->asArray()->one();
        }

        if ($banner['svg']) {
            return $banner['svg'];
        }

        if (!$banner['banners_image']) {
            $blankWidth = $banner_group ? $banner_group :640;

            return '<svg width="' . $blankWidth . '" height="480" xmlns="http://www.w3.org/2000/svg" xmlns:svg="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"></svg>';
        }

        $size = @GetImageSize (DIR_FS_CATALOG_IMAGES . $banner['banners_image']);

        return '
<svg width="' . $size[0] . '" height="' . $size[1] . '" xmlns="http://www.w3.org/2000/svg" xmlns:svg="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
  <image width="' . $size[0] . '" height="' . $size[1] . '" id="svg_1" x="0" xlink:href="' . HTTP_CATALOG_SERVER . DIR_WS_CATALOG_IMAGES . $banner['banners_image'] . '" y="0"/>
</svg>
            ';
    }

    function actionSaveSvg()
    {
        $banners_id = (int)Yii::$app->request->post('banners_id');
        $language_id = (int)Yii::$app->request->post('language_id');
        $banner_group = Yii::$app->request->post('banner_group', false);
        $svg = Yii::$app->request->post('svg');

        if ($banner_group) {
            $banner = BannersGroupsImages::findOne([
                'banners_id' => $banners_id,
                'language_id' => $language_id,
                'image_width' => $banner_group,
            ]);
        } else {
            $banner = BannersLanguages::findOne([
                'banners_id' => $banners_id,
                'language_id' => $language_id,
            ]);
        }

        if (!$banner) {
            if ($banner_group) {
                $banner = new BannersGroupsImages();
                $banner->image_width = $banner_group;
            } else {
                $banner = new BannersLanguages();
            }
            $banner->banners_id = $banners_id;
            $banner->language_id = $language_id;
        }

        $banner->svg = $svg;

        $save = $banner->save();

        return $save ? MESSAGE_SAVED : TEXT_MESSAGE_ERROR;
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

        $this->acl = ['TEXT_SETTINGS', 'BOX_BANNER_GROUPS'];

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
        $groupName = Yii::$app->request->get('banners_group', '');

        $this->selectedMenu = array('marketing', 'banner_manager');
        $this->topButtons[] = '<span class="btn btn-confirm save-group">' . IMAGE_SAVE . '</span>';
        $this->navigation[] = [
            'link' => Yii::$app->urlManager->createUrl('banner_manager/banner-groups'),
            'title' => BOX_BANNER_GROUPS . ': ' . $groupName
        ];
        $this->acl = ['TEXT_SETTINGS', 'BOX_BANNER_GROUPS'];
        $this->view->headingTitle = 'Banner group: ' . $groupName;

        $groupSizes = [];
        if ($groupName) {
            $groupSizes = BannersGroups::find()
                ->where(['like', 'banners_group', $groupName])
                ->asArray()
                ->all();
        }

        $bannerGroups = \common\models\Banners::find()->select('banners_group')->distinct()->asArray()->all();
        $groups = [];
        if ($groupName) {
            $groups[$groupName] = $groupName;
        }
        foreach ($bannerGroups as $group) {
            $groups[$group['banners_group']] = $group['banners_group'];
        }

        $bannersGroups = BannersGroups::find()->select('banners_group')->distinct()->asArray()->all();
        foreach ($bannersGroups as $group) {
            $groups[$group['banners_group']] = $group['banners_group'];
        }

        return $this->render('banner-groups-edit.tpl', [
            'groupName' => $groupName,
            'groupSizes' => $groupSizes,
            'groups' => $groups
        ]);
    }

    public function actionBannerGroupsSave()
    {
        $post = Yii::$app->request->post();

        $ids = [];
        if ($post['banners_group']) {
            $groupSizes = BannersGroups::find()
                ->select('id')
                ->where(['like', 'banners_group', $post['banners_group']])
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
                    'banners_group' => $post['name'],
                    'width_from' => (int)$post['width_from'][$count] ? $post['width_from'][$count] : 0,
                    'width_to' => (int)$post['width_to'][$count]  ? $post['width_to'][$count] : 0,
                    'image_width' => (int)$post['image_width'][$count] ? $post['image_width'][$count] : 0,
                    'image_height' => (int)$post['image_height'][$count] ? $post['image_height'][$count] : 0,
                ];

                if ($ids[$id] ?? null) {
                    unset($ids[$id]);

                    $bannersGroups = BannersGroups::findOne($id);
                    if ($bannersGroups) {
                        $bannersGroups->attributes = $saveData;
                        $bannersGroups->save();
                    }
                } else {
                    $bannersGroups = new BannersGroups();
                    $bannersGroups->attributes = $saveData;
                    $bannersGroups->save();
                }

                $count++;
            }

            if (is_array($ids) && count($ids) > 0) {
                foreach ($ids as $id) {
                    if ($id != 0) {
                        $bannersGroups = BannersGroups::findOne($id);
                        $bannersGroups->delete();
                    }
                }
            }
        }
        return MESSAGE_SAVED;
    }

    public function actionBannerGroupsBar()
    {
        $groupName = Yii::$app->request->get('banners_group', 0);

        if (!$groupName) {
            return '';
        }


        $this->layout = false;
        return $this->render('banner-groups-bar.tpl', [
            'groupName' => $groupName,
        ]);
    }

    public function actionBannerGroupsDeleteConfirm()
    {
        $banners_group = Yii::$app->request->get('banners_group');

        $this->layout = false;
        return $this->render('banner-groups-delete-confirm.tpl', [
            'banners_group' => $banners_group,
        ]);
    }

    public function actionBannerGroupsDelete()
    {
        $banners_group = Yii::$app->request->get('banners_group');

        BannersGroups::deleteAll(['banners_group' => $banners_group]);

        $response = [
            'status' => 'ok',
            'text'=> TEXT_REMOVED,
        ];

        return json_encode($response);
    }

    public function actionBannerGroupImages()
    {
        $this->layout = false;
        $banners_group = Yii::$app->request->get('banners_group');
        $banners_id = Yii::$app->request->get('banners_id');

        $groupSizes = BannersGroups::find()
            ->where(['banners_group' => $banners_group])
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
                $sizeImages[$size['image_width']] = [
                    'width_from' => $size['width_from'],
                    'width_to' => $size['width_to'],
                    'image_width' => $size['image_width'],
                    'image_height' => $size['image_height'],
                    'image' => DIR_WS_IMAGES . $groupImagesLang[$language['id']][$size['image_width']]['image'],
                    'svg' => $groupImagesLang[$language['id']][$size['image_width']]['svg'],
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
                    'banners_group' => $banners_group,
                    'sizeImages' => $sizeImages,
                    'language_id' => $language['id'],
                ]),
                'svg' => $this->render('banner-group-svg.tpl', [
                    'banners_group' => $banners_group,
                    'sizeImages' => $sizeImages,
                    'language_id' => $language['id'],
                ])
            ];
        }

        return json_encode($response);
    }

    public static function saveGroupImages ($bannersId)
    {
        $groupImage = Yii::$app->request->post('group_image', []);
        $groupImageUpload = Yii::$app->request->post('group_image_upload', []);
        $groupImageDelete = Yii::$app->request->post('group_image_delete', []);
        $groupSvgDelete = Yii::$app->request->post('group_svg_remove', []);
        $bannersGroup = Yii::$app->request->post('banners_group', '');

        $languages = \common\helpers\Language::get_languages();

        $groupSizes = BannersGroups::find()
            ->where(['banners_group' => $bannersGroup])
            ->asArray()
            ->all();

        foreach ($languages as $language) {
            foreach ($groupSizes as $groupSize) {
                $image = str_replace(DIR_WS_IMAGES, '', $groupImage[$language['id']][$groupSize['image_width']]);
                $imageUpload = $groupImageUpload[$language['id']][$groupSize['image_width']];
                $imageDelete = $groupImageDelete[$language['id']][$groupSize['image_width']];
                $svgDelete = $groupSvgDelete[$language['id']][$groupSize['image_width']];

                $imgPath = 'banners' . DIRECTORY_SEPARATOR . $bannersId;
                if ($imageUpload) {
                    $tmpImg = \backend\design\Uploads::move($imageUpload, DIR_WS_IMAGES . $imgPath, false);
                    $newImg = $imgPath . DIRECTORY_SEPARATOR . $tmpImg;
                } elseif ($image && is_file(DIR_FS_CATALOG_IMAGES .$image)) {
                    $newImg = $image;
                } else {
                    $mainImage = BannersLanguages::find()
                        ->select('banners_image')
                        ->where(['banners_id' => $bannersId, 'language_id' => $language['id']])
                        ->asArray()
                        ->one();

                    if (is_file(DIR_FS_CATALOG_IMAGES . $mainImage['banners_image'])) {
                        $imgExplode = explode('/', $mainImage['banners_image']);
                        $imgName = end($imgExplode);
                        $pos = strrpos($imgName, '.');
                        $name = substr($imgName, 0, $pos);
                        $ext = substr($imgName, $pos);

                        $newImg = $imgPath . DIRECTORY_SEPARATOR . $name . '[' . $groupSize['image_width'] . ']' . $ext;

                        if (!is_file(DIR_FS_CATALOG_IMAGES .$newImg)) {
                            $size = @GetImageSize(DIR_FS_CATALOG_IMAGES . $mainImage['banners_image']);
                            $height = ($size[1] * $groupSize['image_width']) / $size[0];
                            \common\classes\Images::tep_image_resize(DIR_FS_CATALOG_IMAGES . $mainImage['banners_image'], DIR_FS_CATALOG_IMAGES . $newImg, $groupSize['image_width'], $height);
                        }
                    }
                }


                $img = BannersGroupsImages::findOne([
                    'banners_id' => $bannersId,
                    'language_id' => $language['id'],
                    'image_width' => $groupSize['image_width'],
                ]);
                if ($img && $imageDelete) {
                    unlink(DIR_FS_CATALOG_IMAGES . $img->image);
                }
                if (!$img) {
                    $img = new BannersGroupsImages();
                }
                $img->attributes = [
                    'banners_id' => (int)$bannersId,
                    'language_id' => (int)$language['id'],
                    'image_width' => (int)$groupSize['image_width'],
                    'image' => $newImg ? $newImg : '',
                ];
                if ($svgDelete) {
                    $img->svg = '';
                }

                $img->save();

                Images::createWebp($newImg);

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
}
