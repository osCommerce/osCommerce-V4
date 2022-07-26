<?php
/*
 * This file is part of osCommerce ecommerce platform.
 * osCommerce the ecommerce
 *
 * @link https://www.oscommerce.com
 * @copyright Copyright (c) 2005 Holbi Group Ltd
 *
 * Released under the GNU General Public License
 * For the full copyright and license information, please view the LICENSE.TXT file that was distributed with this source code.
 */

namespace common\api\models\AR;

use yii\db\Expression;

class Currencies extends EPMap
{

    protected $hideFields = [
    ];

    protected $childCollections = [
    ];

    protected $indexedCollections = [
    ];

    public static function tableName()
    {
        return TABLE_CURRENCIES;
    }

    public static function primaryKey()
    {
        return ['currencies_id'];
    }

    public function rules() {
        return array_merge(
            parent::rules(),
            [
             ///   ['customers_company_vat', 'default', 'value' => '']
            ]
        );
    }

}