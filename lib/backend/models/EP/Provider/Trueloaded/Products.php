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

namespace backend\models\EP\Provider\Trueloaded;

use Yii;
use common\api\models\XML\IOCore;

class Products extends XmlBase
{
    public function init()
    {

        $this->ConfigureMap = IOCore::getExportStructure('products');
        parent::init();

    }

    public function clearLocalData()
    {
        \common\classes\Images::cleanImageReference();
        $productImagesDirPath = \common\classes\Images::getFSCatalogImagesPath().'products'.DIRECTORY_SEPARATOR;
        if ( is_dir($productImagesDirPath) ) {
            $imagesDirHandle = opendir($productImagesDirPath);
            while (($productImageDirectory = readdir($imagesDirHandle)) !== false) {
                if (!is_numeric($productImageDirectory) || intval($productImageDirectory)!=$productImageDirectory) continue;
                $removeImageDirectory = $productImagesDirPath . DIRECTORY_SEPARATOR . $productImageDirectory;
                if ( is_file($removeImageDirectory) ) continue; //??
                try {
                    \yii\helpers\FileHelper::removeDirectory($removeImageDirectory);
                }catch (\Exception $ex){}
            }
            closedir($imagesDirHandle);
        }

        tep_db_query("TRUNCATE TABLE " . TABLE_PRODUCTS);
        tep_db_query("TRUNCATE TABLE " . TABLE_PRODUCTS_PRICES);
        tep_db_query("TRUNCATE TABLE " . TABLE_SPECIALS);
        tep_db_query("TRUNCATE TABLE " . TABLE_SPECIALS_PRICES);
        tep_db_query("TRUNCATE TABLE " . TABLE_PRODUCTS_DESCRIPTION);
        tep_db_query("TRUNCATE TABLE " . TABLE_PRODUCTS_XSELL);

        tep_db_query("TRUNCATE TABLE " . TABLE_FEATURED);

        tep_db_query("TRUNCATE TABLE " . TABLE_CATS_PRODUCTS_XSELL);
        tep_db_query("TRUNCATE TABLE " . TABLE_PRODUCTS_UPSELL);
        tep_db_query("TRUNCATE TABLE " . TABLE_CATEGORIES_UPSELL);
        tep_db_query("TRUNCATE TABLE " . TABLE_CATS_PRODUCTS_UPSELL);

        tep_db_query("TRUNCATE TABLE " . TABLE_GIFT_WRAP_PRODUCTS);
        tep_db_query("TRUNCATE TABLE " . TABLE_GIVE_AWAY_PRODUCTS);
        tep_db_query("TRUNCATE TABLE " . TABLE_PRODUCTS_NOTIFY);
        tep_db_query("TRUNCATE TABLE " . TABLE_SETS_PRODUCTS);

        tep_db_query("TRUNCATE TABLE " . TABLE_SUPPLIERS_PRODUCTS);

        tep_db_query("TRUNCATE TABLE " . TABLE_VIRTUAL_GIFT_CARD_PRICES);
        tep_db_query("TRUNCATE TABLE " . TABLE_VIRTUAL_GIFT_CARD_BASKET);

        tep_db_query("TRUNCATE TABLE " . TABLE_PRODUCTS_ATTRIBUTES);
        tep_db_query("TRUNCATE TABLE " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD);
        tep_db_query("TRUNCATE TABLE " . TABLE_PRODUCTS_ATTRIBUTES_PRICES);
        tep_db_query("TRUNCATE TABLE " . TABLE_PRODUCTS_NOTIFICATIONS);
        tep_db_query("TRUNCATE TABLE " . TABLE_INVENTORY);
        tep_db_query("TRUNCATE TABLE " . TABLE_INVENTORY_PRICES);
        tep_db_query("TRUNCATE TABLE " . TABLE_STOCK_HISTORY);
        tep_db_query("TRUNCATE TABLE " . \common\models\WarehousesProducts::tableName());
        tep_db_query("TRUNCATE TABLE " . TABLE_PRODUCTS_TO_CATEGORIES);
        tep_db_query("TRUNCATE TABLE " . TABLE_REVIEWS);
        tep_db_query("TRUNCATE TABLE " . TABLE_REVIEWS_DESCRIPTION);

        tep_db_query("TRUNCATE TABLE " . TABLE_PROPERTIES_CATEGORIES);
        tep_db_query("TRUNCATE TABLE " . TABLE_PROPERTIES_CATEGORIES_DESCRIPTION);
        tep_db_query("TRUNCATE TABLE " . TABLE_PROPERTIES_TO_PROPERTIES_CATEGORIES);
        tep_db_query("TRUNCATE TABLE " . TABLE_PROPERTIES);
        tep_db_query("TRUNCATE TABLE " . TABLE_PROPERTIES_DESCRIPTION);
        tep_db_query("TRUNCATE TABLE " . TABLE_PROPERTIES_TO_PRODUCTS);
        tep_db_query("TRUNCATE TABLE " . TABLE_PROPERTIES_VALUES);

        tep_db_query("TRUNCATE TABLE " . TABLE_PRODUCTS_IMAGES);
        tep_db_query("TRUNCATE TABLE " . TABLE_PRODUCTS_IMAGES_DESCRIPTION);
        tep_db_query("TRUNCATE TABLE " . TABLE_PRODUCTS_IMAGES_INVENTORY);
        tep_db_query("TRUNCATE TABLE " . TABLE_PRODUCTS_IMAGES_ATTRIBUTES);
        if ( defined('TABLE_PRODUCTS_IMAGES_EXTERNAL_URL') ){
           tep_db_query("TRUNCATE TABLE " . TABLE_PRODUCTS_IMAGES_EXTERNAL_URL);
        }

        tep_db_query("TRUNCATE TABLE " . TABLE_PRODUCTS_VIDEOS);

        tep_db_query("TRUNCATE TABLE " . TABLE_PRODUCTS_DOCUMENTS);
        tep_db_query("TRUNCATE TABLE " . TABLE_PRODUCTS_DOCUMENTS_TITLES);
        tep_db_query("TRUNCATE TABLE " . TABLE_DOCUMENT_TYPES);

        tep_db_query("TRUNCATE TABLE " . TABLE_PLATFORMS_PRODUCTS);

        tep_db_query("TRUNCATE TABLE ep_holbi_soap_link_products");
        tep_db_query("TRUNCATE TABLE ep_holbi_soap_mapping");

        tep_db_query("TRUNCATE TABLE ep_holbi_soap_link_products");
        tep_db_query("TRUNCATE TABLE ep_holbi_soap_products_flags");
        tep_db_query("TRUNCATE TABLE ep_holbi_soap_kv_storage");
        tep_db_query("TRUNCATE TABLE ep_holbi_soap_kw_id_storage");

        $schemaCheck = Yii::$app->get('db')->schema->getTableSchema('gapi_search');
        if ( $schemaCheck ) {
            tep_db_query("TRUNCATE TABLE gapi_search");
        }
        $schemaCheck = Yii::$app->get('db')->schema->getTableSchema('gapi_search_to_products');
        if ( $schemaCheck ) {
            tep_db_query("TRUNCATE TABLE gapi_search_to_products");
        }

        $schemaCheck = Yii::$app->get('db')->schema->getTableSchema('products_groups');
        if ( $schemaCheck ) {
            tep_db_query("TRUNCATE TABLE products_groups");
        }

        $schemaCheck = Yii::$app->get('db')->schema->getTableSchema('products_linked_parent');
        if ( $schemaCheck ) {
            tep_db_query("TRUNCATE TABLE products_linked_parent");
        }
        $schemaCheck = Yii::$app->get('db')->schema->getTableSchema('products_linked_children');
        if ( $schemaCheck ) {
            tep_db_query("TRUNCATE TABLE products_linked_children");
        }

        tep_db_query("TRUNCATE TABLE personal_catalog");
    }

}