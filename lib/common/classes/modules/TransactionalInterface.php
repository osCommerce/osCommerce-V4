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

interface TransactionalInterface {
    
    /**
     * @param string $transaction_id transaction id from payment system
     * @param PaymentTransactionManager $tManager default null
     */
    public function getTransactionDetails($transaction_id, PaymentTransactionManager $tManager = null);

    public function canRefund($transaction_id);
    
    public function refund($transaction_id, $amount = 0);
    
    public function canVoid($transaction_id);
    
    public function void($transaction_id);
    
    /**
     * can capture/release/authenticate - module should call correct method according transaction details/state
     * @param string $transaction_id transaction id from payment system
     * @return int|false  1 - auth, 2 - deferred, false - not allowed
     */
    public function canCapture($transaction_id);

    public function capture($transaction_id, $amount = 0);

    public function canReauthorize($transaction_id);

    public function reauthorize($transaction_id, $amount = 0);

}
