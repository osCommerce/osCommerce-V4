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

namespace backend\models\EP;

use backend\models\EP\Provider\ProviderAbstract;
use backend\models\EP\Provider\Trueloaded\TrueloadedXmlFeedProvider;
use Yii;
use backend\models\EP\Provider\ImportInterface;

class Providers
{

    protected $providers = [];

    public function __construct()
    {
        \common\helpers\Translation::init('admin/categories');
        \common\helpers\Translation::init('admin/easypopulate');
        \common\helpers\Translation::init('admin/main');

        $this->providers = [
            'product\catalog' => [
                'group' => TEXT_CATALOG_PRODUCTS,
                'name' => TEXT_OPTION_DOWNLOAD_CATALOG,
                'class' => 'Provider\\CatalogArchive',
                'export' =>[
                    'allow_format' => ['ZIP'],
                    'filters' => ['category','products','with-images'],
                    'disableSelectFields' => true,
                ],
            ],
            'product\products' => [
                'group' => TEXT_CATALOG_PRODUCTS,
                'name' => TEXT_OPTION_PRODUCT,
                'class' => 'Provider\\Products',
                'export' =>[
                    'filters' => ['category','price_tax','products'],
                ],
            ],
            'product\categories' => [
                'group' => TEXT_CATALOG_PRODUCTS,
                'name' => TEXT_OPTION_CATEGORIES,
                'class' => 'Provider\\Categories',
                'export' =>[
                    'filters' => ['category','products','with-images'],
                ],
            ],
            'product\brands' => [
                'group' => TEXT_CATALOG_PRODUCTS,
                'name' => TEXT_OPTION_BRANDS,
                'class' => 'Provider\\Brands',
                'export' =>[
                    'filters' => ['category','with-images'],
                ],
            ],
            'product\products_to_categories' => [
                'group' => TEXT_CATALOG_PRODUCTS,
                'name' => TEXT_OPTION_PRODUCTS_TO_CATEGORIES,
                'class' => 'Provider\\ProductsToCategories',
                'export' =>[
                    'filters' => ['category', 'products'],
                    'disableSelectFields' => true,
                ],
            ],
            'product\attributes' => [
                'group' => TEXT_CATALOG_PRODUCTS,
                'name' => TEXT_OPTION_ATTRIBUTES,
                'class' => 'Provider\\Attributes',
                'export' =>[
                    'filters' => ['category','products'],
                ],
            ],
            'product\brands' => [
                'group' => TEXT_CATALOG_PRODUCTS,
                'name' => TEXT_BRANDS,
                'class' => 'Provider\\Brands',
                'export' =>[
                    'filters' => [],
                ],
            ],
            'product\suppliers' => [
                'group' => TEXT_CATALOG_PRODUCTS,
                'name' => BOX_CATALOG_SUPPIERS,
                'class' => 'Provider\\Suppliers',
                'export' =>[
                    'filters' => [],
                ],
            ],
            'product\suppliersproducts' => [
                'group' => TEXT_CATALOG_PRODUCTS,
                'name' => BOX_CATALOG_SUPPIERS_PRODUCTS,
                'class' => 'Provider\\SuppliersProducts',
                'export' =>[
                    'filters' => ['category', 'products'],
                ],
            ],
            'product\stock' => [
                'group' => TEXT_CATALOG_PRODUCTS,
                'name' => TEXT_OPTION_STOCK_FEED,
                'class' => 'Provider\\Stock',
                'export' =>[
                    'filters' => ['category', 'products'],
                ],
            ],
/**/
            'product\sales' => [
                'group' => TEXT_CATALOG_PRODUCTS,
                'name' => TEXT_OPTION_SALES_FEED,
                'class' => 'Provider\\Sales',
                'export' =>[
                    'filters' => ['category', 'products', 'price_tax'/*, 'warehouse', 'supplier'*/],
                ],
            ],
/**/
            'product\warehousestock' => [
                'group' => TEXT_CATALOG_PRODUCTS,
                'name' => 'Warehouse '.TEXT_OPTION_STOCK_FEED,
                'class' => 'Provider\\WarehouseStock',
                'export' =>[
                    'filters' => ['category', 'products'],
                ],
            ],
            'product\images' => [
                'group' => TEXT_CATALOG_PRODUCTS,
                'name' => TEXT_OPTION_IMAGES,
                'class' => 'Provider\\Images',
                'export' =>[
                    'filters' => ['category','products', 'with-images'],
                ],
            ],
            'product\properties' => [
                'group' => TEXT_CATALOG_PRODUCTS,
                'name' => TEXT_OPTION_PROPERTIES,
                'class' => 'Provider\\Properties',
                'export' =>[
                    'filters' => ['category', 'products'],
                    'disableSelectFields' => true,
                ],
            ],
            'product\catalog_properties' => [
                'group' => TEXT_CATALOG_PRODUCTS,
                'name' => TEXT_OPTION_PROPERTIES_SETTINGS,
                'class' => 'Provider\\CatalogProperties',
                'export' =>[
                    'filters' => ['properties'],
                ],

            ],
            'product\assets' => [
                'group' => TEXT_CATALOG_PRODUCTS,
                'name' => TEXT_PRODUCT_ASSETS,
                'class' => 'Provider\\Assets',
                'export' =>[
                    'filters' => ['category','products'],
                    'disableSelectFields' => true,
                ],
            ],
            'product\reviews' => [
                'group' => TEXT_CATALOG_PRODUCTS,
                'name' => BOX_CATALOG_REVIEWS,
                'class' => 'Provider\\ProductReviews',
                'export' =>[
                    'filters' => ['category','products'],
                ],
            ],
            'product\documents' => [
                'group' => TEXT_CATALOG_PRODUCTS,
                'name' => TAB_DOCUMENTS,
                'class' => 'Provider\\Documents',
                'export' =>[
                    'filters' => ['category', 'products', 'with-images'],
                ],
            ],
            'statistic\orders' => [
                'group' => TEXT_SITE_STATISTIC,
                'name' => 'Order Statistic',
                'class' => 'Provider\\OrderStatistic',
                'export' =>[
                    'filters' => ['orders-date-range'],
                    'disableSelectFields' => true,
                ],
            ],
            'BrightPearl\\Stock' => [
                'group' => TEXT_BRIGHT_PEARL,
                'name' => 'Stock',
                'class' => 'Provider\\BrightPearl\\Stock',
                'export' =>[
                    'disableSelectFields' => true,
                ],
            ],
            'BrightPearl\\ExportPrice' => [
                'group' => TEXT_BRIGHT_PEARL,
                'name' => 'Export Price',
                'class' => 'Provider\\BrightPearl\\ExportPrice',
                'export' =>[
                    'disableSelectFields' => true,
                ],
            ],
            'BrightPearl\\ExportOrder' => [
                'group' => TEXT_BRIGHT_PEARL,
                'name' => 'Export Order',
                'class' => 'Provider\\BrightPearl\\ExportOrder',
                'export' =>[
                    'disableSelectFields' => true,
                ],
            ],
            'HolbiLink\\Products' => [
                'group' => 'Holbi Link',
                'name' => 'Import products',
                'class' => 'Provider\\HolbiLink\\ImportProducts',
                'export' =>[
                    'disableSelectFields' => true,
                ],
            ],
            'HPCap\\ImportProducts' => [
                'group' => 'HP Cap',
                'name' => 'Import products',
                'class' => 'Provider\\HPCap\\ImportProducts',
                'export' =>[
                    'disableSelectFields' => true,
                ],
            ],
            'Magento\\ImportGroups' => [
                'group' => 'Magento',
                'name' => 'Import groups',
                'class' => 'Provider\\Magento\\ImportGroups',
                'export' =>[
                    'disableSelectFields' => true,
                ],
            ],
            'Magento\\ImportProducts' => [
                'group' => 'Magento',
                'name' => 'Import products',
                'class' => 'Provider\\Magento\\ImportProducts',
                'export' =>[
                    'disableSelectFields' => true,
                ],
            ],
            'Magento\\ImportCustomers' => [
                'group' => 'Magento',
                'name' => 'Import customers',
                'class' => 'Provider\\Magento\\ImportCustomers',
                'export' =>[
                    'disableSelectFields' => true,
                ],
            ],
            'Magento\\ImportOrders' => [
                'group' => 'Magento',
                'name' => 'Import orders',
                'class' => 'Provider\\Magento\\ImportOrders',
                'export' =>[
                    'disableSelectFields' => true,
                ],
            ],
            'orders\customers' => [
                'group' => TEXT_SITE_ORDER_EXPORT_IMPORT,
                'name' => 'Customers',
                'class' => 'Provider\\Customers',
                'export' =>[
                    'allow_format' => ['CSV'/*,'XML'*/],
                    //'filters' => ['orders-date-range'],
                    //'disableSelectFields' => true,
                ],
//                'import' =>[
//                    'format' => 'XML',
//                ],
            ],
            /*
            'orders\orders' => [
                'group' => TEXT_SITE_ORDER_EXPORT_IMPORT,
                'name' => 'Order Export/Import',
                'class' => 'Provider\\OrderExport',
                'export' =>[
                    'allow_format' => ['XML_orders_new'],
                    'filters' => ['orders-date-range'],
                    'disableSelectFields' => true,
                ],
                'import' =>[
                    'format' => 'XML_orders_new'
                ],
            ],
            */
            'orders\order' => [
                'group' => TEXT_SITE_ORDER_EXPORT_IMPORT,
                'name' => 'Order',
                'class' => 'Provider\\Order',
                'export' =>[
                    'allow_format' => ['CSV','XML'],
                    'filters' => ['orders-date-range'],
                    //'disableSelectFields' => true,
                ],
                'import' =>[
                    'format' => 'XML',
                ],
            ],
            'report\\customers' => [
                'group' => 'Report',
                'name' => 'Customers',
                'class' => 'Provider\\CustomersReport',
                'export' =>[
                    'allow_format' => ['CSV'],
                    'filters' => ['platform'],
                    //'disableSelectFields' => true,
                ],
            ],
            'PaymentBots\\PaypalCollector' => [
                'group' => 'PaymentBots',
                'name' => 'Paypal Transactions Collector',
                'class' => 'Provider\\PaymentBots\\PaypalCollector',
                'export' =>[
                    'disableSelectFields' => true,
                ],
            ],
            'Google\\SyncEcommerce' => [
                'group' => 'Google',
                'name' => 'Sync e-commerce',
                'class' => 'Provider\\Google\\SyncEcommerce',
            ],
            'Trueloaded\\Platforms' => [
                'group' => 'Trueloaded',
                'name' => 'Platforms',
                'class' => 'Provider\\Trueloaded\\Platforms',
                'export' =>[
                    'allow_format' => ['XML', 'XML-ZIP'],
                    'filters' => ['with-images'],
                    'disableSelectFields' => true,
                ],
                'import' =>[
                    'format' => 'XML',
                ],
            ],
            'Trueloaded\\Customers' => [
                'group' => 'Trueloaded',
                'name' => 'Customers',
                'class' => 'Provider\\Trueloaded\\Customers',
                'export' =>[
                    'allow_format' => ['XML', 'XML-ZIP'],
                    'filters' => ['platform'],
                    'disableSelectFields' => true,
                ],
                'import' =>[
                    'format' => 'XML',
                ],
            ],
            'Trueloaded\\Orders' => [
                'group' => 'Trueloaded',
                'name' => 'Orders',
                'class' => 'Provider\\Trueloaded\\Orders',
                'export' =>[
                    'allow_format' => ['XML', 'XML-ZIP'],
                    'filters' => ['platform', 'orders-date-range'],
                    'disableSelectFields' => true,
                ],
                'import' =>[
                    'format' => 'XML',
                ],
            ],
            'Trueloaded\\Quotes' => [
                'group' => 'Trueloaded',
                'name' => 'Quotations',
                'class' => 'Provider\\Trueloaded\\Quotes',
                'export' =>[
                    'allow_format' => ['XML', 'XML-ZIP'],
                    'filters' => ['platform', 'orders-date-range'],
                    'disableSelectFields' => true,
                ],
                'import' =>[
                    'format' => 'XML',
                ],
            ],
            'Trueloaded\\OrdersStatusGroups' => [
                'group' => 'Trueloaded',
                'name' => 'Order Statuses Groups',
                'class' => 'Provider\\Trueloaded\\OrdersStatusGroups',
                'export' =>[
                    'allow_format' => ['XML', 'XML-ZIP'],
                    'disableSelectFields' => true,
                ],
                'import' =>[
                    'format' => 'XML',
                ],
            ],
            'Trueloaded\\OrdersStatuses' => [
                'group' => 'Trueloaded',
                'name' => 'Order Statuses',
                'class' => 'Provider\\Trueloaded\\OrdersStatuses',
                'export' =>[
                    'allow_format' => ['XML', 'XML-ZIP'],
                    'disableSelectFields' => true,
                ],
                'import' =>[
                    'format' => 'XML',
                ],
            ],
            'Trueloaded\\Brands' => [
                'group' => 'Trueloaded',
                'name' => 'Brands',
                'class' => 'Provider\\Trueloaded\\Brands',
                'export' =>[
                    'allow_format' => ['XML', 'XML-ZIP'],
                    'filters' => ['with-images'],
                    'disableSelectFields' => true,
                ],
                'import' =>[
                    'format' => 'XML',
                ],
            ],
            'Trueloaded\\Countries' => [
                'group' => 'Trueloaded',
                'name' => 'Countries',
                'class' => 'Provider\\Trueloaded\\Countries',
                'export' =>[
                    'allow_format' => ['XML', 'XML-ZIP'],
                    'disableSelectFields' => true,
                ],
                'import' =>[
                    'format' => 'XML',
                ],
            ],
            'Trueloaded\\Tax' => [
                'group' => 'Trueloaded',
                'name' => 'Tax',
                'class' => 'Provider\\Trueloaded\\Tax',
                'export' =>[
                    'allow_format' => ['XML', 'XML-ZIP'],
                    'disableSelectFields' => true,
                ],
                'import' =>[
                    'format' => 'XML',
                ],
            ],
            'Trueloaded\\TaxZones' => [
                'group' => 'Trueloaded',
                'name' => 'Tax Zones',
                'class' => 'Provider\\Trueloaded\\TaxZones',
                'export' =>[
                    'allow_format' => ['XML', 'XML-ZIP'],
                    'disableSelectFields' => true,
                ],
                'import' =>[
                    'format' => 'XML',
                ],
            ],
            'Trueloaded\\Currencies' => [
                'group' => 'Trueloaded',
                'name' => 'Currencies',
                'class' => 'Provider\\Trueloaded\\Currencies',
                'export' =>[
                    'allow_format' => ['XML', 'XML-ZIP'],
                    'disableSelectFields' => true,
                ],
                'import' =>[
                    'format' => 'XML',
                ],
            ],
            'Trueloaded\\Groups' => [
                'group' => 'Trueloaded',
                'name' => 'Groups',
                'class' => 'Provider\\Trueloaded\\Groups',
                'export' =>[
                    'allow_format' => ['XML', 'XML-ZIP'],
                    'filters' => ['with-images'],
                    'disableSelectFields' => true,
                ],
                'import' =>[
                    'format' => 'XML',
                ],
            ],
            'Trueloaded\\Languages' => [
                'group' => 'Trueloaded',
                'name' => 'Languages',
                'class' => 'Provider\\Trueloaded\\Languages',
                'export' =>[
                    'allow_format' => ['XML', 'XML-ZIP'],
                    'disableSelectFields' => true,
                ],
                'import' =>[
                    'format' => 'XML',
                ],
            ],
            'Trueloaded\\Products' => [
                'group' => 'Trueloaded',
                'name' => 'Products',
                'class' => 'Provider\\Trueloaded\\Products',
                'export' =>[
                    'allow_format' => ['XML', 'XML-ZIP'],
                    //'filters' => ['with-images'],
                    'disableSelectFields' => true,
                ],
                'import' =>[
                    'format' => 'XML',
                ],
            ],
            'Trueloaded\\Warehouses' => [
                'group' => 'Trueloaded',
                'name' => 'Warehouses',
                'class' => 'Provider\\Trueloaded\\Warehouses',
                'export' =>[
                    'allow_format' => ['XML', 'XML-ZIP'],
                    //'filters' => ['with-images'],
                    'disableSelectFields' => true,
                ],
                'import' =>[
                    'format' => 'XML',
                ],
            ],
            'Trueloaded\\Suppliers' => [
                'group' => 'Trueloaded',
                'name' => 'Suppliers',
                'class' => 'Provider\\Trueloaded\\Suppliers',
                'export' =>[
                    'allow_format' => ['XML', 'XML-ZIP'],
                    //'filters' => ['with-images'],
                    'disableSelectFields' => true,
                ],
                'import' =>[
                    'format' => 'XML',
                ],
            ],
            'Trueloaded\\Themes' => [
                'group' => 'Trueloaded',
                'name' => 'Themes',
                'class' => 'Provider\\Trueloaded\\Themes',
                'export' =>[
                    'allow_format' => ['XML', 'XML-ZIP'],
                    'filters' => ['with-images'],
                    'disableSelectFields' => true,
                ],
                'import' =>[
                    'format' => 'XML',
                ],
            ],
        ];
        $this->providers = array_merge($this->providers, TrueloadedXmlFeedProvider::getProviderList());

        if (!\common\helpers\Acl::checkExtensionTableExist('Quotations', 'QuoteOrders')) {
            unset($this->providers['Trueloaded\\Quotes']);
        }

        $this->providers = $this->providers + \common\helpers\Acl::getExtensionEpProviders();

        foreach ( DataSources::getAvailableList() as $dataSourceInfo){
            if ( method_exists($dataSourceInfo['className'],'getProviderList') ) {
                $dataSourceProviderList = call_user_func([$dataSourceInfo['className'],'getProviderList']);
                if ( is_array($dataSourceProviderList) && count($dataSourceProviderList)>0 ) {
                    $this->providers = array_merge($this->providers, $dataSourceProviderList);
                }
            }
        }

        $get_custom_r = tep_db_query(
            "SELECT custom_provider_id, name, parent_provider, provider_configure ".
            "FROM " . TABLE_EP_CUSTOM_PROVIDERS . " ".
            "WHERE 1 ".
            "ORDER BY 1"
        );
        if ( tep_db_num_rows($get_custom_r)>0 ) {
            while( $custom = tep_db_fetch_array($get_custom_r) ){
                $parentProvider = $custom['parent_provider'];
                if ( !isset($this->providers[$parentProvider]) ) continue;
                $provider_info = $this->providers[$parentProvider];

                $provider_info['name'] = $custom['name'];
                $provider_key = 'custom\\'.$custom['custom_provider_id'];

                //$provider_info['provider_configure'];

                $this->providers[ $provider_key ] = $provider_info;

            }
        }

    }

