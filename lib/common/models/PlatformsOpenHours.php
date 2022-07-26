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
use common\extensions\ProductDesigner\models as ProductDesignerORM;

class PlatformsOpenHours extends ActiveRecord
{
    /**
     * set table name
     * @return string
     */
    public static function tableName()
    {
        return 'platforms_open_hours';
    }

    /**
     * one-to-one
     * @return object
     */
    public function getPlatform()
    {
        return $this->hasOne(Platforms::className(), ['platform_id' => 'platform_id']);
    }
}