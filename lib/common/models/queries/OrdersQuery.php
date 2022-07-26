<?php

namespace common\models\queries;

use yii\db\ActiveQuery;


class OrdersQuery extends ActiveQuery {

    public function crossUpEmailSend($send = true) {
        $cross_up_email_send = $send ? 1 : 0;
        return $this->where(['cross_up_email_send' => $cross_up_email_send]);
    }


    /**
     * @inheritdoc
     * @return Coupons[]|array
     */
    public function all($db = null) {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return Coupons|array|null
     */
    public function one($db = null) {
        return parent::one($db);
    }
    

}
