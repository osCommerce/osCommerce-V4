<?php
namespace MailChimp\Ecommerce;

class Customers extends Ecommerce
{

    /**
     * Get information about a store’s customers.
     *
     * @param string $store_id
     * @param  array (optional) $query
     * @return object
     */
    public function getCustomers($store_id, array $query = [])
    {
        return self::execute("GET", "ecommerce/stores/{$store_id}/customers", $query);
    }

    /**
     * Get information about a specific customer.
     *
     * @param string $store_id
     * @param string $customer_id
     * @param  array (optional) $query
     * @return object
     */
    public function getCustomer($store_id, $customer_id, array $query = [])
    {
        return self::execute("GET", "ecommerce/stores/{$store_id}/customers/{$customer_id}", $query);
    }

    /**
     * Add a new customer to a store
     *
     * @param string $store_id
     * @param string $customer_id
     * @param string $email_address
     * @param boolean $opt_in_status
     * @param  array (optional) $optional_settings
     * @return object
     */
    public function addCustomer($store_id, $customer_id, $email_address, $opt_in_status, array $optional_settings = null)
    {
        $optional_fields = ["company", "first_name", "last_name", "orders_count", "vendor", "total_spent", "address"];

        $data = array(
            "id" => $customer_id,
            "email_address" => $email_address,
            "opt_in_status" => $opt_in_status
        );

        // If the optional fields are passed, process them against the list of optional fields.
        if (isset($optional_settings)) {
            $data = array_merge($data, self::optionalFields($optional_fields, $optional_settings));
        }

        return self::execute("POST", "ecommerce/stores/{$store_id}/customers/", $data);
    }

    /**
     * Update a customer
     *
     * @param string $string_id
     * @param string $customer_id
     * @param array $data
     * @return object
     */
    public function updateCustomer($store_id, $customer_id, array $data = [] )
    {
        return self::execute("PATCH", "ecommerce/stores/{$store_id}/customers/{$customer_id}", $data);
    }

    /**
     * Add or update a customer
     *
     * @param string $string_id
     * @param string $customer_id
     * @param array $data
     * @return object
     */
    public function upsertCustomer($store_id, $customer_id, array $data = [] )
    {
        return self::execute("PUT", "ecommerce/stores/{$store_id}/customers/{$customer_id}", $data);
    }

    /**
     * Delete a customer
     *
     * @param string $string_id
     * @param string $customer_id
     */
    public function deleteCustomer($store_id, $customer_id)
    {
        return self::execute("DELETE", "ecommerce/stores/{$store_id}/customers/{$customer_id}");
    }


}
