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

class Categories extends XmlBase
{
    public function init()
    {

        $this->ConfigureMap = IOCore::getExportStructure('categories');
        parent::init();

    }

    public function clearLocalData()
    {
        $query = tep_db_query("select * from " . TABLE_CATEGORIES);
        while ($data = tep_db_fetch_array($query)){
            @unlink(DIR_FS_CATALOG_IMAGES . $data['categories_image']);
        }
        tep_db_query("DELETE FROM " . TABLE_FILTERS . " WHERE filters_of = 'category'");
        parent::clearLocalData();
    }

}