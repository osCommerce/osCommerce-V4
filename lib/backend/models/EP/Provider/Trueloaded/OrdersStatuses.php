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

namespace backend\models\EP\Provider\Trueloaded;


use common\api\models\XML\IOCore;

class OrdersStatuses extends XmlBase
{

    public function init()
    {

        $this->ConfigureMap = IOCore::getExportStructure('order_statuses');
        parent::init();

    }

    public function clearLocalData()
    {
        // Delete orders status only
        tep_db_query('DELETE FROM `orders_status` WHERE `orders_status_groups_id` IN (SELECT DISTINCT `orders_status_groups_id` FROM `orders_status_groups` WHERE `orders_status_type_id` = 1)');
    }

}