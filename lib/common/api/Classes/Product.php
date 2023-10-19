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

class Product extends AbstractClass
{
    public $productId = null;

    public $productRecord = [];
    public $descriptionRecordArray = [];
    public $productsPricesRecordArray = [];
    public $productsAttributesRecordArray = [];
    public $productsToCategoriesRecordArray = [];
    public $platformsProductsRecordArray = [];
    public $departmentsProductsRecordArray = [];
    public $productsImagesRecordArray = [];
    public $inventoryRecordArray = [];
    public $giveAwayProductsRecordArray = [];
    public $featuredRecordArray = [];
    public $productsXsellRecordArray = [];
    public $productsUpsellRecordArray = [];
    public $propertiesToPropductsRecordArray = [];
    public $productsVideosRecordArray = [];
    public $giftWrapProductsRecordArray = [];
    public $productsNotesRecordArray = [];
    public $suppliersProductsRecordArray = [];
    public $warehousesProductsRecordArray = [];
    public $platformStockControlRecordArray = [];
    public $warehouseStockControlRecordArray = [];
    public $oldSeoRedirectArray = [];
    // ...
    public $newProductsImagesArray = [];

    public $specialsArray = [];
    //public $specialPricesArray = []; not done yet

    public function getId()
    {
        return $this->productId;
    }

    public function setId($productId)
    {
        $productId = (int)$productId;
        if ($productId >= 0) {
            $this->productId = $productId;
            return true;
        }
        return $this;
    }

    public function load($productId)
    {
        $this->clear();
        $productId = (int)$productId;
        $productRecord = \common\models\Products::find()->where(['products_id' => $productId])->one();
        if ($productRecord instanceof \common\models\Products) {
            $this->productId = $productId;
            $this->productRecord = $productRecord->toArray();
            unset($productRecord);
            /**
             * Description
             */
            $this->descriptionRecordArray = \common\models\ProductsDescription::find()->where(['products_id' => $productId])->asArray(true)->all();
            /**
             * Prices
             */
            $this->productsPricesRecordArray = \common\models\ProductsPrices::find()->where(['products_id' => $productId])->asArray(true)->all();
            /**
             * Attributes
             */
            foreach (\common\models\ProductsAttributes::find()->where(['products_id' => $productId])->asArray(true)->all() as $productsAttributesRecord ) {
                $productsAttributesRecord['productsAttributesPricesRecordArray'] = \common\models\ProductsAttributesPrices::find()->where(['products_attributes_id' => $productsAttributesRecord['products_attributes_id']])->asArray(true)->all();
                $productsAttributesRecord['productsAttributesDownloadRecord'] = \common\models\ProductsAttributesDownload::find()->where(['products_attributes_id' => $productsAttributesRecord['products_attributes_id']])->one();
                $this->productsAttributesRecordArray[] = $productsAttributesRecord;
            }
            unset($productsAttributesRecord);
            /**
             * Categories
             */
            $this->productsToCategoriesRecordArray = \common\models\Products2Categories::find()->where(['products_id' => $productId])->asArray(true)->all();
            /**
             * Platforms
             */
            $this->platformsProductsRecordArray = \common\models\PlatformsProducts::find()->where(['products_id' => $productId])->asArray(true)->all();
            /**
             * Departments
             */
            $this->departmentsProductsRecordArray = \common\models\DepartmentsProducts::find()->where(['products_id' => $productId])->asArray(true)->all();
            /**
             * Images
             */
            foreach (\common\models\ProductsImages::find()->where(['products_id' => $productId])->asArray(true)->all() as $productsImagesRecord ) {
                $productsImagesRecord['productsImagesDescriptionRecordArray'] = \common\models\ProductsImagesDescription::find()->where(['products_images_id' => $productsImagesRecord['products_images_id']])->asArray(true)->all();
                $productsImagesRecord['productsImagesAttributesRecordArray'] = \common\models\ProductsImagesAttributes::find()->where(['products_images_id' => $productsImagesRecord['products_images_id']])->asArray(true)->all();
                $productsImagesRecord['productsImagesExternalUrlRecordArray'] = \common\models\ProductsImagesExternalUrl::find()->where(['products_images_id' => $productsImagesRecord['products_images_id']])->asArray(true)->all();
                $productsImagesRecord['productsImagesInventoryRecordArray'] = \common\models\ProductsImagesInventory::find()->where(['products_images_id' => $productsImagesRecord['products_images_id']])->asArray(true)->all();
                $this->productsImagesRecordArray[] = $productsImagesRecord;
            }
            unset($productsImagesRecord);
            $this->newProductsImagesArray = [];
            /**
             * Inventory
             */
            foreach (\common\models\Inventory::find()->where(['prid' => $productId])->asArray(true)->all() as $inventoryRecord ) {
                $inventoryRecord['inventoryPricesRecordArray'] = \common\models\InventoryPrices::find()->where(['inventory_id' => $inventoryRecord['inventory_id']])->asArray(true)->all();

                if ($extScl = \common\helpers\Acl::checkExtensionAllowed('StockControl', 'allowed')) {
                    $extScl::updateApiProductInventoryLoad($inventoryRecord);
                }

                $this->inventoryRecordArray[] = $inventoryRecord;
            }
            unset($inventoryRecord);
            /**
             * Give Away
             */
            $this->giveAwayProductsRecordArray = \common\models\GiveAwayProducts::find()->where(['products_id' => $productId])->asArray(true)->all();
            /**
             * Featured
             */
            $this->featuredRecordArray = \common\models\Featured::find()->where(['products_id' => $productId])->asArray(true)->all();
            /**
             * Cross Sell
             */
            $xsellModel = \common\helpers\Extensions::getModel('UpSell', 'ProductsXsell');
            $this->productsXsellRecordArray = (!empty($xsellModel)) ? $xsellModel::find()->where(['products_id' => $productId])->asArray(true)->all() : [];

            $upsellModel = \common\helpers\Extensions::getModel('UpSell', 'ProductsUpsell');
            $this->productsUpsellRecordArray = (!empty($upsellModel)) ? $upsellModel::find()->where(['products_id' => $productId])->asArray(true)->all() : [];
            /**
             * Properties To Propducts
             */
            $this->propertiesToPropductsRecordArray = \common\models\Properties2Propducts::find()->where(['products_id' => $productId])->asArray(true)->all();
            /**
             * Videos
             */
            $this->productsVideosRecordArray = \common\models\ProductsVideos::find()->where(['products_id' => $productId])->asArray(true)->all();
            /**
             * Gift Wrap
             */
            $this->giftWrapProductsRecordArray = \common\models\GiftWrapProducts::find()->where(['products_id' => $productId])->asArray(true)->all();
            /**
             * Notes
             */
            $this->productsNotesRecordArray = \common\models\Product\ProductsNotes::find()->where(['products_id' => $productId])->asArray(true)->all();
            /**
             * Suppliers Products
             */
            $this->suppliersProductsRecordArray = \common\models\SuppliersProducts::find()->where(['products_id' => $productId])->asArray(true)->all();
            /**
             * Warehouses Products
             */
            $this->warehousesProductsRecordArray = \common\models\WarehousesProducts::find()->where(['prid' => $productId])->asArray(true)->all();
            /**
             * Specials
             */
            $this->specialsArray = \common\models\Specials::find()->where(['products_id' => $productId])->asArray(true)->all();

            if ($extScl = \common\helpers\Acl::checkExtensionAllowed('StockControl', 'allowed')) {
                $extScl::updateApiProductLoad($this);
            }

            // ...
            return true;
        }
        return false;
    }

