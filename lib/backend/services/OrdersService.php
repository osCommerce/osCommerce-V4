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


use common\classes\currencies;
use common\classes\extended\OrderAbstract;
use common\classes\platform_config;
use common\models\Orders;
use common\models\OrdersStatusHistory;
use common\models\repositories\NotFoundException;
use common\models\repositories\OrderRepository;
use common\models\repositories\OrdersStatusHistoryRepository;
use common\services\CustomersService;

class OrdersService
{
    /** @var OrderRepository */
    private $orderRepository;
    /** @var CustomersService */
    private $customersService;
    /** @var OrdersTotalService */
    private $ordersTotalService;
    /** @var OrdersStatusHistoryRepository */
    private $ordersStatusHistoryRepository;
    public function __construct(
        OrderRepository $orderRepository,
        CustomersService $customersService,
        OrdersTotalService $ordersTotalService,
        OrdersStatusHistoryRepository $ordersStatusHistoryRepository
    )
    {
        $this->orderRepository = $orderRepository;
        $this->customersService = $customersService;
        $this->ordersTotalService = $ordersTotalService;
        $this->ordersStatusHistoryRepository = $ordersStatusHistoryRepository;
    }

    /**
     * @param int|array $orderId
     * @param bool $asArray
     * @return Orders|Orders[]
     */
    public function getById($orderId, bool $asArray = false)
    {
        return $this->orderRepository->getById($orderId,$asArray);
    }

    public function getTotalsFromDataOrder(array $totals)
    {
        $result = [];
        for ($i = 0, $n = count($totals); $i < $n; $i++) {
            if (file_exists(DIR_FS_CATALOG . DIR_WS_MODULES . 'order_total/' . $totals[$i]['class'] . '.php')) {
                include_once DIR_FS_CATALOG . DIR_WS_MODULES . 'order_total/' . $totals[$i]['class'] . '.php';
            }
            if (class_exists($totals[$i]['class'])) {
                $object = new $totals[$i]['class'];
                $result[$object->code] = $totals[$i];
            }
        }
        return $result;
    }

