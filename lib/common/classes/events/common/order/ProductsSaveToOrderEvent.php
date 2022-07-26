<?php
declare (strict_types=1);


namespace common\classes\events\common\order;


use common\classes\extended\OrderAbstract;

class ProductsSaveToOrderEvent
{
    /** @var OrderAbstract */
    private $order;

    public function __construct(OrderAbstract $order)
    {
        $this->order = $order;
    }

    /**
     * @return OrderAbstract
     */
    public function getOrder(): OrderAbstract
    {
        return $this->order;
    }
}
