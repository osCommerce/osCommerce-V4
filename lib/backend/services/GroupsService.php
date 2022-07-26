<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 7/26/18
 * Time: 6:52 PM
 */

namespace backend\services;


use common\models\repositories\CategoriesRepository;
use common\models\repositories\GroupsRepository;
use common\models\repositories\ProductsRepository;

class GroupsService
{
    /**
     * @var ProductsRepository
     */
    private $productsRepository;
    /**
     * @var CategoriesRepository
     */
    private $categoriesRepository;
    /**
     * @var GroupsRepository
     */
    private $groupsRepository;

    public function __construct(GroupsRepository $groupsRepository,ProductsRepository $productsRepository, CategoriesRepository $categoriesRepository)
    {
        $this->productsRepository = $productsRepository;
        $this->categoriesRepository = $categoriesRepository;
        $this->groupsRepository = $groupsRepository;
    }

    public function load_tree_slice($categoryId = 0, $languageId = 1)
    {
        $treeInitData = [];
        $parentCategories = $this->categoriesRepository->findParentCategories($categoryId, $languageId);
        if ($parentCategories) {
            foreach ($parentCategories as $category) {
                $category['folder'] = true;
                $category['lazy'] = true;
                $category['selected'] = 0;
                $treeInitData[] = $category;
            }
        }
        $productsAllKey = $this->productsRepository->findCategoryProductsKey($categoryId,$languageId);
        if($productsAllKey){
            foreach ($productsAllKey as $product) {
                $product['selected'] = 0;
                $treeInitData[] = $product;
            }
        }
        return $treeInitData;
    }
    public function getAssignedCatalog(int $groupId, int $languageId, $active = false)
    {
        $assignedKey = [];
        $assignedProducts = $this->productsRepository->findAssignedGroupProductsCatalog($groupId,$languageId,$active);
        $assignedCategories = $this->categoriesRepository->findAssignedGroupCategoriesCatalog($groupId,$languageId,$active);
        if($assignedProducts){
            foreach ($assignedProducts as $product) {
                $_key = 'p' . (int) $product['id'] . '_' . $product['cid'];
                $assignedKey[$_key] = $_key;
            }
        }
        if($assignedCategories) {
            foreach ($assignedCategories as $category) {
                $_key = 'c'.$category['id'];
                $assignedKey[$_key] = $_key;
            }
        }
        return $assignedKey;
    }
    public function get_category_children(&$children, $categoryId,$languages_id) {
        if (!is_array($children)) {
            $children = [];
        }
        $slice =  $this->load_tree_slice($categoryId,$languages_id);
        foreach ($slice as $item) {
            $key = $item['key'];
            $children[] = $key;
            if ($item['folder'] ?? null) {
                $this->get_category_children($children, intval(substr($item['key'], 1)),$languages_id);
            }
        }
    }

    /**
     * @param int $groupId
     * @param $productsIds
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function addProductToGroup(int $groupId,$productsIds)
    {
        $this->groupsRepository->addProductToGroup($groupId,$productsIds);
    }

    /**
     * @param int $groupId
     * @param array|int $productsIds
     */
    public function removeProductToGroup(int $groupId,$productsIds)
    {
        $this->groupsRepository->removeProductToGroup($groupId,$productsIds);
    }

    /**
     * @param int $groupId
     * @param $productsIds
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function addCategoryToGroup(int $groupId, $productsIds)
    {
        $this->groupsRepository->addCategoryToGroup($groupId,$productsIds);
    }

    /**
     * @param int $groupId
     * @param array|int $productsIds
     */
    public function removeCategoryToGroup(int $groupId,$productsIds)
    {
        $this->groupsRepository->removeCategoryToGroup($groupId,$productsIds);
    }

    /**
     * @param $categoryIds
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function addCategoryToAllGroups($categoryIds)
    {
        $this->groupsRepository->addCategoryToAllGroups($categoryIds);
    }

    /**
     * @param $productIds
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function addProductToAllGroups($productIds)
    {
        $this->groupsRepository->addProductToAllGroups($productIds);
    }

    /**
     * @param bool $asArray
     * @return array|\common\models\Groups[]
     */
    public function getAll(bool $asArray = false)
    {
        return $this->groupsRepository->getAll();
    }

    /**
     * @param bool $defaultValue
     * @param bool $forceDefault
     * @return array
     */
    public function getBonusPointsCurrencyRates($defaultValue = false, bool $forceDefault = false): array
    {
        $groups = $this->getAll(true);
        $coefficient = [];
        foreach ($groups as $group) {
            if ($forceDefault) {
                $coefficient[(int)$group['groups_id']] = $defaultValue;
            } else {
                $coefficient[(int)$group['groups_id']] = (float)$group['bonus_points_currency_rate'] > 0.0 ? (float)$group['bonus_points_currency_rate'] : $defaultValue;
            }

        }
        return $coefficient;
    }

    /**
     * @param int $groupId
     * @param bool|float $defaultValue
     * @param bool $forceDefault
     * @return bool|float
     */
    public function getBonusPointsGroupCurrencyRate(int $groupId, $defaultValue = false, bool $forceDefault = false)
    {
        if ($forceDefault) {
            return $defaultValue;
        }
        $group = $this->groupsRepository->findById($groupId, true);
        if (!$group) {
            return $defaultValue;
        }
        return (float)$group['bonus_points_currency_rate'] > 0.0 ? (float)$group['bonus_points_currency_rate'] : $defaultValue;
    }
}
