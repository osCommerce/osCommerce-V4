<?php

namespace common\helpers;

use common\models\CategoriesDescription;

class CategoriesDescriptionHelper
{
    /**
     * @param int $currentCategoryId
     * @param int $languagesId
     * @param int $session
     * @param int|bool $customerGroupsId
     * @param bool $groupJoin
     * @param bool $groupWhere
     *
     * @return array|null
     */
    public static function getCategoriesDescriptionList($currentCategoryId, $languagesId, $session, $customerGroupsId = false, $groupJoin = false, $groupWhere = false)
    {
        $languages_id = \Yii::$app->settings->get('languages_id');
        $category = CategoriesDescription::find()
                ->alias('cd')
                ->select([
                    'c.categories_id AS id',
                    "(CASE WHEN cd1.categories_name = '' THEN cd.categories_name ELSE cd1.categories_name END) AS categories_name",
                    "(CASE WHEN cd1.categories_heading_title = '' THEN cd.categories_heading_title ELSE cd1.categories_heading_title END) AS categories_heading_title",
                    "(CASE WHEN cd1.categories_description = '' THEN cd.categories_description ELSE cd1.categories_description END) AS categories_description",
                    'c.categories_image', 'c.banners_group', 'cd.noindex_option', 'cd.nofollow_option', 'cd.rel_canonical', 'cd.categories_h1_tag'
                        ]);
        $category->innerJoinWith(['categories c'], false);
        $category->leftJoin('categories_description cd1', 'cd1.categories_id = c.categories_id');

        if ($groupJoin && ($model = \common\helpers\Acl::checkExtensionTableExist('UserGroupsRestrictions', 'GroupsCategories'))) {
            $category->innerJoin($model::tableName() .' gc', 'gc.categories_id=c.categories_id');
        }

        $category
            ->where(['c.categories_id' => $currentCategoryId])
            ->andWhere([
                'cd1.language_id' => $languagesId,
                'cd1.affiliate_id' => $session,
                'cd.categories_id' => $currentCategoryId,
                'cd.language_id' => $languages_id,
                'c.categories_status' => 1
            ]);

        if ($groupWhere && \common\helpers\Acl::checkExtensionTableExist('UserGroupsRestrictions', 'GroupsCategories')) {
            $category->andWhere([
                'gc.groups_id' => $customerGroupsId
            ]);
        }

        return $category->asArray()->one();
    }
}