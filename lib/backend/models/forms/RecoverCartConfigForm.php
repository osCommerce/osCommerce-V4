<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 14.02.18
 * Time: 11:01
 */

namespace backend\models\forms;


use common\models\Platforms;
use yii\base\Model;

class RecoverCartConfigForm extends Model {

	public $platform_id;
	public $enable_email_delivery;
	public $first_email_start;
	public $first_email_coupon_id;
	public $second_email_start;
	public $second_email_coupon_id;
	public $third_email_start;
	public $third_email_coupon_id;


	public function rules(){
		return [
			['platform_id', 'required'],
			[
				['enable_email_delivery', 'first_email_start', 'first_email_coupon_id', 'second_email_start', 'second_email_coupon_id', 'third_email_start', 'third_email_coupon_id' ], 'safe']
		];
	}

	public function getPlatform(){
		return Platforms::findOne(['platform_id' => $this->platform_id]);
	}

    public function getIntervals(){
		return array_merge( ['0' => 'Select interval'] , [
		   1 => '1 hour',
		   2 => '2 hours',
		   3 => '3 hours',
		   4 => '4 hours',
		   5 => '5 hours',
		   6 => '6 hours',
		   7 => '7 hours',
		   8 => '8 hours',
		   9 => '9 hours',
		   10 => '10 hours',
		   11 => '11 hours',
		   12 => '12 hours',
		   13 => '13 hours',
		   14 => '14 hours',
		   15 => '15 hours',
		   16 => '16 hours',
		   17 => '17 hours',
		   18 => '18 hours',
		   19 => '19 hours',
		   20 => '20 hours',
		   21 => '21 hours',
		   22 => '22 hours',
		   23 => '23 hours',
		   24 => '1 day',
		   48 => '2 days',
		   72 => '3 days',
		   96 => '4 days',
		   120 => '5 days',
		   144 => '6 days',
		   168 => '1 week',
		   336 => '2 weeks',
		   504 => '3 weeks',
		   672 => '1 month',
		]);
    }

}

