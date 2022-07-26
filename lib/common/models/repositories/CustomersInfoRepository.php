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

namespace common\models\repositories;


use common\models\CustomersInfo;

class CustomersInfoRepository {

    public function getByCustomer($customerId)
    {
        $customersInfo = CustomersInfo::find()->where(['customers_info_id' =>$customerId])->limit(1)->one();
        if(!$customersInfo){
            throw new NotFoundException('Customer info not found');
        }
        return $customersInfo;
    }
    public function edit( CustomersInfo $customersInfo, $params = [], $validate = false, $safeOnly = false ) {
        foreach ( $params as $attribute => $param ) {
            if ( ! $customersInfo->hasAttribute( $attribute ) ) {
                unset( $params[ $attribute ] );
            }
        }
        $customersInfo->setAttributes( $params, $safeOnly );
        if ( ! $customersInfo->update( $validate, array_keys( $params ) ) ) {
            return $customersInfo->getErrors();
        }
        return true;
    }
    public function save( CustomersInfo $customerInfo ) {
        if ( ! $customerInfo->save() ) {
            throw new \RuntimeException( 'Customer info saving error.' );
        }
        return true;
    }
}