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

namespace common\models;


use yii\db\ActiveRecord;

class EpLinkProducts extends ActiveRecord
{
    const EP_LINK_CREATED = 1;
    const EP_LINK_UPDATED = 2;

    public static function tableName()
    {
        return 'ep_holbi_soap_link_products';
    }

    public static function getLinkByRemoteId($directory_id, $remote_product_id, $as_array=true)
    {
        return static::find()->where([
            'ep_directory_id' => (int)$directory_id,
            'remote_products_id' => (int)$remote_product_id,
        ])->asArray($as_array)->one();
    }

    public static function getLinkByLocalId($directory_id, $local_product_id, $as_array=true)
    {
        return static::find()->where([
            'ep_directory_id' => (int)$directory_id,
            'local_products_id' => (int)$local_product_id,
        ])->asArray($as_array)->one();
    }

    /**
     * @param $directory_id
     * @param $local_product_id
     * @param array $data - insert data and update (if $custom_update_data not array)
     * @param null $custom_update_data update column data
     * @return int
     * @throws \yii\db\Exception
     */
    public static function touchById($directory_id, $local_product_id, $data=[], $custom_update_data=false)
    {
        $link_key = [
            'ep_directory_id' => (int)$directory_id,
            'local_products_id' => (int)$local_product_id,
        ];

        if ( static::find()->where($link_key)->count() ){
            if ( is_array($custom_update_data) ){
                if ( count($custom_update_data)>0 ) {
                    static::updateAll($custom_update_data, $link_key);
                }
            }else {
                static::updateAll($data, $link_key);
            }

            return static::EP_LINK_UPDATED;
        }else{
            $create_link = array_merge($data, $link_key);

            static::getDb()->createCommand()->batchInsert(
                static::tableName(),
                array_keys($create_link),
                [array_values($create_link)]
            )->execute();

            return static::EP_LINK_CREATED;
        }
    }
}