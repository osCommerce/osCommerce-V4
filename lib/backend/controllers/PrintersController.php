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

use Yii;
use yii\helpers\FileHelper;
use common\components\google\GooglePrinters;

class PrintersController extends Sceleton {

    public $acl = ['TEXT_SETTINGS', 'BOX_HEADING_PRINTERS'];
    public $configDir;

    public function __construct($id, $module) {
        parent::__construct($id, $module);
        \common\helpers\Translation::init('admin/printers');
        $this->configDir = Yii::getAlias('@common'). DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'google' . DIRECTORY_SEPARATOR;
    }

    public function actionIndex() {

        $this->selectedMenu = array('settings', 'printers');
        $this->navigation[] = array('link' => \Yii::$app->urlManager->createUrl('printers/index'), 'title' => HEADING_TITLE);
        $this->topButtons[] = '<a href="' . Yii::$app->urlManager->createUrl(['printers/service']) . '" class="btn add-service">New Service</a>';
        $this->view->headingTitle = HEADING_TITLE;
        
        $platform_id = Yii::$app->request->get('platform_id', 0);

        $platforms = \common\classes\platform::getList(true, true);

        $this->view->tabList = [
            array(
                'title' => 'Service',
                'not_important' => 0,
            ),
            array(
                'title' => TEXT_ACCEPTED_PRINTERS,
                'not_important' => 0,
            ),
        ];
        $messages = Yii::$app->session->getAllFlashes();
        Yii::$app->session->removeAllFlashes();
        return $this->render('index', [
                    'platforms' => $platforms,
                    'first_platform_id' => ($platform_id?$platform_id:\common\classes\platform::firstId()),
                    'default_platform_id' => \common\classes\platform::defaultId(),
                    'isMultiPlatforms' => \common\classes\platform::isMulti(),
                    'messages' => $messages
        ]);
    }

    public function actionList() {
        $draw = Yii::$app->request->get('draw', 1);
        $start = Yii::$app->request->get('start', 0);
        $length = Yii::$app->request->get('length', 10);
        
        $platform_id = Yii::$app->request->get('platform_id');

        $responseList = [];
        if ($length == -1)
            $length = 10000;
        $recordsTotal = 0;

        $condition = ['and', ['platform_id' => $platform_id]];
        if (isset($_GET['search']['value']) && tep_not_null($_GET['search']['value'])) {
            $keywords = tep_db_input(tep_db_prepare_input($_GET['search']['value']));
            $condition[] = ['like', 'service', $keywords];
        }

        if (isset($_GET['order'][0]['column']) && $_GET['order'][0]['dir']) {
            switch ($_GET['order'][0]['column']) {
                case 0:
                    $orderBy = "module " . tep_db_prepare_input($_GET['order'][0]['dir']);
                    break;
                default:
                    $orderBy = "module";
                    break;
            }
        } else {
            $orderBy = "module";
        }

        foreach(\common\models\CloudServices::find()->where($condition)->all() as $service){
            $responseList[] = array(
                "<div>" . $service->service . '<input class="cell_identify" type="hidden" value="' . $service->id . '"></div>',
                $service->getPrinters()->count()
            );
        }        

        $response = [
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsTotal,
            'data' => $responseList,
        ];
        echo json_encode($response);
    }

    public function actionPreview() {
        $this->layout = false;
        $service_id = (int) Yii::$app->request->get('service_id');
        
        $service = \common\models\CloudServices::find()->where(['id' => $service_id])->one();
        
        return $this->render('preview', [
            'service' => $service
        ]);
    }

    public function actionService() {
        \common\helpers\Translation::init('admin/adminfiles');
        
        $this->selectedMenu = array('settings', 'printers');
        $this->navigation[] = array('link' => \Yii::$app->urlManager->createUrl('printers/index'), 'title' => HEADING_TITLE);
        $this->view->headingTitle = HEADING_TITLE;
        
        $messages = [];
        
        $service_id = (int)Yii::$app->request->post('id', 0);
        if (!$service_id){
            $service_id = (int)Yii::$app->request->get('id', 0);
        }
        
        
        $service = \common\models\CloudServices::findOne(['id' => $service_id]);
        if (!$service){
            $service = new \common\models\CloudServices;
            if (!\common\classes\platform::isMulti()){
                $service = \common\classes\platform::defaultId();
            }
        }
        
        $uploadDir = Yii::getAlias('@webroot') .DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
        
        if (Yii::$app->request->isPost){
            if ($service->load(Yii::$app->request->post()) && $service->validate()){
                if (empty($service->service)) $service->service = 'Cloud Service';
                if (file_exists($uploadDir . $service->key) && is_file($uploadDir . $service->key)){                    
                    if (!is_dir($this->configDir)) FileHelper::createDirectory ($this->configDir, 0775);
                    if(copy($uploadDir . $service->key, $this->configDir . $service->key)){
                        FileHelper::unlink($uploadDir . $service->key);
                    }
                }
                if ($service->save()){
                    return $this->redirect(['printers/service', 'id' => $service->id]);
                }
            } else {
                if ($service->hasErrors()){
                    foreach($service->getErrors() as $errors){
                        $messages['danger'] .= is_array($errors) ? implode("<br>", $errors): $errors;
                    }
                }
            }
        }
        
        $platforms = \common\classes\platform::getList(true, true);
        
        return $this->render('edit', [
            'messages' => $messages,
            'service' => $service,
            'platforms' => $platforms,
            'isMultiPlatforms' => \common\classes\platform::isMulti(),
        ]);
    }

