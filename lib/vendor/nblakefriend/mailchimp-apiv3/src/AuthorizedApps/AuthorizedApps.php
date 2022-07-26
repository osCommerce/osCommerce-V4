<?php
namespace MailChimp\AuthorizedApps;

use MailChimp\MailChimp as MailChimp;

class AuthorizedApps extends MailChimp
{

    /**
    * Get a list of Authorized Apps for the account
     *
     * Available query fields:
     * array["fields"]              array       list of strings of response fields to return
     * array["exclude_fields"]      array       list of strings of response fields to exclude (not to be used with "fields")
     * array["count"]               int         number of records to return
     * array["offset"]              int         number of records from a collection to skip.
     */
    public function getApps(array $query = [])
    {
        return self::execute("GET", "authorized-apps", $query);
    }

    /**
     * Get a list of Authorized Apps for the account
     *
     * Available query fields:
     * array["fields"]              array       list of strings of response fields to return
     * array["exclude_fields"]      array       list of strings of response fields to exclude (not to be used with "fields")
     */
    public function getApp($app_id, array $query = [])
    {
        return self::execute("GET", "authorized-apps/{$app_id}", $query);
    }

    /**
     * Retrieve OAuth2-based credentials to associate API calls with your application.
     *
     * @param string $client_id
     * @param string $client_secret
     * @return object with access_token and viewer_token
     */
    public function manageApp($client_id, $client_secret)
    {
        $data = [
            "client_id" => $client_id,
            "client_secret" => $client_secret
        ];
        return self::execute("POST", "authorized-apps", $data);
    }

}
