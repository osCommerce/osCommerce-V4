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

namespace common\extensions\ErrorLogViewer\backend\controllers;

use common\extensions\ErrorLogViewer\ErrorLogViewer;
use common\extensions\ErrorLogViewer\LogReader;
use Yii;

class ErrorLogViewerController extends \common\classes\modules\SceletonExtensionsBackend
{

    public function __construct($id, $module = null, $config = [])
    {
        parent::__construct($id, $module, $config);
        if((new ErrorLogViewer())->DeleteOldZip() !== true)
        {
            \Yii::$app->session->setFlash('ELV', sprintf(EXT_ELV_ERR_DELETE_OLD_ZIP, (new ErrorLogViewer())->DeleteOldZip()));
        }
    }

    public function actionIndex()
    {
        $languages_id = Yii::$app->settings->get('languages_id');
        $this->topButtons[] = '<button onclick="deleteAllLog()" class="btn btn-primary"><i class="icon-trash"></i>' . EXT_ELV_TEXT_CLEAR_ALL . '</button>';
        $this->topButtons[] = '<a href="'.Yii::$app->urlManager->createUrl('error-log-viewer/download').'" class="btn btn-primary"><i class="icon-download"></i>' . EXT_ELV_TEXT_DOWNLOAD_ALL_LOGS . '</a>';
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('error-log-viewer/index'), 'title' => EXT_ELV_HEADING_TITLE);

        $this->view->headingTitle = EXT_ELV_HEADING_TITLE;

        $this->view->logTable = array(
            array(
                'title' => '<input type="checkbox" class="checkbox">',
                'not_important' => 2
            ),
            array(
                'title' => EXT_ELV_TABLE_FILENAME,
                'not_important' => 0
            ),
            array(
                'title' => EXT_ELV_TABLE_FILESIZE,
                'not_important' => 0
            ),
            array(
                'title' => EXT_ELV_TABLE_LAST_MODIFIED,
                'not_important' => 0
            ),
            array(
                'title' => EXT_ELV_TABLE_FILESIZE,
                'not_important' => 0
            ),
        );

        $this->view->filters = new \stdClass();

        $by = [
            [
                'name' => EXT_ELV_TEXT_BACKEND,
                'value' => 'backend',
                'selected' => '',
            ],
            [
                'name' => EXT_ELV_TEXT_FRONTEND,
                'value' => 'frontend',
                'selected' => '',
            ],
            [
                'name' => EXT_ELV_TEXT_CONSOLE,
                'value' => 'console',
                'selected' => '',
            ],
        ];

        foreach ($by as $key => $value)
        {
            if (isset($_GET['by']) && $value['value'] == $_GET['by'])
            {
                $by[$key]['selected'] = 'selected';
            }
        }



        $this->view->filters->by = $by;

