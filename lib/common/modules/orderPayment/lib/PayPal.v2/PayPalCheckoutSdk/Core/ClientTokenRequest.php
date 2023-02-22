<?php
/**
 * NOT part of Lib
 * @Holbi Group Ltd
 */
namespace PayPalCheckoutSdk\Core;

use PayPalHttp\HttpRequest;

class ClientTokenRequest extends HttpRequest
{
    /**
     *
     * @param string $customer_id ^[0-9a-zA-Z_-]{1:22}$
     */
    public function __construct($customer_id)
    {
        parent::__construct("/v1/identity/generate-token", "POST");
        $this->headers["Content-Type"] = "application/json";

        $body = [
            "customer_id" => $customer_id
        ];

        $this->body = $body;
    }
}

