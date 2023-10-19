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

class Category extends AbstractClass
{
    public $categoryId = null;

    public $categoryRecord = [];
    public $descriptionRecordArray = [];
    public $affiliateRecordArray = [];
    public $platformRecordArray = [];
    public $platformSettingRecordArray = [];
    public $templateRecordArray = [];
    public $groupRecordArray = [];
    public $supplierDiscountRecordArray = [];
    public $supplierPriceRuleRecordArray = [];
    public $filterRecordArray = [];
    public $productRecordArray = [];
    public $categoryImageNewArray = [];
    public $oldSeoRedirectArray = [];

    public function getId()
    {
        return $this->categoryId;
    }

    public function setId($categoryId)
    {
        $categoryId = (int)$categoryId;
        if ($categoryId >= 0) {
            $this->categoryId = $categoryId;
            return true;
        }
        return $this;
    }

    public function load($categoryId)
    {
        $this->clear();
        $categoryId = (int)$categoryId;
        $categoryRecord = \common\models\Categories::find()->where(['categories_id' => $categoryId])->one();
        if ($categoryRecord instanceof \common\models\Categories) {
            $this->categoryId = $categoryId;
            $this->categoryRecord = $categoryRecord->toArray();
            unset($categoryRecord);
            // DESCRIPTION
            $this->descriptionRecordArray = \common\models\CategoriesDescription::find()->where(['categories_id' => $categoryId])->asArray(true)->all();
            // EOF DESCRIPTION
            // AFFILIATE
            $this->affiliateRecordArray = !\common\helpers\Acl::checkExtensionAllowed('Affiliate') ? [] :
                    \common\extensions\Affiliate\models\CategoriesToAffiliates::find()->where(['categories_id' => $categoryId])->asArray(true)->all();
            // EOF AFFILIATE*/
            // PLATFORM
            $this->platformRecordArray = \common\models\PlatformsCategories::find()->where(['categories_id' => $categoryId])->asArray(true)->all();
            // EOF PLATFORM
            // PLATFORM SETTING
            $this->platformSettingRecordArray = \common\models\CategoriesPlatformSettings::find()->where(['categories_id' => $categoryId])->asArray(true)->all();
            // EOF PLATFORM SETTING
            // TEMPLATE
            $this->templateRecordArray = \common\models\CategoriesToTemplate::find()->where(['categories_id' => $categoryId])->asArray(true)->all();
            // EOF TEMPLATE
            // GROUP
            if ($model = \common\helpers\Acl::checkExtensionTableExist('UserGroupsRestrictions', 'GroupsCategories')) {
                $this->groupRecordArray = $model::find()->where(['categories_id' => $categoryId])->asArray(true)->all();
            }
            // EOF GROUP
            // SUPPLIER DISCOUNT
            $this->supplierDiscountRecordArray = \common\models\SuppliersCatalogDiscount::find()->where(['category_id' => $categoryId])->asArray(true)->all();
            // EOF SUPPLIER DISCOUNT
            // SUPPLIER PRICE RULE
            $this->supplierPriceRuleRecordArray = \common\models\SuppliersCatalogPriceRules::find()->where(['category_id' => $categoryId])->asArray(true)->all();
            // EOF SUPPLIER PRICE RULE
            // FILTER
            $this->filterRecordArray = \common\models\Filters::find()->where(['categories_id' => $categoryId])->asArray(true)->all();
            // EOF FILTER
            // PRODUCT
            $this->productRecordArray = \common\models\Products2Categories::find()->alias('ptc')
                ->leftJoin(\common\models\Products::tableName() . ' AS p', 'p.products_id = ptc.products_id')
                ->select(['ptc.*', 'p.products_model'])->where(['categories_id' => $categoryId])->asArray(true)->all();
            // EOF PRODUCT
            return true;
        }
        return false;
    }

