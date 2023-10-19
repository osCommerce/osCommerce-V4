<?php

namespace common\models;

use common\models\queries\InformationQuery;
use Yii;

/**
 * This is the model class for table "information".
 *
 * @property int $platform_id
 * @property int $information_id
 * @property string $visible
 * @property int $v_order
 * @property string $info_title
 * @property string $description
 * @property int $languages_id
 * @property string $page_title
 * @property string $meta_title
 * @property string $page
 * @property string $scope
 * @property int $affiliate_id
 * @property string $page_type
 * @property string $meta_description
 * @property string $meta_key
 * @property string $seo_page_name
 * @property string $old_seo_page_name
 * @property string $date_added
 * @property string $last_modified
 * @property int $no_logged
 * @property int $hide
 * @property string $template_name
 * @property int $noindex_option
 * @property int $nofollow_option
 * @property string $rel_canonical
 * @property string $information_h1_tag
 * @property string $information_h2_tag
 * @property string $information_h3_tag
 * @property int $type
 * @property string $image
 * @property string $description_short
 *
 * @property CatalogPagesToInformation[] $catalogPagesToInformations
 * @property CatalogPages[] $catalogPages
 * @property Platforms $platform
 */
class Information extends \yii\db\ActiveRecord
{
    const STATUS_DISABLE = 0;
    const STATUS_ACTIVE = 1;

    const STATUS_HIDE = 1;
    const STATUS_SHOW = 0;

    const TYPE_DEFAULT = 0;
    const TYPE_CATALOG_PAGES = 1;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'information';
    }

    public static function mergeDescriptionColumnList()
    {
        $merge_list = [];
        foreach (static::getTableSchema()->columns as $column){
            if ($column->phpType=='string'){
                $merge_list[] = $column->name;
            }
        }
        return $merge_list;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['platform_id', 'v_order', 'languages_id', 'affiliate_id', 'no_logged', 'hide', 'noindex_option', 'nofollow_option', 'type', 'maps_id'], 'integer'],
            [['visible', 'description', 'scope', 'meta_description','description_short', 'meta_key'], 'string'],
            [['date_added', 'last_modified'], 'safe'],
            [['info_title', 'page_title', 'meta_title', 'page', 'information_h1_tag', 'information_h2_tag', 'image', 'information_h3_tag'], 'string', 'max' => 255],
            [['page_type'], 'string', 'max' => 6],
            [['seo_page_name', 'old_seo_page_name', 'rel_canonical'], 'string', 'max' => 128],
            [['template_name'], 'string', 'max' => 256],
            [['platform_id'], 'exist', 'skipOnError' => true, 'targetClass' => Platforms::class, 'targetAttribute' => ['platform_id' => 'platform_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'platform_id' => 'Platform ID',
            'information_id' => 'Information ID',
            'visible' => 'Visible',
            'v_order' => 'V Order',
            'info_title' => 'Info Title',
            'description' => 'Description',
            'languages_id' => 'Languages ID',
            'page_title' => 'Page Title',
            'meta_title' => 'Meta Title',
            'page' => 'Page',
            'scope' => 'Scope',
            'affiliate_id' => 'Affiliate ID',
            'page_type' => 'Page Type',
            'meta_description' => 'Meta Description',
            'meta_key' => 'Meta Key',
            'seo_page_name' => 'Seo Page Name',
            'old_seo_page_name' => 'Old Seo Page Name',
            'date_added' => 'Date Added',
            'last_modified' => 'Last Modified',
            'no_logged' => 'No Logged',
            'hide' => 'Hide',
            'template_name' => 'Template Name',
            'noindex_option' => 'Noindex Option',
            'nofollow_option' => 'Nofollow Option',
            'rel_canonical' => 'Rel Canonical',
            'information_h1_tag' => 'Information H1 Tag',
            'information_h2_tag' => 'Information H2 Tag',
            'information_h3_tag' => 'Information H3 Tag',
            'type' => 'type',
            'image' => 'Image',
            'description_short' => 'Short Description',
            'maps_id' => 'Maps id',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCatalogPagesToInformations()
    {
        return $this->hasMany(CatalogPagesToInformation::class, ['information_id' => 'information_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCatalogPages()
    {
        return $this->hasMany(CatalogPages::class, ['catalog_pages_id' => 'catalog_pages_id'])->viaTable('catalog_pages_to_information', ['information_id' => 'information_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPlatform()
    {
        return $this->hasOne(Platforms::class, ['platform_id' => 'platform_id']);
    }

    /**
     * @inheritdoc
     * @return InformationQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new InformationQuery(get_called_class());
    }
}