    public function actionDelete() {
        $service_id = (int)Yii::$app->request->post('id');
        if ($service_id){
            $service = \common\models\CloudServices::findOne(['id' => $service_id]);
            if ($service) $service->delete();
        }
        return 'ok';
    }
    
    public function actionCheckPrinters(){
        $this->layout = false;
        $service_id = Yii::$app->request->post('id');
        $response = [];
        if ($service_id){
            $service = \common\models\CloudServices::findOne(['id' => $service_id]);
            if ($service){
                $file = $this->configDir . $service->key;
                $googlePrinters = new GooglePrinters($file);
                if ($googlePrinters){
                    $printers = $googlePrinters->searchPrinters();
                    if ($printers){
                        $accepted = $service->getPrinters()->indexBy('cloud_printer_id')->all();
                        $printers = array_diff_key($printers, $accepted);
                        $response['printers'] = $this->renderPartial('cloud-printers', ['printers' => $printers]);
                    } else {
                        $response['error'] = $googlePrinters->getError();
                    }
                }
            }
        }
        echo json_encode($response);
        exit();
    }
        
    public function actionDescribe(){
        $service_id = Yii::$app->request->get('sid');
        $printer_id = Yii::$app->request->get('pid');
        $data = $this->describePrinter($service_id, $printer_id);
        return $this->renderPartial('printer-describe', ['data' => $data]);
    }
    
    public function describePrinter($servceId, $printerId){
        $data = [];
        if ($servceId && $printerId){
            $service = \common\models\CloudServices::findOne(['id' => $servceId]);
            if ($service){
                $printer = $service->getPrinters()->where(['id' => $printerId])->one();
                $file = $this->configDir . $service->key;
                $googlePrinters = new GooglePrinters($file);
                if ($googlePrinters){
                    $resposne = $googlePrinters->getPrinterDescription($printer->cloud_printer_id);
                    if ($resposne){
                        $data = $resposne;
                    } else {
                        $data['error'] = $googlePrinters->getError();
                    }
                }
            }
        }
        return $data;
    }
    
    public function actionAcepted(){
        $draw = Yii::$app->request->get('draw', 1);
        $start = Yii::$app->request->get('start', 0);
        $length = Yii::$app->request->get('length', 10);
        
        $service_id = Yii::$app->request->get('id');
        $responseList = [];
        $recordsTotal = 0;
        if ($service_id){
            
            foreach(\common\models\CloudPrinters::findAll(['service_id' => $service_id]) as $printer){
                $responseList[] = [
                    $printer->cloud_printer_id,
                    $printer->cloud_printer_name,
                    $this->renderPartial('actions', ['printer' => $printer]),
                ];
                $recordsTotal++;
            }
        }
        $response = [
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsTotal,
            'data' => $responseList,
        ];
        echo json_encode($response);
    }


    public function actionAccept(){
        $service_id = Yii::$app->request->post('id');
        $toAccept = Yii::$app->request->post('printers', null);
        $response = [];
        if ($service_id){
            $service = \common\models\CloudServices::findOne(['id' => $service_id]);
            if ($service){
                $accepted = $service->getPrinters()->indexBy('cloud_printer_id')->all();
                if (!isset($accepted[$toAccept])){
                    $accept = new \common\models\CloudPrinters();
                    $file = $this->configDir . $service->key;
                    $googlePrinters = new GooglePrinters($file);
                    if ($googlePrinters){
                        $printer = $googlePrinters->getPrinter($toAccept);
                        if ($printer){
                            $accept->cloud_printer_id = $printer['id'];
                            $accept->cloud_printer_name = $printer['name'];
                            $accept->status = (int)$printer['status'];
                            $accept->link('service', $service);
                            $response = [
                                'type' => 'alert-success',
                                'message' => TEXT_ACCEPTED_SUCCESSFULY,
                            ];
                        } else {
                            $error = $googlePrinters->getError();
                            $response[] = [
                                'type' => 'alert-danger',
                                'message' => $error['errorMessage'],
                            ];
                        }
                    } else {
                        $response = [
                            'type' => 'alert-danger',
                            'message' => TEXT_ACCEPTED_ALREADY,
                        ];
                    }
                } else {
                    $response = [
                        'type' => 'alert-warning',
                        'message' => TEXT_ACCEPTED_ALREADY,
                    ];
                }
            }
        }
        echo json_encode($response);
        exit();
    }
    
