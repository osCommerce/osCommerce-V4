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

use common\models\queries\CategoriesQuery;
use Yii;
use yii\db\ActiveRecord;

use paulzi\nestedsets\NestedSetsBehavior;

/**
 * This is the model class for table "categories".
 *
 * @property int $categories_id
 * @property string $categories_image
 * @property string $categories_image_2
 * @property int $parent_id
 * @property int $sort_order
 * @property string $date_added
 * @property string $last_modified
 * @property int $categories_status
 * @property int $manual_control_status
 * @property int $previous_status
 * @property string $last_xml_import
 * @property string $last_xml_export
 * @property int $categories_level
 * @property int $categories_left
 * @property int $categories_right
 * @property string $categories_seo_page_name
 * @property string $categories_old_seo_page_name
 * @property int $show_on_home
 * @property int $ep_holbi_soap_disable_update
 * @property int $created_by_platform_id
 * @property int $maps_id
 *
 * @property Groups[] $groups
 * @property string default_sort_order
 */

class Categories extends ActiveRecord
{
    /**
     * set table name
     * @return string
     */
    public static function tableName()
    {
        return 'categories';
    }

    public function behaviors() {
        return [
            'nestedSets' => [
                'class' => NestedSetsBehavior::class,
                'leftAttribute' => 'categories_left',
                'rightAttribute' => 'categories_right',
                'depthAttribute' => 'categories_level',
                // 'treeAttribute' => 'tree',
            ],
        ];
    }

