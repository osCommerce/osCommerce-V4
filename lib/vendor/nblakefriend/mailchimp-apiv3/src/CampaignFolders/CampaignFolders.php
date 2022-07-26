<?php
namespace MailChimp\CampaignFolders;

use MailChimp\MailChimp as MailChimp;

class CampaignFolders extends MailChimp
{

    /**
     * Get all folders used to organize campaigns.
     *
     * Available query fields:
     * array["fields"]                  array       list of strings of response fields to return
     * array["exclude_fields"]          array       list of strings of response fields to exclude (not to be used with "fields")
     * array["count"]                   int         number of records to return
     * array["offset"]                  int         number of records from a collection to skip.
     * @param array $query (See Above) OPTIONAL associative array of query parameters.
     * @return object
     */
    public function getCampaignFolders(array $query = [])
    {
        return self::execute("GET", "campaign-folders", $query);
    }

    /**
     * Get information about a specific folder used to organize campaigns.
     *
     * Available query fields:
     * array["fields"]                  array       list of strings of response fields to return
     * array["exclude_fields"]          array       list of strings of response fields to exclude (not to be used with "fields")
     * @param string $folder_id
     * @param array $query (See Above) OPTIONAL associative array of query parameters.
     * @return object
     */
    public function getCampaignFolder($folder_id, array $query = [])
    {
        return self::execute("GET", "campaign-folders/{$folder_id}", $query);
    }

    /**
     * Create a new campaign folder.
     *
     * @param string folder name
     * @return object
     */
    public function createCampaignFolder($folder_name)
    {
        $data = ["name" => $folder_name];
        return self::execute("POST", "campaign-folders", $data);
    }

    /**
     * Update a specific folder used to organize campaigns.
     *
     * @param string folder name
     * @return object
     */
    public function updateCampaignFolder($folder_id, $folder_name)
    {
        $data = ["name" => $folder_name];
        return self::execute("PATCH", "campaign-folders/{$folder_id}", $data);
    }

    /**
     * Delete a specific campaign folder, and mark all the campaigns in the folder as ‘unfiled’.
     *
     * @param string $folder_id
     */
    public function deleteCampaignFolder($folder_id)
    {
        return self::execute("DELETE", "campaign-folders/{$folder_id}");
    }

}
