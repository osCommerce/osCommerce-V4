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

namespace common\helpers;

class Compare
{
    public static function getCategoryId($productId)
    {
        $categoryId = \common\models\Products2Categories::find()
            ->where(['products_id' => $productId])
            ->andWhere(['!=', 'categories_id', 0])
            ->one()
            ->categories_id ?? null;

        return self::getCategoryIdByCategory($categoryId);
    }

    public static function getCategoryIdByCategory($categoryId)
    {
        $parentId = true;
        $cutout = 20;
        while ($categoryId && $parentId && $cutout) {
            $parentId = \common\models\Categories::findOne(['categories_id' => $categoryId])->parent_id ?? null;
            if ($parentId) {
                $categoryId = $parentId;
            }
            $cutout--;
        }

        return $categoryId;
    }
}