    public function actionUnlink(){
        $printer_id = Yii::$app->request->post('id', 0);
        $response = [];
        if ($printer_id){
            $printer = \common\models\CloudPrinters::findOne(['id' => $printer_id]);
            if ($printer){
                $printer->delete();
            }
        }
        echo json_encode($response);
        exit();
    }
    
    public function actionTest(){
        $service_id = Yii::$app->request->post('sid');
        $printer_id = Yii::$app->request->post('pid');
        $message = Yii::$app->request->post('job');
        $response = [];
        if ($service_id && $printer_id){
            $service = \common\models\CloudServices::find()->alias('s')->where(['s.id' => $service_id])
                    ->joinWith(['printers p' => function($query) use ($printer_id){
                        $query->where(['p.id' => $printer_id]);
                    }])->one();
            if ($service){
                $file = $this->configDir . $service->key;
                $googlePrinters = new GooglePrinters($file);
                if ($googlePrinters){
                    $job = $googlePrinters->createJob($service->printers[0]->cloud_printer_id);
                    $job->setContentType('text/html');
                    $processed = $googlePrinters->processJob($message);
                    if ($processed['message']){
                        $response['message'] = $processed['message'];
                    }
                }
            }
        }
        echo json_encode($response);
        exit();
    }
    
    public function actionJobs(){
        $service_id = Yii::$app->request->get('sid');
        $printer_id = Yii::$app->request->get('pid');
        Yii::$app->response->format = \yii\web\Response::FORMAT_HTML;
        $echo = '';
        if ($service_id && $printer_id){
            $service = \common\models\CloudServices::find()->alias('s')->where(['s.id' => $service_id])
                    ->joinWith(['printers p' => function($query) use ($printer_id){
                        $query->where(['p.id' => $printer_id]);
                    }])->one();
            if ($service){
                $file = $this->configDir . $service->key;
                $googlePrinters = new GooglePrinters($file);
                if ($googlePrinters){
                    $jobs = $googlePrinters->getJobs($service->printers[0]->cloud_printer_id);
                    if (is_array($jobs)){
                        foreach ($jobs as $job){
                            $echo.= $this->renderPartial('job', ['job' => $job]);
                        }
                    } else {
                        $error = $googlePrinters->getError();
                        $echo = $error['message'];
                    }
                }
            }
        }
        return Yii::$app->response->data = $echo;
        exit();
    }
    
    public function actionDocuments(){
        
        $service_id = Yii::$app->request->get('sid');
        $printer_id = Yii::$app->request->get('pid');
        
        $list = ['invoice' => 'Invoice', 'packingslip' => 'Packingslip', 'creditnote' => 'Credit Note', 'purchase' => 'Purchase orders'];//predefined documents

        $service = \common\models\CloudServices::find()->alias('s')->where(['s.id' => $service_id])->one();
        $theme_id = \common\models\PlatformsToThemes::findOne($service->platform_id)->theme_id;
        $theme_name = \common\models\Themes::findOne(['id' => $theme_id])->theme_name;
        
        //manually added documents
        $docs = array_keys(\common\models\ThemesSettings::find()
            ->select(['setting_value'])
            ->where([
                'theme_name' => $theme_name,
                'setting_group' => 'added_page',
                'setting_name' => ['invoice', 'packingslip', 'purchase']
            ])
            ->indexBy('setting_value')
            ->asArray()
            ->all());
        
        foreach($docs as $name){
            $key = \common\classes\design::pageName($name);
            $list[$key] = $name;
        }
        
        $assigned = array_keys(\common\models\CloudPrintersDocuments::find()
                ->where(['printer_id' => $printer_id])
                ->indexBy('document_name')
                ->all());
        
        return $this->renderAjax('documents',[
            'list' => $list,
            'assigned' => $assigned,
            'printer_id' => $printer_id
        ]);
    }

    public function actionSaveDocuments(){
        $response = [];
        if (Yii::$app->request->isPost){
            $printer_id = (int)Yii::$app->request->post('printer_id');
            if ($printer_id){
                $docs = Yii::$app->request->post('documents');
                $remain = [];
                $response = [
                    'type' => 'alert-success',
                    'message' => TEXT_MESSEAGE_SUCCESS,
                ];
                if (is_array($docs)){
                    foreach($docs as $value){
                        $doc = \common\models\CloudPrintersDocuments::create($printer_id, $value);
                        if ($doc->validate()) {
                            $doc->save();
                            array_push($remain, $doc->id);
                        } else {
                            $response = [
                                'type' => 'alert-danger',
                                'message' => ERROR_INVALID_DOCUMENT_ASSIGNMENT,
                            ];
                        }
                    }
                }
                \common\models\CloudPrintersDocuments::deleteAll(['and', ['not in', 'id', $remain], ['printer_id' => $printer_id]]);
            }
        }
        echo json_encode($response);
        exit();
    }
}
