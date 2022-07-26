<?php
/**
 * This file is part of True Loaded.
 * 
 * @link http://www.holbi.co.uk
 * @copyright Copyright (c) 2005 Holbi Group LTD
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace backend\controllers;

use Yii;

use common\models\Emails;
use common\models\Themes;
use common\models\ThemesSettings;
use yii\db\Expression;


/**
 * default controller to handle user requests.
 */
class EmailEditorController extends Sceleton {

    public $acl = ['BOX_HEADING_DESIGN_CONTROLS', 'BOX_HEADING_BUILD_NEWSLETTER'];
    
    public function actionIndex() {

        $this->selectedMenu = array('design_controls', 'email-editor');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('email-editor'), 'title' => BOX_HEADING_BUILD_NEWSLETTER);
        $this->view->headingTitle = BOX_HEADING_BUILD_NEWSLETTER;
        $this->topButtons[] = '<a href="' . Yii::$app->urlManager->createUrl('email-editor/edit') . '" class="btn btn-primary">' . IMAGE_NEW . '</a>';

        return $this->render('index.tpl', [
        ]);

    }

    public function actionList()
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

        switch ($order[0]['column']) {
            case 0: $sortCol = 'subject'; break;
            case 1: $sortCol = 'date_modified'; break;
            case 2: $sortCol = 'date_added'; break;
            default: $sortCol = 'subject';
        }

        $emails = Emails::find()
            ->where(['like', 'subject', $search['value']])
            ->limit($length)
            ->offset($start)
            ->orderBy([
                $sortCol => $order[0]['dir'] == 'asc' ? SORT_ASC : SORT_DESC,
            ])
            ->all();

        $responseList = [];
        foreach ($emails as $email) {

            $responseList[] = array(
                '<div class="item" data-item-id="'. $email->emails_id . '">'. $email->subject . '</div>',
                \common\helpers\Date::datetime_short($email->date_modified),
                \common\helpers\Date::datetime_short($email->date_added),
            );
        }

        $countItems = Emails::find()->count();

        $response = array(
            'draw'            => $draw,
            'recordsTotal'    => $countItems,
            'recordsFiltered' => $countItems,
            'data'            => $responseList
        );
        echo json_encode( $response );
    }

    public function actionEdit()
    {
        $emailId = (int)Yii::$app->request->get('email_id', 0);

        $this->selectedMenu = array('design_controls', 'email-editor');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('email-editor'), 'title' => BOX_HEADING_BUILD_NEWSLETTER);
        $this->view->headingTitle = BOX_HEADING_BUILD_NEWSLETTER;
        $this->topButtons[] = '
            <span class="btn btn-confirm btn-save-boxes btn-elements">' . IMAGE_SAVE . '</span>
            <span class="btn btn-elements btn-preview">' . IMAGE_PREVIEW . '</span>
            <span class="btn btn-elements btn-export">' . EXPORT_HTML . '</span>
            <a href="' . Yii::$app->urlManager->createUrl(['email-editor', 'item_id' => $emailId]) . '"
                class="btn btn-cancel">' . IMAGE_CANCEL . '</a>
                ';


        $themes = Themes::find()
            ->orderBy('title')
            ->asArray()
            ->all();

        $styles = [];
        $themeTemplates = [];
        foreach ($themes as $theme) {

            $templates = ThemesSettings::find()
                ->select('setting_value')
                ->where([
                    'setting_group' => 'added_page',
                    'setting_name' => 'email',
                    'theme_name' => $theme['theme_name']
                ])
                ->asArray()
                ->all();

            $theme['templates']['email'] = 'Default';
            foreach ($templates as $template) {
                $theme['templates'][\common\classes\design::pageName($template['setting_value'])] = $template['setting_value'];
            }
            $themeTemplates[] = $theme;

            $st = \backend\design\Style::getStylesByClasses($theme['theme_name'], '.b-email-editor');

            foreach ($st['attributesText'] as $class => $attrText) {
                $attrArr = explode(';', $attrText);
                foreach ($attrArr as $attr) {
                    $_attr = explode(':', $attr);
                    if (trim($_attr[0])) {
                        $styles[$theme['theme_name']][$class][trim($_attr[0])] = trim($_attr[1]);
                    }
                }
            }
        }



        $email = Emails::findOne($emailId);

        return $this->render('edit.tpl', [
            'platforms' => \common\classes\platform::getList(false),
            'id' => $emailId,
            'themes' => $themeTemplates,
            'themesJSON' => json_encode($themeTemplates),
            'subject' => $email->subject ?? null,
            'data' => $email->data ?? null,
            'theme_name' => $email->theme_name ?? null,
            'template' => $email->template ?? null,
            'styles' => json_encode($styles),
            'tr' => \common\helpers\Translation::translationsForJs(['DROP_IMAGE_HERE', 'DROP_PRODUCT_HERE',
                'EDIT_WIDGET', 'MOVE_BLOCK', 'REMOVE_WIDGET', 'IMAGE_SAVE', 'IMAGE_CANCEL', 'PRODUCTS_IN_ROW',
                'EDIT_PRODUCTS_ROW', 'CHOOSE_IMAGE', 'CHOOSE_PRODUCT', 'START_TYPING_PRODUCT_NAME', 'EDIT_TEXT'])
        ]);
    }

    public function actionBar()
    {
        $emailId = Yii::$app->request->get('email_id', 0);

        if (!$emailId) {
            return '';
        }

        $email = Emails::findOne($emailId);

        $this->layout = false;
        return $this->render('bar.tpl', [
            'emailId' => $emailId,
            'title' => $email->subject
        ]);
    }

    public function actionUpload()
    {
        if (isset($_FILES['file'])) {
            $path = \Yii::getAlias('@webroot');
            $path .= DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'emails' . DIRECTORY_SEPARATOR;
            $response = [];

            $name = $_FILES['file']['name'];

            $uploadFile = $path . $name;

            if (!is_writeable(dirname($uploadFile))) {
                $response[] = [
                    'status' => 'error',
                    'text' => sprintf(ERROR_DATA_DIRECTORY_NOT_WRITEABLE, $uploadFile),
                    'file' => $name
                ];
            } elseif (!is_uploaded_file($_FILES['file']['tmp_name']) || filesize($_FILES['file']['tmp_name']) == 0) {
                $response[] = [
                    'status' => 'error',
                    'text' => WARNING_NO_FILE_UPLOADED,
                    'file' => $name
                ];
            } elseif (is_file($uploadFile)) {
                $response[] = [
                    'status' => 'choice',
                    'text' => FILE_ALREADY_EXIST . ' <span>' . DO_YOU_WANT_USE_UPLOADED_FILE . '</span>',
                    'file' => $name
                ];
            } elseif (move_uploaded_file($_FILES['file']['tmp_name'], $uploadFile)) {

                self::resizeImg($uploadFile, $path, $name);

                $response[] = [
                    'status' => 'ok',
                    'text' => TEXT_MESSEAGE_SUCCESS_ADDED,
                    'file' => $name
                ];

            } else {
                $response[] = [
                    'status' => 'error',
                    'text'=> 'error',
                    'file' => $name
                ];
            }

        }
        return json_encode($response);
    }

    public static function resizeImg($uploadFile, $path, $name){

        $imagine = new \Imagine\Gd\Imagine();
        $artImage = $imagine->open($uploadFile);

        $size = @GetImageSize($uploadFile);
        $width = $size[0];
        $height = $size[1];


        $options = array(
            'resolution-units' => \Imagine\Image\ImageInterface::RESOLUTION_PIXELSPERINCH,
            'resolution-x' => 72,
            'resolution-y' => 72,
            'resampling-filter' => \Imagine\Image\ImageInterface::FILTER_LANCZOS,
            'png_compression_level' => 9,
        );

        if ($width > 100 || $height > 100) {
            $scaledSize = $artImage->getSize()->scale(min(array(
                100 / $artImage->getSize()->getWidth(),
                100 / $artImage->getSize()->getHeight(),
            )));
            $artImage = $artImage->resize($scaledSize);
        }

        $t_location = $path . 'thumbnails' . DIRECTORY_SEPARATOR . $name;

        $artImage->save($t_location, $options);
    }

    public function actionSave()
    {
        $emailId = (int)Yii::$app->request->post('email_id', '');
        $subject = Yii::$app->request->post('subject', '');
        $html = Yii::$app->request->post('html', '');
        $data = Yii::$app->request->post('data', '');
        $theme_name = Yii::$app->request->post('theme_name', '');
        $template = Yii::$app->request->post('template', '');
        if (!$subject) $subject = ' ';
        if (!$html) $html = ' ';
        if (!$data) $data = '{}';

        $attr = [
            'subject' => $subject,
            'html' => $html,
            'data' => $data,
            'theme_name' => $theme_name,
            'template' => $template,
            'date_modified' => new Expression('NOW()'),
        ];

        if ($emailId) {
            $email = Emails::findOne($emailId);
            if (!$email) {
                $email = new Emails();
            }
        } else {
            $email = new Emails();

            $attr['date_added'] = new Expression('NOW()');
        }
        $email->attributes = $attr;
        $email->save();

        $response = [
            'status' => 'ok',
            'text'=> 'Saved',
            'email_id' => $email->emails_id,
        ];
        return json_encode($response);
    }

    public function actionDeleteConfirm()
    {
        $emailId = (int)Yii::$app->request->get('email_id', 0);

        $email = Emails::findOne($emailId);

        $this->layout = false;
        return $this->render('delete-confirm.tpl', [
            'emails_id' => $emailId,
            'title' => $email->subject
        ]);
    }

    public function actionDelete()
    {
        $emailId = (int)Yii::$app->request->get('emails_id', 0);

        if (!$emailId) {
            return '';
        }

        Emails::deleteAll(['emails_id' => $emailId]);

        $response = [
            'status' => 'ok',
            'text'=> 'Removed',
            'emails_id' => $emailId,
        ];

        return json_encode($response);
    }

    public function actionGallery() {

        $path = DIR_FS_CATALOG . 'images' . DIRECTORY_SEPARATOR . 'emails' . DIRECTORY_SEPARATOR;
        if (!file_exists($path)) {
            mkdir($path);
        }
        if (!file_exists($path . DIRECTORY_SEPARATOR . 'thumbnails' . DIRECTORY_SEPARATOR)) {
            mkdir($path . 'thumbnails' . DIRECTORY_SEPARATOR);
        }

        $images = [];
        $files = scandir($path);
        foreach ($files as $item){
            $s = strtolower(substr($item, -3));
            if ($s == 'gif' || $s == 'png' || $s == 'jpg' || $s == 'peg'){
                if (!file_exists($path . DIRECTORY_SEPARATOR . 'thumbnails' . DIRECTORY_SEPARATOR)) {
                    mkdir($path . 'thumbnails' . DIRECTORY_SEPARATOR);
                }

                if (!file_exists($path . DIRECTORY_SEPARATOR . 'thumbnails' . DIRECTORY_SEPARATOR . $item)) {

                    self::resizeImg($path . $item, $path, $item);

                }
                $images[] = $item;
            }
        }
        return json_encode($images);
    }

    public function actionPass()
    {
      $platform_id = Yii::$app->request->get('platform_id');
      $email_id = Yii::$app->request->get('email_id');
      $update = Yii::$app->request->get('update', 0);
      $ret = \common\extensions\Newsletters\Newsletters::passHTMLTemplateConfirm($platform_id, $email_id, $update);
      echo json_encode($ret );
    }
}
