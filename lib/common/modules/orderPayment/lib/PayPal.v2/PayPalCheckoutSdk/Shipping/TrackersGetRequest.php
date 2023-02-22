<?php
/**
 * NOT part of Lib
 * @Holbi Group Ltd
 */
namespace PayPalCheckoutSdk\Shipping;

use PayPalHttp\HttpRequest;

class TrackersGetRequest extends HttpRequest
{
    function __construct($transaction_id)
    {
        parent::__construct("/v1/shipping/trackers/{transaction_id}?", "GET");

        $this->path = str_replace("{transaction_id}", urlencode($transaction_id), $this->path);

        $this->headers["Content-Type"] = "application/json";
    }
}
