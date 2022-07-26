<?php
declare (strict_types=1);


namespace common\models\repositories;


use common\models\OrdersLabel;
use common\models\OrdersLabelToOrdersProducts;
use common\services\TransactionManager;

class OrdersLabelRepository
{
    /** @var TransactionManager */
    private $transactionManager;

    public function __construct(
        TransactionManager $transactionManager
    )
    {
        $this->transactionManager = $transactionManager;
    }

    /**
     * @param string|array $ordersLabelId
     * @param bool $asArray
     * @return array|OrdersLabel|OrdersLabel[]|null
     */
    public function get($ordersLabelId, bool $asArray = false)
    {
        if ($ordersLabel= $this->find($ordersLabelId, $asArray)) {
            throw new NotFoundException('Orders Label not found');
        }
        return $ordersLabel;
    }

    /**
     * @param string|array|int $ordersLabelId
     * @param bool $asArray
     * @return array|OrdersLabel|OrdersLabel[]|null
     */
    public function find($ordersLabelId, bool $asArray = false)
    {
        $ordersLabel = OrdersLabel::find()->where(['orders_label_id' => $ordersLabelId])->asArray($asArray);
        if (is_array($ordersLabelId)) {
            return $ordersLabel->all();
        }
        return $ordersLabel->limit(1)->one();
    }

    /**
     * @param OrdersLabel $ordersLabel
     * @param array $params
     * @param bool $validation
     * @param bool $safeOnly
     * @return array|bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function edit(OrdersLabel $ordersLabel, array $params = [], bool $validation = false, bool $safeOnly = false)
    {
        foreach ($params as $attribute => $param) {
            if (!$ordersLabel->hasAttribute($attribute)) {
                unset($params[$attribute]);
            }
        }
        $ordersLabel->setAttributes($params, $safeOnly);
        if ($ordersLabel->update($validation, array_keys($params)) === false) {
            return $ordersLabel->getErrors();
        }
        return true;
    }

    /**
     * @param OrdersLabel $ordersLabel
     * @return bool
     * @throws \Exception
     */
    public function remove(OrdersLabel $ordersLabel): bool
    {
        $this->transactionManager->wrap(static function() use ($ordersLabel){
            OrdersLabelToOrdersProducts::deleteAll([
                'orders_label_id' => $ordersLabel->orders_label_id,
                'orders_id' => $ordersLabel->orders_id
            ]);
            if ($ordersLabel->delete() === false) {
                throw new \RuntimeException( 'Orders Label remove error.' );
            }
        });
        return true;
    }

    /**
     * @param int $orderId
     * @return int
     */
    public function removeOrderProductLabelsByOrder(int $orderId): int
    {
        return OrdersLabelToOrdersProducts::deleteAll([
            'orders_id' => $orderId
        ]);;
    }

    /**
     * @param OrdersLabel $ordersLabel
     * @param bool $validation
     * @return bool
     * @throws \RuntimeException
     */
    public function save(OrdersLabel $ordersLabel, bool $validation = false) {
        if ($ordersLabel->save($validation) === false) {
            throw new \RuntimeException( 'Orders Label save error.' );
        }
        return true;
    }

    /**
     * @param int $orderId
     * @param int $orderLabelId
     * @param bool $asArray
     * @return array|OrdersLabel|null
     */
    public function findLabelByOrder(int $orderId, int $orderLabelId, bool $asArray = false)
    {
        $orderLabel = OrdersLabel::find()
            ->where([
                'orders_label_id' => $orderLabelId,
                'orders_id' =>$orderId
            ])
            ->limit(1)
            ->asArray($asArray)
            ->one();
        return $orderLabel;
    }
}
