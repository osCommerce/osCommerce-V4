<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "plain_products_name_search".
 *
 * @property int $id
 * @property int $status
 * @property int $language_id
 * @property string $products_name
 * @property string $search_details
 * @property string $search_soundex
 * @property string $search_fulltext
 * @property int $tmp_prid
 */
class PlainProductsNameSearch extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'plain_products_name_search';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['status', 'language_id', 'tmp_prid'], 'integer'],
            [['products_name', 'search_details', 'search_soundex', 'search_fulltext'], 'required'],
            [['search_details', 'search_soundex', 'search_fulltext'], 'string'],
            [['products_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'status' => 'Status',
            'language_id' => 'Language ID',
            'products_name' => 'Products Name',
            'search_details' => 'Search Details',
            'search_soundex' => 'Search Soundex',
            'search_fulltext' => 'Search Fulltext',
            'tmp_prid' => 'Tmp Prid',
        ];
    }
}
