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

namespace common\api\models\AR\Products\Images;


use common\api\models\AR\EPMap;
use common\classes\Images;

class ExternalUrl extends EPMap
{

    protected $hideFields = [
        'products_images_id',
        'language_id',
        'image_types_id',
    ];

    protected $parentObject;

    public static function tableName()
    {
        return TABLE_PRODUCTS_IMAGES_EXTERNAL_URL;
    }

    public static function primaryKey()
    {
        return ['products_images_id', 'language_id', 'image_types_id'];
    }

    public function parentEPMap(EPMap $parentObject)
    {
        $this->products_images_id = $parentObject->products_images_id;
        $this->language_id = $parentObject->language_id;
        $this->parentObject = $parentObject;
    }

    public function exportArray(array $fields = [])
    {
        static $typeIdToName = false;
        if ( !is_array($typeIdToName) ) {
            $typeIdToName = [];
            foreach (Images::getImageTypes() as $imageType) {
                $typeIdToName[$imageType['image_types_id']] = $imageType['image_types_name'];
            }
        }
        $data = parent::exportArray($fields);
        $data['image_types_name'] = $typeIdToName[$this->image_types_id];
        return $data;
    }

    public function importArray($data)
    {

        if ( isset($data['image_types_name']) ) {
            $typeArray = Images::getImageTypes($data['image_types_name']);
            if ( !is_array($typeArray) ) return false;
            $data['image_types_id'] = $typeArray['image_types_id'];
        }else{
            return false;
        }

        $result = parent::importArray($data);
        return $result;
    }

    public function matchIndexedValue(EPMap $importedObject)
    {
        if ( !is_null($importedObject->image_types_id) && !is_null($this->image_types_id) && $importedObject->image_types_id==$this->image_types_id ){
            $this->pendingRemoval = false;
            return true;
        }
        return false;
    }
}