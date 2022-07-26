<?php

namespace common\models\queries;
use common\models\BirthdayDirectCoupons;
use common\models\Customers;

/**
 * This is the ActiveQuery class for [[Customers]].
 *
 * @see Customers
 */
class CustomersQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        return $this->andWhere(['customers_status' => Customers::STATUS_ACTIVE]);
    }
    public function byPlatform(int $platformId)
    {
        return $this->andWhere(['platform_id' => $platformId]);
    }
    public function withEmail()
    {
        return $this->andWhere(['<>','customers_email_address','']);
    }
    public function noGuest()
    {
        return $this->andWhere(['opc_temp_account' => 0]);
    }
    /**
     * {@inheritdoc}
     * @return Customers[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Customers|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
