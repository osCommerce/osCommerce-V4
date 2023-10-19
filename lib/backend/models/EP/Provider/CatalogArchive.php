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

namespace backend\models\EP\Provider;


use backend\models\EP\Messages;
use backend\models\EP\Providers;

class CatalogArchive extends ProviderAbstract implements ImportInterface, ExportInterface
{

    protected $archiveProviders = [];
    protected $archiveSettings = ['useColumns' => [], 'filter'=> [] ];

    public function init()
    {
        parent::init();
        $this->archiveProviders[] = [
            'format' => 'CSV',
            'provider' => 'product\categories',
            'feedname' => 'catalog_categories.csv',
        ];
        $this->archiveProviders[] =[
            'format' => 'CSV',
            'provider' => 'product\products',
            'feedname' => 'catalog_products.csv',
        ];
        $this->archiveProviders[] = [
            'format' => 'CSV',
            'provider' => 'product\images',
            'feedname' => 'catalog_product_images.csv',
        ];
        $this->archiveProviders[] =[
            'format' => 'CSV',
            'provider' => 'product\products_to_categories',
            'feedname' => 'catalog_categories_product_assign.csv',
        ];

        $this->archiveProviders[] =[
            'format' => 'CSV',
            'provider' => 'product\attributes',
            'feedname' => 'catalog_product_attributes.csv',
        ];

        if (\common\helpers\Extensions::isAllowed('Inventory')) {
            $this->archiveProviders[] =[
                'format' => 'CSV',
                'provider' => 'Inventory\Product',
                'feedname' => 'catalog_inventory.csv',
            ];
        }

        $this->archiveProviders[] =[
            'format' => 'CSV',
            'provider' => 'product\suppliers',
            'feedname' => 'catalog_suppliers.csv',
        ];

        $this->archiveProviders[] =[
            'format' => 'CSV',
            'provider' => 'product\suppliersproducts',
            'feedname' => 'catalog_suppliers_products.csv',
        ];

        $this->archiveProviders[] =[
            'format' => 'CSV',
            'provider' => 'product\warehousestock',
            'feedname' => 'catalog_warehouse_stock.csv',
        ];
        if (\common\helpers\Extensions::isAllowed('ProductBundles')) {
            $this->archiveProviders[] = [
                'format' => 'CSV',
                'provider' => 'ProductBundles\ProductBundles',
                'feedname' => 'catalog_bundles.csv',
            ];
        }
        if (\common\helpers\Extensions::isAllowed('LinkedProducts')) {
            $this->archiveProviders[] = [
                'format' => 'CSV',
                'provider' => 'LinkedProducts\LinkedProducts',
                'feedname' => 'catalog_linked_products.csv',
            ];
        }
        if (\common\helpers\Extensions::isAllowed('UpSell')) {
            $this->archiveProviders[] = [
                'format' => 'CSV',
                'provider' => 'UpSell\CrossSell',
                'feedname' => 'catalog_xsell_products.csv',
            ];
        }
        $this->archiveProviders[] =[
            'format' => 'CSV',
            'provider' => 'product\properties',
            'feedname' => 'catalog_properties.csv',
        ];

        $providers = new Providers();
        foreach ($this->archiveProviders as $idx=>$archiveProvider) {
            $obj = $providers->getProviderInstance($archiveProvider['provider']);
            if ( !is_object($obj) ) {
                unset($this->archiveProviders[$idx]);
            }else{
                $this->archiveProviders[$idx]['obj'] = $obj;
                $this->archiveProviders[$idx]['name'] = $providers->getProviderName($archiveProvider['provider']);
            }
        }
        //$this->archiveProviders = array_values($this->archiveProviders);

        $this->initFields();

    }

    protected function initFields()
    {
        $this->fields = array();
        $this->fields[] = array( 'name' => 'name', 'value' => 'Feed Name' );
        $this->fields[] = array( 'name' => 'feedname', 'value' => 'Feed Process Queue' );
        $this->fields[] = array( 'name' => 'provider', 'value' => 'Feed Type' );
    }

    public function prepareExport($useColumns, $filter)
    {
        foreach ($this->archiveProviders as $idx=>$archiveProvider) {
            $exportColumns = $archiveProvider['obj']->getColumns();
            $this->archiveProviders[$idx]['columns'] = $exportColumns;
            $archiveProvider['obj']->setColumns($exportColumns);
            $archiveProvider['obj']->prepareExport(array_keys($exportColumns), $filter);
        }
        $this->archiveSettings['useColumns'] = $useColumns;
        $this->archiveSettings['filter'] = $filter;
        reset($this->archiveProviders);
    }

    public function exportRow()
    {
        /**
         * @var $exportProviderObj ProviderAbstract
         */
        $exportProviderInfo = current($this->archiveProviders);
        if ( !is_array($exportProviderInfo) ) return false;
        $exportProviderObj = $exportProviderInfo['obj'];
        $format = $exportProviderInfo['format'];
        $archiveFilename = $exportProviderInfo['feedname'];

        $createdFeedFilename = tempnam(sys_get_temp_dir(), 'ep_all_catalog_write');
        $writer = \Yii::createObject([
            'class' => 'backend\\models\\EP\\Writer\\'.$format,
            'filename' => $createdFeedFilename,
        ]);
        $writer->setColumns($exportProviderInfo['columns']);

        while(is_array($providerData = $exportProviderObj->exportRow())){
            if (substr(strval(key($providerData)),0,1)==':') {
                if (isset($providerData[':feed_data'])) {
                    $writer->write($providerData[':feed_data']);
                }
                if (isset($providerData[':attachments'])) {
                    foreach($providerData[':attachments'] as $writeFile) {
                        $filesAdd[] = ['filename' => $writeFile['filename'], 'localname' => $writeFile['localname']];
                    }
                }
            }else{
                $writer->write($providerData);
            }
        }
        $writer->close();


        $filesAdd[] = [
            'filename' => $createdFeedFilename,
            'localname' => $archiveFilename,
        ];

        $nextData = next($this->archiveProviders);
        if ( !$nextData ) {
            ob_start();
            $sequenceWriter = \Yii::createObject([
                'class' => 'backend\\models\\EP\\Writer\\'.$format,
                'filename' => 'php://output',
            ]);
            $sequenceWriter->setColumns($this->getColumns());
            foreach($this->archiveProviders as $__archiveProvider) {
                $sequenceWriter->write($__archiveProvider);
            }
            $feedQueue = ob_get_clean();
            $sequenceWriter->close();

            $filesAdd[] = [
                'filename' => 'process_sequence.csv',
                'string' => $feedQueue,
            ];
        }

        return $filesAdd;
    }

    public function importRow($data, Messages $message)
    {
        $message->command('persist_messages',true);
        $sub_job = $this->directoryObj->findJobByFilename($data['feedname']);
        if ( $sub_job ) {
            $message->info('<b>Process "'.$sub_job->file_name.'"</b>');
            try {
                $sub_job->run($message);
            }catch (\Exception $ex){
                $message->info($ex->getMessage());
            }
        }
    }

    public function postProcess(Messages $message)
    {
        $message->command('persist_messages',false);
    }

}