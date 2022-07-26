<?php

namespace frontend\models\repositories;

use common\models\Customers;

class CustomersRepository 
{
    /**
     * 
     * @param int $customerId
     * @return array
     */
    public function getCustomerByDefAddresId(int $customerId)
    {
        $customer = Customers::find()->where(['customers_id' => $customerId])->with('defaultAddress')->asArray()->one();
        return $customer;
    }
    
    public function getOneArray(array $array)
    {
        $customersArr = [];
        
        foreach ($array as $key => $val) {
            if (!is_array($val)) {
                $customersArr[$key] = $val;
            } else {
                foreach ($val as $k => $v) {
                    $customersArr[$k] = $v;
                }
            }
        }

        return $customersArr;
    }
}
