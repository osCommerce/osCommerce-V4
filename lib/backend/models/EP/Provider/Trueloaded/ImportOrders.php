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

class ImportOrders extends ImportXmlBase {
    protected $feed = 'orders';
    protected $providerClass = 'backend\\models\\EP\\Provider\\Trueloaded\\Orders';
}