    public function unrelate()
    {
        if (is_array($this->productsAttributesRecordArray)) {
            foreach ($this->productsAttributesRecordArray as &$productsAttributesRecord) {
                unset($productsAttributesRecord['products_attributes_id']);
                unset($productsAttributesRecord['productsAttributesDownloadRecord']['products_attributes_id']);
                if (is_array($productsAttributesRecord['productsAttributesPricesRecordArray'])) {
                    foreach ($productsAttributesRecord['productsAttributesPricesRecordArray'] as &$productsAttributesPricesRecord) {
                        unset($productsAttributesPricesRecord['products_attributes_id']);
                    }
                    unset($productsAttributesPricesRecord);
                }
            }
            unset($productsAttributesRecord);
        }
        if (is_array($this->productsImagesRecordArray)) {
            foreach ($this->productsImagesRecordArray as &$productsImagesRecord) {
                unset($productsImagesRecord['products_images_id']);
                if (is_array($productsImagesRecord['productsImagesDescriptionRecordArray'])) {
                    foreach ($productsImagesRecord['productsImagesDescriptionRecordArray'] as &$productsImagesDescriptionRecord) {
                        unset($productsImagesDescriptionRecord['products_images_id']);
                    }
                    unset($productsImagesDescriptionRecord);
                }
                if (is_array($productsImagesRecord['productsImagesAttributesRecordArray'])) {
                    foreach ($productsImagesRecord['productsImagesAttributesRecordArray'] as &$productsImagesAttributesRecord) {
                        unset($productsImagesAttributesRecord['products_images_id']);
                    }
                    unset($productsImagesAttributesRecord);
                }
                if (is_array($productsImagesRecord['productsImagesExternalUrlRecordArray'])) {
                    foreach ($productsImagesRecord['productsImagesExternalUrlRecordArray'] as &$productsImagesExternalUrlRecord) {
                        unset($productsImagesExternalUrlRecord['products_images_id']);
                    }
                    unset($productsImagesExternalUrlRecord);
                }
                if (is_array($productsImagesRecord['productsImagesInventoryRecordArray'])) {
                    foreach ($productsImagesRecord['productsImagesInventoryRecordArray'] as &$productsImagesInventoryRecord) {
                        unset($productsImagesInventoryRecord['products_images_id']);
                    }
                    unset($productsImagesInventoryRecord);
                }
            }
            unset($productsImagesRecord);
        }
        if (is_array($this->inventoryRecordArray)) {
            foreach ($this->inventoryRecordArray as &$inventoryRecord) {
                unset($inventoryRecord['inventory_id']);
                if (is_array($inventoryRecord['inventoryPricesRecordArray'])) {
                    foreach ($inventoryRecord['inventoryPricesRecordArray'] as &$inventoryPricesRecord) {
                        unset($inventoryPricesRecord['inventory_id']);
                    }
                    unset($inventoryPricesRecord);
                }
            }
            unset($inventoryRecord);
        }
        if (is_array($this->giveAwayProductsRecordArray)) {
            foreach ($this->giveAwayProductsRecordArray as &$giveAwayProductsRecord) {
                unset($giveAwayProductsRecord['gap_id']);
            }
            unset($giveAwayProductsRecord);
        }
        if (is_array($this->featuredRecordArray)) {
            foreach ($this->featuredRecordArray as &$featuredRecord) {
                unset($featuredRecord['featured_id']);
            }
            unset($featuredRecord);
        }
        if (is_array($this->productsXsellRecordArray)) {
            foreach ($this->productsXsellRecordArray as &$productsXsellRecord) {
                unset($productsXsellRecord['ID']);
            }
            unset($productsXsellRecord);
        }
        if (is_array($this->productsUpsellRecordArray)) {
            foreach ($this->productsUpsellRecordArray as &$productsUpsellRecord) {
                unset($productsUpsellRecord['ID']);
            }
            unset($productsUpsellRecord);
        }
        // ...
        return parent::unrelate();
    }

    public function validate()
    {
        $this->productId = (int)(((int)$this->productId > 0) ? $this->productId : 0);
        if (!is_array($this->productRecord)) {
            return false;
        }
        if (!parent::validate()) {
            return false;
        }
        unset($this->productRecord['products_id']);
        $this->descriptionRecordArray = (is_array($this->descriptionRecordArray) ? $this->descriptionRecordArray : array());
        $this->productsPricesRecordArray = (is_array($this->productsPricesRecordArray) ? $this->productsPricesRecordArray : array());
        $this->productsAttributesRecordArray = (is_array($this->productsAttributesRecordArray) ? $this->productsAttributesRecordArray : array());
        foreach ($this->productsAttributesRecordArray as $key => $productsAttributesRecord) {
            $this->productsAttributesRecordArray[$key]['productsAttributesPricesRecordArray'] = (is_array($productsAttributesRecord['productsAttributesPricesRecordArray']) ? $productsAttributesRecord['productsAttributesPricesRecordArray'] : array());
            $this->productsAttributesRecordArray[$key]['productsAttributesDownloadRecord'] = (is_array($productsAttributesRecord['productsAttributesDownloadRecord']) ? $productsAttributesRecord['productsAttributesDownloadRecord'] : null);
        }
        unset($productsAttributesRecord);
        unset($key);
        $this->productsToCategoriesRecordArray = (is_array($this->productsToCategoriesRecordArray) ? $this->productsToCategoriesRecordArray : array());
        $this->platformsProductsRecordArray = (is_array($this->platformsProductsRecordArray) ? $this->platformsProductsRecordArray : array());
        $this->departmentsProductsRecordArray = (is_array($this->departmentsProductsRecordArray) ? $this->departmentsProductsRecordArray : array());
        $this->productsImagesRecordArray = (is_array($this->productsImagesRecordArray) ? $this->productsImagesRecordArray : array());
        foreach ($this->productsImagesRecordArray as $key => $productsImagesRecord) {
            $this->productsImagesRecordArray[$key]['productsImagesDescriptionRecordArray'] = (is_array($productsImagesRecord['productsImagesDescriptionRecordArray']) ? $productsImagesRecord['productsImagesDescriptionRecordArray'] : array());
            $this->productsImagesRecordArray[$key]['productsImagesAttributesRecordArray'] = (is_array($productsImagesRecord['productsImagesAttributesRecordArray']) ? $productsImagesRecord['productsImagesAttributesRecordArray'] : array());
            $this->productsImagesRecordArray[$key]['productsImagesExternalUrlRecordArray'] = (is_array($productsImagesRecord['productsImagesExternalUrlRecordArray']) ? $productsImagesRecord['productsImagesExternalUrlRecordArray'] : array());
            $this->productsImagesRecordArray[$key]['productsImagesInventoryRecordArray'] = (is_array($productsImagesRecord['productsImagesInventoryRecordArray']) ? $productsImagesRecord['productsImagesInventoryRecordArray'] : array());
        }
        unset($productsImagesRecord);
        unset($key);
        $this->newProductsImagesArray = (is_array($this->newProductsImagesArray) ? $this->newProductsImagesArray : array());
        $this->inventoryRecordArray = (is_array($this->inventoryRecordArray) ? $this->inventoryRecordArray : array());
        foreach ($this->inventoryRecordArray as $key => $inventoryRecord) {
            $this->inventoryRecordArray[$key]['inventoryPricesRecordArray'] = (is_array($inventoryRecord['inventoryPricesRecordArray']) ? $inventoryRecord['inventoryPricesRecordArray'] : array());
            $this->inventoryRecordArray[$key]['platformInventoryControlRecordArray'] = (is_array($inventoryRecord['platformInventoryControlRecordArray']) ? $inventoryRecord['platformInventoryControlRecordArray'] : array());
            $this->inventoryRecordArray[$key]['warehouseInventoryControlRecordArray'] = (is_array($inventoryRecord['warehouseInventoryControlRecordArray']) ? $inventoryRecord['warehouseInventoryControlRecordArray'] : array());
        }
        unset($inventoryRecord);
        unset($key);
        $this->giveAwayProductsRecordArray = (is_array($this->giveAwayProductsRecordArray) ? $this->giveAwayProductsRecordArray : array());
        $this->featuredRecordArray = (is_array($this->featuredRecordArray) ? $this->featuredRecordArray : array());
        $this->productsXsellRecordArray = (is_array($this->productsXsellRecordArray) ? $this->productsXsellRecordArray : array());
        $this->productsUpsellRecordArray = (is_array($this->productsUpsellRecordArray) ? $this->productsUpsellRecordArray : array());
        $this->propertiesToPropductsRecordArray = (is_array($this->propertiesToPropductsRecordArray) ? $this->propertiesToPropductsRecordArray : array());
        $this->productsVideosRecordArray = (is_array($this->productsVideosRecordArray) ? $this->productsVideosRecordArray : array());
        $this->giftWrapProductsRecordArray = (is_array($this->giftWrapProductsRecordArray) ? $this->giftWrapProductsRecordArray : array());
        $this->productsNotesRecordArray = (is_array($this->productsNotesRecordArray) ? $this->productsNotesRecordArray : array());
        $this->suppliersProductsRecordArray = (is_array($this->suppliersProductsRecordArray) ? $this->suppliersProductsRecordArray : array());
        $this->warehousesProductsRecordArray = (is_array($this->warehousesProductsRecordArray) ? $this->warehousesProductsRecordArray : array());
        $this->platformStockControlRecordArray = (is_array($this->platformStockControlRecordArray) ? $this->platformStockControlRecordArray : array());
        $this->warehouseStockControlRecordArray = (is_array($this->warehouseStockControlRecordArray) ? $this->warehouseStockControlRecordArray : array());
        $this->oldSeoRedirectArray = (is_array($this->oldSeoRedirectArray) ? $this->oldSeoRedirectArray : array());
        $this->specialsArray = (is_array($this->specialsArray) ? $this->specialsArray : array());
        // ...
        return true;
    }

