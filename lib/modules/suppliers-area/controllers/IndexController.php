<?php

namespace suppliersarea\controllers;

use Yii;

class IndexController extends Sceleton
{
    public function behaviors() {
        
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'rules' =>[
                    [
                        'allow' => true,
                        'actions' => ['login', 'logoff'],
                        'roles' => []
                    ],
                    [
                        'allow' => true,
                        'actions' => ['index', 'settings', 'generate-key'],
                        'roles' => ['@']
                    ],
                ],
                'denyCallback' => function ($rule, $action){                    
                    return $this->redirect(['index/login']);
                    },
                ]
        ];
    }
        
    public function actionIndex(){
                        
        return $this->render('index',[
            
        ]);
    }
        
    public function actionLogin(){
        
        $loginModel = new \suppliersarea\forms\LoginForm();
        
        $messageStack = Yii::$container->get('message_stack');
        
        if (Yii::$app->request->isPost){
            if ($loginModel->load(Yii::$app->request->post()) && $loginModel->validate()){                
                if ($loginModel->loginSupplier()){                    
                    return $this->redirect('index');
                }
            }
            if ($loginModel->hasErrors()){
                foreach($loginModel->getErrors() as $error){
                    $messageStack->add((is_array($error)?implode("", $error):$error), 'login', 'error');
                }                
            }
        }
        
        return $this->render('login',[
            'loginModel' => $loginModel,
            'messages' => $messageStack->output('login'),
        ]);
        
    }
    
    public function actionLogoff(){
        if (!$this->module->user->isGuest){
            $this->module->user->logout(false);
        } else {
            return $this->redirect('index');
        }
        
        return $this->render('logoff');
    }
    
    public function actionSettings(){
        
        $messageStack = Yii::$container->get('message_stack');
                        
        \common\helpers\Translation::init('admin/platforms');
        \common\helpers\Translation::init('admin/categories');

        Yii::configure($this->service, [
            'allow_change_status' => true,            
            'allow_change_surcharge' => false,
            'allow_change_margin' => false,
            'allow_change_price_formula' => false,
            'allow_change_auth' => true,
            'currencies_editor_simple' => true,
        ]);

        $supplier = $this->service->get('supplier');
        $this->service->currenciesMap = \yii\helpers\ArrayHelper::index($supplier->supplierCurrencies, 'currencies_id');
        
        if (Yii::$app->request->isPost){
            $suppliers_data = Yii::$app->request->post('suppliers_data');
            $supplier = $this->service->get('supplier');
            if ($supplier->load($suppliers_data, '') && $supplier->validate()){
                if ($supplier->saveSupplier($suppliers_data)){
                    if (is_array($suppliers_data['currencies'])){
                        $supplier->saveCurrencies($suppliers_data['currencies']);
                    }
                    $messageStack->add_session('Settings have been changed', 'settings', 'success');                        
                    return $this->redirect('settings');
                }
            } else {
                if ($supplier->hasErrors()){
                    foreach($supplier->getErrors() as $error){
                        $messageStack->add((is_array($error)?implode("", $error):$error), 'settings', 'error');
                    }
                }
            }
        }

        return $this->render('settings',[
            'content' => $this->service->render('\common\widgets\SupplierEdit', ['baseUrl' => \suppliersarea\SupplierModule::getInstance()->baseUrl]),
            'messages' => $messageStack->output('settings'),
        ]);
        
    }
    
    public function actionGenerateKey(){
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return ['password' => \common\helpers\Password::create_random_value(6)];
    }
}