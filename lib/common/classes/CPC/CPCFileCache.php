<?php

namespace common\classes\CPC;

use yii\caching\TagDependency;

class CPCFileCache extends CPCBase implements CPCCacheInterface
{

    static $cache;

    /**
     * @inheritDoc
     */
    public static function getCategories($categoriesIds, $platformId, $groupId = 0): array
    {
        $res = [];
        $notCachedIds = self::addResultFromCache($res, $categoriesIds, $platformId, $groupId);
        if (!empty($notCachedIds)) {
            self::updateCacheWithNewCats($notCachedIds, $platformId, $groupId);
            self::addResultFromCache($res, $notCachedIds, $platformId, $groupId);
        }
        return $res;
    }

    /**
     * @inheritDoc
     */
    public static function getCached($platformId, $groupId = 0)
    {
        $cacheId = self::getCacheName($platformId, $groupId);
        if (!isset(self::$cache[$cacheId]) && ($arr = \Yii::$app->getCache()->get($cacheId))) {
            self::$cache[$cacheId] = $arr;
        }
        return self::$cache[$cacheId] ?? null;
    }


    /**
     * @inheritDoc
     */
    public static function invalidateProducts($productIds): void
    {
        $categoriesIds = \common\models\Products2Categories::find()
            ->select('categories_id')
            ->where(['products_id' => $productIds])
            ->asArray()
            ->column();
        self::invalidateCategories($categoriesIds);
    }

    /**
     * @inheritDoc
     */
    public static function invalidateCategories($categoriesIds): void
    {
        if (empty($categoriesIds)) return;
        if (!is_array($categoriesIds)) $categoriesIds = [$categoriesIds];
        // include all parents
        $categoriesIds = \common\models\Categories::find()->alias('c')
            ->withNestedCategories()
            ->select('c.categories_id')
            ->FilterWhere(['c1.categories_id' => $categoriesIds])
            //->andWhere('c.categories_status = 1')
            ->asArray()
            ->column();

        $platformsIds = \common\models\PlatformsCategories::find()
            ->select('platform_id')
            ->where(['categories_id' => $categoriesIds])
            ->distinct()
            ->column();
        if (\common\helpers\Extensions::isAllowed('UserGroupsRestrictions')) {
            $groupsIds = \common\models\Groups::find()->select('groups_id')->column();
            if (count($groupsIds) * count($platformsIds) > 10) {
                // clear cache, too expensive to modify the cache in a large number of files
                self::invalidatePlatforms($platformsIds);
            } else {
                foreach ($platformsIds as $platformsId) {
                    foreach ($groupsIds as $groupsId) {
                        self::updateCacheByRemovingCats($categoriesIds, $platformsId, $groupsId);
                    }
                }
            }
        } else {
            foreach ($platformsIds as $platformsId) {
                self::updateCacheByRemovingCats($categoriesIds, $platformsId);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public static function invalidateAll(): void
    {
        self::$cache = null;
        TagDependency::invalidate(\Yii::$app->cache, 'cpcc_all');
    }

    /**
     * @inheritDoc
     */
    public static function invalidateGroups($groupsIds): void
    {
        $groupsIds = is_array($groupsIds) ? $groupsIds : [(int) $groupsIds];
        foreach ($groupsIds as $groupsId) {
            TagDependency::invalidate(\Yii::$app->cache, 'cpcc_group'.$groupsId);
        }
    }

    /**
     * @inheritDoc
     */
    public static function invalidatePlatforms($platformsIds): void
    {
        if (!is_array($platformsIds)) $platformsIds = [$platformsIds];
        if (!empty($platformsIds)) {
            self::$cache = null;
            foreach ($platformsIds as $platformId) {
                TagDependency::invalidate(\Yii::$app->cache, 'cpcc_platform'.$platformId);
            }
        }
    }

    /**
     * @param $res result array [ categoryId => productsCount ]
     * @param int|array $categoriesIds categoryId/Ids
     * @param $platformId
     * @param $groupId
     * @return array|mixed non cached Ids
     */
    private static function addResultFromCache(&$res, $categoriesIds, $platformId, $groupId)
    {
        $notCachedIds = [];
        if (!is_array($categoriesIds)) $categoriesIds = [$categoriesIds];
        $cached = self::getCached($platformId, $groupId);
        if (empty($cached)) {
            $notCachedIds = $categoriesIds;
        } else {
            foreach ($categoriesIds as $categoryId) {
                if (isset($cached[$categoryId])) {
                    $res[$categoryId] = $cached[$categoryId];
                } else {
                    $notCachedIds[] = $categoryId;
                }
            }
        }
        return $notCachedIds;
    }

    private static function updateCacheWithNewCats(array $categoriesIds, $platformId, $groupId = 0)
    {
        $res = parent::runQuery($platformId, $groupId, $categoriesIds);
        foreach($categoriesIds as $categoriyId) {
            if (!isset($res[$categoriyId])) {
                $res[$categoriyId] = 0;
            }
        }
        $res = array_replace(self::getCached($platformId, $groupId) ?? [], $res);
        self::setCache($platformId, $groupId, $res);
    }

    private static function updateCacheByRemovingCats(array $categoriesIds, $platformId, $groupId = 0)
    {
        $res = self::getCached($platformId, $groupId);
        if (!empty($res)) {
            foreach ($categoriesIds as $categoriesId) {
                unset($res[$categoriesId]);
            }
            self::setCache($platformId, $groupId, $res);
        }
    }

    private static function setCache($platformId, $groupId, array $res)
    {
        $cacheId = self::getCacheName($platformId, $groupId);
        \Yii::$app->getCache()->set($cacheId, $res, 0, new TagDependency(['tags' => ['cpcc_all', 'cpcc_platform'.$platformId, 'cpcc_group'.$groupId]]) );
        self::$cache[$cacheId] = $res;
    }

    private static function getCacheName($platformId, $groupId = 0)
    {
        return sprintf('CategoriesProductCountCache%d-%d', (int) $platformId, (int) $groupId);
    }

}