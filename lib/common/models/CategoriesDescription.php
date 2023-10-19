<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "categories_description".
 *
 * @property integer $categories_id
 * @property integer $language_id
 * @property string $categories_name
 * @property string $categories_heading_title
 * @property string $categories_description
 * @property string $categories_head_title_tag
 * @property string $categories_head_desc_tag
 * @property string $categories_head_keywords_tag
 * @property integer $affiliate_id
 * @property string $categories_seo_page_name
 * @property integer $noindex_option
 * @property integer $nofollow_option
 * @property string $rel_canonical
 * @property string $categories_h1_tag
 * @property string $categories_h2_tag
 * @property string $categories_h3_tag
 * @property string $categories_image_alt_tag_mask
 * @property string $categories_image_title_tag_mask
 */
class CategoriesDescription extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'categories_description';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['categories_id', 'language_id', 'categories_name', 'categories_head_title_tag', 'categories_head_desc_tag', 'categories_head_keywords_tag', 'affiliate_id', 'categories_seo_page_name', 'noindex_option', 'nofollow_option', 'rel_canonical', 'categories_h1_tag', 'categories_h2_tag', 'categories_h3_tag', 'categories_image_alt_tag_mask', 'categories_image_title_tag_mask'], 'required'],
            [['categories_id', 'language_id', 'affiliate_id', 'noindex_option', 'nofollow_option'], 'integer'],
            [['categories_description', 'categories_head_desc_tag', 'categories_head_keywords_tag', 'categories_h2_tag', 'categories_h3_tag'], 'string'],
            [['categories_name', 'categories_heading_title'], 'string', 'max' => 255],
            [['categories_head_title_tag'], 'string', 'max' => 125],
            [['categories_seo_page_name', 'categories_h1_tag', 'categories_image_alt_tag_mask', 'categories_image_title_tag_mask'], 'string', 'max' => 255],
            [['rel_canonical'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'categories_id' => 'Categories ID',
            'language_id' => 'Language ID',
            'categories_name' => 'Categories Name',
            'categories_heading_title' => 'Categories Heading Title',
            'categories_description' => 'Categories Description',
            'categories_head_title_tag' => 'Categories Head Title Tag',
            'categories_head_desc_tag' => 'Categories Head Desc Tag',
            'categories_head_keywords_tag' => 'Categories Head Keywords Tag',
            'affiliate_id' => 'Affiliate ID',
            'categories_seo_page_name' => 'Categories Seo Page Name',
            'noindex_option' => 'Noindex Option',
            'nofollow_option' => 'Nofollow Option',
            'rel_canonical' => 'Rel Canonical',
            'categories_h1_tag' => 'Categories H1 Tag',
            'categories_h2_tag' => 'Categories H2 Tag',
            'categories_h3_tag' => 'Categories H3 Tag',
            'categories_image_alt_tag_mask' => 'Categories Image Alt Tag Mask',
            'categories_image_title_tag_mask' => 'Categories Image Title Tag Mask',
        ];
    }

    public function getCategories()
    {
        return $this->hasOne(Categories::className(), ['categories_id' => 'categories_id']);
    }

    //public function getGroupsCategories() - removed due extracting extension UsersGroupsRestriction - use this
    // if ($model = Acl::checkExtensionTableExist('UserGroupsRestrictions', 'GroupsCategories')) {
    //    $yourModel->innerJoin($model::tableName() ...)

}
