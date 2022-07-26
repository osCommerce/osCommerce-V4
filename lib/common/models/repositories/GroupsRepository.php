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

use common\models\Groups;
use common\models\GroupsCategories;
use common\models\GroupsDiscounts;
use common\models\GroupsProducts;
use common\services\TransactionManager;


/**
 * Class GroupsRepository
 * @package common\models\repositories
 */
class GroupsRepository
{
    /**
     * @var TransactionManager
     */
    private $transaction;

    public function __construct(TransactionManager $transaction)
    {
        $this->transaction = $transaction;
    }

    public function findById($groupId, bool $asArray = false)
    {
        $group = Groups::find()->where(['groups_id' => $groupId])->asArray($asArray);
        if (is_array($groupId)) {
            return $group->all();
        }
        return $group->limit(1)->one();
    }

    /**
     * @param bool $asArray
     * @return array|Groups[]
     */
    public function getAll(bool $asArray = false)
    {
        return Groups::find()->asArray($asArray)->all();
    }
    /**
     * @param Groups $groups
     * @return bool
     */
    public function remove(Groups $groups)
    {
        try {
            $this->transaction->wrap(static function () use ($groups) {
                GroupsCategories::deleteAll(['groups_id' => $groups->groups_id]);
                GroupsProducts::deleteAll(['groups_id' => $groups->groups_id]);
                GroupsDiscounts::deleteAll(['groups_id' => $groups->groups_id]);
                if ($groups->delete() === false) {
                    throw new \RuntimeException('Group remove error.');
                }
            });
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
        return true;
    }

    /**
     * @param Groups $groups
     * @param bool $validation
     * @return bool
     */
    public function save(Groups $groups, bool $validation = false)
    {
        if ($groups->save($validation) === false) {
            throw new \RuntimeException('Group saving  error.');
        }
        return true;
    }

    /**
     * @param Groups $groups
     * @param array $params
     * @param bool $validation
     * @param bool $safeOnly
     * @return array|bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function edit(Groups $groups, array $params = [], bool $validation = false, bool $safeOnly = false)
    {
        foreach ($params as $attribute => $param) {
            if (!$groups->hasAttribute($attribute)) {
                unset($params[$attribute]);
            }
        }
        $groups->setAttributes($params, $safeOnly);
        if ($groups->update($validation, array_keys($params)) === false) {
            return $groups->getErrors();
        }
        return true;
    }

    /**
     * @param int $groupId
     * @param string|int|array $productsIds
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function addProductToGroup(int $groupId, $productsIds)
    {
        $values = [];
        if (is_numeric($productsIds)) {
            $productsIds = [$productsIds];
        }
        foreach ($productsIds as $productId) {
            $values[] = [
                'groups_id' => $groupId,
                'products_id' => $productId,
            ];
        }
        $columns = $this->getGroupsProductsColumns();
        \Yii::$app->db->createCommand()
            ->batchInsert('groups_products', $columns, $values)
            ->execute();
    }

    /**
     * @param int $groupId
     * @param $categoriesIds
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function addCategoryToGroup(int $groupId, $categoriesIds)
    {
        $values = [];
        if (is_numeric($categoriesIds)) {
            $categoriesIds = [$categoriesIds];
        }
        foreach ($categoriesIds as $categoryId) {
            $values[] = [
                'groups_id' => $groupId,
                'categories_id' => $categoryId,
            ];
        }
        $columns = $this->getGroupsCategoriesColumns();
        \Yii::$app->db->createCommand()
            ->batchInsert('groups_categories', $columns, $values)
            ->execute();
    }

    /**
     * @param int $groupId
     * @param int|array $productsIds
     */
    public function removeProductToGroup(int $groupId, $productsIds)
    {
        GroupsProducts::deleteAll(['groups_id' => $groupId, 'products_id' => $productsIds]);
    }

    /**
     * @param int $groupId
     * @param int|array $categoriesIds
     */
    public function removeCategoryToGroup(int $groupId, $categoriesIds)
    {
        GroupsCategories::deleteAll(['groups_id' => $groupId, 'categories_id' => $categoriesIds]);
    }

    /**
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function getGroupsProductsColumns()
    {
        return GroupsProducts::getTableSchema()->getColumnNames();
    }

    /**
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function getGroupsCategoriesColumns()
    {
        return GroupsCategories::getTableSchema()->getColumnNames();
    }


    /**
     * @param $productsIds
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function addProductToAllGroups($productsIds)
    {
        $values = [];
        if (is_numeric($productsIds)) {
            $productsIds = [$productsIds];
        }
        foreach (array_keys(\common\helpers\Group::get_customer_groups_list()) as $groupId) {
            foreach ($productsIds as $productId) {
                $values[] = [
                    'groups_id' => $groupId,
                    'products_id' => $productId,
                ];
            }
        }
        $columns = $this->getGroupsProductsColumns();
        \Yii::$app->db->createCommand()
            ->batchInsert('groups_products', $columns, $values)
            ->execute();
    }

    /**
     * @param $categoriesIds
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function addCategoryToAllGroups($categoriesIds)
    {
        $values = [];
        if (is_numeric($categoriesIds)) {
            $categoriesIds = [$categoriesIds];
        }
        foreach (array_keys(\common\helpers\Group::get_customer_groups_list()) as $groupId) {
            foreach ($categoriesIds as $categoryId) {
                $values[] = [
                    'groups_id' => $groupId,
                    'categories_id' => $categoryId,
                ];
            }
        }
        $columns = $this->getGroupsCategoriesColumns();
        \Yii::$app->db->createCommand()
            ->batchInsert('groups_categories', $columns, $values)
            ->execute();
    }
}
