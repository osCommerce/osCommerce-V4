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

use common\models\Inventory;

final class InventoryRepository
{

    /**
     * @param string|array $inventoryId
     * @param bool $asArray
     * @return array|Inventory|Inventory[]|null
     */
    public function get($inventoryId, bool $asArray = false)
    {
        if ($product = $this->findById($inventoryId, $asArray)) {
            throw new NotFoundException('Inventory not found');
        }
        return $product;
    }

    /**
     * @param string|array|int $inventoryId
     * @param bool $asArray
     * @return array|Inventory|Inventory[]|null
     */
    public function findById($inventoryId, bool $asArray = false)
    {
        $product = Inventory::find()->where(['products_id' => $inventoryId])->asArray($asArray);
        if (is_array($inventoryId)) {
            return $product->all();
        }
        return $product->limit(1)->one();
    }

    /**
     * @param string $sku
     * @param bool $nonExistent
     * @param bool $withProduct
     * @param bool $asArray
     * @return array|Inventory[]
     */
    public function findByModel(string $sku, bool $nonExistent = true, bool $withProduct = false, bool $asArray = false): array
    {
        $inventory = Inventory::find()->where(['products_model' => $sku]);
        if ($nonExistent) {
            $inventory->andWhere(['non_existent' => 0]);
        }
        if ($withProduct) {
            $inventory->with('product');
        }
        return $inventory->asArray($asArray)->all();
    }

    /**
     * @param Inventory $inventory
     * @param array $params
     * @param bool $validation
     * @param bool $safeOnly
     * @return array|bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function edit(Inventory $inventory, array $params = [], bool $validation = false, bool $safeOnly = false)
    {
        foreach ($params as $attribute => $param) {
            if (!$inventory->hasAttribute($attribute)) {
                unset($params[$attribute]);
            }
        }
        $inventory->setAttributes($params, $safeOnly);
        if ($inventory->update($validation, array_keys($params)) === false) {
            return $inventory->getErrors();
        }
        return true;
    }

    /**
     * @param int $productId
     * @param bool $asArray
     * @return array|Inventory[]
     */
    public function getInventoriesForProductId(int $productId, bool $asArray = false): array
    {
        $inventories = Inventory::find()
            ->where(['prid' => $productId])
            ->existent()
            ->asArray($asArray);
        return $inventories->all();
    }
}
