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
namespace common\models\queries;

use common\models\Information;
use yii\db\ActiveQuery;

class InformationQuery extends ActiveQuery
{

    public function active()
    {
        return $this->andWhere(['visible' => Information::STATUS_ACTIVE]);
    }
    public function blog($typeId)
    {
        return $this->andWhere(['type' => $typeId]);
    }
    public function disable()
    {
        return $this->andWhere(['visible' => Information::STATUS_DISABLE]);
    }
    public function hide($show = Information::STATUS_HIDE)
    {
        if($show === Information::STATUS_HIDE ){
            return $this->andWhere(['hide' => $show]);
        }
        return $this;
    }
}