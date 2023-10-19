<?php

namespace common\classes\CPC;

/**
 * Cache for CPC
 */
interface CPCCacheInterface
{
    /**
     * Returns cached array [categoryId => productCount] for platform and group if cached. Return null if not cached
     * @param mixed $platformId
     * @param mixed $groupId
     * @return null|array [categoryId => productCount]
     */
    public static function getCached($platformId, $groupId = 0);

    /**
     * Invalidated cache for categories related for specified products
     * @param mixed $productIds single productId or array of productsId
     */
    public static function invalidateProducts($productIds): void;

    /**
     * Invalidated cache for specified categories
     * @param mixed|array $categoriesIds single categoryId or array of categoryId
     * @return void
     */
    public static function invalidateCategories($categoriesIds): void;

    /**
     * Invalidates cache for specified platforms
     * @return void
     */
    public static function invalidatePlatforms($platformsIds): void;

    /**
     * Invalidates cache for specified groups
     * @return void
     */
    public static function invalidateGroups($groupsIds): void;

    /**
     * Invalidates all cache for products count
     * @return void
     */
    public static function invalidateAll(): void;

}