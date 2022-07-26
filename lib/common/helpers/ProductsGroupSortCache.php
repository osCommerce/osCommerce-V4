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


class ProductsGroupSortCache
{

    public static function update($productId=0)
    {
        \Yii::$app->getDb()->createCommand("
            UPDATE products p
              LEFT JOIN (
                select p2pi.products_id, CAST(group_concat(p2pi.properties_id ORDER BY pp.sort_order) AS UNSIGNED) AS properties_id
                FROM properties_to_products p2pi
                  INNER JOIN properties pp ON p2pi.properties_id=pp.properties_id AND pp.products_groups=1 
                 GROUP BY p2pi.products_id
              ) prop ON prop.products_id=p.products_id 
              LEFT JOIN properties_to_products p2p ON p2p.products_id=p.products_id AND prop.properties_id=p2p.properties_id 
              LEFT JOIN properties_values pv ON pv.properties_id=p2p.properties_id AND p2p.values_id=pv.values_id and pv.language_id=1
            SET p.products_groups_sort=IFNULL(pv.sort_order,0)
            WHERE ".(empty($productId)?'1':"p.products_id='".(int)$productId."'")."
        ")->execute();
    }

}