    public function create()
    {
        $this->productId = 0;
        return $this->save();
    }

    public function save($isReplace = false)
    {
        $return = false;
        if (!$this->validate()) {
            return $return;
        }
        $productClass = \common\models\Products::find()->where(['products_id' => $this->productId])->one();
        if (!($productClass instanceof \common\models\Products)) {
            $productClass = new \common\models\Products();
            $productClass->loadDefaultValues();
            if ($this->productId > 0) {
                $productClass->products_id = $this->productId;
            } else {
                $this->unrelate();
            }
        }
        $productClass->setAttributes($this->productRecord, false);
        $productClass->detachBehavior('nestedSets');
        if ($productClass->save(false)) {
            $this->productRecord = $productClass->toArray();
            $this->productId = (int)$productClass->products_id;
            /**
             * Description
             */
            foreach ($this->descriptionRecordArray as $descriptionRecord) {
                $languageId = (int)(isset($descriptionRecord['language_id']) ? $descriptionRecord['language_id'] : 0);
                $platformId = (int)(isset($descriptionRecord['platform_id']) ? $descriptionRecord['platform_id'] : -1);
                $departmentId = (int)(isset($descriptionRecord['department_id']) ? $descriptionRecord['department_id'] : 0);
                unset($descriptionRecord['products_id']);
                unset($descriptionRecord['language_id']);
                unset($descriptionRecord['platform_id']);
                unset($descriptionRecord['department_id']);
                if (($languageId > 0) and ($platformId >= 0)) {
                    $descriptionClass = \common\models\ProductsDescription::find()->where(['products_id' => $this->productId, 'language_id' => $languageId, 'platform_id' => $platformId, 'department_id' => $departmentId])->one();
                    if (!($descriptionClass instanceof \common\models\ProductsDescription)) {
                        $descriptionClass = new \common\models\ProductsDescription();
                        $descriptionClass->loadDefaultValues();
                        $descriptionClass->products_id = $this->productId;
                        $descriptionClass->language_id = $languageId;
                        $descriptionClass->platform_id = $platformId;
                        $descriptionClass->department_id = $departmentId;
                    }
                    $descriptionClass->setAttributes($descriptionRecord, false);
                    if ($descriptionClass->save(false)) {

                    } else {
                        $this->messageAdd($descriptionClass->getErrorSummary(true));
                    }
                    unset($descriptionClass);
                }
                unset($departmentId);
                unset($platformId);
                unset($languageId);
            }
            unset($descriptionRecord);
            /**
             * Prices
             */
            foreach ($this->productsPricesRecordArray as $priceRecord) {
                $currenciesId = (int)(isset($priceRecord['currencies_id']) ? $priceRecord['currencies_id'] : -1);
                $groupsId = (int)(isset($priceRecord['groups_id']) ? $priceRecord['groups_id'] : -1);
                unset($priceRecord['products_id']);
                unset($priceRecord['currencies_id']);
                unset($priceRecord['groups_id']);
                if (($currenciesId >= 0) and ($groupsId >= 0)) {
                    $priceClass = \common\models\ProductsPrices::find()->where(['products_id' => $this->productId, 'groups_id' => $groupsId, 'currencies_id' => $currenciesId])->one();
                    if (!($priceClass instanceof \common\models\ProductsPrices)) {
                        $priceClass = new \common\models\ProductsPrices();
                        $priceClass->loadDefaultValues();
                        $priceClass->products_id = $this->productId;
                        $priceClass->groups_id = $groupsId;
                        $priceClass->currencies_id = $currenciesId;
                    }
                    $priceClass->setAttributes($priceRecord, false);
                    if (is_null($priceClass->products_group_price)) {
                        $priceClass->products_group_price = 0;
                    }
                    if ($priceClass->save(false)) {

                    } else {
                        $this->messageAdd($priceClass->getErrorSummary(true));
                    }
                    unset($priceClass);
                }
                unset($groupsId);
                unset($currenciesId);
            }
            unset($priceRecord);
            /**
             * Attributes
             */
            foreach ($this->productsAttributesRecordArray as $key => $productsAttributesRecord) {
                $isSave = false;
                $productsAttributesId = (int)(isset($productsAttributesRecord['products_attributes_id']) ? $productsAttributesRecord['products_attributes_id'] : 0);
                $optionsId = (int)(isset($productsAttributesRecord['options_id']) ? $productsAttributesRecord['options_id'] : 0);
                $optionsValuesId = (int)(isset($productsAttributesRecord['options_values_id']) ? $productsAttributesRecord['options_values_id'] : 0);
                unset($productsAttributesRecord['products_id']);
                unset($productsAttributesRecord['products_attributes_id']);
                unset($productsAttributesRecord['options_id']);
                unset($productsAttributesRecord['options_values_id']);
                try {
                    if ($optionsId > 0 && $optionsValuesId > 0) {
                        $atributeClass = \common\models\ProductsAttributes::find()->where(['products_id' => $this->productId, 'options_id' => $optionsId, 'options_values_id' => $optionsValuesId])->one();
                        if (!($atributeClass instanceof \common\models\ProductsAttributes)) {
                            $atributeClass = new \common\models\ProductsAttributes();
                            $atributeClass->loadDefaultValues();
                            $atributeClass->products_id = $this->productId;
                            $atributeClass->options_id = $optionsId;
                            $atributeClass->options_values_id = $optionsValuesId;
                            /*if ($productsAttributesId > 0) {
                                $atributeClass->products_attributes_id = $productsAttributesId;
                            }*/
                        }
                        if ($atributeClass->save(false)) {
                            $isSave = true;
                            $this->productsAttributesRecordArray[$key] = ($productsAttributesRecord + $atributeClass->toArray());
                            $productsAttributesId = $atributeClass->products_attributes_id;
                        } else {
                            $this->messageAdd($atributeClass->getErrorSummary(true));
                        }
                    }
                } catch (\Exception $exc) {
                }
                unset($atributeClass);
                if ($isSave != true) {
                    unset($this->productsAttributesRecordArray[$key]);
                }
                unset($isSave);
                if (($productsAttributesId > 0) and (count($productsAttributesRecord['productsAttributesPricesRecordArray']) > 0)) {
                    foreach ($productsAttributesRecord['productsAttributesPricesRecordArray'] as $productsAttributesPricesRecord) {
                        $currenciesId = (int)(isset($productsAttributesPricesRecord['currencies_id']) ? $productsAttributesPricesRecord['currencies_id'] : -1);
                        $groupsId = (int)(isset($productsAttributesPricesRecord['groups_id']) ? $productsAttributesPricesRecord['groups_id'] : -1);
                        unset($productsAttributesPricesRecord['products_attributes_id']);
                        unset($productsAttributesPricesRecord['currencies_id']);
                        unset($productsAttributesPricesRecord['groups_id']);
                        if (($currenciesId >= 0) and ($groupsId >= 0)) {
                            $productsAttributesClass = \common\models\ProductsAttributesPrices::find()->where(['products_attributes_id' => $productsAttributesId, 'groups_id' => $groupsId, 'currencies_id' => $currenciesId])->one();
                            if (!($productsAttributesClass instanceof \common\models\ProductsAttributesPrices)) {
                                $productsAttributesClass = new \common\models\ProductsAttributesPrices();
                                $productsAttributesClass->loadDefaultValues();
                                $productsAttributesClass->products_attributes_id = $productsAttributesId;
                                $productsAttributesClass->groups_id = $groupsId;
                                $productsAttributesClass->currencies_id = $currenciesId;
                            }
                            $productsAttributesClass->setAttributes($productsAttributesPricesRecord, false);
                            if ($productsAttributesClass->save(false)) {
                                $this->messageAdd($productsAttributesClass->getErrorSummary(true));
                            }
                            unset($productsAttributesClass);
                        }
                        unset($groupsId);
                        unset($currenciesId);
                    }
                    unset($productsAttributesPricesRecord);
                }
                if (($productsAttributesId > 0) and is_array($productsAttributesRecord['productsAttributesDownloadRecord'])) {
                    $productsAttributesDownloadRecord = $productsAttributesRecord['productsAttributesDownloadRecord'];
                    unset($productsAttributesDownloadRecord['products_attributes_id']);
                    $productsAttributesDownloadClass = \common\models\ProductsAttributesDownload::find()->where(['products_attributes_id' => $productsAttributesId])->one();
                    if (!($productsAttributesDownloadClass instanceof \common\models\ProductsAttributesDownload)) {
                        $productsAttributesDownloadClass = new \common\models\ProductsAttributesDownload();
                        $productsAttributesDownloadClass->loadDefaultValues();
                        $productsAttributesDownloadClass->products_attributes_id = $productsAttributesId;
                    }
                    $productsAttributesDownloadClass->setAttributes($productsAttributesDownloadRecord, false);
                    if ($productsAttributesDownloadClass->save(false)) {
                        $this->messageAdd($productsAttributesDownloadClass->getErrorSummary(true));
                    }
                    unset($productsAttributesDownloadClass);
                    unset($productsAttributesDownloadRecord);
                }
                unset($productsAttributesId);
            }
            unset($key);
            unset($productsAttributesRecord);
            /**
             * Categories
             */
            foreach ($this->productsToCategoriesRecordArray as $productsCategoriesRecord) {
                $categoryId = (int)(isset($productsCategoriesRecord['categories_id']) ? $productsCategoriesRecord['categories_id'] : -1);
                unset($productsCategoriesRecord['products_id']);
                unset($productsCategoriesRecord['categories_id']);
                if ($categoryId >= 0) {
                    $categoryClass = \common\models\Products2Categories::find()->where(['products_id' => $this->productId, 'categories_id' => $categoryId])->one();
                    if (!($categoryClass instanceof \common\models\Products2Categories)) {
                        $categoryClass = new \common\models\Products2Categories();
                        $categoryClass->loadDefaultValues();
                        $categoryClass->products_id = $this->productId;
                        $categoryClass->categories_id = $categoryId;
                    }
                    $categoryClass->setAttributes($productsCategoriesRecord, false);
                    if ($categoryClass->save(false)) {

                    } else {
                        $this->messageAdd($categoryClass->getErrorSummary(true));
                    }
                    unset($categoryClass);
                }
                unset($categoryId);
            }
            unset($productsCategoriesRecord);
            /**
             * Platforms
             */
            foreach ($this->platformsProductsRecordArray as $platformsProductsRecord) {
                $platformId = (int)(isset($platformsProductsRecord['platform_id']) ? $platformsProductsRecord['platform_id'] : 0);
                unset($platformsProductsRecord['products_id']);
                unset($platformsProductsRecord['platform_id']);
                if ($platformId > 0) {
                    $platformClass = \common\models\PlatformsProducts::find()->where(['products_id' => $this->productId, 'platform_id' => $platformId])->one();
                    if (!($platformClass instanceof \common\models\PlatformsProducts)) {
                        $platformClass = new \common\models\PlatformsProducts();
                        $platformClass->loadDefaultValues();
                        $platformClass->products_id = $this->productId;
                        $platformClass->platform_id = $platformId;
                    }
                    $platformClass->setAttributes($platformsProductsRecord, false);
                    if ($platformClass->save(false)) {

                    } else {
                        $this->messageAdd($platformClass->getErrorSummary(true));
                    }
                    unset($platformClass);
                }
                unset($platformId);
            }
            unset($platformsProductsRecord);
            /**
             * Departments
             */
            foreach ($this->departmentsProductsRecordArray as $departmentsProductsRecord) {
                $departmentId = (int)(isset($departmentsProductsRecord['departments_id']) ? $departmentsProductsRecord['departments_id'] : 0);
                unset($departmentsProductsRecord['products_id']);
                unset($departmentsProductsRecord['platform_id']);
                if ($departmentId > 0) {
                    $departmentClass = \common\models\DepartmentsProducts::find()->where(['products_id' => $this->productId, 'platform_id' => $platformId])->one();
                    if (!($departmentClass instanceof \common\models\DepartmentsProducts)) {
                        $departmentClass = new \common\models\DepartmentsProducts();
                        $departmentClass->loadDefaultValues();
                        $departmentClass->products_id = $this->productId;
                        $departmentClass->departments_id = $departmentId;
                    }
                    $departmentClass->setAttributes($departmentsProductsRecord, false);
                    if ($departmentClass->save(false)) {

                    } else {
                        $this->messageAdd($departmentClass->getErrorSummary(true));
                    }
                    unset($departmentClass);
                }
                unset($departmentId);
            }
            unset($departmentsProductsRecord);
            /**
             * Images
             */
            foreach ($this->productsImagesRecordArray as $key => $productsImagesRecord) {
                $isSave = false;
                $productImageId = (int)(isset($productsImagesRecord['products_images_id']) ? $productsImagesRecord['products_images_id'] : 0);
                unset($productsImagesRecord['products_id']);
                unset($productsImagesRecord['products_images_id']);
                try {
                    $imageClass = \common\models\ProductsImages::find()->where(['products_id' => $this->productId, 'products_images_id' => $productImageId])->one();
                    if (!($imageClass instanceof \common\models\ProductsImages)) {
                        $imageClass = new \common\models\ProductsImages();
                        $imageClass->loadDefaultValues();
                        $imageClass->products_id = $this->productId;
                        if ($productImageId > 0) {
                            $imageClass->products_images_id = $productImageId;
                        }
                    }
                    $imageClass->setAttributes($productsImagesRecord, false);
                    if ($imageClass->save(false)) {
                        $isSave = true;
                        $this->productsImagesRecordArray[$key] = ($productsImagesRecord + $imageClass->toArray());
                        $productImageId = $imageClass->products_images_id;
                    } else {
                        $this->messageAdd($imageClass->getErrorSummary(true));
                    }
                } catch (\Exception $exc) {
                }
                unset($imageClass);
                if ($isSave != true) {
                    $productImageId = 0;
                    unset($this->productsImagesRecordArray[$key]);
                }
                unset($isSave);
                if (($productImageId > 0) and (count($productsImagesRecord['productsImagesDescriptionRecordArray']) > 0)) {
                    foreach ($productsImagesRecord['productsImagesDescriptionRecordArray'] as $productsImagesDescriptionRecord) {
                        $languageId = (int)(isset($productsImagesDescriptionRecord['language_id']) ? $productsImagesDescriptionRecord['language_id'] : -1);
                        unset($productsImagesDescriptionRecord['products_images_id']);
                        unset($productsImagesDescriptionRecord['language_id']);
                        if ($languageId >= 0) {
                            $descriptionClass = \common\models\ProductsImagesDescription::find()->where(['products_images_id' => $productImageId, 'language_id' => $languageId])->one();
                            if (!($descriptionClass instanceof \common\models\ProductsImagesDescription)) {
                                $descriptionClass = new \common\models\ProductsImagesDescription();
                                $descriptionClass->loadDefaultValues();
                                $descriptionClass->products_images_id = $productImageId;
                                $descriptionClass->language_id = $languageId;
                            }
                            $descriptionClass->setAttributes($productsImagesDescriptionRecord, false);
                            if ($descriptionClass->save(false)) {

                            } else {
                                $this->messageAdd($descriptionClass->getErrorSummary(true));
                            }
                            unset($descriptionClass);
                        }
                        unset($languageId);
                    }
                    unset($productsImagesDescriptionRecord);
                }
                if (($productImageId > 0) and (count($productsImagesRecord['productsImagesAttributesRecordArray']) > 0)) {
                    foreach ($productsImagesRecord['productsImagesAttributesRecordArray'] as $productsImagesAttributesRecord) {
                        $productsOptionsId = (int)(isset($productsImagesAttributesRecord['products_options_id']) ? $productsImagesAttributesRecord['products_options_id'] : 0);
                        $productsOptionsValuesId = (int)(isset($productsImagesAttributesRecord['products_options_values_id']) ? $productsImagesAttributesRecord['products_options_values_id'] : 0);
                        unset($productsImagesAttributesRecord['products_images_id']);
                        unset($productsImagesAttributesRecord['products_options_id']);
                        unset($productsImagesAttributesRecord['products_options_values_id']);
                        if (($productsOptionsId > 0) and ($productsOptionsValuesId >= 0)) {
                            $imagesAttributesClass = \common\models\ProductsImagesAttributes::find()->where(['products_images_id' => $productImageId, 'products_options_id' => $productsOptionsId, 'products_options_values_id' => $productsOptionsValuesId])->one();
                            if (!($imagesAttributesClass instanceof \common\models\ProductsImagesAttributes)) {
                                $imagesAttributesClass = new \common\models\ProductsImagesAttributes();
                                $imagesAttributesClass->loadDefaultValues();
                                $imagesAttributesClass->products_images_id = $productImageId;
                                $imagesAttributesClass->products_options_id = $productsOptionsId;
                                $imagesAttributesClass->products_options_values_id = $productsOptionsValuesId;
                            }
                            $imagesAttributesClass->setAttributes($productsImagesAttributesRecord, false);
                            if ($imagesAttributesClass->save(false)) {

                            } else {
                                $this->messageAdd($imagesAttributesClass->getErrorSummary(true));
                            }
                            unset($imagesAttributesClass);
                        }
                        unset($productsOptionsId);
                        unset($productsOptionsValuesId);
                    }
                    unset($productsImagesAttributesRecord);
                }
                if (($productImageId > 0) and (count($productsImagesRecord['productsImagesExternalUrlRecordArray']) > 0)) {
                    foreach ($productsImagesRecord['productsImagesExternalUrlRecordArray'] as $productsImagesExternalUrlRecord) {
                        $languageId = (int)(isset($productsImagesExternalUrlRecord['language_id']) ? $productsImagesExternalUrlRecord['language_id'] : -1);
                        $imageTypesId = (int)(isset($productsImagesExternalUrlRecord['image_types_id']) ? $productsImagesExternalUrlRecord['image_types_id'] : 0);
                        unset($productsImagesExternalUrlRecord['products_images_id']);
                        unset($productsImagesExternalUrlRecord['image_types_id']);
                        unset($productsImagesExternalUrlRecord['language_id']);
                        if (($languageId >= 0) and ($imageTypesId >= 0)) {
                            $imagesExternalUrlClass = \common\models\ProductsImagesExternalUrl::find()->where(['products_images_id' => $productImageId, 'image_types_id' => $imageTypesId, 'language_id' => $languageId])->one();
                            if (!($imagesExternalUrlClass instanceof \common\models\ProductsImagesExternalUrl)) {
                                $imagesExternalUrlClass = new \common\models\ProductsImagesExternalUrl();
                                $imagesExternalUrlClass->loadDefaultValues();
                                $imagesExternalUrlClass->products_images_id = $productImageId;
                                $imagesExternalUrlClass->image_types_id = $imageTypesId;
                                $imagesExternalUrlClass->language_id = $languageId;
                            }
                            $imagesExternalUrlClass->setAttributes($productsImagesExternalUrlRecord, false);
                            if ($imagesExternalUrlClass->save(false)) {

                            } else {
                                $this->messageAdd($imagesExternalUrlClass->getErrorSummary(true));
                            }
                            unset($imagesExternalUrlClass);
                        }
                        unset($imageTypesId);
                        unset($languageId);
                    }
                    unset($productsImagesExternalUrlRecord);
                }
                if (($productImageId > 0) and (count($productsImagesRecord['productsImagesInventoryRecordArray']) > 0)) {
                    foreach ($productsImagesRecord['productsImagesInventoryRecordArray'] as $productsImagesInventoryRecord) {
                        $inventoryId = (int)(isset($productsImagesInventoryRecord['inventory_id']) ? $productsImagesInventoryRecord['inventory_id'] : 0);
                        unset($productsImagesInventoryRecord['products_images_id']);
                        unset($productsImagesInventoryRecord['inventory_id']);
                        if ($inventoryId > 0) {
                            $imagesInventoryClass = \common\models\ProductsImagesInventory::find()->where(['products_images_id' => $productImageId, 'inventory_id' => $inventoryId])->one();
                            if (!($imagesInventoryClass instanceof \common\models\ProductsImagesInventory)) {
                                $imagesInventoryClass = new \common\models\ProductsImagesInventory();
                                $imagesInventoryClass->loadDefaultValues();
                                $imagesInventoryClass->products_images_id = $productImageId;
                                $imagesInventoryClass->inventory_id = $inventoryId;
                            }
                            $imagesInventoryClass->setAttributes($productsImagesInventoryRecord, false);
                            if ($imagesInventoryClass->save(false)) {

                            } else {
                                $this->messageAdd($imagesInventoryClass->getErrorSummary(true));
                            }
                            unset($imagesInventoryClass);
                        }
                        unset($inventoryId);
                    }
                    unset($productsImagesInventoryRecord);
                }
                unset($productImageId);
            }
            unset($key);
            unset($productsImagesRecord);
            foreach ($this->newProductsImagesArray as $newProductsImages) {
                $this->attachNewImage($newProductsImages);
            }
            unset($newProductsImages);
            $isDefault = false;
            foreach (\common\models\ProductsImages::find()
                         ->where(['products_id' => $this->productId])
                         ->orderBy(['default_image' => SORT_DESC, 'products_images_id' => SORT_ASC])
                         ->asArray(false)->all() as $productImageRecord
            ) {
                if ($isDefault == false) {
                    $productImageRecord->default_image = 1;
                    $isDefault = true;
                } else {
                    $productImageRecord->default_image = 0;
                }
                try {
                    $productImageRecord->save();
                } catch (\Exception $exc) {
                }
            }
            unset($productImageRecord);
            unset($isDefault);
            /**
             * Inventory
             */
            foreach ($this->inventoryRecordArray as $key => $inventoryRecord) {
                $isSave = false;
                $inventoryId = (int)(isset($inventoryRecord['inventory_id']) ? $inventoryRecord['inventory_id'] : 0);
                $uprid = (isset($inventoryRecord['products_id']) ? $inventoryRecord['products_id'] : $this->productId);
                unset($inventoryRecord['inventory_id']);
                unset($inventoryRecord['products_id']);
                unset($inventoryRecord['prid']);
                $uprid = preg_replace('/^\d*(\{.+)$/', ($this->productId . '$1'), $uprid);
                try {
                    $inventoryClass = \common\models\Inventory::find()->where(['prid' => $this->productId, 'products_id' => $uprid])->one();
                    if (!($inventoryClass instanceof \common\models\Inventory)) {
                        $inventoryClass = new \common\models\Inventory();
                        $inventoryClass->loadDefaultValues();
                        $inventoryClass->products_id = $uprid;
                        $inventoryClass->prid = $this->productId;
                        if ($inventoryId > 0) {
                            $inventoryClass->inventory_id = $inventoryId;
                        }
                    }
                    $inventoryClass->setAttributes($inventoryRecord, false);
                    if ($inventoryClass->save(false)) {
                        $isSave = true;
                        $this->inventoryRecordArray[$key] = ($inventoryRecord + $inventoryClass->toArray());
                        $inventoryId = $inventoryClass->inventory_id;
                    } else {
                        $this->messageAdd($inventoryClass->getErrorSummary(true));
                    }

                } catch (\Exception $exc) {
                }
                unset($inventoryClass);

                if ($isSave != true) {
                    unset($this->inventoryRecordArray[$key]);
                }
                unset($isSave);
                // TODO
                if (($inventoryId > 0) and (count($inventoryRecord['inventoryPricesRecordArray']) > 0)) {
                    foreach ($inventoryRecord['inventoryPricesRecordArray'] as $inventoryPricesRecord) {
                        $currenciesId = (int)(isset($inventoryPricesRecord['currencies_id']) ? $inventoryPricesRecord['currencies_id'] : -1);
                        $groupsId = (int)(isset($inventoryPricesRecord['groups_id']) ? $inventoryPricesRecord['groups_id'] : -1);
                        unset($inventoryPricesRecord['inventory_id']);
                        unset($inventoryPricesRecord['groups_id']);
                        unset($inventoryPricesRecord['currencies_id']);
                        if (($currenciesId >= 0) and ($groupsId >= 0)) {
                            $inventoryClass = \common\models\InventoryPrices::find()->where(['prid' => $this->productId, 'products_id' => $uprid, 'groups_id' => $groupsId, 'currencies_id' => $currenciesId])->one();
                            if (!($inventoryClass instanceof \common\models\InventoryPrices)) {
                                $inventoryClass = new \common\models\InventoryPrices();
                                $inventoryClass->loadDefaultValues();
                                $inventoryClass->inventory_id = $inventoryId;
                                $inventoryClass->groups_id = $groupsId;
                                $inventoryClass->currencies_id = $currenciesId;
                                $inventoryClass->products_id = $uprid;
                                $inventoryClass->prid = $this->productId;
                            }
                            $inventoryClass->setAttributes($inventoryPricesRecord, false);
                            if ($inventoryClass->save(false)) {

                            } else {
                                $this->messageAdd($inventoryClass->getErrorSummary(true));
                            }
                            unset($inventoryClass);
                        }
                        unset($groupsId);
                        unset($currenciesId);
                    }
                    unset($inventoryPricesRecord);
                }
                if ($inventoryId > 0) {
                    if (isset($inventoryRecord['inventoryImagesArray'])) {
                        foreach ((is_array($inventoryRecord['inventoryImagesArray']) ? $inventoryRecord['inventoryImagesArray'] : array()) as $inventoryImage) {
                            $productImageId = $this->findImageId($inventoryImage);
                            if ($productImageId > 0) {

                                $imagesInventoryClass = \common\models\ProductsImagesInventory::find()->where(['products_images_id' => $productImageId, 'inventory_id' => $inventoryId])->one();
                                if (!($imagesInventoryClass instanceof \common\models\ProductsImagesInventory)) {
                                    $imagesInventoryClass = new \common\models\ProductsImagesInventory();
                                    $imagesInventoryClass->loadDefaultValues();
                                    $imagesInventoryClass->products_images_id = $productImageId;
                                    $imagesInventoryClass->inventory_id = $inventoryId;
                                }
                                $imagesInventoryClass->setAttributes($productsImagesInventoryRecord, false);
                                if ($imagesInventoryClass->save(false)) {

                                } else {
                                    $this->messageAdd($imagesInventoryClass->getErrorSummary(true));
                                }
                                unset($imagesInventoryClass);


                            }
                            unset($productImageId);
                        }
                        unset($inventoryImage);
                        unset($inventoryRecord['inventoryImagesArray']);
                    }
                }

                if ($extScl = \common\helpers\Acl::checkExtensionAllowed('StockControl', 'allowed')) {
                    $extScl::updateApiProductInventorySave($this, $inventoryRecord, $inventoryId, $uprid);
                }

            }
            unset($key);
            unset($inventoryRecord);
            /**
             * Give Away
             */
            foreach ($this->giveAwayProductsRecordArray as $giveAwayProductsRecord) {
                $currenciesId = (int)(isset($giveAwayProductsRecord['currencies_id']) ? $giveAwayProductsRecord['currencies_id'] : -1);
                $groupsId = (int)(isset($giveAwayProductsRecord['groups_id']) ? $giveAwayProductsRecord['groups_id'] : -1);
                unset($giveAwayProductsRecord['products_id']);
                unset($giveAwayProductsRecord['currencies_id']);
                unset($giveAwayProductsRecord['groups_id']);
                if (($currenciesId >= 0) and ($groupsId >= 0)) {
                    $giveAwayClass = \common\models\GiveAwayProducts::find()->where(['products_id' => $this->productId, 'groups_id' => $groupsId, 'currencies_id' => $currenciesId])->one();
                    if (!($giveAwayClass instanceof \common\models\GiveAwayProducts)) {
                        $giveAwayClass = new \common\models\ProductsPrices();
                        $giveAwayClass->loadDefaultValues();
                        $giveAwayClass->products_id = $this->productId;
                        $giveAwayClass->groups_id = $groupsId;
                        $giveAwayClass->currencies_id = $currenciesId;
                    }
                    $giveAwayClass->setAttributes($giveAwayProductsRecord, false);
                    if ($giveAwayClass->save(false)) {

                    } else {
                        $this->messageAdd($giveAwayClass->getErrorSummary(true));
                    }
                    unset($giveAwayClass);
                }
                unset($groupsId);
                unset($currenciesId);
            }
            unset($giveAwayProductsRecord);
            /**
             * Featured
             */
            foreach ($this->featuredRecordArray as $featuredRecordArray) {
                $affiliateId = (int)(isset($featuredRecordArray['affiliate_id']) ? $featuredRecordArray['affiliate_id'] : 0);
                unset($featuredRecordArray['products_id']);
                unset($featuredRecordArray['affiliate_id']);
                $featuredClass = \common\models\Featured::find()->where(['products_id' => $this->productId, 'affiliate_id' => $affiliateId])->one();
                if (!($featuredClass instanceof \common\models\Featured)) {
                    $featuredClass = new \common\models\Featured();
                    $featuredClass->loadDefaultValues();
                    $featuredClass->products_id = $this->productId;
                    $featuredClass->affiliate_id = $affiliateId;
                }
                $featuredClass->setAttributes($featuredRecordArray, false);
                if ($featuredClass->save(false)) {

                } else {
                    $this->messageAdd($featuredClass->getErrorSummary(true));
                }
                unset($featuredClass);
                unset($affiliateId);
            }
            unset($featuredRecordArray);
            /**
             * Cross Sell
             */
            $xsellModel = \common\helpers\Extensions::getModel('UpSell', 'ProductsXsell');
            if (!empty($xsellModel)) {

                foreach ($this->productsXsellRecordArray as $productsXsellRecord) {
                    $xsellId = (int)(isset($productsXsellRecord['xsell_id']) ? $productsXsellRecord['xsell_id'] : 0);
                    unset($productsXsellRecord['products_id']);
                    unset($productsXsellRecord['xsell_id']);
                    if ($xsellId > 0) {
                        $xsellClass = $xsellModel::find()->where(['products_id' => $this->productId, 'xsell_id' => $xsellId])->one();
                        if (!($xsellClass instanceof $xsellModel)) {
                            $xsellClass = new $xsellModel();
                            $xsellClass->loadDefaultValues();
                            $xsellClass->products_id = $this->productId;
                            $xsellClass->xsell_id = $xsellId;
                        }
                        $xsellClass->setAttributes($productsXsellRecord, false);
                        if ($xsellClass->save(false)) {

                        } else {
                            $this->messageAdd($xsellClass->getErrorSummary(true));
                        }
                        unset($xsellClass);
                    }
                    unset($xsellId);
                }
                unset($productsXsellRecord);
            }
            /**
             * Up Sell
             */
            $upsellModel = \common\helpers\Extensions::getModel('UpSell', 'ProductsUpsell');
            if (!empty($upsellModel)) {

                foreach ($this->productsUpsellRecordArray as $productsUpsellRecord) {
                    $upsellId = (int)(isset($productsUpsellRecord['upsell_id']) ? $productsUpsellRecord['upsell_id'] : 0);
                    unset($productsUpsellRecord['products_id']);
                    unset($productsUpsellRecord['upsell_id']);
                    if ($upsellId > 0) {
                        $upsellClass = $upsellModel::find()->where(['products_id' => $this->productId, 'upsell_id' => $upsellId])->one();
                        if (!($upsellClass instanceof $upsellModel)) {
                            $upsellClass = new $upsellModel();
                            $upsellClass->loadDefaultValues();
                            $upsellClass->products_id = $this->productId;
                            $upsellClass->upsell_id = $upsellId;
                        }
                        $upsellClass->setAttributes($productsUpsellRecord, false);
                        if ($upsellClass->save(false)) {

                        } else {
                            $this->messageAdd($upsellClass->getErrorSummary(true));
                        }
                        unset($upsellClass);
                    }
                    unset($upsellId);
                }
                unset($productsUpsellRecord);
            }
            /**
             * Properties To Propducts
             */
            foreach ($this->propertiesToPropductsRecordArray as $productsPropertiesRecord) {
                $propertyId = (int)(isset($productsPropertiesRecord['properties_id']) ? $productsPropertiesRecord['properties_id'] : 0);
                $valueId = (int)(isset($productsPropertiesRecord['values_id']) ? $productsPropertiesRecord['values_id'] : 0);
                unset($productsPropertiesRecord['products_id']);
                unset($productsPropertiesRecord['properties_id']);
                unset($productsPropertiesRecord['values_id']);
                if ($propertyId > 0 && $valueId > 0) {
                    $productProperyClass = \common\models\Properties2Propducts::find()->where(['products_id' => $this->productId, 'properties_id' => $propertyId, 'values_id' => $valueId])->one();
                    if (!($productProperyClass instanceof \common\models\Properties2Propducts)) {
                        $productProperyClass = new \common\models\Properties2Propducts();
                        $productProperyClass->loadDefaultValues();
                        $productProperyClass->products_id = $this->productId;
                        $productProperyClass->properties_id = $propertyId;
                        $productProperyClass->values_id = $valueId;
                    }
                    $productProperyClass->setAttributes($productsPropertiesRecord, false);
                    if ($productProperyClass->save(false)) {

                    } else {
                        $this->messageAdd($productProperyClass->getErrorSummary(true));
                    }
                    unset($productProperyClass);
                }
                unset($valueId);
                unset($propertyId);
            }
            unset($productsPropertiesRecord);
            /**
             * Videos
             */
            foreach ($this->productsVideosRecordArray as $key => $productsVideosRecord) {
                $languageId = (int)(isset($productsVideosRecord['language_id']) ? $productsVideosRecord['language_id'] : -1);
                $videoId = (int)(isset($productsPropertiesRecord['video_id']) ? $productsPropertiesRecord['video_id'] : 0);
                unset($productsVideosRecord['products_id']);
                unset($productsVideosRecord['language_id']);
                unset($productsPropertiesRecord['video_id']);
                if ($languageId >= 0) {
                    $productVideoClass = \common\models\ProductsVideos::find()->where(['products_id' => $this->productId, 'video_id' => $videoId, 'language_id' => $languageId])->one();
                    if (!($productVideoClass instanceof \common\models\ProductsVideos)) {
                        $productVideoClass = new \common\models\ProductsVideos();
                        $productVideoClass->loadDefaultValues();
                        $productVideoClass->products_id = $this->productId;
                        $productVideoClass->language_id = $languageId;
                    }
                    $productVideoClass->setAttributes($productsVideosRecord, false);
                    if ($productVideoClass->save(false)) {
                        $this->productsVideosRecordArray[$key] = $productVideoClass->toArray();
                    } else {
                        $this->messageAdd($productVideoClass->getErrorSummary(true));
                    }
                    unset($productVideoClass);
                }
                unset($videoId);
                unset($languageId);
            }
            unset($productsVideosRecord);
            /**
             * Gift Wrap
             */
            foreach ($this->giftWrapProductsRecordArray as $giftWrapProductsRecord) {
                $currenciesId = (int)(isset($giftWrapProductsRecord['currencies_id']) ? $giftWrapProductsRecord['currencies_id'] : -1);
                $groupsId = (int)(isset($giftWrapProductsRecord['groups_id']) ? $giftWrapProductsRecord['groups_id'] : -1);
                unset($giftWrapProductsRecord['products_id']);
                unset($giftWrapProductsRecord['currencies_id']);
                unset($giftWrapProductsRecord['groups_id']);
                if (($currenciesId >= 0) and ($groupsId >= 0)) {
                    $giftWrapClass = \common\models\GiftWrapProducts::find()->where(['products_id' => $this->productId, 'groups_id' => $groupsId, 'currencies_id' => $currenciesId])->one();
                    if (!($giftWrapClass instanceof \common\models\GiftWrapProducts)) {
                        $giftWrapClass = new \common\models\GiftWrapProducts();
                        $giftWrapClass->loadDefaultValues();
                        $giftWrapClass->products_id = $this->productId;
                        $giftWrapClass->groups_id = $groupsId;
                        $giftWrapClass->currencies_id = $currenciesId;
                    }
                    $giftWrapClass->setAttributes($giftWrapProductsRecord, false);
                    if ($giftWrapClass->save(false)) {

                    } else {
                        $this->messageAdd($giftWrapClass->getErrorSummary(true));
                    }
                    unset($giftWrapClass);
                }
                unset($groupsId);
                unset($currenciesId);

            }
            unset($giftWrapProductsRecord);
            /**
             * Notes
             */
            foreach ($this->productsNotesRecordArray as $key => $productsNotesRecord) {
                $noteId = (int)(isset($productsNotesRecord['products_notes_id']) ? $productsNotesRecord['products_notes_id'] : 0);
                unset($productsVideosRecord['products_id']);
                unset($productsVideosRecord['products_notes_id']);
                $noteClass = \common\models\ProductsNotes::find()->where(['products_id' => $this->productId, 'products_notes_id' => $noteId])->one();
                if (!($noteClass instanceof \common\models\ProductsNotes)) {
                    $noteClass = new \common\models\ProductsNotes();
                    $noteClass->loadDefaultValues();
                    $noteClass->products_id = $this->productId;
                }
                $noteClass->setAttributes($productsNotesRecord, false);
                if ($noteClass->save(false)) {
                    $this->productsNotesRecordArray[$key] = $noteClass->toArray();
                } else {
                    $this->messageAdd($noteClass->getErrorSummary(true));
                }
                unset($noteClass);
                unset($noteId);
            }
            unset($productsNotesRecord);
            /**
             * Suppliers Products
             */
            foreach ($this->suppliersProductsRecordArray as $suppliersProductsRecord) {
                $supplierId = (int)(isset($suppliersProductsRecord['suppliers_id']) ? $suppliersProductsRecord['suppliers_id'] : 0);
                $uprid = (isset($suppliersProductsRecord['uprid']) ? $suppliersProductsRecord['uprid'] : 0);
                unset($suppliersProductsRecord['products_id']);
                unset($suppliersProductsRecord['suppliers_id']);
                if ($supplierId > 0 && !empty($uprid)) {
                    $suppliersProductClass = \common\models\SuppliersProducts::find()->where(['products_id' => $this->productId, 'uprid' => $uprid, 'suppliers_id' => $supplierId])->one();
                    if (!($suppliersProductClass instanceof \common\models\SuppliersProducts)) {
                        $suppliersProductClass = new \common\models\SuppliersProducts();
                        $suppliersProductClass->loadDefaultValues();
                        $suppliersProductClass->products_id = $this->productId;
                        $suppliersProductClass->uprid = $uprid;
                        $suppliersProductClass->suppliers_id = $supplierId;
                    }
                    $suppliersProductClass->setAttributes($suppliersProductsRecord, false);
                    if ($suppliersProductClass->save(false)) {

                    } else {
                        $this->messageAdd($suppliersProductClass->getErrorSummary(true));
                    }
                    unset($suppliersProductClass);
                }
                unset($uprid);
                unset($supplierId);
            }
            unset($suppliersProductsRecord);
            /**
             * Warehouses Products
             */
            foreach ($this->warehousesProductsRecordArray as $warehousesProductsRecord) {
                $warehouseId = (int)(isset($warehousesProductsRecord['warehouse_id']) ? $warehousesProductsRecord['warehouse_id'] : 0);
                $supplierId = (int)(isset($warehousesProductsRecord['suppliers_id']) ? $warehousesProductsRecord['suppliers_id'] : 0);
                $locationId = (int)(isset($warehousesProductsRecord['location_id']) ? $warehousesProductsRecord['location_id'] : 0);
                $uprid = (isset($warehousesProductsRecord['products_id']) ? $warehousesProductsRecord['products_id'] : $this->productId);
                unset($warehousesProductsRecord['products_id']);
                unset($warehousesProductsRecord['warehouse_id']);
                unset($warehousesProductsRecord['suppliers_id']);
                unset($warehousesProductsRecord['location_id']);
                unset($warehousesProductsRecord['prid']);
                if ($warehouseId > 0 && $supplierId > 0 && !empty($uprid)) {
                    $warehouseProductClass = \common\models\WarehousesProducts::find()->where(['products_id' => $uprid, 'suppliers_id' => $supplierId, 'warehouse_id' => $warehouseId, 'location_id' => $locationId])->one();
                    if (!($warehouseProductClass instanceof \common\models\WarehousesProducts)) {
                        $warehouseProductClass = new \common\models\WarehousesProducts();
                        $warehouseProductClass->loadDefaultValues();
                        $warehouseProductClass->products_id = $uprid;
                        $warehouseProductClass->suppliers_id = $supplierId;
                        $warehouseProductClass->warehouse_id = $warehouseId;
                        $warehouseProductClass->location_id = $locationId;
                        $warehouseProductClass->prid = $this->productId;
                    }
                    $warehouseProductClass->setAttributes($warehousesProductsRecord, false);
                    if ($warehouseProductClass->save(false)) {

                    } else {
                        $this->messageAdd($warehouseProductClass->getErrorSummary(true));
                    }
                    unset($warehouseProductClass);
                }
                unset($uprid);
                unset($locationId);
                unset($supplierId);
                unset($warehouseId);
            }
            unset($warehousesProductsRecord);

            if ($extScl = \common\helpers\Acl::checkExtensionAllowed('StockControl', 'allowed')) {
                $extScl::updateApiProductSave($this);
            }

            // ...
            // OLD SEO REDIRECT
            $seoModel = \common\helpers\Extensions::getModel('SeoRedirectsNamed', 'SeoRedirectsNamed');
            if (!empty($seoModel)) {
                foreach ($this->oldSeoRedirectArray as $seoRedirectArray) {
                    try {
                        $platformId = (int)(isset($seoRedirectArray['platform_id']) ? $seoRedirectArray['platform_id'] : 0);
                        if ($platformId > 0) {
                            $languageId = (int)(isset($seoRedirectArray['language_id']) ? $seoRedirectArray['language_id'] : 0);
                            if (isset($seoRedirectArray['language_code'])) {
                                $languageId = $this->getLanguageIdByCode($seoRedirectArray['language_code'], $languageId);
                            }
                            $searchArray = [
                                'platform_id' => $platformId,
                                'language_id' => $languageId,
                                'redirects_type' => 'product',
                                'owner_id' => $this->productId,
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

            // SPECIALS
            foreach ($this->specialsArray as $special) {
                try {
                    $specialId = $special['specials_id'] ?? null;
                    unset($special['products_id']);
                    $searchArray = [
                        'products_id' => $this->productId
                    ];
                    $specialRecord = null;
                    if ( $specialId > 0) {
                        $searchArray['specials_id'] = $specialId;
                        $specialRecord = \common\models\Specials::findOne($searchArray);
                    }
                    if (empty($specialRecord)) {
                        $specialRecord = new \common\models\Specials();
                        $specialRecord->loadDefaultValues();
                        $specialRecord->setAttributes($searchArray);
                    }
                    $specialRecord->setAttributes($special);
                    $specialRecord->save();

                    $searchPricesArray = [
                        'specials_id' => $specialRecord->specials_id,
                        'groups_id' => 0,
                        'currencies_id' => 0,
                    ];
                    $specialPricesRecord = \common\models\SpecialsPrices::findOne($searchPricesArray);
                    if (empty($specialPricesRecord)) {
                        $specialPricesRecord = new \common\models\SpecialsPrices();
                        $specialPricesRecord->loadDefaultValues();
                        $specialPricesRecord->setAttributes($searchPricesArray);
                    }
                    $specialPricesRecord->specials_new_products_price = $specialRecord->specials_new_products_price;
                    $specialPricesRecord->save();

                }  catch (\Throwable $exc) {
                    \Yii::warning($exc->getMessage() . "\n" . $exc->getTraceAsString());
                }
                unset($specialRecord);
                unset($searchArray);
            }
            // END OF SPECIALS

            $return = $this->productId;
        } else {
            $this->messageAdd($productClass->getErrorSummary(true));
        }
        unset($productClass);
        unset($isReplace);
        return $return;
    }

    private function findImageId($origFilename)
    {
        $check = \common\models\ProductsImages::find()
                ->select(['pi.products_images_id'])
                ->from(\common\models\ProductsImages::tableName() . " pi")
                ->leftJoin(\common\models\ProductsImagesDescription::tableName() . " pid", "pi.products_images_id = pid.products_images_id")
                ->where(['pi.products_id' => $this->productId, 'pid.language_id' => 0, 'pid.orig_file_name' => $origFilename ])
                ->asArray()
                ->one();
        return $check['products_images_id']??null;
    }

    private function attachNewImage($newProductsImages)
    {
        if ($this->productId == 0) {
            return false;
        }

        $origFilename = (string)$newProductsImages['file_name'];
        $products_images_id = $this->findImageId($origFilename);
        if ($products_images_id > 0) {
            return false;
        }

        try {
            $image_location = DIR_FS_CATALOG . 'images' . DIRECTORY_SEPARATOR . 'products' . DIRECTORY_SEPARATOR . $this->productId . DIRECTORY_SEPARATOR;
            if (!file_exists($image_location)) {
                mkdir($image_location, 0777, true);
            }
            $imgdata = file_get_contents($newProductsImages['file_url']);
            if ($imgdata !== false) {

                $languageId = (int)\Yii::$app->settings->get('languages_id');
                $platformId = \common\classes\platform::defaultId();

                $hashName = md5($origFilename . "_" . date('dmYHis') . "_" . microtime(true));

                $ProductsImagesClass = new \common\models\ProductsImages();
                $ProductsImagesClass->loadDefaultValues();
                $ProductsImagesClass->default_image = 1;
                $ProductsImagesClass->image_status = 1;
                $ProductsImagesClass->products_id = $this->productId;
                if (!$ProductsImagesClass->save(false)) {
                    $this->messageAdd($ProductsImagesClass->getErrorSummary(true));
                    return false;
                }
                $imageId = $ProductsImagesClass->products_images_id;
                unset($ProductsImagesClass);

                $image_location .=  $imageId . DIRECTORY_SEPARATOR;
                if (!file_exists($image_location)) {
                    mkdir($image_location, 0777, true);
                }
                $new_name = $image_location . $hashName;
                $fp = fopen($new_name, 'w+');
                fwrite($fp, $imgdata);
                fclose($fp);
                unset($new_name);

                $filename = \common\helpers\Product::getSeoName($this->productId, $languageId, $platformId);
                $uploadExtension = strtolower(pathinfo($origFilename, PATHINFO_EXTENSION));
                $filename .= '.' . $uploadExtension;

                $productName = \common\helpers\Product::get_products_name($this->productId);

                $Images = new \common\classes\Images();
                $Images->createImages($this->productId, $imageId, $hashName, $filename, '');
                unset($Images);

                $ProductsImagesDescriptionClass = new \common\models\ProductsImagesDescription();
                $ProductsImagesDescriptionClass->loadDefaultValues();
                $ProductsImagesDescriptionClass->language_id = 0;
                $ProductsImagesDescriptionClass->file_name = $filename;
                $ProductsImagesDescriptionClass->hash_file_name = $hashName;
                $ProductsImagesDescriptionClass->orig_file_name = $origFilename;
                $ProductsImagesDescriptionClass->image_title = $productName;
                $ProductsImagesDescriptionClass->image_alt = $productName;
                $ProductsImagesDescriptionClass->products_images_id = (int)$imageId;
                if (!$ProductsImagesDescriptionClass->save(false)) {
                    $this->messageAdd($ProductsImagesDescriptionClass->getErrorSummary(true));
                    return false;
                }
                unset($ProductsImagesDescriptionClass);
                unset($productName);
                unset($filename);
                unset($imageId);
                unset($hashName);
                unset($platformId);
                unset($languageId);
                return true;
            }
            unset($imgdata);
            unset($image_location);
        } catch (\Exception $e) {
            $this->messageAdd($e->getMessage());
        }
        unset($origFilename);
        return false;
    }
}