    public function sendRequestPay(OrderAbstract $order,currencies $currencies)
    {
        if(!is_object($order) || !is_object($currencies)) {
            throw new NotFoundException('Order data not found');
        }
        $totals = $this->getTotalsFromDataOrder($order->totals);

        $ot_paid_value = $totals['ot_paid']['value_inc_tax'] ?? 0;
        $ot_total_value = $totals['ot_total']['value_inc_tax'] ?? 0;
        $update_and_pay_amount = (round($ot_total_value, 2)-round($ot_paid_value, 2));

        /**
         * @var platform_config $platform_config
         */
        $platform_config = \Yii::$app->get('platform')->config($order->info['platform_id']);

        $STORE_NAME = $platform_config->const_value('STORE_NAME');
        $STORE_OWNER_EMAIL_ADDRESS = $platform_config->const_value('STORE_OWNER_EMAIL_ADDRESS');
        $STORE_OWNER = $platform_config->const_value('STORE_OWNER');

        $customer = $this->customersService->getById((int)$order->customer['customer_id']);
        $token = $this->customersService->setLoginToken($customer);

        $email_params = [];
        $email_params['STORE_NAME'] = $STORE_NAME;
        $email_params['CUSTOMER_NAME'] = $order->customer['firstname'] . ' ' . $order->customer['lastname'];
        $email_params['ORDER_NUMBER'] = method_exists($order, 'getOrderNumber')?$order->getOrderNumber():$order->order_id;
        $email_params['REQUEST_MESSAGE'] = $currencies->format(abs($update_and_pay_amount));
        $email_params['REQUEST_URL'] = tep_catalog_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'action=payment_request&order_id=' . $order->order_id . '&email_address=' . $order->customer['email_address'] . '&token=' . $token, 'SSL', false);
        $email_params['CUSTOMER_FIRSTNAME'] = $order->customer['firstname'];
        $email_params['ORDER_DATE_LONG'] = strftime(DATE_FORMAT_LONG);
        $email_params['ORDER_DATE_LONG'] = strftime(DATE_FORMAT_LONG);
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('DelayedDespatch', 'allowed')) {
            $email_params['ORDER_DATE_LONG'] .= $ext::mailInfo($order->info['delivery_date']);
        }
        $email_params['PRODUCTS_ORDERED'] = $order->getProductsHtmlForEmail();

        $email_params['ORDER_TOTALS'] = '';
        $order_total_output = [];
        foreach ($order->totals as $total) {
            if (class_exists($total['code'])) {
                if (\Yii::$container->get($total['code'])->visibility()) {
                    $platformId = 0;
                    if( (!defined('PLATFORM_ID') || PLATFORM_ID == 0) && ((int)$order->info['platform_id'] > 0) ){
                        $platformId = $order->info['platform_id'];
                    }elseif (defined('PLATFORM_ID') && PLATFORM_ID > 0){
                        $platformId = PLATFORM_ID;
                    }
                    if (\Yii::$container->get($total['code'])->visibility($platformId, 'TEXT_EMAIL')) {
                        $order_total_output[] = \Yii::$container->get($total['code'])->displayText($platformId, 'TEXT_EMAIL', $total);
                    }
                }
            }
        }
        $email_params['ORDER_TOTALS'] = \frontend\design\boxes\email\OrderTotals::widget(['params' => ['order_total_output' => $order_total_output , 'platform_id' => $order->info['platform_id']]]);
        $email_params['BILLING_ADDRESS'] = \common\helpers\Address::address_format($order->billing['format_id'],$order->billing,0, '', '<br>');
        $email_params['DELIVERY_ADDRESS'] = '';
        if($order->content_type !== 'virtual'){
            $email_params['DELIVERY_ADDRESS'] = \common\helpers\Address::address_format($order->delivery['format_id'],$order->delivery,0, '', '<br>');
        }
        $email_params['PAYMENT_METHOD'] = $order->info['payment_method'];
        $email_params['SHIPPING_METHOD'] = $order->info['shipping_method'];

        list($email_subject, $email_text) = \common\helpers\Mail::get_parsed_email_template('Request for payment', $email_params, -1, $order->info['platform_id']);
        \common\helpers\Mail::send(
            $order->customer['firstname'] . ' ' . $order->customer['lastname'], $order->customer['email_address'], $email_subject, $email_text, $STORE_OWNER, $STORE_OWNER_EMAIL_ADDRESS, [], '', '', ['add_br' => 'no', 'platform_id' => $order->info['platform_id']]
        );
        return ['messageType' => 'success', 'message' => SUCCESS_ORDER_UPDATED];
    }

    public function setPayOnOrder(Orders $order, $amount, currencies $currencies, bool $addComment = false, int $adminId = 0)
    {
        if(!is_numeric($amount) ) {
            throw new NotFoundException('Order data not found');
        }
        $totals = $this->ordersTotalService->getByOrderId($order->orders_id);
        $otTotal = $totals['ot_total'];
        $taxRate = 1;
        if($otTotal->value_inc_tax > 0){
            $taxRate = $otTotal->value_exc_vat / $otTotal->value_inc_tax;
        }
        $otPaid = $totals['ot_paid'];
        $otPaidNewValue = $otPaid->value_inc_tax + $amount * $currencies->get_market_price_rate($order->currency, DEFAULT_CURRENCY);
        $otPaidNewText = $currencies->format($otPaidNewValue, true, $order->currency, $order->currency_value);


        $otData = [
            'value_inc_tax' => $otPaidNewValue,
            'text_inc_tax' => $otPaidNewText,
            'value' => $otPaidNewValue,
            'text' => $otPaidNewText,
            'value_exc_vat' => $otPaidNewValue * $taxRate,
            'text_exc_tax' => $currencies->format($otPaidNewValue * $taxRate, true, $order->currency, $order->currency_value),
        ];
        $this->ordersTotalService->update($otPaid,$otData);
        $otDue = $totals['ot_due'];
        $otDueNewValue = $otDue->value_inc_tax - $amount * $currencies->get_market_price_rate($order->currency, DEFAULT_CURRENCY);
        if($otDueNewValue < 0 ) {
            $otDueNewValue = 0;
        }
        $otDueNewText = $currencies->format($otDueNewValue, true, $order->currency, $order->currency_value);

        $otData = [
            'value_inc_tax' => $otDueNewValue,
            'text_inc_tax' => $otDueNewText,
            'value' => $otDueNewValue,
            'text' => $otDueNewText,
            'value_exc_vat' => $otDueNewValue * $taxRate,
            'text_exc_tax' => $currencies->format($otDueNewValue * $taxRate, true, $order->currency, $order->currency_value),
        ];
        $this->ordersTotalService->update($otDue,$otData);
        if($addComment) {
            $comments = ' ' . TEXT_PAID_AMOUNT . ' ' . $currencies->format($amount, true, $order->currency, $order->currency_value);
            $this->changeStatus($order,$order->orders_status,$comments,$adminId);
        }
        return true;
    }

    /**
     * @param Orders $order
     * @param array $params
     * @param bool $validate
     * @param bool $safeOnly
     * @return array|bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function edit(Orders $order, array $params = [], bool $validate = false, bool $safeOnly = false)
    {
        return $this->orderRepository->edit($order,$params,$validate,$safeOnly);
    }

    /**
     * @param Orders $order
     * @param int $orderStatusId
     * @param string $comments
     * @param int $customerNotified
     * @param int $adminId
     * @param string $smsComments
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function changeStatus(Orders $order, int $orderStatusId, string $comments = '', int $customerNotified = 0, int $adminId = 0, string $smsComments = '')
    {
        $this->orderRepository->changeStatus($order, $orderStatusId);
        $this->addHistory($order, $orderStatusId, $comments, $customerNotified, $adminId, $smsComments);
    }
    
    public function addHistory(Orders $order, int $orderStatusId, string $comments = '', int $customerNotified = 0, int $adminId = 0, string $smsComments = '')
    {
        $orderStatusHistory = $this->ordersStatusHistoryRepository->create($order->orders_id, $orderStatusId, $comments, $customerNotified, $adminId, $smsComments);
        $this->ordersStatusHistoryRepository->save($orderStatusHistory);
    }

}
