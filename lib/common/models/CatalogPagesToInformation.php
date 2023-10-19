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

use Yii;

/**
 * This is the model class for table "catalog_pages_to_information".
 *
 * @property int $catalog_pages_id
 * @property int $information_id
 *
 * @property CatalogPages $catalogPages
 * @property Information $information
 */
class CatalogPagesToInformation extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'catalog_pages_to_information';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['catalog_pages_id', 'information_id'], 'required'],
            [['catalog_pages_id', 'information_id'], 'integer'],
            [['catalog_pages_id', 'information_id'], 'unique', 'targetAttribute' => ['catalog_pages_id', 'information_id']],
            [['catalog_pages_id'], 'exist', 'skipOnError' => true, 'targetClass' => CatalogPages::className(), 'targetAttribute' => ['catalog_pages_id' => 'catalog_pages_id']],
            [['information_id'], 'exist', 'skipOnError' => true, 'targetClass' => Information::className(), 'targetAttribute' => ['information_id' => 'information_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'catalog_pages_id' => 'Catalog Pages ID',
            'information_id' => 'Information ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCatalogPages()
    {
        return $this->hasOne(CatalogPages::class, ['catalog_pages_id' => 'catalog_pages_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getInformation()
    {
        return $this->hasOne(Information::class, ['information_id' => 'information_id']);
    }
    /**
     * @inheritdoc
     * for create new Catalog Page Description for Catalog Page
     */
    public static function create($catalog_pages_id, $information_id)
    {
        $model = new static();
        $model->catalog_pages_id = (int)$catalog_pages_id;
        $model->information_id = (int)$information_id;
        return $model;
    }
}
