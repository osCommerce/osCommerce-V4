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
 * This is the model class for table "specials_types".
 *
 * @property int $specials_type_id
 * @property int $language_id
 * @property string $specials_type_name
 * @property string $specials_type_code
 */
class SpecialsTypes extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'specials_types';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['specials_type_id', 'language_id'], 'required'],
            [['specials_type_id', 'language_id'], 'integer'],
            [['specials_type_name'], 'string', 'max' => 64],
            [['specials_type_code'], 'string', 'max' => 12],
            [['specials_type_id', 'language_id'], 'unique', 'targetAttribute' => ['specials_type_id', 'language_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'specials_type_id' => 'Specials Type ID',
            'language_id' => 'Language ID',
            'specials_type_name' => 'Specials Type Name',
            'specials_type_code' => 'Specials Type Code',
        ];
    }
}
