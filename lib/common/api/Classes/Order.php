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

namespace common\api\Classes;

class Order
{
    public $orders_id;

    public $orders;
    public $orders_products;
    public $orders_history;
    public $orders_status_history;
    public $orders_total;
    public $orders_transactions;
    public $orders_payment;
    public $trackingNumberRecordArray;
    // settings
    public $deleteStatusHistorybeforeCreation = false;

    public function set($orderArray = array())
    {
        $this->orders_id = 0;
        $this->orders = [];
        $this->orders_products = [];
        $this->orders_history = [];
        $this->orders_status_history = [];
        $this->orders_total = [];
        $this->orders_transactions = [];
        $this->orders_payment = [];
        $this->trackingNumberRecordArray = [];
        foreach ($orderArray as $key => $value) {
            if (isset($this->{$key})) {
                $this->{$key} = $value;
            }
        }
        unset($orderArray);
        unset($value);
        unset($key);
        return true;
    }

    public function get($propertyName = null)
    {
        if (is_null($propertyName)) {
            $response = [];
            foreach ((new \ReflectionObject($this))->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
                $response[$property->name] = $this->{$property->name};
            }
        } elseif (property_exists($this, $propertyName)) {
            $response = $this->$propertyName;
        }
        return $response;
    }

    public function messageGet()
    {
        return ['Unknown error in Order API!' => 'error'];
    }

    public function load($orders_id) {
        $this->orders_id = $orders_id;

        $this->orders = \common\models\Orders::find()->where(['orders_id' => $orders_id])->asArray()->one();
        if (isset($this->orders['orders_id'])) {
            unset($this->orders['orders_id']);
        }

        $this->orders_history = \common\models\OrdersHistory::find()->where(['orders_id' => $orders_id])->orderBy('orders_history_id')->asArray()->all();
        if(is_array($this->orders_history)) {
            foreach ($this->orders_history as $index => $value) {
                /*if (isset($this->orders_history[$index]['orders_history_id'])) {
                    unset($this->orders_history[$index]['orders_history_id']);
                }*/
                if (isset($this->orders_history[$index]['orders_id'])) {
                    unset($this->orders_history[$index]['orders_id']);
                }
            }
        }

        $this->orders_status_history = \common\models\OrdersStatusHistory::find()->where(['orders_id' => $orders_id])->orderBy('orders_status_history_id')->asArray()->all();
        if(is_array($this->orders_status_history)) {
            foreach ($this->orders_status_history as $index => $value) {
                /*if (isset($this->orders_status_history[$index]['orders_status_history_id'])) {
                    unset($this->orders_status_history[$index]['orders_status_history_id']);
                }*/
                if (isset($this->orders_status_history[$index]['orders_id'])) {
                    unset($this->orders_status_history[$index]['orders_id']);
                }
            }
        }

        $this->orders_total = \common\models\OrdersTotal::find()->where(['orders_id' => $orders_id])->orderBy('sort_order')->asArray()->all();
        if(is_array($this->orders_total)) {
            foreach ($this->orders_total as $index => $value) {
                /*if (isset($this->orders_total[$index]['orders_total_id'])) {
                    unset($this->orders_total[$index]['orders_total_id']);
                }*/
                if (isset($this->orders_total[$index]['orders_id'])) {
                    unset($this->orders_total[$index]['orders_id']);
                }
            }
        }

        $this->orders_transactions = \common\models\OrdersTransactions::find()->where(['orders_id' => $orders_id])->orderBy('orders_transactions_id')->asArray()->all();
        if(is_array($this->orders_transactions)) {
            foreach ($this->orders_transactions as $index => $value) {
                if (isset($this->orders_transactions[$index]['orders_id'])) {
                    unset($this->orders_transactions[$index]['orders_id']);
                }
            }
        }

        $this->orders_payment = \common\models\OrdersPayment::find()->where(['orders_payment_order_id' => $orders_id])->orderBy('orders_payment_id')->asArray()->all();
        if(is_array($this->orders_payment)) {
            foreach ($this->orders_payment as $index => $value) {
                if (isset($this->orders_payment[$index]['orders_payment_order_id'])) {
                    unset($this->orders_payment[$index]['orders_payment_order_id']);
                }
            }
        }

        $orders_products = \common\models\OrdersProducts::find()->where(['orders_id' => $orders_id])->orderBy(['sort_order' => SORT_ASC, 'orders_products_id' => SORT_ASC])->asArray()->all();
        if(is_array($orders_products)) {
            foreach ($orders_products as $value) {
                $orders_products_id = $value['orders_products_id'];
                unset($value['orders_id']);
                //unset($value['orders_products_id']);
                $this->orders_products[$orders_products_id] = $value;
            }
        }

        $orders_products_allocate = \common\models\OrdersProductsAllocate::find()->where(['orders_id' => $orders_id])->orderBy('orders_products_id')->asArray()->all();
        if(is_array($orders_products_allocate)) {
            foreach ($orders_products_allocate as $value) {
                $orders_products_id = $value['orders_products_id'];
                unset($value['orders_id']);
                unset($value['orders_products_id']);
                if (isset($this->orders_products[$orders_products_id])) {
                    $this->orders_products[$orders_products_id]['orders_products_allocate'][] = $value;
                }
            }
        }

        $orders_products_attributes = \common\models\OrdersProductsAttributes::find()->where(['orders_id' => $orders_id])->orderBy('orders_products_id')->asArray()->all();
        if(is_array($orders_products_attributes)) {
            foreach ($orders_products_attributes as $value) {
                $orders_products_id = $value['orders_products_id'];
                unset($value['orders_id']);
                unset($value['orders_products_id']);
                if (isset($this->orders_products[$orders_products_id])) {
                    $this->orders_products[$orders_products_id]['orders_products_attributes'][] = $value;
                }
            }
        }

        $orders_products_download = \common\models\OrdersProductsDownload::find()->where(['orders_id' => $orders_id])->orderBy('orders_products_id')->asArray()->all();
        if(is_array($orders_products_download)) {
            foreach ($orders_products_download as $value) {
                $orders_products_id = $value['orders_products_id'];
                unset($value['orders_id']);
                unset($value['orders_products_id']);
                if (isset($this->orders_products[$orders_products_id])) {
                    $this->orders_products[$orders_products_id]['orders_products_download'][] = $value;
                }
            }
        }

        $orders_products_status_history = \common\models\OrdersProductsStatusHistory::find()->where(['orders_id' => $orders_id])->orderBy('orders_products_id')->asArray()->all();
        if(is_array($orders_products_status_history)) {
            foreach ($orders_products_status_history as $value) {
                $orders_products_id = $value['orders_products_id'];
                unset($value['orders_id']);
                unset($value['orders_products_id']);
                unset($value['orders_products_history_id']);
                if (isset($this->orders_products[$orders_products_id])) {
                    $this->orders_products[$orders_products_id]['orders_products_status_history'][] = $value;
                }
            }
        }

        $this->trackingNumberRecordArray = \common\models\TrackingNumbers::find()
            ->where(['orders_id' => $this->orders_id])->orderBy(['tracking_numbers_id' => SORT_ASC])->asArray()->all();
    }

