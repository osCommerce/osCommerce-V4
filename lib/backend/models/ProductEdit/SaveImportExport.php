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
use common\models\Products;

class SaveImportExport
{
    protected $product;

    public function __construct(Products $product)
    {
        $this->product = $product;
    }

    public function save()
    {
        $directories = Directory::getAll();
        foreach ($directories as $directory){
            /**
             * @var Directory $directory
             */
            if ($directory->directory_type=='datasource' && $datasource = $directory->getDatasource()){
                if ( !$datasource->allowProductView() ) continue;
                $datasource->productSave($directory, $this->product);
            }
        }
    }

}