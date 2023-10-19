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

class Customer extends AbstractClass
{
    public $customerId = 0;
    public $customerRecord = array();
    public $addressRecordArray = array();
    public $emailRecordArray = array();
    public $phoneRecordArray = array();
    public $extraGroupRecordArray = array();

    public function getId()
    {
        return $this->customerId;
    }

    public function setId($customerId)
    {
        $customerId = (int)$customerId;
        if ($customerId >= 0) {
            $this->customerId = $customerId;
            return true;
        }
        return false;
    }

    public function load($customerId)
    {
        $this->clear();
        $customerId = (int)$customerId;
        $customerRecord = \common\models\Customers::find()->alias('c')->select('*')
            ->leftJoin(\common\models\CustomersInfo::tableName() . ' ci', 'ci.customers_info_id = c.customers_id')
            ->where(['c.customers_id' => $customerId])->asArray(true)->one();
        if (is_array($customerRecord) AND (count($customerRecord) > 0)) {
            $this->customerId = $customerId;
            $this->customerRecord = $customerRecord;
            unset($customerRecord);
            // ADDRESS
            $this->addressRecordArray = \common\models\AddressBook::find()->where(['customers_id' => $customerId])->asArray(true)->all();
            // EOF ADDRESS
            // EMAIL
            $this->emailRecordArray = \common\models\CustomersEmails::find()->where(['customers_id' => $customerId])->asArray(true)->all();
            // EOF EMAIL
            // PHONE
            $this->phoneRecordArray = \common\models\CustomersPhones::find()->where(['customers_id' => $customerId])->asArray(true)->all();
            // EOF PHONE*/
            // EXTRA GROUP
            if (\common\helpers\Acl::checkExtensionAllowed('ExtraGroups', 'allowed')) {
                $model = \common\helpers\Extensions::getModel('ExtraGroups', 'CustomerExtraGroups');
                $this->extraGroupRecordArray = (!empty($model)) ? $model::find()->where(['customer_id' => $customerId])->asArray(true)->all() : [];
            }
            // EOF EXTRA GROUP
            return true;
        }
        return false;
    }

    public function unrelate()
    {
        if (is_array($this->customerRecord)) {
            unset($this->customerRecord['customers_default_address_id']);
        }
        if (is_array($this->addressRecordArray)) {
            foreach ($this->addressRecordArray as &$addressRecord) {
                unset($addressRecord['address_book_id']);
            }
            unset($addressRecord);
        }
        return parent::unrelate();
    }

    public function validate()
    {
        $this->customerId = (int)(((int)$this->customerId > 0) ? $this->customerId : 0);
        if (!is_array($this->customerRecord) OR (count($this->customerRecord) < 5)) {
            return false;
        }
        if (!parent::validate()) {
            return false;
        }
        unset($this->customerRecord['customers_id']);
        unset($this->customerRecord['customers_info_id']);
        $this->addressRecordArray = (is_array($this->addressRecordArray) ? $this->addressRecordArray : array());
        $this->emailRecordArray = (is_array($this->emailRecordArray) ? $this->emailRecordArray : array());
        $this->phoneRecordArray = (is_array($this->phoneRecordArray) ? $this->phoneRecordArray : array());
        $this->extraGroupRecordArray = (is_array($this->extraGroupRecordArray) ? $this->extraGroupRecordArray : array());
        return true;
    }

    public function create()
    {
        $this->customerId = 0;
        return $this->save();
    }