    public function getAvailableProviders($type, $filterGroup='')
    {
        $providerList = array();
        foreach ( $this->providers as $provider_key=>$provider_info ) {
            if ( !empty($filterGroup) && is_callable($filterGroup) ) {
                if (!$filterGroup($provider_key, $provider_info)) continue;
            }else{
                if ( !empty($filterGroup) && strpos($provider_key,$filterGroup.'\\')!==0 ) continue;        
            }
            $providerClassName = $this->getProviderFullClassName($provider_key);
            if ( $type=='Import' && is_subclass_of($providerClassName,'backend\models\EP\Provider\ImportInterface',true) && call_user_func([$providerClassName,'isImportAvailable'] )){
                $provider_info['key'] = $provider_key;
                $providerList[] = $provider_info;
            }elseif ( $type=='Export' && is_subclass_of($providerClassName,'backend\models\EP\Provider\ExportInterface',true) && call_user_func([$providerClassName,'isExportAvailable'] )){
                $provider_info['key'] = $provider_key;
                $providerList[] = $provider_info;
            }elseif ( $type=='Datasource' && is_subclass_of($providerClassName,'backend\models\EP\Provider\DatasourceInterface',true)) {
                $provider_info['key'] = $provider_key;
                $providerList[] = $provider_info;
            }elseif($ext = \common\helpers\Acl::checkExtension($provider_info['class'], 'allowed')){
                $provider_info['key'] = $provider_key;
                $providerList[] = $provider_info;
            }
        }
        return $providerList;
    }

