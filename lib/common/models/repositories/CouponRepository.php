<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 14.02.18
 * Time: 17:08
 */

namespace common\models\repositories;


use common\models\Coupons;

class CouponRepository {

	public function forRecover() {
		return Coupons::find()->active()->forRecovery()->all();
	}

    /**
     * @param array|int|string $id
     * @return array|Coupons|null|\yii\db\ActiveRecord
     */
	public function getById( $id ) {
		return Coupons::find()->where(['coupon_id' => $id])->limit(1)->one();
	}

	public function getByIdAsArray( $id ) {
		return Coupons::find()->where( [ 'coupon_id' => $id ] )->asArray()->one();
	}

    public function findAll(bool $active = true, bool $asArray = false)
    {
        $coupons= Coupons::find();
        if($active){
            $coupons->active();
        }
        return $coupons->indexBy('coupon_id')->asArray($asArray)->all();
    }

    /**
     * @param Coupons $coupons
     * @param array $params
     * @param bool $safeOnly
     * @return array|bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function edit( Coupons $coupons, array $params = [], bool $safeOnly = false ) {
        foreach ( $params as $attribute => $param ) {
            if ( ! $coupons->hasAttribute( $attribute ) ) {
                unset( $params[ $attribute ] );
            }
        }
        $coupons->setAttributes( $params, $safeOnly );
        if ( ! $coupons->update( false, array_keys( $params ) ) ) {
            return $coupons->getErrors();
        }
        return true;
    }

    public function save( Coupons $coupons ) {
        if ( ! $coupons->save() ) {
            throw new \RuntimeException( 'Coupon saving error.' );
        }
        return true;
    }
}