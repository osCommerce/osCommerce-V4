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

namespace common\models\repositories;


use common\models\Categories;
use yii\db\ActiveQuery;

class CategoriesRepository {

    public function findParentCategories($categoryId = 0 ,$languageId = 1)
    {
       return Categories::find()->alias('c')
           ->select(["CONCAT('c',c.categories_id) as 'key'",'cd.categories_name as title'])
           ->innerJoinWith(['descriptions cd'=> function($query) use ($languageId) {
               /** @var ActiveQuery $query */
               return $query->andOnCondition(['language_id' => $languageId]);
           }],false)
           ->where(['parent_id' => $categoryId])
           ->orderBy('c.sort_order, cd.categories_name')
           ->asArray(true)
           ->all();

    }
    public function findAssignedGroupCategoriesCatalog(int $groupId, int $languageId, $active = false)
    {
        $categories = Categories::find()->alias('c')
            ->select(['c.categories_id AS id'])
            ->innerJoinWith(['descriptions pd'=> function($query) use ($languageId) {
                /** @var ActiveQuery $query */
                return $query->andOnCondition(['language_id' => $languageId]);
            }],false);

        if ($model = \common\helpers\Acl::checkExtensionTableExist('UserGroupsRestrictions', 'GroupsCategories')) {
            $categories->innerJoin($model::tableName() . ' c2g', 'c2g.categories_id=c.categories_id AND c2g.groups_id=:groups_id', ['groups_id' => $groupId]);
        }
        if($active){
            $categories->active();
        }
        return $categories->asArray(true)->all();
    }
}