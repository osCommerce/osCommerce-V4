<?php
declare (strict_types=1);


namespace common\classes\events\common\order;


use common\classes\extended\OrderAbstract;

class OrderSaveEvent
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
