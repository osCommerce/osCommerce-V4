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


use common\models\Platforms;
use yii\base\Model;

class CatalogsPageForm extends Model {

	public $platform_id;
	public $parent_id;
	public $status;
	public $image;
	public $image_delete;
    public $imageGallery;
    public $created_at;
    public $created_at_view;

    public function rules()
    {
        return [
            [['platform_id', 'parent_id'], 'required'],
            [['platform_id', 'parent_id', 'status','image_delete'], 'integer'],
            [['image','imageGallery'], 'string', 'max' => 255],
            ['created_at', 'date', 'format' => 'php:Y-m-d'],
            ['created_at_view', 'date', 'format' => 'php:d M Y'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'platform_id' => TABLE_HEADING_PLATFORM,
            'image' => TEXT_IMAGE_,
            'parent_id' => TEXT_INFO_HEADING_PARENT_CATEGORY_TEXT,
            'status' => TABLE_HEADING_STATUS,
            'created_at' => TABLE_HEADING_DATE_ADDED,
            'created_at_view' => TABLE_HEADING_DATE_ADDED,
        ];
    }

}

