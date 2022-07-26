<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 07.12.17
 * Time: 16:45
 */

namespace common\models;


use yii\db\ActiveRecord;

class CustomersEmails extends ActiveRecord {

	public static function tableName() {
		return 'customers_emails';
	}

	public function getCustomer(){
		return $this->hasOne(Customers::className(), ['customers_id' => 'customers_id']);
	}
}