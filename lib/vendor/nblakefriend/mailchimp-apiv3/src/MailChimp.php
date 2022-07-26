<?php
namespace MailChimp;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use MailChimp\AuthorizedApps\AuthorizedApps as AuthorizedApps;
use MailChimp\Automations\Automations as Automations;
use MailChimp\Batches\Batches as Batches;
use MailChimp\CampaignFolders\CampaignFolders as CampaignFolders;
use MailChimp\Campaigns\Campaigns as Campaigns;
use MailChimp\Conversations\Conversations as Conversations;
use MailChimp\Ecommerce\Ecommerce as Ecommerce;
use MailChimp\FileManager\Files as FileManagerFiles;
use MailChimp\FileManager\Folders as FileManagerFolders;
use MailChimp\Lists\Lists as Lists;
use MailChimp\Reports\Reports as Reports;
use MailChimp\TemplateFolders\TemplateFolders as TemplateFolders;
use MailChimp\Templates\Templates as Templates;


class MailChimp
{

    private static $mc_root;
    private static $api_key;
    private static $config = "config.ini";
    private static $client;
    private static $conf = [];

    public function __construct($conf = [])
    {
      if (count($conf)>0)  {
        MailChimp::$conf = $conf;
      } else {
        $conf = MailChimp::$conf;
      }
        // Setting http_errors to false since guzzle explodes for anything not 200
        $client = new Client(array_merge([
            'base_uri' => !isset($conf['base_uri'])?self::getUrl():'',
            'auth' => ['api', !isset($conf['auth'])?self::getActiveKey():''],
            'cookies' => true,
            'allow_redirects' => true,
            'http_errors' => false,
            "headers" => [
                "User-Agent" => "MCv3.0 / PHP",
                "Accept" => "application/json"
            ]
      ], $conf));
        MailChimp::$client = $client;
    }

    /**
     * Get the API URL to use
     */
    private static function getUrl()
    {
        $dc = self::getDatacenter();
        return  "https://{$dc}.api.mailchimp.com/3.0/";
    }

    /**
     * Get the Datacenter from a the set API key
     */
    private static function getDatacenter()
    {
        // Determine the Datacenter from the API Key
        $dc = trim(strstr(self::getActiveKey(), "-"), "-");
        return $dc;
    }

    /**
     * Get the config file from the name set in $config
     */
    private static function getConfig()
    {
        $path_to_config = self::$config;
        $config = parse_ini_file($path_to_config, true);
        return $config;
    }

    /**
     * Find the key to use from the "active" key.
     * TODO: Is this way unnessesary?
     */
    private static function getActiveKey()
    {
        $config = self::getConfig();
        foreach ($config["api_keys"] as $api) {
            if ($api["active"]) {
                return $api["api_key"];
                break;
            }
        }
    }

    /**
     * Set the data passed for GET query parameters or POST/PUT/PATCH data
     *
     * @param array $data
     * @return array
     */
    private static function setData($method, array $data = [])
    {
        // TODO: consider sanitizing incoming data?
        foreach ($data as $key => $value) {
            // Set query parameters if method is GET
            if ($method == "GET") {
                // If the value is an array convert it to a string
                if (is_array($value)) {
                    $value = implode(',', $value);
                }
                // Set the query param to an associative array
                $params['query'][$key] = $value;
            } else {
                $params['json'][$key] = $value;
            }
        }
        return $params;
    }

    protected static function execute($method, $url, array $data = [])
    {
        if ($data) {
            $response = self::$client->request($method, $url, self::setData($method, $data));
        } else {
            $response = self::$client->request($method, $url);
        }

        $status_code = $response->getStatusCode();
        $response_body = json_decode($response->getBody()->getContents());
        // if ($status_code === 200 || $status_code === 204) {
            // return $response_body;
        // } else {

        // }
        return $response_body;
    }

    /**
     * Create the member hash
     *
     * @param string email address
     * @return string
     */
    protected static function getMemberHash($email_address)
    {
        return md5(strtolower($email_address));
    }

    /**
     * Process optional fields for POST requests
     * @param array $optional_fields
     * @param array $provided_fields
     * @return array
     */
    protected static function optionalFields(array $optional_fields, array $provided_fields)
    {
        $data = [];
        foreach ($provided_fields as $key => $value) {
            if (in_array(strtolower($key), $optional_fields) ) {
                $data[$key] = $value;
            }
        }
        return $data;
    }

    protected static function createLog($output, $overwrite = false, $file_name = "request.log", $tag = null)
    {
        $w = "a+";
        if ($overwrite) {
            $w = "w+";
        }
        $file = $file_name;
        $json_output = json_encode($output);
        $date = new \DateTime("now", new \DateTimeZone('America/New_York'));
        $time_formatted = $date->format("Y/m/d H:i:s");
        $handle = fopen($file, $w);
        $content = "Request: {$time_formatted}\n";
        if ($tag) {
            $content .= "TAGGED: {$tag}";
            $content .= "\n";
        }
        $content .= $json_output;
        $content .= "\n";
        // $content .= print_r($output, true)."\n  ----------------------------------------------------  \n";
        $content .= "\n  ----------------------------------------------------  \n";
        fwrite($handle, $content);
        fclose($handle);
    }


    public function logData($data, $tag, array $optional_settings = [])
    {
        if (isset($optional_settings["file_name"])) {
            $file_name = $optional_settings["file_name"];
        } else {
            $file_name = null;
        }

        if (isset($optional_settings["overwrite"])) {
            $overwrite = true;
        } else {
            $overwrite = false;
        }

        return self::createLog($data, $overwrite, $file_name, $tag);
    }

    /** RESOURCES */

    /**
     * Get account information from the API Root
     * Available query fields:
     * array["fields"]              array       list of strings of response fields to return
     * array["exclude_fields"]      array       list of strings of response fields to exclude (not to be used with "fields")
     * @param array $query (See Above) OPTIONAL associative array of query parameters.
     * @return object
     */
    public function getAccountInfo(array $query = [])
    {
        return self::execute("GET", "", $query);
    }

    /**
     * Search all lists for members matching query
     * @param string $query
     * @return array of objects
     */
    public function searchMembers($query)
    {
        return self::execute("GET", "search-members", $query);
    }


    public function authorizedApps()
    {
        return new AuthorizedApps;
    }

    public function automations()
    {
        return new Automations;
    }

    public function batchOps()
    {
        return new Batches;
    }

    public function campaignFolders()
    {
        return new CampaignFolders;
    }

    public function campaigns()
    {
        return new Campaigns;
    }

    public function conversations()
    {
        return new Conversations;
    }

    public function ecommerce()
    {
        /**
         * TODO: Collection in progress
         */
        return new Ecommerce;
    }

    public function fileManager()
    {
        return new FileManager\Files;
    }

    public function fileManagerFolders()
    {
        return new FileManager\Folders;
    }

    public function lists()
    {
        return new Lists;
    }

    public function reports()
    {
        return new Reports;
    }

    public function templateFolders()
    {
        return new TemplateFolders;
    }

    public function templates()
    {
        /**
         * TODO: Collection in progress
         */
        return new Templates;
    }

} // End MailChimp class
