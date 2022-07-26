<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 27.02.18
 * Time: 10:41
 */

namespace backend\controllers;


use common\extensions\UkrPost\services\Service;
use common\extensions\UkrPost\UkrPost;
use common\extensions\UkrPost\models\ShippingUkrpostOrderParams;
use common\models\Orders;
use Imagine\Exception\RuntimeException;

class ShipmentHelperController extends Sceleton {


	public function __construct($id, $module)
	{
		parent::__construct($id, $module);

		$order_id = \Yii::$app->request->get('order_id', 0);
		if($order_id){
			$order = Orders::findOne(['orders_id' => $order_id]);
			\Yii::$app->get('platform')->config($order->platform_id)->constant_up();
			return;
		}

		if ( \Yii::$app->request->isPost ) {
			$order_id = \Yii::$app->request->post('order_id', 0);
			if($order_id){
				$order = Orders::findOne(['orders_id' => $order_id]);
				\Yii::$app->get('platform')->config($order->platform_id)->constant_up();
				return;
			}
		}


	}

	public function actionCreateUkrPostPost() {
		$order_id = \Yii::$app->request->get( 'order_id' );
		$action   = \Yii::$app->request->get( 'action' );

		$params = ShippingUkrpostOrderParams::findOne( [ 'order_id' => $order_id ] );

		return UkrPost::widget( [
			'view'      => 'create_shipment',
			'order_id'  => $order_id,
			'action'    => $action,
			'actionUrl' => \Yii::$app->urlManager->createUrl( [ 'shipment-helper/ukr-post-save-shipment' ] ),
			'weight'    => $params ? $params->shipment_weight : '0',
			'length'    => $params ? $params->shipment_length : '0',
			'types'     => [ 'EXPRESS' => 'EXPRESS', 'STANDARD' => 'STANDARD' ],
			'type'      => $params ? $params->shipment_type : '0'
		] );

	}

	public function actionUkrPostSaveShipment() {
		\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

		$order_id = \Yii::$app->request->post( 'order_id' );
		$type     = \Yii::$app->request->post( 'type' );
		$weight   = \Yii::$app->request->post( 'weight', 1 );
		$length   = \Yii::$app->request->post( 'length', 1 );

		$service = new Service();

		$response = $service->createPost( $order_id, $type, $weight, $length );

		if( ! isset( $response['error'] ) ) {
			if( ! ( $oParams = ShippingUkrpostOrderParams::findOne( [ 'order_id' => $order_id ] ) ) ) {
				$oParams = ShippingUkrpostOrderParams::create( $order_id );
			}
			$oParams->shipment_uuid   = $response['uuid'];
			$oParams->shipment_weight = $response['weight'];
			$oParams->shipment_length = $response['length'];
			$oParams->shipment_type   = $response['type'];

			if( ! $oParams->save() ) {
				throw  new RuntimeException( "saving error" );
			}

			return [
				'success' => true,
				'html' => UkrPost::widget([
					'order_id' => $order_id,
					'view' => 'backend'
				])
			];
		}
		return [
			'success' => false,
			'message' => $response['message']
		];

	}

	public function actionPrintLabelUkrPostPost() {
		$order_id = \Yii::$app->request->get('order_id');
		if(!$order_id){
			return ['success' => false, 'message' => "order not found"];
		}
		$service = new Service();
		$service->getLabel($order_id);
	}
}