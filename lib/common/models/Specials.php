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
use common\models\queries\SpecialsQuery;

/**
 * This is the model class for table "specials".
 *
 * @property int $specials_id
 * @property int $products_id
 * @property double $specials_new_products_price
 * @property string $specials_date_added
 * @property string $specials_last_modified
 * @property string $expires_date
 * @property string $date_status_change
 * @property int $status 
 * @property int $specials_enabled 
 * @property int $specials_disabled 
 * @property string $start_date
 */
class Specials extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'specials';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['products_id', 'status', 'specials_disabled', 'specials_enabled', 'max_per_order', 'total_qty'], 'integer'],
            [['max_per_order', 'total_qty'], 'default', 'value' => 0],
            [['specials_new_products_price'], 'number'],
            [['specials_date_added', 'specials_last_modified', 'expires_date', 'date_status_change', 'start_date'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'specials_id' => 'Specials ID',
            'products_id' => 'Products ID',
            'specials_new_products_price' => 'Specials New Products Price',
            'specials_date_added' => 'Specials Date Added',
            'specials_last_modified' => 'Specials Last Modified',
            'expires_date' => 'Expires Date',
            'date_status_change' => 'Date Status Change',
            'status' => 'Status',
            'specials_disabled' => 'Force disabled',
            'specials_enabled' => 'Force enabled',
            'max_per_order' => 'Max per order',
            'total_qty' => 'Max q-ty',
            'start_date' => 'Start Date',
        ];
    }

    public function beforeDelete() {
      SpecialsPrices::deleteAll(['specials_id' => $this->specials_id]);
      return parent::beforeDelete();
    }
    
    public function beforeSave($insert) {
      $this->specials_last_modified = date(\common\helpers\Date::DATABASE_DATETIME_FORMAT);
      return parent::beforeSave($insert);
    }
    
    public static function find()
    {
        return new SpecialsQuery(get_called_class());

    }
    public function getPrices() {
      return $this->hasMany(\common\models\SpecialsPrices::class, ['specials_id' => 'specials_id']);
    }

    public function getProductPrices() {
      return $this->hasMany(\common\models\ProductsPrices::class, ['products_id' => 'products_id']);
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
                ->andOnCondition(['language_id' => (int)$languages_id,
                         'platform_id' => intval(\common\classes\platform::defaultId())
                  ])
                ->orderBy($nameColumn);
    }


    public function getSpecialsType() {
      $languages_id = \Yii::$app->settings->get('languages_id');

      return $this->hasOne(\common\models\SpecialsTypes::class, ['specials_type_id' => 'specials_type_id'])
                ->andOnCondition([\common\models\SpecialsTypes::tableName() . '.language_id' => (int)$languages_id
                  ]);
    }

}
