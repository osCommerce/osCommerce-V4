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

namespace backend\models\forms\catalogpages;

use yii\base\Model;

class CatalogsPageDescriptionForm extends Model {

	public $languages_id;
	public $description_short;
	public $description;
	public $name;
    public $slug;
    public $meta_title;
    public $meta_description;
    public $meta_keyword;
    public $h1_tag;
    public $h2_tag = [''];
    public $h3_tag = [''];

    public function rules()
    {
        return [
            [['languages_id'], 'required'],
            ['languages_id', 'integer'],
            [['description_short', 'description'], 'string'],
            [['name', 'slug', 'meta_title', 'meta_description', 'meta_keyword','h1_tag'], 'string', 'min' => 2  , 'max' => 255],
	        [['name', 'slug', 'meta_title', 'meta_description', 'meta_keyword','h1_tag'], 'filter', 'filter' => 'trim', 'skipOnArray' => true],
            [['h2_tag', 'h3_tag'], 'each', 'rule' => ['string']],
            //[['slug'], 'unique', 'skipOnError' => true, 'targetClass' => CatalogPagesDescription::class, 'targetAttribute' => ['slug' => 'slug']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'languages_id' => TEXT_LANGUAGES,
            'name' => TABLE_TEXT_NAME,
            'description_short' => TEXT_PRODUCTS_DESCRIPTION_SHORT,
            'description' => TEXT_DESCRIPTION,
            'slug' => TEXT_INFO_SEO_PAGE_NAME,
            'meta_title' => TEXT_TITLE,
            'meta_description' => TEXT_DESCRIPTION,
            'meta_keyword' => TEXT_KEYWORDS,
            'h1_tag' => TEXT_H1_TAG,
            'h2_tag' => TEXT_H2_TAG,
            'h3_tag' => TEXT_H3_TAG,
        ];
    }


}

