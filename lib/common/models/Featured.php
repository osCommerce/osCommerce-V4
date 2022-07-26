<?php

namespace common\models;

use Yii;
use common\models\queries\FeaturedQuery;

/**
 * This is the model class for table "featured".
 *
 * @property int $featured_id
 * @property int $products_id
 * @property string $featured_date_added
 * @property string $featured_last_modified
 * @property string $expires_date
 * @property string $date_status_change
 * @property int $status
 * @property int $affiliate_id
 */
class Featured extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'featured';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['products_id', 'status', 'affiliate_id'], 'integer'],
            [['featured_date_added', 'featured_last_modified', 'expires_date', 'date_status_change'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'featured_id' => 'Featured ID',
            'products_id' => 'Products ID',
            'featured_date_added' => 'Featured Date Added',
            'featured_last_modified' => 'Featured Last Modified',
            'expires_date' => 'Expires Date',
            'date_status_change' => 'Date Status Change',
            'status' => 'Status',
            'affiliate_id' => 'Affiliate ID',
        ];
    }

    public static function find()
    {
        return new FeaturedQuery(get_called_class());

    }

    public function getProduct() {
      return $this->hasOne(\common\models\Products::class, ['products_id' => 'products_id']);
    }

    public function getBackendProductDescription() {
      $languages_id = \Yii::$app->settings->get('languages_id');

      if (\backend\models\ProductNameDecorator::instance()->useInternalNameForListing()) {
        $nameColumn = new \yii\db\Expression("IF(LENGTH(products_internal_name), products_internal_name, products_name)");
      } else {
        $nameColumn = 'products_name';
      }

      return $this->hasOne(\common\models\ProductsDescription::class, ['products_id' => 'products_id'])->via('product')
                ->select(['products_name' => $nameColumn])
                ->addSelect(['platform_id', 'products_id', 'language_id'])
                ->where([\common\models\ProductsDescription::tableName() . '.language_id' => (int)$languages_id,
                         'platform_id' => intval(\common\classes\platform::defaultId())
                  ])
                ->orderBy($nameColumn);
    }

    public function getFeaturedType() {
      $languages_id = \Yii::$app->settings->get('languages_id');

      return $this->hasOne(\common\models\FeaturedTypes::class, ['featured_type_id' => 'featured_type_id'])
                ->andOnCondition([\common\models\FeaturedTypes::tableName() . '.language_id' => (int)$languages_id
                  ]);
    }
}