    public function create() {
        $this->orders_id = 0;
        $this->save();
    }

    public function save($isCreate = false) {
        $isCreate = (((int)$isCreate > 0) ? true : false);
        $orders_id = (int)(((int)$this->orders_id > 0) ? $this->orders_id : 0);

        if ($orders_id > 0) {
            $orders = \common\models\Orders::find()->where(['orders_id' => $orders_id])->one();
            if (($isCreate == true) AND !($orders instanceof \common\models\Orders)) {
                $orders = new \common\models\Orders();
                $orders->loadDefaultValues();
                $orders->orders_id = $orders_id;
            }
        } else {
            $orders = new \common\models\Orders();
            $orders->loadDefaultValues();
        }

        if (!is_object($orders)) {
            return false;
        }

        if (is_array($this->orders)) {
            $orders->setAttributes($this->orders, false);
        }
        $orders->save();//$orders->orders_id

        if (is_array($this->orders_history)) {
            foreach ($this->orders_history as $item) {
                if ($orders_id > 0) {
                    $orders_history = \common\models\OrdersHistory::find()
                        ->where(['orders_history_id' => $item['orders_history_id']])->one();
                    if ($isCreate == true) {
                        if (!($orders_history instanceof \common\models\OrdersHistory)) {
                            $item['orders_id'] = $orders->orders_id;
                            $orders_history = new \common\models\OrdersHistory();
                            $orders_history->setAttributes($item, false);
                            $orders_history = \common\models\OrdersHistory::findOne($orders_history->toArray());
                            if (!($orders_history instanceof \common\models\OrdersHistory)) {
                                $orders_history = new \common\models\OrdersHistory();
                                $orders_history->loadDefaultValues();
                            }
                        }
                        $orders_history->orders_id = $orders->orders_id;
                    }
                } else {
                    if (isset($item['orders_history_id'])) {
                        unset($item['orders_history_id']);
                    }
                    $orders_history = new \common\models\OrdersHistory();
                    $orders_history->loadDefaultValues();
                    $orders_history->orders_id = $orders->orders_id;
                }
                $orders_history->setAttributes($item, false);
                $orders_history->save();
            }
        }

        if (is_array($this->orders_status_history)) {
            $renewFull = $this->deleteStatusHistorybeforeCreation && $isCreate == true && $orders_id > 0;
            if ($renewFull) {
                \common\models\OrdersStatusHistory::deleteAll(['orders_id' => $orders_id]);
            }
            foreach ($this->orders_status_history as $item) {
                if ($orders_id > 0) {
                    $orders_status_history = \common\models\OrdersStatusHistory::find()
                        ->where(['orders_status_history_id' => $item['orders_status_history_id']])->one();
                    if ($isCreate == true) {
                        if (!($orders_status_history instanceof \common\models\OrdersStatusHistory)) {
                            $item['orders_id'] = $orders->orders_id;
                            if ($renewFull) {
                                $orders_status_history = new \common\models\OrdersStatusHistory();
                                $orders_status_history->loadDefaultValues();
                            } else {
                                $orders_status_history = new \common\models\OrdersStatusHistory();
                                $orders_status_history->setAttributes($item, false);
                                $orders_status_history = \common\models\OrdersStatusHistory::findOne($orders_status_history->toArray());
                                if (!($orders_status_history instanceof \common\models\OrdersStatusHistory)) {
                                    $orders_status_history = new \common\models\OrdersStatusHistory();
                                    $orders_status_history->loadDefaultValues();
                                }
                            }
                        }
                        $orders_status_history->detachBehavior('date_added');
                        if ((!isset($item['date_added']) AND $orders_status_history->isNewRecord)
                            OR (isset($item['date_added']) AND ($item['date_added'] == ''))
                        ) {
                            $item['date_added'] = date('Y-m-d H:i:s');
                        }
                        $orders_status_history->orders_id = $orders->orders_id;
                    }
                } else {
                    if (isset($item['orders_status_history_id'])) {
                        unset($item['orders_status_history_id']);
                    }
                    $orders_status_history = new \common\models\OrdersStatusHistory();
                    $orders_status_history->loadDefaultValues();
                    $orders_status_history->orders_id = $orders->orders_id;
                }
                $orders_status_history->setAttributes($item, false);
                $orders_status_history->save();
            }
        }

        if (is_array($this->orders_total)) {
            foreach ($this->orders_total as $item) {
                if ($orders_id > 0) {
                    $orders_total = \common\models\OrdersTotal::find()->where(['orders_total_id' => $item['orders_total_id']])->one();
                    if ($isCreate == true) {
                        if (!($orders_total instanceof \common\models\OrdersTotal)) {
                            $orders_total = \common\models\OrdersTotal::find()->where(['orders_id' => $orders->orders_id, 'class' => $item['class']])->one();
                            if (!($orders_total instanceof \common\models\OrdersTotal)) {
                                $orders_total = new \common\models\OrdersTotal();
                                $orders_total->loadDefaultValues();
                            }
                        }
                        $orders_total->orders_id = $orders->orders_id;
                    }
                } else {
                    if (isset($item['orders_total_id'])) {
                        unset($item['orders_total_id']);
                    }
                    $orders_total = new \common\models\OrdersTotal();
                    $orders_total->loadDefaultValues();
                    $orders_total->orders_id = $orders->orders_id;
                }
                $orders_total->setAttributes($item, false);
                $orders_total->save();
            }
        }

        if (is_array($this->orders_transactions)) {
            foreach ($this->orders_transactions as $item) {
                if ($orders_id > 0) {
                    $orders_transactions = \common\models\OrdersTransactions::find()->where(['orders_transactions_id' => $item['orders_transactions_id']])->one();
                    if ($isCreate == true) {
                        if (!($orders_transactions instanceof \common\models\OrdersTransactions)) {
                            $orders_transactions = \common\models\OrdersTransactions::find()->where([
                                'orders_id' => $orders->orders_id,
                                'transaction_id' => $item['transaction_id']
                            ])->one();
                            if (!($orders_transactions instanceof \common\models\OrdersTransactions)) {
                                $orders_transactions = new \common\models\OrdersTransactions();
                                $orders_transactions->loadDefaultValues();
                            }
                        }
                        $orders_transactions->orders_id = $orders->orders_id;
                    }
                } else {
                    if (isset($item['orders_transactions_id'])) {
                        unset($item['orders_transactions_id']);
                    }
                    $orders_transactions = new \common\models\OrdersTransactions();
                    $orders_transactions->loadDefaultValues();
                    $orders_transactions->orders_id = $orders->orders_id;
                }
                $orders_transactions->setAttributes($item, false);
                $orders_transactions->save();
            }
        }

        if (is_array($this->orders_payment)) {
            foreach ($this->orders_payment as $item) {
                if ($orders_id > 0) {
                    $orders_payment = \common\models\OrdersPayment::find()->where(['orders_payment_id' => $item['orders_payment_id']])->one();
                    if ($isCreate == true) {
                        if (!($orders_payment instanceof \common\models\OrdersPayment)) {
                            $orders_payment = \common\models\OrdersPayment::find()->where([
                                'orders_payment_order_id' => $orders->orders_id,
                                'orders_payment_module' => $item['orders_payment_module'],
                                'orders_payment_is_credit' => $item['orders_payment_is_credit'],
                                'orders_payment_transaction_id' => $item['orders_payment_transaction_id']
                            ])->one();
                            if (!($orders_payment instanceof \common\models\OrdersPayment)) {
                                $orders_payment = new \common\models\OrdersPayment();
                                $orders_payment->loadDefaultValues();
                            }
                        }
                        $orders_payment->orders_payment_order_id = $orders->orders_id;
                    }
                } else {
                    if (isset($item['orders_payment_id'])) {
                        unset($item['orders_payment_id']);
                    }
                    $orders_payment = new \common\models\OrdersPayment();
                    $orders_payment->loadDefaultValues();
                    $orders_payment->orders_payment_order_id = $orders->orders_id;
                }
                $orders_payment->setAttributes($item, false);
                $orders_payment->save();
            }
        }

        if (is_array($this->orders_products)) {
            foreach ($this->orders_products as $keyOp => $item) {
                if (isset($item['orders_products_allocate'])) {
                    $orders_products_allocate_data = $item['orders_products_allocate'];
                    unset($item['orders_products_allocate']);
                } else {
                    $orders_products_allocate_data = false;
                }
                if (isset($item['orders_products_attributes'])) {
                    $orders_products_attributes_data = $item['orders_products_attributes'];
                    unset($item['orders_products_attributes']);
                } else {
                    $orders_products_attributes_data = false;
                }
                if (isset($item['orders_products_download'])) {
                    $orders_products_download_data = $item['orders_products_download'];
                    unset($item['orders_products_download']);
                } else {
                    $orders_products_download_data = false;
                }
                if (isset($item['orders_products_status_history'])) {
                    $orders_products_status_history_data = $item['orders_products_status_history'];
                    unset($item['orders_products_status_history']);
                } else {
                    $orders_products_status_history_data = false;
                }
                if ($orders_id > 0) {
                    $orders_products = \common\models\OrdersProducts::find()->where(['orders_products_id' => $item['orders_products_id']])->one();
                    if ($isCreate == true) {
                        if (!($orders_products instanceof \common\models\OrdersProducts)) {
                            $orders_products = new \common\models\OrdersProducts();
                            $orders_products->loadDefaultValues();
                        }
                        $orders_products->orders_id = $orders->orders_id;
                    }
                } else {
                    if (isset($item['orders_products_id'])) {
                        unset($item['orders_products_id']);
                    }
                    $orders_products = new \common\models\OrdersProducts();
                    $orders_products->loadDefaultValues();
                    $orders_products->orders_id = $orders->orders_id;
                }
                $orders_products->setAttributes($item, false);
                if ($orders_products->save()) {//$orders_products->orders_products_id
                    $this->orders_products[$keyOp]['orders_products_id'] = $orders_products->orders_products_id;
                }
                if (is_array($orders_products_attributes_data)) {
                    foreach ($orders_products_attributes_data as $data) {
                        if ($orders_id > 0) {
                            $orders_products_attributes = \common\models\OrdersProductsAttributes::find()
                                ->where(['orders_products_attributes_id' => $data['orders_products_attributes_id']])->one();
                            if ($isCreate == true) {
                                if (!($orders_products_attributes instanceof \common\models\OrdersProductsAttributes)) {
                                    $orders_products_attributes = \common\models\OrdersProductsAttributes::find()->where([
                                        'orders_id' => $orders->orders_id,
                                        'orders_products_id' => $orders_products->orders_products_id,
                                        'products_options_id' => $data['products_options_id'],
                                        'products_options_values_id' => $data['products_options_values_id']
                                    ])->one();
                                    if (!($orders_products_attributes instanceof \common\models\OrdersProductsAttributes)) {
                                        $orders_products_attributes = new \common\models\OrdersProductsAttributes();
                                        $orders_products_attributes->loadDefaultValues();
                                    }
                                }
                                $orders_products_attributes->orders_id = $orders->orders_id;
                                $orders_products_attributes->orders_products_id = $orders_products->orders_products_id;
                            }
                        } else {
                            if (isset($data['orders_products_attributes_id'])) {
                                unset($data['orders_products_attributes_id']);
                            }
                            $orders_products_attributes = new \common\models\OrdersProductsAttributes();
                            $orders_products_attributes->loadDefaultValues();
                            $orders_products_attributes->orders_id = $orders->orders_id;
                            $orders_products_attributes->orders_products_id = $orders_products->orders_products_id;
                        }
                        $orders_products_attributes->setAttributes($data, false);
                        $orders_products_attributes->save();
                    }
                }
            }
        }

        $this->trackingNumberRecordArray = (is_array($this->trackingNumberRecordArray) ? $this->trackingNumberRecordArray : array());
        foreach ($this->trackingNumberRecordArray as $keyTn => &$trackingNumberRecord) {
            $trackingNumberId = (int)(isset($trackingNumberRecord['tracking_numbers_id']) ? $trackingNumberRecord['tracking_numbers_id'] : 0);
            unset($trackingNumberRecord['orders_id']);
            unset($trackingNumberRecord['tracking_numbers_id']);
            $trackingNumberRecord['tracking_number'] = trim(isset($trackingNumberRecord['tracking_number']) ? $trackingNumberRecord['tracking_number'] : '');
            $trackingNumberRecord['tracking_carriers_id'] = (int)(isset($trackingNumberRecord['tracking_carriers_id']) ? $trackingNumberRecord['tracking_carriers_id'] : 0);
            if (($trackingNumberRecord['tracking_number'] == '') OR ($trackingNumberRecord['tracking_carriers_id'] <= 0)) {
                continue;
            }
            $tnRecord = \common\models\TrackingNumbers::find()->where([
                'orders_id' => $orders->orders_id,
                'tracking_carriers_id' => $trackingNumberRecord['tracking_carriers_id'],
                'tracking_number' => $trackingNumberRecord['tracking_number']
            ])->one();
            if ($tnRecord instanceof \common\models\TrackingNumbers) {
                $trackingNumberRecord = $tnRecord->getAttributes();
                continue;
            }
            if ($trackingNumberId > 0) {
                $tnRecord = \common\models\TrackingNumbers::findOne(['tracking_numbers_id' => $trackingNumberId]);
            }
            if (!($tnRecord instanceof \common\models\TrackingNumbers)) {
                $tnRecord = new \common\models\TrackingNumbers();
                $tnRecord->loadDefaultValues();
                if ($trackingNumberId > 0) {
                    $tnRecord->tracking_numbers_id = $trackingNumberId;
                }
            }
            $tnRecord->setAttributes($trackingNumberRecord, false);
            $tnRecord->orders_id = $orders->orders_id;
            if ($tnRecord->save(false)) {
                $trackingNumberRecord = $tnRecord->getAttributes();
            } else {
                unset($this->trackingNumberRecordArray[$keyTn]);
            }
        }
        unset($trackingNumberRecord);
        unset($trackingNumberId);
        unset($tnRecord);
        unset($keyTn);

        $this->orders_id = $orders->orders_id;
        return $this->orders_id;
    }
}