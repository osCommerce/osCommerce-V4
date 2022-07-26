<?php
namespace MailChimp\Ecommerce;

use MailChimp\MailChimp as MailChimp;
use MailChimp\Ecommerce\Carts as Carts;
use MailChimp\Ecommerce\Customers as Customers;
use MailChimp\Ecommerce\Orders as Orders;
use MailChimp\Ecommerce\Products as Products;

class Ecommerce extends MailChimp
{

    /**
     * Get a list of ecommerce stores for the account
     *
     * @param  array (optional)  $query
     * @return object
     */
    public function getStores(array $query = [] )
    {
        return self::execute("GET", "ecommerce/stores", $query);
    }

    /**
     * Get a list of ecommerce stores for the account
     *
     * @param string $store_id
     * @param array (optional) $query
     * @return object
     */
    public function getStore($store_id, array $query = [] )
    {
        return self::execute("GET", "ecommerce/stores/{$store_id}", $query);
    }

    /**
     * Add a new store
     *
     * @param string $store_id
     * @param string $list_id
     * @param string $name
     * @param string $currency_code The three-letter ISO 4217 code for the currency that the store accepts.
     * @param array (optional) $optional_settings
     * @return object
     */
    public function addStore($store_id, $list_id, $name, $currency_code, array $optional_settings = null)
    {
        $optional_fields = ["platform", "domain", "email_address", "money_format", "primary_locale", "timezone", "phone", "address"];

        $data = [
            "id" => $store_id,
            "list_id" => $list_id,
            "name" => $name,
            "currency_code" => $currency_code
        ];

        // If the optional fields are passed, process them against the list of optional fields.
        if (isset($optional_settings)) {
            $data = array_merge($data, self::optionalFields($optional_fields, $optional_settings));
        }
        return self::execute("POST", "ecommerce/stores", $data);
    }

    /**
     * Update a store
     *
     * @param string $store_id
     * @param array $data
     */
    public function updateStore($store_id, array $data = [])
    {
        return self::execute("PATCH", "ecommerce/stores/{$store_id}", $data);
    }

    /**
     * Delete a store
     *
     * @param string $string_id
     */
    public function deleteStore($store_id)
    {
        return self::execute("DELETE", "ecommerce/stores/{$store_id}");
    }

    /**
     *  Ecommerce subresources
     */

     /**
      * Instantiate Ecommerce Cart subresources
      *
      */
     public function carts()
     {
         return new Carts;
     }

     /**
      * Instantiate Ecommerce Customer subresources
      *
      */
     public function customers()
    {
        return new Customers;
    }

    /**
     * Instantiate Ecommerce Orders subresources
     *
     */
    public function orders()
    {
        return new Orders;
    }

    /**
     * Instantiate Ecommerce Products subresources
     *
     */
    public function products()
    {
        return new Products;
    }

}
