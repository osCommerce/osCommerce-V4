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
use yii\db\ActiveRecord;

/**
 * This is the model class for table "tax_class".
 *
 * @property integer $tax_class_id
 * @property string $tax_class_title
 * @property string $tax_class_description
 * @property string $last_modified
 * @property string $date_added
 */
class TaxClass extends ActiveRecord
{
    /**
     * set table name
     * @return string
     */
    public static function tableName()
    {
        return 'tax_class';
    }

    public function getTaxRateList()
    {
        return $this->hasMany(TaxRates::className(), ['tax_class_id' => 'tax_class_id']);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tax_class_title', 'tax_class_description'], 'required'],
            [['last_modified', 'date_added'], 'safe'],
            [['tax_class_title'], 'string', 'max' => 32],
            [['tax_class_description'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'tax_class_id' => 'Tax Class ID',
            'tax_class_title' => 'Tax Class Title',
            'tax_class_description' => 'Tax Class Description',
            'last_modified' => 'Last Modified',
            'date_added' => 'Date Added',
        ];
    }
}