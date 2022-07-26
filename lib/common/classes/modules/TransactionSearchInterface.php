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

namespace common\classes\modules;

use common\services\PaymentTransactionManager;

interface TransactionSearchInterface {

  /**
   * transaction filter/search fields
   * @return array [ [['ENDDATE'], 'datetime', 'format' => 'yyyy-MM-dd HH:mm:ss'], .....]
   */
  public function getFields();

  /**
   * search transaction at gateway
   * @param array $queryParams ['ENDDATE'=> 'STARTDATE' => ...]
   * @return array of transactions
   */
  public function search($queryParams);
    
}
