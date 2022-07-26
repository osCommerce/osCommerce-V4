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


/**
 * This is the model class for table "products_xsell_type".
 *
 * @property int $xsell_type_id
 * @property int $language_id
 * @property string $xsell_type_name
 * @property int $link_update_disable
 */
class ProductsXsellType extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'products_xsell_type';
    }



}
