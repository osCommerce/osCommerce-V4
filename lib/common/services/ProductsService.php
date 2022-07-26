<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 7/26/18
 * Time: 6:52 PM
 */

namespace common\services;

use common\models\repositories\CategoriesRepository;
use common\models\repositories\ProductsOptionsRepository;
use common\models\repositories\ProductsRepository;

class ProductsService
{
    /** @var ProductsRepository */
    private $productsRepository;
    /** @var CategoriesRepository */
    private $categoriesRepository;
    /** @var ProductsOptionsRepository */
    private $productsOptionsRepository;

    public function __construct(
        ProductsRepository $productsRepository,
        ProductsOptionsRepository $productsOptionsRepository,
        CategoriesRepository $categoriesRepository
    )
    {
        $this->productsRepository = $productsRepository;
        $this->categoriesRepository = $categoriesRepository;
        $this->productsOptionsRepository = $productsOptionsRepository;
    }

    /**
     * @param string $condition
     *
     * @return int
     */
    public function onQuoteAllProducts($condition = '1')
    {
        return $this->productsRepository->updateAll(['request_quote' => 1,'request_quote_out_stock' => 1], $condition);
    }

    /**
     * @param string $condition
     *
     * @return int
     */
    public function offQuoteAllProducts($condition = '1')
    {
        return $this->productsRepository->updateAll(['request_quote' => 0], $condition);
    }

    public function getAssignedQuoteProductCatalog($active = false)
    {
        return $this->getAssignedCatalog('request_quote',$active);
    }

    private function getAssignedCatalog($productField = 'products_status',$active = false)
    {
        $assignedKey = [];
        $assignedProducts = $this->productsRepository->findAssignedCatalog($productField,$active);
        if($assignedProducts){
            foreach ($assignedProducts as $product) {
                $_key = 'p' . (int) $product['id'] . '_' . $product['cid'];
                $assignedKey[$_key] = $_key;
            }
        }
        return $assignedKey;
    }
    public function load_tree_slice($categoryId = 0, $languageId = 1) {
        $treeInitData = [];
        $parentCategories = $this->categoriesRepository->findParentCategories($categoryId,$languageId);
        if($parentCategories) {
            foreach ($parentCategories as $category) {
                $category['folder']   = true;
                $category['lazy']     = true;
                $category['selected'] = 0;
                $treeInitData[]       = $category;
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

    public function get_category_children(&$children, $categoryId,$languages_id) {
        if (!is_array($children)) {
            $children = [];
        }
        $slice =  $this->load_tree_slice($categoryId,$languages_id);
        foreach ($slice as $item) {
            $key = $item['key'];
            $children[] = $key;
            if ($item['folder']) {
                $this->get_category_children($children, intval(substr($item['key'], 1)),$languages_id);
            }
        }
    }

    /**
     * @param int $optionId
     * @param int $languageId
     * @param bool $asArray
     * @return array|\common\models\ProductsOptions|null
     */
    public function findOptionByPrimary(int $optionId, int $languageId, bool $asArray = false)
    {
        return $this->productsOptionsRepository->findByPrimary($optionId, $languageId, $asArray);
    }
}
