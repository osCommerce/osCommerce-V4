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

use yii\behaviors\SluggableBehavior;

/**
 * This is the model class for table "catalog_pages_description".
 *
 * @property int $catalog_pages_id
 * @property int $languages_id
 * @property string $name
 * @property string $description_short
 * @property string $description
 * @property string $slug
 * @property string $meta_title
 * @property string $meta_description
 * @property string $meta_keyword
 * @property string $h1_tag
 * @property string $h2_tag
 * @property string $h3_tag
 *
 * @property Languages $languages
 */
class CatalogPagesDescription extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'catalog_pages_description';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['catalog_pages_id', 'languages_id'], 'required'],
            [['catalog_pages_id', 'languages_id'], 'integer'],
            [['description_short', 'description'], 'string'],
            [['name', 'slug', 'meta_title', 'meta_description', 'meta_keyword','h1_tag','h2_tag','h3_tag'], 'string', 'max' => 255],
            //[['slug'], 'unique'],
            [['catalog_pages_id', 'languages_id'], 'unique', 'targetAttribute' => ['catalog_pages_id', 'languages_id']],
            [['languages_id'], 'exist', 'skipOnError' => true, 'targetClass' => Languages::class, 'targetAttribute' => ['languages_id' => 'languages_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'catalog_pages_id' => 'Catalog Pages ID',
            'languages_id' => 'Languages ID',
            'name' => 'Name',
            'description_short' => 'Description Short',
            'description' => 'Description',
            'slug' => 'Slug',
            'meta_title' => 'Meta Title',
            'meta_description' => 'Meta Description',
            'meta_keyword' => 'Meta Keyword',
            'h1_tag' => 'H1 Tag',
            'h2_tag' => 'H2 Tag',
            'h3_tag' => 'H3 Tag',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLanguages()
    {
        return $this->hasOne(Languages::class, ['languages_id' => 'languages_id']);
    }

    public function behaviors()
    {
        return [
            [
                'class' => SluggableBehavior::class,
                'attribute' => 'name',
            ],
        ];
    }
    /**
     * @inheritdoc
     * for create new Catalog Page Description for Catalog Page
     */
    public static function create($catalog_pages_id, $languages_id,$name='',$description_short='',$description='',$slug = '',$meta_title = '',$meta_description = '',$meta_keyword = '',$h1_tag = '',$h2_tag = '',$h3_tag = '')
    {
        $model = new static();
        $model->catalog_pages_id = (int)$catalog_pages_id;
        $model->languages_id = (int)$languages_id;
        $model->name = $name;
        $model->description_short = $description_short;
        $model->description = $description;
        $model->slug = $slug;
        $model->meta_title = $meta_title;
        $model->meta_description = $meta_description;
        $model->meta_keyword = $meta_keyword;
        $model->h1_tag = $h1_tag;
        if(is_array($h2_tag)){
            $model->h2_tag = implode("\n",$h2_tag);
        }else{
            $model->h2_tag = $h2_tag;
        }
        if(is_array($h3_tag)){
            $model->h3_tag = implode("\n",$h3_tag);
        }else{
            $model->h3_tag = $h3_tag;
        }
        return $model;
    }

	/**
	 * @inheritdoc
	 * for create edit Catalog Page Description for Catalog Page
	 */
	public function edit($catalog_pages_id = null, $languages_id = null,$name = null,$description_short = null,$description = null,$slug = null,$meta_title = null,$meta_description = null,$meta_keyword = null,$h1_tag = null,$h2_tag = null,$h3_tag = null)
	{
		if(!is_null($catalog_pages_id)){
			$this->catalog_pages_id = (int)$catalog_pages_id;
		}
		if(!is_null($languages_id)){
			$this->languages_id = (int)$languages_id;
		}
		if(!is_null($name)){
			$this->name = $name;
		}
		if(!is_null($description_short)){
			$this->description_short = $description_short;
		}
		if(!is_null($description)){
			$this->description = $description;
		}
		if(!is_null($slug)){
			$this->slug = $slug;
		}
		if(!is_null($meta_title)){
			$this->meta_title = $meta_title;
		}
		if(!is_null($meta_description)){
			$this->meta_description = $meta_description;
		}
        if(!is_null($meta_keyword)){
            $this->meta_keyword = $meta_keyword;
        }
        if(!is_null($h1_tag)){
            $this->h1_tag = $h1_tag;
        }
        if(!is_null($h2_tag)){
		    if(is_array($h2_tag)){
                $this->h2_tag = implode("\n",$h2_tag);
            }else{
                $this->h2_tag = $h2_tag;
            }
        }
        if(!is_null($h3_tag)){
            if(is_array($h3_tag)){
                $this->h3_tag = implode("\n",$h3_tag);
            }else{
                $this->h3_tag = $h3_tag;
            }
        }

	}
}
