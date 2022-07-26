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


class Transform
{
    protected $columnMap = [];
    protected $mapping = [];

    public function setProviderColumns($columns)
    {
        $this->columnMap = $columns;
        if ( count($this->mapping)==0 ) $this->mapping = array_flip($columns);
    }

    public function setTransformMap($external)
    {
        $this->mapping = $external;
    }

    public function transform($data)
    {
        if ( !is_array($data) ) return $data;

        $transformedData = [];
        foreach( $this->mapping as $file_key=>$db_key ) {
            if ( !array_key_exists($file_key, $data) ) continue;
            $transformedData[$db_key] = $data[$file_key];
        }

        return $transformedData;
    }
}