<?php

namespace common\classes\CPC;

/**
 * CPC - Counts of products in categories
 */
interface CPCGetInterface
{
    /**
     * Returns array [categoryId => productCount]
     * @param mixed|array $categoriesIds single categoryId or array of categoryId
     * @param mixed $platformId
     * @param mixed $groupId
     * @return array [categoryId => productCount]
     */
    public static function getCategories($categoriesIds, $platformId, $groupId = 0): array;

    public static function getAllCategories($platformId, $groupId = 0): array;

}