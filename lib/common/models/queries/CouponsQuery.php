<?php

namespace common\models\queries;

use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[Coupons]].
 *
 * @see Coupons
 */
class CouponsQuery extends ActiveQuery {

	public function type( $type = '' ) {
		return $this->andWhere( [ 'type' => $type ] );
	}

	public function forRecovery() {
		return $this->andWhere( [ 'coupon_for_recovery_email' => 1 ] );
	}

	public function active( $active = true ) {
		return $this->andWhere( [ 'coupon_active' => $active ? 'Y' : 'N' ] );
	}

	public function expired( $expired = false ) {
		if( ! $expired ) {
			return $this->andWhere( [ '<', 'coupon_expire_date', date( "Y-m-d H:i:s" ) ] );
		}

		return $this;
	}


	/**
	 * @inheritdoc
	 * @return Coupons[]|array
	 */
	public function all( $db = null ) {
		return parent::all( $db );
	}

	/**
	 * @inheritdoc
	 * @return Coupons|array|null
	 */
	public function one( $db = null ) {
		return parent::one( $db );
	}


}
