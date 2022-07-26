<?php

namespace suppliersarea\controllers;

use Yii;

class ProductsController extends Sceleton {

    public function behaviors() {

        return [
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@']
                    ]
                ],
                'denyCallback' => function ($rule, $action) {
                    return $this->redirect(['index/login']);
                },
            ]
        ];
    }

    public function actionIndex() {

        $supplier_id = $this->module->user->getId();

        $query = \common\models\Products::find()->joinWith([
                    'inventories' => function($query) use ($supplier_id) {
                        $query->with([
                            'suppliersProducts' => function($query)use ($supplier_id) {
                                $query->onCondition(['suppliers_id' => $supplier_id]);
                            },
                        ]);
                    },
                ])->with([
                    'suppliersProducts' => function($query) use ($supplier_id) {
                        $query->where(['suppliers_id' => $supplier_id]);
                    }])->joinWith('description');

        $productSearch = new \suppliersarea\models\SuppliersProductsSearch();
        if ($productSearch->load(Yii::$app->request->get()) && $productSearch->validate()) {
            $productSearch->search($query);
        }
        $productSearch->setSort($query);
        $query->andWhere(['is_bundle' => 0]);

        $provider = new \yii\data\ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => ['attributes' => ['products_model', 'suppliers_model']],
        ]);

        $_currencies = $this->service->get('currencies');
        $cMap = \yii\helpers\ArrayHelper::map($_currencies->currencies, 'id', 'code');
        $_baseUrl = (\suppliersarea\SupplierModule::getInstance())->baseUrl;

        $columns = [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'class' => 'yii\grid\DataColumn',
                'attribute' => 'products_name',
                'enableSorting' => true,
                'value' => function($model, $key, $index, $object) {
                    return $model->description->products_name;
                }
            ],
            [
                'class' => 'yii\grid\DataColumn',
                'attribute' => 'products_model',
                'enableSorting' => true,
            ],
            [
                'class' => 'yii\grid\DataColumn',
                'label' => "Supplier Model",
                'attribute' => 'suppliers_model',
                'value' => function($model, $key, $index, $object) {
                    if (!$model->inventories) {
                        if ($model->suppliersProducts[0]) {
                            return $model->suppliersProducts[0]->suppliers_model;
                        }
                    }
                    return '';
                },
            ],
            [
                'class' => 'yii\grid\Column',
                'header' => "Supplier Price",
                'content' => function($model, $key, $index) use ($cMap, $_currencies, $_baseUrl) {
                    if (!$model->inventories) {
                        if ($model->suppliersProducts[0]) {
                            return \suppliersarea\widgets\PriceEditor::widget([
                                    'product' => $model->suppliersProducts[0],
                                    'currencies' => $_currencies,
                            ]);
                        }
                    }
                    return '';
                }
            ],
            [
                'class' => 'yii\grid\Column',
                'header' => 'Supplier Discount, %',
                'content' => function($model, $key, $index) {
                    if (!$model->inventories) {
                        if ($model->suppliersProducts[0]) {
                            return \suppliersarea\widgets\DiscountEditor::widget([
                                    'product' => $model->suppliersProducts[0],
                            ]);
                        }
                    }
                    return '';
                }
            ],
            [
                'class' => 'yii\grid\Column',
                'header' => 'Suppliers Quantity',
                'content' => function ($model, $key, $index, $column) {
                    if (!$model->inventories) {
                        if ($model->suppliersProducts[0]) {
                            return \suppliersarea\widgets\QuantityEditor::widget([
                                    'product' => $model->suppliersProducts[0],
                            ]);
                        }
                    }
                    return '';
                },
            ],
            [
                'class' => 'yii\grid\Column',
                'header' => 'Status',
                'content' => function ($model, $key, $index, $column) {
                    if (!$model->inventories) {
                        if ($model->suppliersProducts[0]) {
                            return \yii\helpers\Html::checkbox('status[]', $model->suppliersProducts[0]->status, [
                                        'value' => 1,
                                        'class' => 'check_on_off',
                                        'data-sid' => $model->suppliersProducts[0]->suppliers_id,
                                        'data-uprid' => $model->suppliersProducts[0]->uprid,
                            ]);
                        }
                    }
                    return ' ';
                },
            ],
            [
                'class' => 'yii\grid\Column',
                'header' => 'Action',
                'content' => function($model, $key, $index, $column) use ($_baseUrl) {
                    if (!$model->inventories) {
                        if ($model->suppliersProducts[0]) {
                            return \suppliersarea\widgets\ActionButton::widget(['template' => '{update}', 'url' => Yii::$app->urlManager->createUrl([$_baseUrl . '/products/update', 'uprid' => $model->products_id])]);
                        } else {
                            return \suppliersarea\widgets\ActionButton::widget(['template' => '{propose}', 'url' => Yii::$app->urlManager->createUrl([$_baseUrl . '/products/propose', 'uprid' => $model->products_id])]);
                        }
                    }
                },
            ]
        ];

        return $this->render('index', [
                    'list' => \suppliersarea\widgets\ProductsList::widget([
                        'provider' => $provider,
                        'columns' => $columns,
                        'productSearch' => $productSearch,
                        'service' => $this->service,
                    ]),
        ]);
    }

    public function actionChangeStatus() {
        $sid = Yii::$app->request->post('sid');
        $uprid = Yii::$app->request->post('uprid');
        $status = Yii::$app->request->post('value', '');
        $status = strtolower($status) == 'true';
        $supplierProduct = $this->service->get('\common\models\SuppliersProducts', 'suppliersProducts')
                        ->getSupplierUpridProducts($uprid, $sid)->one();
        $_saved = false;
        if ($supplierProduct) {
            $supplierProduct->status = $status;
            if ($supplierProduct->save()) {
                $_saved = true;
            }
        }
        if ($_saved) {
            Yii::$app->session->addFlash('success', 'Successfuly upadted');
        } else {
            Yii::$app->session->addFlash('dange', 'Updating error..');
        }
        return \common\widgets\Alert::widget();
    }

    public function actionUpdate() {

        $messageStack = Yii::$container->get('message_stack');

        $uprid = Yii::$app->request->get('uprid');
        $sProduct = null;

        $objName = 'sProduct';
        $this->service->get('\common\models\SuppliersProducts', $objName);

        if ($uprid) {
            $sProduct = \common\models\SuppliersProducts::findOne(['suppliers_id' => $this->module->user->getId(), 'uprid' => $uprid]);

            if (!$sProduct) {
                return $this->redirect(['products/propose', 'uprid' => $uprid]);
            }
        }

        if (Yii::$app->request->isPost) {
            $this->handleSupplierData($sProduct, $uprid);
        }

        $mainProduct = $sProduct->getProduct()->with('description')->one();

        Yii::configure($this->service, [
            'allow_change_status' => true,
            'allow_change_surcharge' => false,
            'allow_change_margin' => false,
            'allow_change_price_formula' => false,
            'allow_change_auth' => true,
        ]);

        $_referer = Yii::$app->request->getReferrer() != Yii::$app->request->getAbsoluteUrl() ? Yii::$app->request->getReferrer() : null;
        if (is_null($_referer)) {
            $_referer = Yii::$app->session->getFlash('_ref');
        }

        if (strpos(Yii::$app->request->getReferrer(), 'propose') !== false) {
            $_referer = Yii::$app->urlManager->createAbsoluteUrl(\suppliersarea\SupplierModule::getInstance()->baseUrl . '/products/index');
        }

        Yii::$app->session->setFlash('_ref', $_referer);

        $this->service->set($objName, $sProduct);

        return $this->render('update', [
                    'content' => $this->service->render('\common\widgets\SupplierProductEdit', ['baseUrl' => \suppliersarea\SupplierModule::getInstance()->baseUrl, 'objName' => $objName]),
                    'messages' => $messageStack->output('product-edit'),
                    'uprid' => $uprid,
                    'sProduct' => $sProduct,
                    'mProductName' => $mainProduct->description->products_name,
                    'cancelLink' => $_referer,
        ]);
    }

    public function actionSavePrice() {

        $suppliers_data = Yii::$app->request->post('suppliers_data');
        if (is_array($suppliers_data)) {
            foreach ($suppliers_data as $uprid => $spData) {
                $sProduct = \common\models\SuppliersProducts::findOne(['suppliers_id' => $this->module->user->getId(), 'uprid' => $uprid]);
                if ($sProduct) {
                    $sProduct->setAttributes([
                        'suppliers_price' => floatval($spData['suppliers_price']),
                        'currencies_id' => intval($spData['currencies_id']),
                            ], false);
                    $sProduct->save();
                }
            }
        }

        return \suppliersarea\widgets\PriceEditor::widget([
                'product' => $sProduct,
                'currencies' => $this->service->get('currencies'),
        ]);
    }
    
    public function actionSaveQuantity() {

        $suppliers_data = Yii::$app->request->post('suppliers_data');
        if (is_array($suppliers_data)) {
            foreach ($suppliers_data as $uprid => $spData) {
                $sProduct = \common\models\SuppliersProducts::findOne(['suppliers_id' => $this->module->user->getId(), 'uprid' => $uprid]);
                if ($sProduct) {
                    $sProduct->setAttributes([
                        'suppliers_quantity' => floatval($spData['suppliers_quantity']),                        
                            ], false);
                    $sProduct->save();
                }
            }
        }

        return \suppliersarea\widgets\QuantityEditor::widget([
                'product' => $sProduct,                    
        ]);
    }
    
    public function actionSaveDiscount() {

        $suppliers_data = Yii::$app->request->post('suppliers_data');
        if (is_array($suppliers_data)) {
            foreach ($suppliers_data as $uprid => $spData) {
                $sProduct = \common\models\SuppliersProducts::findOne(['suppliers_id' => $this->module->user->getId(), 'uprid' => $uprid]);
                if ($sProduct) {
                    $sProduct->setAttributes([
                        'supplier_discount' => floatval($spData['supplier_discount']),                        
                            ], false);
                    $sProduct->save();
                }
            }
        }

        return \suppliersarea\widgets\DiscountEditor::widget([
                'product' => $sProduct,                    
        ]);
    }

    public function actionCalculateProductPrice() {
        $uprid = Yii::$app->request->post('uprid');

        $newPrice = null;
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $response = [];
        if ($uprid) {
            $product = \common\models\Products::findOne(['products_id' => (int) $uprid]);
            if ($product) {
                $sProduct = $product->getSuppliersProducts()->where(['uprid' => $uprid, 'suppliers_id' => $this->module->user->getId()])->one();
                if (!$sProduct) {
                    $sProduct = new \common\models\SuppliersProducts();
                    $sProduct->loadDefaultValues();
                }
                $post = Yii::$app->request->post('suppliers_data');
                $suppliers_data = $post[$uprid][$this->service->supplier->suppliers_id];
                $currencies = $this->service->get('currencies');
                $currencies_id = (int) $suppliers_data['currencies_id'];
                $discount = (float) $suppliers_data['supplier_discount'];
                $price = (float) $suppliers_data['suppliers_price'];
                //$price = floatval($price * $currencies->get_market_price_rate(\common\helpers\Currencies::getCurrencyCode($currencies_id), DEFAULT_CURRENCY));
                /*$newPrice = \common\helpers\PriceFormula::apply(
                                \common\helpers\PriceFormula::getSupplierFormula($this->module->user->getId()), [
                            'price' => floatval($price),
                            'margin' => floatval($sProduct->suppliers_margin_percentage),
                            'surcharge' => floatval($sProduct->suppliers_surcharge_amount),
                            'discount' => floatval($discount)
                ]);*/
                
                $params = [
                    'products_id' => (int)$uprid,
                    'categories_id' => [],
                    'manufacturers_id' => 0,
                    'currencies_id' => $currencies_id,
                    'PRICE' => floatval($price),
                    'MARGIN' => floatval($sProduct->suppliers_margin_percentage),
                    'SURCHARGE' => floatval($sProduct->suppliers_surcharge_amount),
                    'DISCOUNT' => floatval($discount),
                ];
                $data = [];
                if ( $params['PRICE']>0 ) {
                    $data['result'] = \common\helpers\PriceFormula::applyRules($params, $this->module->user->getId());
                    if ( $data['result']===false ) {
                        $data['error'] = 'No applicable rule found';
                    } else {
                        $taxRate = \common\helpers\Tax::get_tax_rate_value($product->products_tax_class_id);
                        $response = [
                            'formatedSuplPrice' => $currencies->display_price($price, $taxRate),
                            'formatedCostPrice' => $currencies->display_price($data['result']['applyParams']['PRICE'], $taxRate),
                        ];
                    }
                }
            }
        }

        return $response;
    }

    public function actionPropose() {

        $messageStack = Yii::$container->get('message_stack');

        $uprid = Yii::$app->request->get('uprid');

        $objName = 'sProduct';

        $sProduct = $this->service->get('\common\models\SuppliersProducts', $objName);
        $sProduct->loadSupplierValues($this->module->user->getId());
        $sProduct->status = 1;
        $sProduct->uprid = $uprid;
        $sProduct->products_id = (int) $uprid;

        $mainProduct = \common\models\Products::find()->where(['products_id' => (int) $uprid])->with('description')->with('inventories')->one();

        if (!$mainProduct) {
            throw new \Exception('Undefined product');
        }

        if (Yii::$app->request->isPost) {
            $this->handleSupplierData($sProduct, $uprid);
        }

        $_referer = Yii::$app->request->getReferrer() != Yii::$app->request->getAbsoluteUrl() ? Yii::$app->request->getReferrer() : null;
        if (is_null($_referer)) {
            $_referer = Yii::$app->session->getFlash('_ref');
        }

        return $this->render('update', [
                    'content' => $this->service->render('\common\widgets\SupplierProductEdit', ['baseUrl' => \suppliersarea\SupplierModule::getInstance()->baseUrl, 'objName' => $objName]),
                    'messages' => $messageStack->output('product-edit'),
                    'uprid' => $uprid,
                    'sProduct' => $sProduct,
                    'mProductName' => $mainProduct->description->products_name,
                    'cancelLink' => $_referer,
        ]);
    }

    private function handleSupplierData($sProduct, $uprid) {
        $messageStack = Yii::$container->get('message_stack');

        $suppliers_data = Yii::$app->request->post('suppliers_data');
        if (is_array($suppliers_data)) {
            foreach ($suppliers_data as $post_uprid => $post_data) {
                if ($post_uprid == $uprid) {
                    $updateSData = $post_data[$this->module->user->getId()];
                    if (is_array($updateSData)) {
                        if ($sProduct->load($updateSData, '') && ($sProduct->validate())) {
                            if ($sProduct->save(false)) {
                                $messageStack->add_session('updated', 'product-edit', 'success');
                                return $this->redirect(['update', 'uprid' => $uprid]);
                            }
                        } else {
                            foreach ($sProduct->getErrors() as $error) {
                                $messageStack->add($error, 'product-edit');
                            }
                        }
                    }
                }
            }
        }
    }

}