    public function transactions()
    {
        return [
            self::SCENARIO_DEFAULT => self::OP_ALL,
        ];
    }


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['parent_id', 'sort_order', 'categories_status', 'manual_control_status', 'previous_status', 'categories_level', 'categories_left', 'categories_right', 'show_on_home', 'ep_holbi_soap_disable_update', 'created_by_platform_id', 'maps_id'], 'integer'],
            [['date_added', 'last_modified', 'last_xml_import', 'last_xml_export'], 'safe'],
            [['categories_image', 'categories_image_2', 'categories_image_3'], 'string', 'max' => 128],
            [['categories_seo_page_name', 'categories_old_seo_page_name',], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'categories_id' => 'Categories ID',
            'categories_image' => 'Categories Image',
            'categories_image_2' => 'Categories Image 2',
            'parent_id' => 'Parent ID',
            'sort_order' => 'Sort Order',
            'date_added' => 'Date Added',
            'last_modified' => 'Last Modified',
            'categories_status' => 'Categories Status',
            'manual_control_status' => 'Manual Control Status',
            'previous_status' => 'Previous Status',
            'last_xml_import' => 'Last Xml Import',
            'last_xml_export' => 'Last Xml Export',
            'categories_level' => 'Categories Level',
            'categories_left' => 'Categories Left',
            'categories_right' => 'Categories Right',
            'categories_seo_page_name' => 'Categories Seo Page Name',
            'categories_old_seo_page_name' => 'Categories Old Seo Page Name',
            'show_on_home' => 'Show On Home',
            'ep_holbi_soap_disable_update' => 'Ep Holbi Soap Disable Update',
            'created_by_platform_id' => 'Created By Platform ID',
            'maps_id' => 'Maps ID',
        ];
    }

    /**
     * one-to-many
     * @return \yii\db\ActiveQuery
     */
    public function getDescriptions()
    {
        return $this->hasMany(CategoriesDescription::class, ['categories_id' => 'categories_id']);
    }

    public function getSupplierDiscounts()
    {
        return $this->hasMany(SuppliersCatalogDiscount::class,['category_id' => 'categories_id']);
    }

    public function getSupplierPriceRules()
    {
        return $this->hasMany(SuppliersCatalogPriceRules::class,['category_id' => 'categories_id']);
    }

    //public function getGroupsCategories() - removed due extracting extension UsersGroupsRestriction - use this
    // if ($model = Acl::checkExtensionTableExist('UserGroupsRestrictions', 'GroupsProducts')) {
    //    $yourModel->innerJoin($model::tableName() ...)

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGroups()
    {
        \Yii::warning('Using UserGroupRestrictions table. Not recommended - the table may be not exist in some osCommerce versions');
        return $this->hasMany(Groups::class, ['groups_id' => 'groups_id'])->viaTable('groups_categories', ['categories_id' => 'categories_id']);
    }

    public static function find()
    {
        return new CategoriesQuery(get_called_class());
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPlatforms()
    {
        return $this->hasMany(PlatformsCategories::class, ['categories_id' => 'categories_id'])->indexBy('platform_id');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCurrentPlatform()
    {
      $platform_id = \common\classes\platform::currentId();
        return $this->hasMany(PlatformsCategories::className(), ['categories_id' => 'categories_id'])
            ->andWhere(['{{%platforms_categories}}.platform_id' => $platform_id])
            ;
    }

    /**
     * one-to-many
     * @return \yii\db\ActiveQuery
     */
    public function getDescription()
    {
      $languages_id = \Yii::$app->settings->get('languages_id');
        return $this->hasOne(CategoriesDescription::className(), ['categories_id' => 'categories_id'])
            ->andWhere(['{{%categories_description}}.language_id' => $languages_id])
            ;
    }

    /**
     * get current platform settings
     * @return \yii\db\ActiveQuery
     */
    public function getPlatformSettings()
    {
      $currentPlatformId = \common\classes\platform::currentId();
      return $this->hasOne(CategoriesPlatformSettings::className(), ['categories_id' => 'categories_id'])
            ->andOnCondition(['{{%categories_platform_settings}}.platform_id' => $currentPlatformId]);
    }

/**
 * return homepage categories
 * @return \yii\db\ActiveQuery
 */
    public static function getHomepageCategories() {
      $languages_id = \Yii::$app->settings->get('languages_id');
       return self::find()->active()->withDescription($languages_id)->joinWith(['platformSettings'])
             ->andWhere(['or',
                         '{{%categories}}.show_on_home = 1 and {{%categories_platform_settings}}.show_on_home is null ' ,
                        ['{{%categories_platform_settings}}.show_on_home' => 1],
                        ]
                )
           ;
    }


/**
 * link to products_to_categories
 * @return \yii\db\ActiveQuery
 */
    public function getProductIds() {
       return $this->hasMany(Products2Categories::class, ['categories_id' => 'categories_id'])           ;
    }

    public function getProducts() {
        return $this->hasMany(Products::class, ['products_id' => 'products_id'])->via('productsIds');
    }

// SeoRedirectsNamed model moved to extensions/SeoRedirectsNamed/models
//    public function getSeoRedirects() {
//       return $this->hasMany(SeoRedirectsNamed::class, ['owner_id' => 'categories_id'])->andOnCondition('redirects_type = "category"');
//    }

/**
 * return active parents linked to current platform
 * @return \yii\db\ActiveQuery
 */
    public function getVisibleParents() {
      $currentPlatformId = \common\classes\platform::currentId();
       return $this->getParents()
            ->andWhere(['categories_status' => 1])
            ->joinWith('platforms', false)
            ->andWhere(['platform_id' => $currentPlatformId]);
    }

    /**
     * checks whether the category is visible at current platform:
     * - active itself and all parents
     * - linked to platform
     * @param int $categoryId category id
     * @return bool
     */
    public static function isVisible($categoryId) {
      $ret = false;
      $currentPlatformId = \common\classes\platform::currentId();
      $c = self::find()->alias('c')
          ->andWhere(['c.categories_id' => (int)$categoryId, 'categories_status' => 1])
          ->joinWith('platforms p', false)
          ->andWhere(['platform_id' => $currentPlatformId])
          ->one();

      if ($c) {
        $p = $c->getParents()
            ->andWhere(['categories_status' => 1])
            ->joinWith('platforms', false)
            ->andWhere(['platform_id' => $currentPlatformId])
            ->asArray()->all();

        if (is_array($p) && (!isset($p[0]['parents_id']) || $p[0]['parents_id'] == 0) && $c->categories_level == count($p)+1) {
          $ret=true;
        }
      }
      return $ret;
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ( $insert ) {
            /** @var \common\extensions\UserGroupsRestrictions\UserGroupsRestrictions $ext */
            if ($ext = \common\helpers\Acl::checkExtensionAllowed('UserGroupsRestrictions', 'allowed')) {
                if ( $groupService = $ext::getGroupsService() ){
                    $groupService->addCategoryToAllGroups($this->categories_id);
                }
            }
        }
    }


    public function getPlatform()
    {
        return $this->hasMany(PlatformsCategories::className(), ['categories_id' => 'categories_id']);
    }

// SeoRedirectsNamed model moved to extensions/SeoRedirectsNamed/models
//    public function getSeoRedirectsNamed()
//    {
//        return $this->hasMany(SeoRedirectsNamed::className(), ['owner_id' => 'categories_id'])->andWhere(['redirects_type'=>'category']);
//    }

}