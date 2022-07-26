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

use Yii;
use common\api\models\XML\IOCore;

class Customers extends XmlBase
{
    public function init()
    {

        $this->ConfigureMap = IOCore::getExportStructure('customers');
        parent::init();

    }

    public function prepareExport($useColumns, $filter)
    {
        if ( is_array($filter) ) {
            if (isset($filter['platform_id']) && !empty($filter['platform_id'])) {
                $this->activeQuery->andWhere(['=', 'platform_id', (int)$filter['platform_id']]);
            }
        }
        parent::prepareExport($useColumns, $filter);
    }

    public function clearLocalData()
    {
        \common\helpers\Customer::trunk_customers();

        tep_db_query("TRUNCATE TABLE " . TABLE_PRODUCTS_NOTIFY);
        tep_db_query("TRUNCATE TABLE " . TABLE_VIRTUAL_GIFT_CARD_BASKET);


        tep_db_query("TRUNCATE TABLE wedding_registry");
        tep_db_query("TRUNCATE TABLE wedding_registry_inviting");
        tep_db_query("TRUNCATE TABLE wedding_registry_products");


        tep_db_query("TRUNCATE TABLE ep_holbi_soap_link_customers");
        tep_db_query("TRUNCATE TABLE ep_holbi_soap_kv_storage");

        tep_db_query("TRUNCATE TABLE gdpr_check");
        tep_db_query("TRUNCATE TABLE guest_check");

        tep_db_query("TRUNCATE TABLE personal_catalog");

    }

}