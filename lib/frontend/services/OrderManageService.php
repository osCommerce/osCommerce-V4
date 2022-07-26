<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 15.02.18
 * Time: 18:24
 */

namespace frontend\services;

use common\models\Orders;
use common\models\OrdersStatusHistory;
use common\models\repositories\OrderRepository;
use yii\web\NotFoundHttpException;


class OrderManageService {

	private $orderRepository;

	public function __construct( OrderRepository $orderRepository ) {
		$this->orderRepository = $orderRepository;
	}

	public function changeStatus( $order_id, $status, $comments = '' ) {
		$order = Orders::findOne( $order_id );
		if( is_object( $order ) ) {
			$ordersStatusHistory  = OrdersStatusHistory::create( $order_id, $status, 0, $comments );
			$order->orders_status = $status;
			if( ! $order->save() ) {
				throw new \DomainException( "Order saving error" );
			}
			$ordersStatusHistory->link( 'order', $order );
		} else {
			throw new NotFoundHttpException( "Order not found" );
		}
	}

}