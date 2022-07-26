<?php
declare (strict_types=1);


namespace common\models\repositories;


use common\models\OrdersLabelToOrdersProducts;

class OrdersLabelToOrdersProductsRepository
{
    /**
     * @param string|array $ordersLabelId
     * @param bool $asArray
     * @return array|OrdersLabelToOrdersProducts[]
     */
    public function get($ordersLabelId, bool $asArray = false)
    {
        if ($product = $this->find($ordersLabelId, $asArray)) {
            throw new NotFoundException('Orders Label not found');
        }
        return $product;
    }

    /**
     * @param string|array|int $ordersLabelId
     * @param bool $asArray
     * @return array|OrdersLabelToOrdersProducts|OrdersLabel[]|null
     */
    public function find($ordersLabelId, bool $asArray = false)
    {
        $product = OrdersLabelToOrdersProducts::find()->where(['orders_label_id' => $ordersLabelId])->asArray($asArray);
        return $product->all();
    }
    
    /**
     * @param OrdersLabelToOrdersProducts $ordersLabelProduct
     * @param array $params
     * @param bool $validation
     * @param bool $safeOnly
     * @return array|bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function edit(OrdersLabelToOrdersProducts $ordersLabelProduct, array $params = [], bool $validation = false, bool $safeOnly = false)
    {
        foreach ($params as $attribute => $param) {
            if (!$ordersLabelProduct->hasAttribute($attribute)) {
                unset($params[$attribute]);
            }
        }
        $ordersLabelProduct->setAttributes($params, $safeOnly);
        if ($ordersLabelProduct->update($validation, array_keys($params)) === false) {
            return $ordersLabelProduct->getErrors();
        }
        return true;
    }

    /**
     * @param OrdersLabelToOrdersProducts $ordersLabelProduct
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * @throws \RuntimeException
     */
    public function remove(OrdersLabelToOrdersProducts $ordersLabelProduct) {
        if ($ordersLabelProduct->delete() === false) {
            throw new \RuntimeException( 'Orders Label remove error.' );
        }
        return true;
    }

    /**
     * @param OrdersLabelToOrdersProducts $ordersLabelProduct
     * @param bool $validation
     * @return bool
     * @throws \RuntimeException
     */
    public function save(OrdersLabelToOrdersProducts $ordersLabelProduct, bool $validation = false) {
        if ($ordersLabelProduct->save($validation) === false) {
            throw new \RuntimeException( 'Orders Label save error.' );
        }
        return true;
    }

    /**
     * @param int $orderId
     * @param int|array $productsId
     * @param bool $asArray
     * @return array|OrdersLabelToOrdersProducts|null
     */
    public function findLabelByOrder(int $orderId, $productsId, bool $asArray=false)
    {
        $orderLabelProduct = OrdersLabelToOrdersProducts::find()
            ->where([
                'orders_products_id' => $productsId,
                'orders_id' => $orderId
            ])
            ->limit(1)
            ->asArray($asArray)
            ->one();
        return $orderLabelProduct;
    }
}