    public function save($isReplace = false)
    {
        $return = false;
        if (!$this->validate()) {
            return $return;
        }
        $customerClass = \common\models\Customers::find()->where(['customers_id' => $this->customerId])->one();
        if (!($customerClass instanceof \common\models\Customers)) {
            $customerClass = new \common\models\Customers();
            $customerClass->loadDefaultValues();
            if ($this->customerId > 0) {
                $customerClass->customers_id = $this->customerId;
            } else {
                $this->unrelate();
            }
        }
        $customerClass->setAttributes($this->customerRecord, false);
        if ($customerClass->save(false)) {
            $customerInfoRecord = $this->customerRecord;
            $this->customerRecord = $customerClass->toArray();
            $this->customerId = (int)$customerClass->customers_id;
            // INFORMATION
            try {
                $customerInfoClass = \common\models\CustomersInfo::find()->where(['customers_info_id' => $this->customerId])->one();
                if (!($customerInfoClass instanceof \common\models\CustomersInfo)) {
                    $customerInfoClass = new \common\models\CustomersInfo();
                    $customerInfoClass->loadDefaultValues();
                    $customerInfoClass->customers_info_id = $this->customerId;
                }
                $customerInfoClass->setAttributes($customerInfoRecord, false);
                $customerInfoClass->detachBehavior('timestampBehavior');
                if ($customerInfoClass->save(false)) {
                    $this->customerRecord = ($this->customerRecord + $customerInfoClass->toArray());
                } else {
                    $this->messageAdd($customerInfoClass->getErrorSummary(true));
                }
            } catch (\Exception $exc) {
                $this->messageAdd($exc->getMessage());
            }
            unset($customerInfoRecord);
            unset($customerInfoClass);
            // EOF INFORMATION
            // ADDRESS
            $addressRecordArray = &$this->addressRecordArray;
            foreach ($addressRecordArray as $key => &$addressRecord) {
                $isSave = false;
                $addressId = (int)(isset($addressRecord['address_book_id']) ? $addressRecord['address_book_id'] : 0);
                unset($addressRecord['customers_id']);
                unset($addressRecord['address_book_id']);
                try {
                    $addressClass = \common\models\AddressBook::find()->where(['customers_id' => $this->customerId, 'address_book_id' => $addressId])->one();
                    if (!($addressClass instanceof \common\models\AddressBook)) {
                        $addressClass = new \common\models\AddressBook();
                        $addressClass->loadDefaultValues();
                        $addressClass->customers_id = $this->customerId;
                        if ($addressId > 0) {
                            $addressClass->address_book_id = $addressId;
                        }
                    }
                    $addressClass->setAttributes($addressRecord, false);
                    if ($addressClass->save(false)) {
                        $isSave = true;
                        $addressRecord = ($addressClass->toArray() + $addressRecord);
                    } else {
                        $this->messageAdd($addressClass->getErrorSummary(true));
                    }
                } catch (\Exception $exc) {
                    $this->messageAdd($exc->getMessage());
                }
                unset($addressClass);
                unset($addressId);
                if ($isSave != true) {
                    unset($addressRecordArray[$key]);
                }
                unset($isSave);
            }
            unset($addressRecordArray);
            unset($addressRecord);
            unset($key);
            // EOF ADDRESS
            // EMAIL
            $emailRecordArray = &$this->emailRecordArray;
            foreach ($emailRecordArray as $key => &$emailRecord) {
                $isSave = false;
                $email = trim(isset($emailRecord['customers_email']) ? $emailRecord['customers_email'] : '');
                unset($emailRecord['customers_id']);
                unset($emailRecord['customers_email']);
                if ($email != '') {
                    try {
                        $emailClass = \common\models\CustomersEmails::find()->where(['customers_id' => $this->customerId, 'customers_email' => $email])->one();
                        if (!($emailClass instanceof \common\models\CustomersEmails)) {
                            $emailClass = new \common\models\CustomersEmails();
                            $emailClass->loadDefaultValues();
                            $emailClass->customers_id = $this->customerId;
                            $emailClass->customers_email = $email;
                        }
                        $emailClass->setAttributes($emailRecord, false);
                        if ($emailClass->save(false)) {
                            $isSave = true;
                            $emailRecord = $emailClass->toArray();
                        } else {
                            $this->messageAdd($emailClass->getErrorSummary(true));
                        }
                    } catch (\Exception $exc) {
                        $this->messageAdd($exc->getMessage());
                    }
                    unset($emailClass);
                }
                unset($email);
                if ($isSave != true) {
                    unset($emailRecordArray[$key]);
                }
                unset($isSave);
            }
            unset($emailRecordArray);
            unset($emailRecord);
            unset($key);
            // EOF EMAIL
            // PHONE
            $phoneRecordArray = &$this->phoneRecordArray;
            foreach ($phoneRecordArray as $key => &$phoneRecord) {
                $isSave = false;
                $phone = trim(isset($phoneRecord['customers_phone']) ? $phoneRecord['customers_phone'] : '');
                unset($phoneRecord['customers_id']);
                unset($phoneRecord['customers_phone']);
                if ($phone != '') {
                    try {
                        $phoneClass = \common\models\CustomersPhones::find()->where(['customers_id' => $this->customerId, 'customers_phone' => $phone])->one();
                        if (!($phoneClass instanceof \common\models\CustomersPhones)) {
                            $phoneClass = new \common\models\CustomersPhones();
                            $phoneClass->loadDefaultValues();
                            $phoneClass->customers_id = $this->customerId;
                            $phoneClass->customers_phone = $phone;
                        }
                        $phoneClass->setAttributes($phoneRecord, false);
                        if ($phoneClass->save(false)) {
                            $isSave = true;
                            $phoneRecord = $phoneClass->toArray();
                        } else {
                            $this->messageAdd($phoneClass->getErrorSummary(true));
                        }
                    } catch (\Exception $exc) {
                        $this->messageAdd($exc->getMessage());
                    }
                    unset($phoneClass);
                }
                unset($phone);
                if ($isSave != true) {
                    unset($phoneRecordArray[$key]);
                }
                unset($isSave);
            }
            unset($phoneRecordArray);
            unset($phoneRecord);
            unset($key);
            // EOF PHONE
            // EXTRA GROUP
            $model = \common\helpers\Extensions::getModel('ExtraGroups', 'CustomerExtraGroups');
            if (!empty($model)) {
                $extraGroupRecordArray = &$this->extraGroupRecordArray;
                foreach ($extraGroupRecordArray as $key => &$extraGroupRecord) {
                    $isSave = false;
                    $groupId = (int)(isset($extraGroupRecord['group_id']) ? $extraGroupRecord['group_id'] : '');
                    unset($extraGroupRecord['customer_id']);
                    unset($extraGroupRecord['group_id']);
                    if ($groupId > 0) {
                        try {
                            $groupClass = $model::find()->where(['customer_id' => $this->customerId, 'group_id' => $groupId])->one();
                            if (!($groupClass instanceof $model)) {
                                $groupClass = new $model();
                                $groupClass->loadDefaultValues();
                                $groupClass->customer_id = $this->customerId;
                                $groupClass->group_id = $groupId;
                            }
                            $groupClass->setAttributes($extraGroupRecord, false);
                            if ($groupClass->save(false)) {
                                $isSave = true;
                                $extraGroupRecord = $groupClass->toArray();
                            } else {
                                $this->messageAdd($groupClass->getErrorSummary(true));
                            }
                        } catch (\Exception $exc) {
                            $this->messageAdd($exc->getMessage());
                        }
                        unset($groupClass);
                    }
                    unset($groupId);
                    if ($isSave != true) {
                        unset($extraGroupRecordArray[$key]);
                    }
                    unset($isSave);
                }
                unset($extraGroupRecordArray);
                unset($extraGroupRecord);
                unset($key);
            }
            // EOF EXTRA GROUP
            $return = $this->customerId;
        } else {
            $this->messageAdd($customerClass->getErrorSummary(true));
        }
        unset($customerClass);
        unset($isReplace);
        return $return;
    }
}