    public function validate()
    {
        $this->categoryId = (int)(((int)$this->categoryId > 0) ? $this->categoryId : 0);
        if (!is_array($this->categoryRecord)) {
            return false;
        }
        if (!parent::validate()) {
            return false;
        }
        unset($this->categoryRecord['categories_id']);
        $this->descriptionRecordArray = (is_array($this->descriptionRecordArray) ? $this->descriptionRecordArray : array());
        $this->affiliateRecordArray = (is_array($this->affiliateRecordArray) ? $this->affiliateRecordArray : array());
        $this->platformRecordArray = (is_array($this->platformRecordArray) ? $this->platformRecordArray : array());
        $this->platformSettingRecordArray = (is_array($this->platformSettingRecordArray) ? $this->platformSettingRecordArray : array());
        $this->templateRecordArray = (is_array($this->templateRecordArray) ? $this->templateRecordArray : array());
        $this->groupRecordArray = (is_array($this->groupRecordArray) ? $this->groupRecordArray : array());
        $this->supplierDiscountRecordArray = (is_array($this->supplierDiscountRecordArray) ? $this->supplierDiscountRecordArray : array());
        $this->supplierPriceRuleRecordArray = (is_array($this->supplierPriceRuleRecordArray) ? $this->supplierPriceRuleRecordArray : array());
        $this->filterRecordArray = (is_array($this->filterRecordArray) ? $this->filterRecordArray : array());
        $this->productRecordArray = (is_array($this->productRecordArray) ? $this->productRecordArray : array());
        $this->categoryImageNewArray = (is_array($this->categoryImageNewArray) ? $this->categoryImageNewArray : array());
        $this->oldSeoRedirectArray = (is_array($this->oldSeoRedirectArray) ? $this->oldSeoRedirectArray : array());
        return true;
    }

    public function create()
    {
        $this->categoryId = 0;
        return $this->save();
    }