        return $this->render('index', []);
    }

    public function actionList()
    {
        $type = strtolower(Yii::$app->request->get('by', 'backend'));

        if(ErrorLogViewer::getFiles($type))
        {
            foreach (ErrorLogViewer::getFiles($type) as $file)
            {
                $list[] = array(
                    '<input type="checkbox" class="checkbox">' . '<input class="cell_identify" type="hidden" value="'.$type.'/'.$file->name.'">',
                    $file->name,
                    $file->sizeText,
                    $file->date,
                    $file->size,
                );
            }
        }else{
            $list = array();
        }

        $response = array(
            'data' => $list
        );

        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return $response;
    }

    public function actionDeleteAll()
    {
        if(Yii::$app->request->isAjax)
        {
            ErrorLogViewer::deleteAll();
        }else{
            throw new \Exception("Direct request denied");
        }
    }

    public function actionLogsDelete()
    {
        $data = Yii::$app->request->post('logs');
        if(is_array($data))
        {
            foreach ($data as $file)
            {
                $log = ErrorLogViewer::getFile($file);
                if(!unlink($log->fullPath))
                {
                    throw new \Exception("Can't delete file: ".$log->fullPath);
                }
            }
        }elseif(is_string($data))
        {
            $log = ErrorLogViewer::getFile($data);
            if(!unlink($log->fullPath))
            {
                throw new \Exception("Can't delete file: ".$log->fullPath);
            }
        }
    }

    public function actionActions()
    {
        $log = Yii::$app->request->post('log');
        if(!is_null($log))
        {
            $file = ErrorLogViewer::getFile($log);
            return $this->renderPartial('actions', ['file' => $file]);
        }
    }

    public function actionAdvancedActions()
    {
        $id = \Yii::$app->request->post('id', false);
        $file = \Yii::$app->request->post('file', false);
        $file = str_replace('|', '.', $file);
        $mask = str_replace('.', '|', $file);
        $reader = new LogReader($file);
        $tmp = explode('/', $file);
        try {
            $headers = $reader->getHeaders();
            if(!array_key_exists($id, $headers)) return false;

            $result = new \stdClass();
            $result->date = $headers[$id][1];
            $result->ip = $headers[$id][2];
            $result->level = $headers[$id][5];
            $result->category = $headers[$id][6];
            $result->text = $headers[$id][7];
            $result->description = $reader->getDetails($id);
            $result->file = $file;
            $result->source = $tmp[0];
            $result->mask = $mask;

            return $this->renderPartial('advanced-actions', ['log' => $result]);
        }catch (\Exception $e) // if file content not support
        {
            return null;
        }


    }

    public function actionView()
    {
        $this->topButtons[] = '<button class="btn btn-delete btn-no-margin" onclick="deleteLog()">'.IMAGE_DELETE.'</button>';
        $this->topButtons[] = '<button onclick="viewAsText()" class="btn btn-primary"><i class="icon-file-text-alt"></i>' . EXT_ELV_TEXT_VIEW_AS_TEXT . '</button>';
        $this->view->headingTitle = EXT_ELV_HEADING_TITLE;

        $file = \Yii::$app->request->get('log', false);
        $mask = str_replace('.', '|', $file);
        $realFiename = str_replace('|', '.', $file);
        if(!$file) throw new \Exception("Invalid request");

        $this->view->logTable = array(
            array(
                'title' => 'ID',
                'not_important' => 0,
            ),
            array(
                'title' => EXT_ELV_TEXT_LOG_POSITION_DATE,
                'not_important' => 0
            ),
            array(
                'title' => EXT_ELV_TEXT_IP,
                'not_important' => 0
            ),
            array(
                'title' => EXT_ELV_TEXT_ERROR_LEVEL,
                'not_important' => 0
            ),
            array(
                'title' => EXT_ELV_TEXT_CATEGORY,
                'not_important' => 0
            ),
            array(
                'title' => EXT_ELV_TEXT_ERROR_DESCRIPTION,
                'not_important' => 0
            ),
        );

        return $this->render('view', ['file' => $file, 'back' => explode('/', $file)[0], 'mask' => $mask, 'filename' => $realFiename]);
    }

    public function actionAdvancedList()
    {
        $file = \Yii::$app->request->get('file', false);
        $file = str_replace('|', '.', $file);
        if(!$file) throw new \Exception("Invalid request");

        $logger = new LogReader($file);
        try {
            foreach ($logger->getHeaders() as $key => $header) {
                $list[] = array(
                    $key,
                    $header[1] . '<input class="cell_identify" type="hidden" value="' . $key . '">',
                    $header[2],
                    $header[5],
                    $header[6],
                    $header[7],
                );
            }

            $response = array('data' => array_reverse($list));
        }catch (\Exception $e)
        {
            \Yii::$app->session->setFlash('ELV', sprintf('File %s not supported', $file));
            $response = ['error' => true];
        }


        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return $response;
    }

    public function actionDownload()
    {
        $zip = (new ErrorLogViewer())->Zipping();
        if($zip['status'] == "ok")
        {
            if (file_exists($zip['description'])) {
                \Yii::$app->response->sendFile($zip['description'])->on(\yii\web\Response::EVENT_AFTER_SEND, function ($event) {
                    unlink($event->data);
                }, $zip['description']);
            }else{
                \Yii::$app->session->setFlash('ELV', EXT_ELV_ERR_NO_FILE_TO_DOWNLOAD);
                return $this->redirect(\Yii::$app->urlManager->createUrl(['error-log-viewer']));
            }
        }else{
            \Yii::$app->session->setFlash('ELV', sprintf(EXT_ELV_ERR_CREATE_ZIP, $zip['description']));
            return $this->redirect(\Yii::$app->urlManager->createUrl(['error-log-viewer']));
        }
    }

    public function actionViewAsText()
    {
        $log = Yii::$app->request->get('file', 'false');
        $file = ErrorLogViewer::getFile($log);

        if($file->error)
        {
            $content = "<pre>".$file->errorMessage."</pre>";
            Yii::$app->response->content = $content;
        }else{
            header("Content-type: text/plain");
            header("Pragma: no-cache");
            header("Expires: 0");
            header('Content-Length: ' . filesize($file->fullPath));

            if (ob_get_level()) {
                ob_end_clean();
            }
            readfile("$file->fullPath");
            exit;
        }
    }

}
