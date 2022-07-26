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

class Themes extends XmlBase
{
    public function init()
    {

        $this->ConfigureMap = IOCore::getExportStructure('themes');
        parent::init();

    }

    public function prepareExport($useColumns, $filter)
    {
        //hideProperties
        if ( is_array($filter) ) {
            $this->withImages = ( isset($filter['with_images']) && $filter['with_images']);
            if ( !$this->withImages ) {
                $keyData = key($this->ConfigureMap['Data']);
                $this->ConfigureMap['Data'][$keyData]['properties']['themeBackup'] = false;
                parent::init();
            }
        }

        parent::prepareExport($useColumns, $filter);
    }


}