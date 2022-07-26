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

/**
 * This is the model class for table "specials_prices".
 *
 * @property int $specials_id
 * @property int $currencies_id
 * @property int $groups_id
 * @property double $specials_new_products_price
 */
class SpecialsPrices extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'specials_prices';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['specials_id', 'currencies_id', 'groups_id'], 'required'],
            [['specials_id', 'currencies_id', 'groups_id'], 'integer'],
            [['specials_new_products_price'], 'number'],
            [['specials_id', 'currencies_id', 'groups_id'], 'unique', 'targetAttribute' => ['specials_id', 'currencies_id', 'groups_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'specials_id' => 'Specials ID',
            'currencies_id' => 'Currencies ID',
            'groups_id' => 'Groups ID',
            'specials_new_products_price' => 'Specials New Products Price',
        ];
    }

    /**
     * delete lost (without related special) price
     */
    public static function cleanup() {
      self::deleteAll([
        'not in',
        'specials_id',
        (Specials::find()->select('specials_id')->distinct())
      ])
      ;

    }
}
