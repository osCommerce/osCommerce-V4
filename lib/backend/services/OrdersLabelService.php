<?php
declare (strict_types=1);


namespace backend\services;


use common\models\OrdersLabel;
use common\models\repositories\OrdersLabelRepository;
use common\models\repositories\OrdersLabelToOrdersProductsRepository;

class OrdersLabelService
{

    /** @var OrdersLabelRepository */
    private $ordersLabelRepository;
    /** @var OrdersLabelToOrdersProductsRepository */
    private $labelToOrdersProductsRepository;

    public function __construct(
        OrdersLabelRepository $ordersLabelRepository,
        OrdersLabelToOrdersProductsRepository $labelToOrdersProductsRepository
    )
    {
        $this->ordersLabelRepository = $ordersLabelRepository;
        $this->labelToOrdersProductsRepository = $labelToOrdersProductsRepository;
    }

    /**
     * @param int $orderId
     * @param int $orderLabelId
     * @param bool $asArray
     * @return array|\common\models\OrdersLabel|null
     */
    public function findLabelByOrder(int $orderId, int $orderLabelId, bool $asArray=false)
    {
        return $this->ordersLabelRepository->findLabelByOrder($orderId, $orderLabelId, $asArray);
    }

    /**
     * @param int $orderId
     * @param int|array $productsId
     * @param bool $asArray
     * @return array|\common\models\OrdersLabel|null
     */
    public function findLabelByProducts(int $orderId, $productsId, bool $asArray=false)
    {
        $productLabel = $this->labelToOrdersProductsRepository->findLabelByOrder($orderId, $productsId, true);
        return $this->findLabelByOrder((int)$productLabel['orders_id'], (int)$productLabel['orders_label_id'], $asArray);
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
        return $this->ordersLabelRepository->edit($ordersLabel, $params, $validation,$safeOnly);
    }

    /**
     * @param OrdersLabel $ordersLabel
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function remove(OrdersLabel $ordersLabel): bool
    {
        return $this->ordersLabelRepository->remove($ordersLabel);
    }

    public function removeOrderProductLabelsByOrder(int $orderId): int
    {
        return $this->ordersLabelRepository->removeOrderProductLabelsByOrder($orderId);
    }
}
