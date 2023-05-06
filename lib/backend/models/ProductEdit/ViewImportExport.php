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

namespace backend\models\ProductEdit;


use backend\models\EP\Directory;
use backend\models\EP\Providers;

class ViewImportExport
{
    /**
     * @var \objectInfo
     */
    protected $productInfoRef;

    protected $list = [];

    public function __construct($productInfo)
    {
        $this->productInfoRef = $productInfo;
        //$this->wrap($this->productInfoRef);

        $directories = Directory::getAll();
        foreach ($directories as $directory){
            /**
             * @var Directory $directory
             */
            if ($directory->directory_type=='datasource' && $datasource = $directory->getDatasource()){
                if ( !$datasource->allowProductView() ) continue;
                $view = $datasource->productView([
                    'directory' => $directory,
                    'productInfo' => $productInfo,
                ]);
                if ( $view ) {
                    $this->list[] = [
                        'datasource' => $datasource,
                        'directory_name' => $directory->directory,
                        'title' => $datasource->getName(),
                        'content' => $view,
                    ];
                }
            }
        }
    }

    public function hasTabs()
    {
        return count($this->list);
    }

    public function tabList()
    {
        return $this->list;
    }

}