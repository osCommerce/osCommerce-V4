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

use common\classes\Migration;

/**
 * Class m230630_080745_categories_images
 */
class m230630_080745_categories_images extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if (!$this->isFieldExists('image_types_id', 'categories_images')) {
            $this->addColumn('categories_images', 'image_types_id', $this->integer(11)->notNull()->defaultValue(0));
        }
        if (!$this->isFieldExists('position', 'categories_images')) {
            $this->addColumn('categories_images', 'position', $this->string(64)->notNull()->defaultValue(''));
        }
        if (!$this->isFieldExists('fit', 'categories_images')) {
            $this->addColumn('categories_images', 'fit', $this->string(64)->notNull()->defaultValue(''));
        }

        $this->update('image_types', ['image_types_name' => 'Category gallery add'], ['image_types_name' => 'Category galery small']);

        $imageTypes = \common\models\ImageTypes::findOne(['image_types_name' => 'Category gallery add']);
        if ($imageTypes) {
            $this->update('categories_images', ['image_types_id' => $imageTypes->image_types_id]);
        }

        $this->addTranslation('admin/categories', [
            'CATEGORY_HERO_IMAGE_RESPONSIVE' => 'Hero image for responsive design'
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
    }
}