    public function save($isReplace = false)
    {
        $return = false;
        if (!$this->validate()) {
            return $return;
        }
        $categoryClass = \common\models\Categories::find()->where(['categories_id' => $this->categoryId])->one();
        if (!($categoryClass instanceof \common\models\Categories)) {
            $categoryClass = new \common\models\Categories();
            $categoryClass->loadDefaultValues();
            if ($this->categoryId > 0) {
                $categoryClass->categories_id = $this->categoryId;
            } else {
                $this->unrelate();
            }
        }
        $categoryClass->setAttributes($this->categoryRecord, false);
        $categoryClass->detachBehavior('nestedSets');
        if ($categoryClass->save(false)) {
            $this->categoryRecord = $categoryClass->toArray();
            $this->categoryId = (int)$categoryClass->categories_id;
            // DESCRIPTION
            foreach ($this->descriptionRecordArray as $key => &$descriptionRecord) {
                $isSave = false;
                $languageId = (int)(isset($descriptionRecord['language_id']) ? $descriptionRecord['language_id'] : 0);
                if (isset($descriptionRecord['language_code'])) {
                    $languageId = $this->getLanguageIdByCode($descriptionRecord['language_code'], $languageId);
                }
                $affiliateId = (int)(isset($descriptionRecord['affiliate_id']) ? $descriptionRecord['affiliate_id'] : -1);
                unset($descriptionRecord['categories_id']);
                unset($descriptionRecord['affiliate_id']);
                unset($descriptionRecord['language_id']);
                if (($languageId > 0) AND ($affiliateId >= 0)) {
                    try {
                        $descriptionClass = \common\models\CategoriesDescription::find()->where(['categories_id' => $this->categoryId, 'language_id' => $languageId, 'affiliate_id' => $affiliateId])->one();
                        if (!($descriptionClass instanceof \common\models\CategoriesDescription)) {
                            $descriptionClass = new \common\models\CategoriesDescription();
                            $descriptionClass->loadDefaultValues();
                            $descriptionClass->categories_id = $this->categoryId;
                            $descriptionClass->affiliate_id = $affiliateId;
                            $descriptionClass->language_id = $languageId;
                        }
                        $descriptionClass->setAttributes($descriptionRecord, false);
                        if ($descriptionClass->save(false)) {
                            $isSave = true;
                            $descriptionRecord = $descriptionClass->toArray();
                        } else {
                            $this->messageAdd($descriptionClass->getErrorSummary(true));
                        }
                    } catch (\Exception $exc) {
                        $this->messageAdd($exc->getMessage());
                    }
                    unset($descriptionClass);
                }
                unset($affiliateId);
                unset($languageId);
                if ($isSave != true) {
                    unset($this->descriptionRecordArray[$key]);
                }
                unset($isSave);
            }
            unset($descriptionRecord);
            unset($key);
            // EOF DESCRIPTION
            // AFFILIATE
            if (\common\helpers\Acl::checkExtensionAllowed('Affiliate')) {
            foreach ($this->affiliateRecordArray as $key => &$affiliateRecord) {
                $isSave = false;
                $affiliateId = (int)(isset($affiliateRecord['affiliate_id']) ? $affiliateRecord['affiliate_id'] : -1);
                unset($affiliateRecord['categories_id']);
                unset($affiliateRecord['affiliate_id']);
                if ($affiliateId >= 0) {
                    try {
                        $affiliateClass = \common\extensions\Affiliate\models\CategoriesToAffiliates::find()->where(['categories_id' => $this->categoryId, 'affiliate_id' => $affiliateId])->one();
                        if (!($affiliateClass instanceof \common\models\CategoriesToAffiliates)) {
                            $affiliateClass = new \common\extensions\Affiliate\models\CategoriesToAffiliates();
                            $affiliateClass->loadDefaultValues();
                            $affiliateClass->categories_id = $this->categoryId;
                            $affiliateClass->affiliate_id = $affiliateId;
                        }
                        $affiliateClass->setAttributes($affiliateRecord, false);
                        if ($affiliateClass->save(false)) {
                            $isSave = true;
                            $affiliateRecord = $affiliateClass->toArray();
                        } else {
                            $this->messageAdd($affiliateClass->getErrorSummary(true));
                        }
                    } catch (\Exception $exc) {
                        $this->messageAdd($exc->getMessage());
                    }
                    unset($affiliateClass);
                }
                unset($affiliateId);
                if ($isSave != true) {
                    unset($this->affiliateRecordArray[$key]);
                }
                unset($isSave);
            }
            unset($affiliateRecord);
            unset($key);
            }
            // EOF AFFILIATE
            // PLATFORM
            foreach ($this->platformRecordArray as $key => &$platformRecord) {
                $isSave = false;
                $platformId = (int)(isset($platformRecord['platform_id']) ? $platformRecord['platform_id'] : 0);
                unset($platformRecord['categories_id']);
                unset($platformRecord['platform_id']);
                if ($platformId > 0) {
                    try {
                        $platformClass = \common\models\PlatformsCategories::find()->where(['categories_id' => $this->categoryId, 'platform_id' => $platformId])->one();
                        if (!($platformClass instanceof \common\models\PlatformsCategories)) {
                            $platformClass = new \common\models\PlatformsCategories();
                            $platformClass->loadDefaultValues();
                            $platformClass->categories_id = $this->categoryId;
                            $platformClass->platform_id = $platformId;
                        }
                        $platformClass->setAttributes($platformRecord, false);
                        if ($platformClass->save(false)) {
                            $isSave = true;
                            $platformRecord = $platformClass->toArray();
                        } else {
                            $this->messageAdd($platformClass->getErrorSummary(true));
                        }
                    } catch (\Exception $exc) {
                        $this->messageAdd($exc->getMessage());
                    }
                    unset($platformClass);
                }
                unset($platformId);
                if ($isSave != true) {
                    unset($this->platformRecordArray[$key]);
                }
                unset($isSave);
            }
            unset($platformRecord);
            unset($key);
            // EOF PLATFORM
            // PLATFORM SETTING
            foreach ($this->platformSettingRecordArray as $key => &$platformSettingRecord) {
                $isSave = false;
                $platformId = (int)(isset($platformSettingRecord['platform_id']) ? $platformSettingRecord['platform_id'] : 0);
                unset($platformSettingRecord['categories_id']);
                unset($platformSettingRecord['platform_id']);
                if ($platformId > 0) {
                    try {
                        $platformSettingClass = \common\models\CategoriesPlatformSettings::find()->where(['categories_id' => $this->categoryId, 'platform_id' => $platformId])->one();
                        if (!($platformSettingClass instanceof \common\models\CategoriesPlatformSettings)) {
                            $platformSettingClass = new \common\models\CategoriesPlatformSettings();
                            $platformSettingClass->loadDefaultValues();
                            $platformSettingClass->categories_id = $this->categoryId;
                            $platformSettingClass->platform_id = $platformId;
                        }
                        $platformSettingClass->setAttributes($platformSettingRecord, false);
                        if ($platformSettingClass->save(false)) {
                            $isSave = true;
                            $platformSettingRecord = $platformSettingClass->toArray();
                        } else {
                            $this->messageAdd($platformSettingClass->getErrorSummary(true));
                        }
                    } catch (\Exception $exc) {
                        $this->messageAdd($exc->getMessage());
                    }
                    unset($platformSettingClass);
                }
                unset($platformId);
                if ($isSave != true) {
                    unset($this->platformSettingRecordArray[$key]);
                }
                unset($isSave);
            }
            unset($platformSettingRecord);
            unset($key);
            // EOF PLATFORM SETTING
            // TEMPLATE
            foreach ($this->templateRecordArray as $key => &$templateRecord) {
                $isSave = false;
                $platformId = (int)(isset($templateRecord['platform_id']) ? $templateRecord['platform_id'] : 0);
                unset($templateRecord['categories_id']);
                unset($templateRecord['platform_id']);
                unset($templateRecord['id']);
                if ($platformId > 0) {
                    try {
                        $templateClass = \common\models\CategoriesToTemplate::find()->where(['categories_id' => $this->categoryId, 'platform_id' => $platformId])->one();
                        if (!($templateClass instanceof \common\models\CategoriesToTemplate)) {
                            $templateClass = new \common\models\CategoriesToTemplate();
                            $templateClass->loadDefaultValues();
                            $templateClass->categories_id = $this->categoryId;
                            $templateClass->platform_id = $platformId;
                        }
                        $templateClass->setAttributes($templateRecord, false);
                        if ($templateClass->save(false)) {
                            $isSave = true;
                            $templateRecord = $templateClass->toArray();
                        } else {
                            $this->messageAdd($templateClass->getErrorSummary(true));
                        }
                    } catch (\Exception $exc) {
                        $this->messageAdd($exc->getMessage());
                    }
                    unset($templateClass);
                }
                unset($platformId);
                if ($isSave != true) {
                    unset($this->templateRecordArray[$key]);
                }
                unset($isSave);
            }
            unset($templateRecord);
            unset($key);
            // EOF TEMPLATE
            // GROUP
            $isRewriteGroup = false;
            if ($groupCategories = \common\helpers\Acl::checkExtensionTableExist('UserGroupsRestrictions', 'GroupsCategories')) {
                foreach ($this->groupRecordArray as $key => &$groupRecord) {
                    $isSave = false;
                    if ($isRewriteGroup == false) {
                        $isRewriteGroup = true;
                        $groupCategories::deleteAll(['categories_id' => $this->categoryId]);
                    }
                    $groupId = (int)(isset($groupRecord['groups_id']) ? $groupRecord['groups_id'] : 0);
                    unset($groupRecord['categories_id']);
                    unset($groupRecord['groups_id']);
                    if ($groupId > 0) {
                        try {
                            $groupClass = $groupCategories::find()->where(['categories_id' => $this->categoryId, 'groups_id' => $groupId])->one();
                            if (empty($groupClass)) {
                                $groupClass = new $groupCategories;
                                $groupClass->loadDefaultValues();
                                $groupClass->categories_id = $this->categoryId;
                                $groupClass->groups_id = $groupId;
                            }
                            $groupClass->setAttributes($groupRecord, false);
                            if ($groupClass->save(false)) {
                                $isSave = true;
                                $groupRecord = $groupClass->toArray();
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
                        unset($this->groupRecordArray[$key]);
                    }
                    unset($isSave);
                }
                unset($isRewriteGroup);
                unset($groupRecord);
                unset($key);
            }
            // EOF GROUP
            // SUPPLIER DISCOUNT
            foreach ($this->supplierDiscountRecordArray as $key => &$supplierDiscountRecord) {
                $isSave = false;
                $manufacturerId = (int)(isset($supplierDiscountRecord['manufacturer_id']) ? $supplierDiscountRecord['manufacturer_id'] : 0);
                $supplierId = (int)(isset($supplierDiscountRecord['suppliers_id']) ? $supplierDiscountRecord['suppliers_id'] : 0);
                unset($supplierDiscountRecord['catalog_discount_id']);
                unset($supplierDiscountRecord['manufacturer_id']);
                unset($supplierDiscountRecord['suppliers_id']);
                unset($supplierDiscountRecord['category_id']);
                if (($supplierId > 0) AND ($manufacturerId > 0)) {
                    try {
                        $supplierDiscountClass = \common\models\SuppliersCatalogDiscount::find()->where(['category_id' => $this->categoryId, 'suppliers_id' => $supplierId, 'manufacturer_id' => $manufacturerId])->one();
                        if (!($supplierDiscountClass instanceof \common\models\SuppliersCatalogDiscount)) {
                            $supplierDiscountClass = new \common\models\SuppliersCatalogDiscount();
                            $supplierDiscountClass->loadDefaultValues();
                            $supplierDiscountClass->category_id = $this->categoryId;
                            $supplierDiscountClass->manufacturer_id = $manufacturerId;
                            $supplierDiscountClass->suppliers_id = $supplierId;
                        }
                        $supplierDiscountClass->setAttributes($supplierDiscountRecord, false);
                        if ($supplierDiscountClass->save(false)) {
                            $isSave = true;
                            $supplierDiscountRecord = $supplierDiscountClass->toArray();
                        } else {
                            $this->messageAdd($supplierDiscountClass->getErrorSummary(true));
                        }
                    } catch (\Exception $exc) {
                        $this->messageAdd($exc->getMessage());
                    }
                    unset($supplierDiscountClass);
                }
                unset($manufacturerId);
                unset($supplierId);
                if ($isSave != true) {
                    unset($this->supplierDiscountRecordArray[$key]);
                }
                unset($isSave);
            }
            unset($supplierDiscountRecord);
            unset($key);
            // EOF SUPPLIER DISCOUNT
            // SUPPLIER PRICE RULE
            foreach ($this->supplierPriceRuleRecordArray as $key => &$supplierPriceRuleRecord) {
                $isSave = false;
                $manufacturerId = (int)(isset($supplierPriceRuleRecord['manufacturer_id']) ? $supplierPriceRuleRecord['manufacturer_id'] : 0);
                $currencyId = (int)(isset($supplierPriceRuleRecord['currencies_id']) ? $supplierPriceRuleRecord['currencies_id'] : -1);
                $supplierId = (int)(isset($supplierPriceRuleRecord['suppliers_id']) ? $supplierPriceRuleRecord['suppliers_id'] : 0);
                unset($supplierPriceRuleRecord['manufacturer_id']);
                unset($supplierPriceRuleRecord['currencies_id']);
                unset($supplierPriceRuleRecord['suppliers_id']);
                unset($supplierPriceRuleRecord['category_id']);
                unset($supplierPriceRuleRecord['rule_id']);
                if (($supplierId > 0) AND ($manufacturerId > 0) AND ($currencyId >= 0)) {
                    try {
                        $supplierPriceRuleClass = \common\models\SuppliersCatalogPriceRules::find()->where(['category_id' => $this->categoryId, 'suppliers_id' => $supplierId, 'manufacturer_id' => $manufacturerId, 'currencies_id' => $currencyId])->one();
                        if (!($supplierPriceRuleClass instanceof \common\models\SuppliersCatalogPriceRules)) {
                            $supplierPriceRuleClass = new \common\models\SuppliersCatalogPriceRules();
                            $supplierPriceRuleClass->loadDefaultValues();
                            $supplierPriceRuleClass->category_id = $this->categoryId;
                            $supplierPriceRuleClass->manufacturer_id = $manufacturerId;
                            $supplierPriceRuleClass->currencies_id = $currencyId;
                            $supplierPriceRuleClass->suppliers_id = $supplierId;
                        }
                        $supplierPriceRuleClass->setAttributes($supplierPriceRuleRecord, false);
                        if ($supplierPriceRuleClass->save(false)) {
                            $isSave = true;
                            $supplierPriceRuleRecord = $supplierPriceRuleClass->toArray();
                        } else {
                            $this->messageAdd($supplierPriceRuleClass->getErrorSummary(true));
                        }
                    } catch (\Exception $exc) {
                        $this->messageAdd($exc->getMessage());
                    }
                    unset($supplierPriceRuleClass);
                }
                unset($manufacturerId);
                unset($currencyId);
                unset($supplierId);
                if ($isSave != true) {
                    unset($this->supplierPriceRuleRecordArray[$key]);
                }
                unset($isSave);
            }
            unset($supplierPriceRuleRecord);
            unset($key);
            // EOF SUPPLIER PRICE RULE
            // FILTER
            foreach ($this->filterRecordArray as $key => &$filterRecord) {
                $isSave = false;
                $manufacturerId = (int)(isset($filterRecord['manufacturers_id']) ? $filterRecord['manufacturers_id'] : 0);
                $propertyId = (int)(isset($filterRecord['properties_id']) ? $filterRecord['properties_id'] : 0);
                $filterType = trim(isset($filterRecord['filters_type']) ? $filterRecord['filters_type'] : '');
                $optionId = (int)(isset($filterRecord['options_id']) ? $filterRecord['options_id'] : 0);
                unset($filterRecord['manufacturers_id']);
                unset($filterRecord['categories_id']);
                unset($filterRecord['properties_id']);
                unset($filterRecord['filters_type']);
                unset($filterRecord['options_id']);
                unset($filterRecord['filters_id']);
                if ($filterType != '') {
                    try {
                        $filterClass = \common\models\Filters::find()->where(['categories_id' => $this->categoryId, 'manufacturers_id' => $manufacturerId, 'filters_type' => $filterType, 'options_id' => $optionId, 'properties_id' => $propertyId])->one();
                        if (!($filterClass instanceof \common\models\Filters)) {
                            $filterClass = new \common\models\Filters();
                            $filterClass->loadDefaultValues();
                            $filterClass->categories_id = $this->categoryId;
                            $filterClass->manufacturers_id = $manufacturerId;
                            $filterClass->properties_id = $propertyId;
                            $filterClass->filters_type = $filterType;
                            $filterClass->options_id = $optionId;
                        }
                        $filterClass->setAttributes($filterRecord, false);
                        if ($filterClass->save(false)) {
                            $isSave = true;
                            $filterRecord = $filterClass->toArray();
                        } else {
                            $this->messageAdd($filterClass->getErrorSummary(true));
                        }
                    } catch (\Exception $exc) {
                        $this->messageAdd($exc->getMessage());
                    }
                    unset($filterClass);
                }
                unset($manufacturerId);
                unset($propertyId);
                unset($filterType);
                unset($optionId);
                if ($isSave != true) {
                    unset($this->filterRecordArray[$key]);
                }
                unset($isSave);
            }
            unset($filterRecord);
            unset($key);
            // EOF FILTER
            // PRODUCT
            foreach ($this->productRecordArray as $key => &$productRecord) {
                $isSave = false;
                $productId = (int)(isset($productRecord['products_id']) ? $productRecord['products_id'] : 0);
                $productModel = trim(isset($productRecord['products_model']) ? $productRecord['products_model'] : '');
                unset($productRecord['products_model']);
                unset($productRecord['categories_id']);
                unset($productRecord['products_id']);
                if ($productModel != '') {
                    $productIdCheck = $productId;
                    $productId = 0;
                    foreach (\common\models\Products::findAll(['products_model' => $productModel]) as $count => $productSearchRecord) {
                        $productId = 0;
                        if ($count == 0) {
                            $productId = (int)$productSearchRecord->products_id;
                        }
                        if ($productIdCheck == (int)$productSearchRecord->products_id) {
                            $productId = $productIdCheck;
                            break;
                        }
                    }
                    unset($productSearchRecord);
                    unset($productIdCheck);
                    unset($count);
                }
                if ($productId > 0) {
                    try {
                        $productClass = \common\models\Products2Categories::find()->where(['categories_id' => $this->categoryId, 'products_id' => $productId])->one();
                        if (!($productClass instanceof \common\models\Products2Categories)) {
                            $productClass = new \common\models\Products2Categories();
                            $productClass->loadDefaultValues();
                            $productClass->categories_id = $this->categoryId;
                            $productClass->products_id = $productId;
                        }
                        $productClass->setAttributes($productRecord, false);
                        if ($productClass->save(false)) {
                            $isSave = true;
                            $productRecord = $productClass->toArray();
                            if ($productModel != '') {
                                $productRecord['products_model'] = $productModel;
                            }
                        } else {
                            $this->messageAdd($productClass->getErrorSummary(true));
                        }
                    } catch (\Exception $exc) {
                        $this->messageAdd($exc->getMessage());
                    }
                    unset($productClass);
                }
                unset($productModel);
                unset($productId);
                if ($isSave != true) {
                    unset($this->productRecordArray[$key]);
                }
                unset($isSave);
            }
            unset($productRecord);
            unset($key);
            // EOF PRODUCT
            // OLD SEO REDIRECT
            $seoModel = \common\helpers\Extensions::getModel('SeoRedirectsNamed', 'SeoRedirectsNamed');
            if (!empty($seoModel)) {
                foreach ($this->oldSeoRedirectArray as $seoRedirectArray) {
                    try {
                        $platformId = (int)(isset($seoRedirectArray['platform_id']) ? $seoRedirectArray['platform_id'] : 0);
                        if ($platformId >= 0) {
                            $languageId = (int)(isset($seoRedirectArray['language_id']) ? $seoRedirectArray['language_id'] : 0);
                            if (isset($seoRedirectArray['language_code'])) {
                                $languageId = $this->getLanguageIdByCode($seoRedirectArray['language_code'], $languageId);
                            }
                            $searchArray = [
                                'platform_id' => $platformId,
                                'language_id' => $languageId,
                                'redirects_type' => 'category',
                                'owner_id' => $this->categoryId,
                                'old_seo_page_name' => $seoRedirectArray['old_seo_page_name']
                            ];
                            $seoRedirectRecord = $seoModel::findOne($searchArray);
                            if (!($seoRedirectRecord instanceof $seoModel)) {
                                $seoRedirectRecord = new $seoModel();
                                $seoRedirectRecord->loadDefaultValues();
                                $seoRedirectRecord->setAttributes($searchArray);
                                $seoRedirectRecord->save();
                            }
                        }
                    } catch (\Exception $exc) {
                        \Yii::warning($exc->getMessage().' '.$exc->getTraceAsString(), 'SeoRedirectNammed');
                    }
                    unset($seoRedirectRecord);
                    unset($searchArray);
                    unset($languageId);
                    unset($platformId);
                }

            }
            unset($seoRedirectArray);
            // EOF OLD SEO REDIRECT
            // IMAGES
            if (count($this->categoryImageNewArray) > 0) {
                $categoryDirectory = ('categories' . DIRECTORY_SEPARATOR . $this->categoryId . DIRECTORY_SEPARATOR);
                foreach (['gallery' => '', 'hero' => '_2', 'homepage' => '_3'] as $imageType => $imageField) {
                    if (isset($this->categoryImageNewArray[$imageType]) AND (trim($this->categoryImageNewArray[$imageType]) != '')) {
                        try {
                            $imageSrc = trim($this->categoryImageNewArray[$imageType]);
                            $imageBody = file_get_contents($imageSrc);
                            if ($imageBody != false) {
                                $imageName = ($this->categoryId . '_' . md5($imageSrc) . '.' . strtolower(pathinfo($imageSrc, PATHINFO_EXTENSION)));
                                $imageDirectory = (DIR_FS_CATALOG . DIR_WS_IMAGES . $categoryDirectory);
                                if (!is_dir($imageDirectory)) {
                                    @mkdir($imageDirectory, 0777, true);
                                }
                                $imageFile = @fopen($imageDirectory . $imageName, 'w+');
                                unset($imageDirectory);
                                if ($imageFile) {
                                    $isCreate = (@fwrite($imageFile, $imageBody) > 0);
                                    @fclose($imageFile);
                                    if ($isCreate == true) {
                                        $categoryClass->{'categories_image' . $imageField} = \common\classes\Images::moveImage(
                                            ($categoryDirectory . $imageName),
                                            ($categoryDirectory . $imageType)
                                        );
                                        $categoryClass->save(false);
                                        \common\classes\Images::createWebp($categoryClass->{'categories_image' . $imageField});
                                        \common\classes\Images::createResizeImages($categoryClass->{'categories_image' . $imageField}, 'Category ' . $imageType);
                                    }
                                    unset($isCreate);
                                }
                                unset($imageFile);
                                unset($imageName);
                            }
                            unset($imageBody);
                            unset($imageSrc);
                        } catch (\Exception $exc) {
                            \Yii::warning("Error while import image '$imageSrc' for category($this->categoryId) : " . $exc->getMessage());
                        }
                    }
                }
                unset($categoryDirectory);
                unset($imageField);
                unset($imageType);
                $categoryClass->save(false);
            }
            // EOF IMAGES
            $return = $this->categoryId;
        } else {
            $this->messageAdd($categoryClass->getErrorSummary(true));
        }
        unset($categoryClass);
        unset($isReplace);
        return $return;
    }
}