    public function pullDownVariants($for='Import', $pullDownData = [], $filterGroup='')
    {
        if ( !isset($pullDownData['items']) ) $pullDownData['items'] = [];
        if ( !isset($pullDownData['options']) ) $pullDownData['options'] = [];
        if ( !isset($pullDownData['options']['options']) ) $pullDownData['options']['options'] = [];

        $option_key = strtolower($for);
        foreach($this->getAvailableProviders($for, $filterGroup) as $providerInfo)
        {
            $group = $providerInfo['group'];
            if ( !isset($pullDownData['items'][$group]) ) $pullDownData['items'][$group] = [];
            $pullDownData['items'][$group][$providerInfo['key']] = $providerInfo['name'];

            $providerOptions = [];
            if ( isset($providerInfo[$option_key]) && is_array($providerInfo[$option_key]) ) {
                $providerOptions = $providerInfo[$option_key];
            }
            $options_data = [];
            if (!isset($providerOptions['disableSelectFields']) || !$providerOptions['disableSelectFields']) {
                $options_data['data-select-fields'] = 'true';
            }
            if (isset($providerOptions['filters']) && count($providerOptions['filters']) > 0) {
                foreach ($providerOptions['filters'] as $filterCode) {
                    $options_data['data-allow-select-' . $filterCode] = 'true';
                }
            }
            if (isset($providerOptions['allow_format']) && count($providerOptions['allow_format']) > 0) {
                $options_data['data-allow-format'] = implode(',',$providerOptions['allow_format']);
            }else{
                $options_data['data-allow-format'] = 'CSV,ZIP,XLSX';
            }

            if (count($options_data) > 0) {
                $pullDownData['options']['options'][$providerInfo['key']] = $options_data;
            }
        }

        return $pullDownData;
    }

