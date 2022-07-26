<?php
namespace MailChimp\Campaigns;

use MailChimp\MailChimp as MailChimp;

class Content extends MailChimp
{


    /**
     * Get the the HTML and plain-text content for a campaign.
     *
     * array["fields"]              array       list of strings of response fields to return
     * array["exclude_fields"]      array       list of strings of response fields to exclude (not to be used with "fields")
     *
     * @param string $campaign_id for the campaign instance
     * @param array $query (See Above) OPTIONAL associative array of query parameters.
     * @return object
     */
    public function getCampaignContent($campaign_id, array $query = [])
    {
        return self::execute("GET", "campaigns/{$campaign_id}/content", $query);
    }

    /**
     * Set the content for a campaign.
     *
     * array $optional_settings     array
     *      ["plain_text"]          string   The plain-text portion of the campaign.
     *      ["html"]                string   The raw HTML for the campaign.
     *      ["url"]                 string   When importing a campaign, the URL where the HTML lives.
     *      ["template"]            array   Use this template to generate the HTML content of the campaign
     *          ["id"]              int     REQUIRED WHEN USING TEMPLATE The id of the template to use.
     *          ["sections"]        array   Content for the sections of the template. Each key should be the unique mc:edit area name from the template.
     *      ["archive"]             array   Available when uploading an archive to create campaign content.
     *          ["archive_content"] string  REQUIRED WHEN USING ARCHIVE base64-encoded representation of the archive file.
     *          ["archive_type"]    string  The type of encoded file. Defaults to zip.
     *                                      Possible Values: zip, tar.gz, tar.bz2,tar, tgz, tbz
     *
     * @param string $campaign_id for the campaign instance
     * @param array $optional_settings (See Above for available fields)
     * @return object
     */
    public function setCampaignContent($campaign_id, array $optional_settings = [])
    {
        $optional_fields = ["plain_text", "html", "url", "template", "archive", "variate_contents" ];

        // If the optional fields are passed, process them against the list of optional fields.
        if (isset($optional_settings)) {
            $data = array_merge($data, self::optionalFields($optional_fields, $optional_settings));
        }
        return self::execute("PUT", "campaigns/{$campaign_id}/content", $data);
    }



}
