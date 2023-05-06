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

use common\models\behaviors\SimpleNested;
use common\models\queries\CatalogPagesQuery;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "catalog_pages".
 *
 * @property int $catalog_pages_id
 * @property int $platform_id
 * @property string $image
 * @property int $parent_id
 * @property int $status
 * @property int $lft
 * @property int $rgt
 * @property int $lvl
 * @property int $sort_order
 * @property int $updated_at
 * @property int $created_at
 *
 * @property Platforms $platform
 * @property CatalogPagesDescription $descriptionLanguageId
 * @property CatalogPagesDescription[] $descriptions
 * @property CatalogPagesToInformation[] $catalogPagesToInformations
 * @property Information[] $information
 */
class CatalogPages extends \yii\db\ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_DISABLE = 0;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'catalog_pages';
    }

	public function behaviors()
	{
		return [
			TimestampBehavior::class,
			SimpleNested::class,
		];
	}
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['platform_id', 'parent_id'], 'required'],
            [['platform_id', 'parent_id', 'status', 'lft', 'rgt', 'lvl', 'sort_order', 'updated_at', 'created_at'], 'integer'],
            [['image'], 'string', 'max' => 255],
            [['platform_id'], 'exist', 'skipOnError' => true, 'targetClass' => Platforms::class, 'targetAttribute' => ['platform_id' => 'platform_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'catalog_pages_id' => 'Catalog Pages ID',
            'platform_id' => 'Platform ID',
            'image' => 'Image',
            'parent_id' => 'Parent ID',
            'status' => 'Status',
            'lft' => 'Lft',
            'rgt' => 'Rgt',
            'lvl' => 'Lvl',
            'sort_order' => 'Sort Order',
            'updated_at' => 'Updated At',
            'created_at' => 'Created At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPlatform()
    {
        return $this->hasOne(Platforms::class, ['platform_id' => 'platform_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCatalogPagesToInformations()
    {
        return $this->hasMany(CatalogPagesToInformation::class, ['catalog_pages_id' => 'catalog_pages_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getInformation()
    {
        return $this->hasMany(Information::class, ['information_id' => 'information_id'])->viaTable('catalog_pages_to_information', ['catalog_pages_id' => 'catalog_pages_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDescriptions()
    {
        return $this->hasMany(CatalogPagesDescription::class, ['catalog_pages_id' => 'catalog_pages_id'])->indexBy('languages_id');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDescriptionLanguageId()
    {
        return $this->hasOne(CatalogPagesDescription::class, ['catalog_pages_id' => 'catalog_pages_id']);
    }

    /**
     * @inheritdoc
     * @return CatalogPagesQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new CatalogPagesQuery(get_called_class());
    }

    /**
     * @inheritdoc
     * for create new Catalog Page for Infopages
     */
    public static function create($platform_id,$parent_id,$image ='',$status = CatalogPages::STATUS_ACTIVE,$created_at = null,$sort_order = 0)
    {
        if((int)$platform_id < 1 || (int)$parent_id < 0  || (int)$sort_order < 0 ){
            throw new \DomainException('wrong input data');
        }
        $model = new static();
        $model->platform_id = (int)$platform_id;
        $model->parent_id = (int)$parent_id;
        $model->image = $image;
        $model->status = (int)$status;
        $model->sort_order = (int)$sort_order;
        if(!empty($created_at)){
            $model->created_at = $created_at;
        }
        return $model;
    }
	/**
	 * @inheritdoc
	 * for edit Catalog Page for Infopages
	 */
	public function edit($platform_id = null,$parent_id = null,$image = null,$status  = null,$created_at = null,$sort_order = null)
	{
		if(!is_null($platform_id)){
			$this->platform_id = (int)$platform_id;
		}
		if(!is_null($parent_id)){
			$this->parent_id = (int)$parent_id;
		}
		if(!is_null($image)){
			$this->image = $image;
		}
		if(!is_null($status)){
			$this->status = (int)$status;
		}
		if(!is_null($sort_order)){
			$this->sort_order = (int)$sort_order;
		}
        if(!empty($created_at)){
            $this->created_at = $created_at;
        }
	}
}