    public function getProviderName($provider)
    {
        if ( isset($this->providers[$provider]) ) {
            return $this->providers[$provider]['name'];
        }
        return 'Unknown';
    }

    public function getProviderFullClassName($key)
    {
        if ( !isset($this->providers[$key]) ) return false;
        if ($providerClassName = \common\helpers\Acl::checkExtension($this->providers[$key]['class'], 'allowed')) {

        }elseif ( class_exists($this->providers[$key]['class']) ) {
            $providerClassName = $this->providers[$key]['class'];
        } else {
            $providerClassName = 'backend\\models\\EP\\' . $this->providers[$key]['class'];
        }
        return $providerClassName;
    }

    public function getProviderConfig($key)
    {
        return isset($this->providers[$key])?$this->providers[$key]:[];
    }

    /**
     * @param $key
     * @param array $providerConfig
     * @return bool|ProviderAbstract
     */
    public function getProviderInstance($key, $providerConfig=[])
    {
        $providerClassName = $this->getProviderFullClassName($key);
        if ( $providerClassName ) {
            /**
             * @var $obj ProviderAbstract
             */
            $obj = Yii::createObject($providerClassName, [$providerConfig]);
            if ( method_exists($obj,'customConfig') ) $obj->customConfig($providerConfig);
            return $obj;
        }
        return false;
    }

    public function bestMatch(array $fileColumns)
    {
        $providersMatchRate = array();
        foreach( $this->getAvailableProviders('Import') as $providerInfo)
        {
            if ( isset($providerInfo['export']) && isset($providerInfo['export']['allow_format']) && count($providerInfo['export']['allow_format'])>0 ) {
                if ( !in_array('CSV',$providerInfo['export']['allow_format']) ) continue;
            }
            if ( strpos($providerInfo['key'],'BrightPearl')!==false ) continue;
            $provider = $this->getProviderInstance($providerInfo['key']);
            if ( !is_object($provider) ) continue;
            /**
             * @var $provider ProviderAbstract
             */

            $score = $provider->getColumnMatchScore($fileColumns);
            if ( $score>0 ) {
                $providersMatchRate[$providerInfo['key']] = $score;
            }
        }
        arsort($providersMatchRate, SORT_NUMERIC);

        return $providersMatchRate;
    }

}