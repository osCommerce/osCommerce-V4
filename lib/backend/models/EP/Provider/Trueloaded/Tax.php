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

class Tax extends XmlBase
{
    public function init()
    {

        $this->ConfigureMap = IOCore::getExportStructure('taxes');
        parent::init();

    }

}