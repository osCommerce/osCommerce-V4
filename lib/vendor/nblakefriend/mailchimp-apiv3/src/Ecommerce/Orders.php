<?php
namespace MailChimp\Ecommerce;

class Orders extends Ecommerce
{

    /**
     * TODO: comment requirements
     */

     /**
      * Get information about a store’s orders.
      *
      * array["fields"]              array       list of strings of response fields to return
      * array["exclude_fields"]      array       list of strings of response fields to exclude (not to be used with "fields")
      * array["count"]               int         number of records to return
      * array["offset"]              int         number of records from a collection to skip.
      * array["customer_id"]         string      Restrict results to orders made by a specific customer.
      *
      * @param string $store_id
      * @param array $query (See Above) OPTIONAL associative array of query parameters.
      * @return object
      */
    public function getOrders($store_id, array $query = [])
    {
        return self::execute("GET", "ecommerce/stores/{$store_id}/orders", $query);
    }

    /**
     * Get information about a specific order.
     *
     * array["fields"]              array       list of strings of response fields to return
     * array["exclude_fields"]      array       list of strings of response fields to exclude (not to be used with "fields")
     */
    public function getOrder($store_id, $order_id, array $query = [])
    {
        return self::execute("GET", "ecommerce/stores/{$store_id}/orders/{$order_id}", $query);
    }

    /**
     * Add a new order to a store.
     *
     * @param string $store_id
     * @param string $order_id
     * @param string $currency_code
     * @param number $order_total
     * @param array $customer See addCustomer method in Customer class
     * @param array $lines See addOrderLine method below
     * @param array $optional_settings
     * @return object
     */
    public function addOrder($store_id, $order_id, $currency_code, $order_total, array $customer = [], array $lines = [], array $optional_settings = null)
    {
        $optional_fields = ["campaign_id", "financial_status", "tax_total", "shipping_total", "tracking_code", "processed_at_foreign", "updated_at_foreign", "cancelled_at_foreign", "shipping_address", "billing_address"];
        $data = [
            "id" => $order_id,
            "customer" => $customer,
            "currency_code" => $currency_code,
            "order_total" => $order_total,
            "lines" => $lines
        ];

        // If the optional fields are passed, process them against the list of optional fields.
        if (isset($optional_settings)) {
            $data = array_merge($data, self::optionalFields($optional_fields, $optional_settings));
        }
        return self::execute("POST", "ecommerce/stores/{$store_id}/orders/", $data);
    }

    /**
     * Update an order
     *
     * @param string $store_id
     * @param string $order_id
     * @param array $data
     * @return object
     */
    public function updateOrder($store_id, $order_id, array $data = [])
    {
        return self::execute("PATCH", "ecommerce/stores/{$store_id}/orders/{$order_id}", $data);
    }

    /**
     * Get information about a order’s line items
     *
     * array["fields"]              array       list of strings of response fields to return
     * array["exclude_fields"]      array       list of strings of response fields to exclude (not to be used with "fields")
     * array["count"]               int         number of records to return
     * array["offset"]              int         number of records from a collection to skip.
     *
     * @param string $store_id
     * @param string $order_id
     * @param array $query (See Above) OPTIONAL associative array of query parameters.
     * @return object
     */
    public function getOrderLines($store_id, $order_id, array $query = [])
    {
        return self::execute("GET", "ecommerce/stores/{$store_id}/orders/{$order_id}/lines", $query);
    }

    /**
     * Get information about a specific order line item.
     *
     * array["fields"]              array       list of strings of response fields to return
     * array["exclude_fields"]      array       list of strings of response fields to exclude (not to be used with "fields")
     *
     * @param string $store_id
     * @param string $order_id
     * @param array $query (See Above) OPTIONAL associative array of query parameters.
     * @return object
     */
    public function getOrderLine($store_id, $order_id, $line_id, array $query = [])
    {
        return self::execute("GET", "ecommerce/stores/{$store_id}/orders/{$order_id}/lines/{$line_id}", $query);
    }

    /**
     * Add a new line item to an existing order
     *
     * @param string $store_id
     * @param string $order_id
     * @param string $line_id
     * @param string $product_id
     * @param string $product_variant_id
     * @param int $quantity
     * @param number price
     * @return object
     */
    public function addOrderLine($store_id, $order_id, $line_id, $product_id, $product_variant_id, $quantity, $price)
    {
        $data = [
            "id" => $line_id,
            "product_id" => $product_id,
            "product_variant_id" => $product_variant_id,
            "quantity" => $quantity,
            "price" => $price
        ];
        return self::execute("POST", "ecommerce/stores/{$store_id}/orders/{$order_id}/lines/", $data);
    }
    /**
     * Update a line item to an existing order
     *
     * @param string $store_id
     * @param string $order_id
     * @param string $line_id
     * @param array $data
     * @return object
     */
    public function updateOrderLine($store_id, $order_id, $line_id, array $data = [])
    {
        return self::execute("PATCH", "ecommerce/stores/{$store_id}/orders/{$order_id}/lines/{$line_id}", $data);
    }

    /**
     * Delete a line item to an existing order
     *
     * @param string $store_id
     * @param string $order_id
     * @param string $line_id
     */
    public function deleteOrderLine($store_id, $order_id, $line_id)
    {
        return self::execute("DELETE", "ecommerce/stores/{$store_id}/orders/{$order_id}/lines/{$line_id}");
    }

    /**
     * Delete an existing order
     *
     * @param string $store_id
     * @param string $order_id
     */
    public function deleteOrder($store_id, $order_id)
    {
        return self::execute("DELETE", "ecommerce/stores/{$store_id}/orders/{$order_id}");
    }

}
