<?php
/**
 * NOT part of Lib
 * @Holbi Group Ltd
 */
namespace PayPalCheckoutSdk\Shipping;

use PayPalHttp\HttpRequest;

class TrackersBatchRequest extends HttpRequest
{
    function __construct()
    {
        parent::__construct("/v1/shipping/trackers-batch?", "POST");
        $this->headers["Content-Type"] = "application/json";
    }
}
