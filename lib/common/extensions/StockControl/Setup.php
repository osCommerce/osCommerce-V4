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


namespace common\extensions\StockControl;

class Setup extends \common\classes\modules\SetupExtensions
{
    public static function getDescription()
    {
        return 'Provides the ability to limit the amount of product available for sale on a platform or warehouse';
    }

    public static function getTranslationArray()
    {
        return [
            'extensions/stock-control' => [
                'TEXT_STOCK_SPLIT' => 'Stock splitting',
                'TEXT_STOCK_OVERALL' => 'Overall stock',
                'TEXT_STOCK_SPLIT_PLATFORMS' => 'Split stock between platforms',
                'TEXT_STOCK_PLATFORMS_TO_WAREHOUSE' => 'Assign platform to warehouse',

            ]
        ];
    }

    public static function getAdminHooks()
    {
        return [
            ['page_name' => 'categories/productedit-beforesave'],
        ];
    }

    public static function getRequiredModules()
    {
        return [
            'osCommerce' => ['version' => /*4.13*/'999999', 'version_applicable' => 'greater-equal'],
        ];
    }

    public static function getVersionHistory()
    {
        return [
            '1.0.1' => 'Added hook',
            '1.0.0' => ['whats_new' => "Basic version"],
        ];
    }

    public static function getDropDatabasesArray()
    {
        return [
            'platform_stock_control',
            'warehouse_stock_control',
            'platform_inventory_control',
            'warehouse_inventory_control'
        ];
    }

    /**
     * @param $platformId
     * @param \common\classes\Migration $migration
     * @return void
     */
    public static function install($platformId, $migration)
    {
        $migration->createTableIfNotExists('platform_stock_control', [
                'products_id' => $migration->integer(10)->notNull(),
                'platform_id' => $migration->integer(10)->notNull(),
                'current_quantity' => $migration->integer(10)->defaultValue(0)->notNull(),
                'manual_quantity' => $migration->integer(10)->defaultValue(0)->notNull(),
            ],
            [ // primary key
                'pk' => ['products_id', 'platform_id']
            ],
            null  // index keys
        );
        $migration->createTableIfNotExists('platform_inventory_control', [
                'products_id' => $migration->string(160)->notNull(),
                'platform_id' => $migration->integer(10)->notNull(),
                'current_quantity' => $migration->integer(10)->defaultValue(0)->notNull(),
                'manual_quantity' => $migration->integer(10)->defaultValue(0)->notNull(),
            ],
            [ // primary key
                'pk' => ['products_id', 'platform_id']
            ],
            null  // index keys
        );
        $migration->createTableIfNotExists('warehouse_stock_control', [
                'products_id' => $migration->integer(10)->notNull(),
                'platform_id' => $migration->integer(10)->notNull(),
                'warehouse_id' => $migration->integer(10)->defaultValue(0)->notNull(),
            ],
            [ // primary key
                'pk' => ['products_id', 'platform_id']
            ],
            null  // index keys
        );
        $migration->createTableIfNotExists('warehouse_inventory_control', [
                'products_id' => $migration->string(160)->notNull(),
                'platform_id' => $migration->integer(10)->notNull(),
                'warehouse_id' => $migration->integer(10)->defaultValue(0)->notNull(),
            ],
            [ // primary key
                'pk' => ['products_id', 'platform_id']
            ],
            null  // index keys
        );
    }
}