<?php

namespace common\classes\CPC;

use \common\helpers\Extensions;

/**
 * Base for CCP:
 *  - implements main interface
 *  - incapsulate base query functions
 *  - stubs for cache functions
 */
abstract class CPCBase implements CPCGetInterface, CPCCacheInterface, CPCInterface
{
    protected static $CheckGroupPriceForOldProjects = false; // products_prices.products_group_price <> -1

    /**
     * Returns [categoriesId => count] array
     * @param $platformId
     * @param $groupId
     * @param $categoriesIds
     * @return array An empty array is returned if the query results in nothing.
     */
    protected static function runQuery($platformId, $groupId = 0, $categoriesIds = null, $optimizeMsg = true): array
    {
        $res = self::getQuery($platformId, $groupId, $categoriesIds)->column();
        if (is_array($categoriesIds)) {
            foreach ($categoriesIds as $catId) {
                if (!isset($res[$catId])) {
                    $res[$catId] = 0;
                }
            }
        }
        return $res;
    }

    protected static function getQuery($platformId, $groupId = 0, $categoriesIds = null)
    {
        $platformId = (int) $platformId;
        $q = \common\models\Products2Categories::find()->alias('p2c')
            ->select('count(*) as total, c1.categories_id')
            ->FilterWhere(['c1.categories_id' => $categoriesIds])
            // platform
            ->innerJoinWith(['platformsCategories pl2c' => function ($query) use ($platformId) {$query->andOnCondition(['pl2c.platform_id' => $platformId]);}], false)
            ->innerJoinWith(['platformsProducts pl2p'  => function ($query) use ($platformId) {$query->andOnCondition(['pl2p.platform_id' => $platformId]);}], false)
            // inner categories
            ->innerJoinWith('categories c', false)
            ->innerJoin('categories c1', 'c.categories_left >= c1.categories_left AND c.categories_right <= c1.categories_right AND c.categories_status = 1')
            // products
            ->innerJoinWith('products p')
            ->andWhere('p.products_status = 1')
            ->groupBy('c1.categories_id')
            ->asArray()
            ->indexBy('categories_id');

        if ($groupId>0 && ($gCat = Extensions::getModel('UserGroupsRestrictions', 'GroupsCategories')) && ($gProd = Extensions::getModel('UserGroupsRestrictions', 'GroupsProducts'))) {
            $q = $q
                ->innerJoin($gCat::tableName() . ' g2c', 'g2c.categories_id = c1.categories_id AND g2c.groups_id = :groupId', ['groupId' => $groupId])
                ->innerJoin($gProd::tableName() . ' g2p', 'g2p.products_id = p2c.products_id AND g2p.groups_id = :groupId', ['groupId' => $groupId]);
        }

        if (self::$CheckGroupPriceForOldProjects) {
            $q = $q
                ->joinWith(['productsPrices pgp' => function ($query) use ($groupId) {$query->andOnCondition(['pgp.currencies_id' => 0, 'pgp.groups_id' => $groupId]);}], false)
                ->andWhere('COALESCE(pgp.products_group_price,1) != -1');
        }

        return $q;
    }

    protected static function getQueryExists($platformId, $groupId = 0, $categoriesIds = null)
    {
        $platformId = (int) $platformId;
        $q = \common\models\Categories::find()->alias('c1')
            ->select('count(*) as total, c1.categories_id')
            ->FilterWhere(['c1.categories_id' => $categoriesIds])
            // platform
            ->innerJoinWith(['platformsCategories pl2c' => function ($query) use ($platformId) {$query->andOnCondition(['pl2c.platform_id' => $platformId]);}], false)
            ->innerJoinWith(['platformsProducts pl2p'  => function ($query) use ($platformId) {$query->andOnCondition(['pl2p.platform_id' => $platformId]);}], false)
            // inner categories
            ->innerJoinWith('categories c', false)
            ->andWhere('c.categories_left >= c1.categories_left AND c.categories_right <= c1.categories_right AND c.categories_status = 1')
            // products
            ->joinWith(['products p' => function ($query) use ($platformId) {$query->andOnCondition(['p.products_status' => 1]);}], false)
            ->asArray()
            ->indexBy('categories_id');

        if ($groupId>0 && ($gCat = Extensions::getModel('UserGroupsRestrictions', 'GroupsCategories')) && ($gProd = Extensions::getModel('UserGroupsRestrictions', 'GroupsProducts'))) {
            $q = $q
                ->innerJoin($gCat::tableName() . ' g2c', 'g2c.categories_id = c1.categories_id AND g2c.groups_id = :groupId', ['groupId' => $groupId])
                ->innerJoin($gProd::tableName() . ' g2p', 'g2p.products_id = products_id AND g2c.groups_id => :groupId', ['groupId' => $groupId]);
        }

        if (self::$CheckGroupPriceForOldProjects) {
            $q = $q
                ->joinWith(['productsPrices pgp' => function ($query) use ($groupId) {$query->andOnCondition(['pgp.currencies_id' => 0, 'pgp.groups_id' => $groupId]);}], false)
                ->andWhere('COALESCE(pgp.products_group_price,1) != -1');
        }

        return $q;

    }


    /**
     * @inheritDoc
     */
    public static function getAllCategories($platformId, $groupId = 0): array
    {
        $q = \common\models\Categories::find()->alias('c')
            ->select('c.categories_id')
            ->innerJoinWith('platforms pl', false)
            ->where(['pl.platform_id' => $platformId]);

        if ($groupId>0 && ($gCat = Extensions::getModel('UserGroupsRestrictions', 'GroupsCategories'))) {
            $q = $q->innerJoin($gCat::tableName() . ' g2c', 'g2c.categories_id = c.categories_id AND g2c.groups_id = :groupId', ['groupId' => $groupId]);
        }
        return static::getCategories($q->column(), $platformId, $groupId);
    }

    /** stabs for CPCCacheInterface */

    public static function getCountAndCache($platformId, $groupId = 0)
    {
        return static::getAllCategories($platformId, $groupId);
    }

    public static function getCached($platformId, $groupId = 0) {}
    public static function invalidateProducts($productIds): void {}
    public static function invalidateCategories($categoriesIds): void {}
    public static function invalidatePlatforms($platformsIds): void {}
    public static function invalidateGroups($groupsIds): void {}
    public static function invalidateAll(): void {}


}