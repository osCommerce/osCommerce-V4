<?php
namespace MailChimp\Ecommerce;

class Carts extends Ecommerce
{

    /**
     * Get information about a store’s carts.
     *
     * array["fields"]              array       list of strings of response fields to return
     * array["exclude_fields"]      array       list of strings of response fields to exclude (not to be used with "fields")
     * array["count"]               int         number of records to return
     * array["offset"]              int         number of records from a collection to skip.
     *
     * @param string $store_id
     * @param array $query (See Above) OPTIONAL associative array of query parameters.
     * @return object
     */
    public function getCarts($store_id, array $query = [])
    {
        return self::execute("GET", "ecommerce/stores/{$store_id}/carts", $query);
    }

    /**
     * Get information about a specific cart.
     *
     * array["fields"]              array       list of strings of response fields to return
     * array["exclude_fields"]      array       list of strings of response fields to exclude (not to be used with "fields")
     */
    public function getCart($store_id, $cart_id, array $query = [])
    {
        return self::execute("GET", "ecommerce/stores/{$store_id}/carts/{$cart_id}", $query);
    }

    /**
    * Add a new cart to a store.
    *
    * @param string $store_id
    * @param string $cart_id
    * @param string $currency_code
    * @param number $cart_total
    * @param array $customer See addCustomer method in Customer class
    * @param array $lines See addOrderLine method below
    * @param array $optional_settings
    * @return object
    */
    public function addCart($store_id, $cart_id, $currency_code, $cart_total, array $customer = [], array $lines = [], array $optional_settings = null)
    {
        $optional_fields = ["campaign_id", "checkout_url", "tax_total"];
        $data = [
            "id" => $cart_id,
            "customer" => $customer,
            "currency_code" => $currency_code,
            "order_total" => $cart_total,
            "lines" => $lines
        ];
        // If the optional fields are passed, process them against the list of optional fields.
        if (isset($optional_settings)) {
            $data = array_merge($data, self::optionalFields($optional_fields, $optional_settings));
        }
        return self::execute("POST", "ecommerce/stores/{$store_id}/carts", $data);
    }

    /**
     * Update a cart
     *
     * @param string $store_id
     * @param string $cart+id
     * @param array $data
     * @return object
     */

    public function updateCart($store_id, $cart_id, array $data = [])
    {
        return self::excecute("PATCH", "ecommerce/stores/{$store_id}/carts/{$cart_id}", $data);
    }

    /**
     * Get information about a cart’s line items
     *
     * array["fields"]              array       list of strings of response fields to return
     * array["exclude_fields"]      array       list of strings of response fields to exclude (not to be used with "fields")
     * array["count"]               int         number of records to return
     * array["offset"]              int         number of records from a collection to skip.
     *
     * @param string $store_id
     * @param string $cart_id
     * @param array $query (See Above) OPTIONAL associative array of query parameters.
     * @return object
     */
    public function getCartLines($store_id, $cart_id, array $query = [])
    {
        return self::execute("GET", "ecommerce/stores/{$store_id}/carts/{$cart_id}/lines", $query);
    }

    /**
     * Get information about a specific cart line item.
     *
     * array["fields"]              array       list of strings of response fields to return
     * array["exclude_fields"]      array       list of strings of response fields to exclude (not to be used with "fields")
     *
     * @param string $store_id
     * @param string $cart_id
     * @param array $query (See Above) OPTIONAL associative array of query parameters.
     * @return object
     */
    public function getCartLine($store_id, $cart_id, $line_id, array $query = [])
    {
        return self::execute("GET", "ecommerce/stores/{$store_id}/carts/{$cart_id}/lines/{$line_id}", $query);
    }

    /**
     * Add a new line item to an existing cart
     *
     * @param string $store_id
     * @param string $cart_id
     * @param string $line_id
     * @param string $product_id
     * @param string $product_variant_id
     * @param int $quantity
     * @param number price
     * @return object
     */
    public function addCartLine($store_id, $cart_id, $line_id, $product_id, $product_variant_id, $quantity, $price)
    {
        $data = [
            "id" => $line_id,
            "product_id" => $product_id,
            "product_variant_id" => $product_variant_id,
            "quantity" => $quantity,
            "price" => $price
        ];
        return self::execute("POST", "ecommerce/stores/{$store_id}/carts/{$cart_id}/lines/", $data);
    }
    /**
     * Update a line item to an existing cart
     *
     * @param string $store_id
     * @param string $cart_id
     * @param string $line_id
     * @param array $data
     * @return object
     */
    public function updateCartLine($store_id, $cart_id, $line_id, array $data = [])
    {
        return self::execute("PATCH", "ecommerce/stores/{$store_id}/carts/{$cart_id}/lines/{$line_id}", $data);
    }

    /**
     * Delete a line item to an existing cart
     *
     * @param string $store_id
     * @param string $cart_id
     * @param string $line_id
     */
    public function deleteCartLine($store_id, $cart_id, $line_id)
    {
        return self::execute("DELETE", "ecommerce/stores/{$store_id}/carts/{$cart_id}/lines/{$line_id}");
    }

    /**
     * Delete a cart
     *
     * @param string $store_id
     * @param string $cart_id
     */
    public function deleteCart($store_id, $cart_id)
    {
        return self::execute("DELETE", "ecommerce/stores/{$store_id}/carts/{$cart_id}");
    }


}
