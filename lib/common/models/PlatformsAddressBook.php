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
 * This is the model class for table "platforms_address_book".
 *
 * @property int $platforms_address_book_id
 * @property int $platform_id
 * @property int $is_default
 * @property string $entry_company
 * @property string $entry_company_vat
 * @property string $entry_company_reg_number
 * @property string $entry_postcode
 * @property string $entry_street_address
 * @property string $entry_suburb
 * @property string $entry_city
 * @property string $entry_state
 * @property int $entry_country_id
 * @property int $entry_zone_id
 *
 * @property string $country
 *
 */
class PlatformsAddressBook extends ActiveRecord
{
    /**
     * set table name
     * @return string
     */
    public static function tableName()
    {
        return 'platforms_address_book';
    }

    /**
     * one-to-one
     * @return object
     */
    public function getPlatform()
    {
        return $this->hasOne(Platforms::className(), ['platform_id' => 'platform_id']);
    }

	/**
	 * @return \yii\db\ActiveQuery
	 */
    public function getCountry(){
	    return $this->hasOne(Countries::className(), ['countries_id' => 'entry_country_id']);
    }
}