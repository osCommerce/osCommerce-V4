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

namespace backend\services;


use common\models\OrdersTotal;
use common\models\repositories\OrdersTotalRepository;

class OrdersTotalService
{
    /**
     * @var OrdersTotalRepository
     */
    private $ordersTotalRepository;

    public function __construct(OrdersTotalRepository $ordersTotalRepository)
    {
        $this->ordersTotalRepository = $ordersTotalRepository;
    }

    public function getByOrderId($orderId,$asArray = false)
    {
        return $this->ordersTotalRepository->getByOrderId($orderId,$asArray);
    }
    public function update( OrdersTotal $orderTotals, $params = [], $validate = false, $safeOnly = false )
    {
        return $this->ordersTotalRepository->edit($orderTotals,$params,$validate,$safeOnly);
    }
}