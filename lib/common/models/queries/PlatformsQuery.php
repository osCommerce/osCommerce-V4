<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 14.02.18
 * Time: 12:08
 */
namespace common\models\queries;

use yii\db\ActiveQuery;

class PlatformsQuery extends ActiveQuery {

	public function active($status = true){
		return $this->andWhere(['status' => $status ? 1 : 0]);
	}
}