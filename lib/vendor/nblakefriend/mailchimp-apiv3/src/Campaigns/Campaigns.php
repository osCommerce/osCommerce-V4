<?php
namespace MailChimp\Campaigns;

use MailChimp\MailChimp as MailChimp;
use MailChimp\Campaigns\Content as Content;
use MailChimp\Campaigns\Feedback as Feedback;

class Campaigns extends MailChimp
{

    /**
     * Get a list of campaigns for the account
     *
     * Available query fields:
     * array["fields"]              array       list of strings of response fields to return
     * array["exclude_fields"]      array       list of strings of response fields to exclude (not to be used with "fields")
     * array["count"]               int         number of records to return
     * array["offset"]              int         number of records from a collection to skip.
     * array["folder_id"]           string      Filter results by a specific campaign folder.
     * array["type"]                string      The campaign type.
     *                                          Possible values: regular,plaintext,absplit,rss,variate
     * array["status"]              string      The status of the campaign.
     *                                          Possible Values: save,paused,schedule,sending,sent
     * array["before_send_time"]    string      Restrict the response to campaigns sent before the set time.
     *                                          ISO 8601 time format: 2015-10-21T15:41:36+00:00.
     * array["since_send_time"]     string      Restrict the response to campaigns sent after the set time.
     *                                          ISO 8601 time format: 2015-10-21T15:41:36+00:00.
     * array["before_create_time"]  string      Restrict the response to campaigns sent after the set time.
     *                                          ISO 8601 time format: 2015-10-21T15:41:36+00:00.
     * array["since_create_time"]   string      Restrict the response to campaigns created after the set time.
     *                                          ISO 8601 time format: 2015-10-21T15:41:36+00:00.
     *
     * @param array $query (See Above) OPTIONAL associative array of query parameters.
     * @return object
     */
    public function getCampaigns(array $query = [])
    {
        return self::execute("GET", "campaigns", $query);
    }

    /**
     * Get a single campaign
     *
     * array["fields"]              array       list of strings of response fields to return
     * array["exclude_fields"]      array       list of strings of response fields to exclude (not to be used with "fields")
     *
     * @param string $campaign_id for the campaign instance
     * @param array $query (See Above) OPTIONAL associative array of query parameters.
     * @return object
     */
    public function getCampaign($campaign_id, array $query = [])
    {
        return self::execute("GET", "campaigns/{$campaign_id}", $query);
    }

    /**
     * Review the send checklist for a campaign, and resolve any issues before sending.
     *
     * array["fields"]              array       list of strings of response fields to return
     * array["exclude_fields"]      array       list of strings of response fields to exclude (not to be used with "fields")
     *
     * @param string $campaign_id for the campaign instance
     * @param array $query (See Above) OPTIONAL associative array of query parameters.
     * @return object
     */
    public function getCampaignChecklist($campaign_id, array $query = [])
    {
        return self::execute("GET", "campaigns/{$campaign_id}/send-checklist", $query);
    }

    /**
     * Create a campaign
     *
     * Example Request Body:
     *      "type"                          string      REQUIRED The campaign type. Possible Values: regular, plaintext, variate, rss
     *      "recipients"                    array       List setting for the campaign
     *          ["list_id"]                 string      REQUIRED The unique list id from lists()->getLists()
     *          ["segment_opts"]            array       optional segmentation options
     *               ["saved_segment_id"]   int         The id for an existing saved segment from lists()->segments()->getListSegments($listId)
     *               ["match"]              string      Segement match type. Possible Values: any, all
     *               ["conditions"]         array       An array of segment conditions
     *                                                  Structure depends on segment http://developer.mailchimp.com/documentation/mailchimp/reference/lists/segments/#
     *      "settings"                      array       REQUIRED
     *          ["subject_line"]            string      REQUIRED The subject line for the campaign.
     *          ["title"]                   string      The title of the campaign.
     *          ["from_name"]               string      REQUIRED The ‘from’ name on the campaign (not an email address).
     *          ["reply_to"]                string      REQUIRED The reply-to email address for the campaign.
     *          ["use_conversation"]        boolean     Use MailChimp Conversation feature to manage out-of-office replies.
     *          ["to_name"]                 string      The campaign’s custom ‘To’ name. Typically the first name merge field.
     *          ["folder_id"]               string      If the campaign is listed in a folder, the id for that folder.
     *          ["authenticate"]            boolean     Whether MailChimp authenticated the campaign. Defaults to true.
     *          ["auto_footer"]             boolean     Automatically append MailChimp’s default footer to the campaign.
     *          ["inline_css"]              boolean     Automatically inline the CSS included with the campaign content.
     *          ["auto_tweet"]              boolean     Automatically tweet a link to the campaign archive page when the campaign is sent.
     *          ["auto_fb_post"]            array       An array of Facebook page ids to auto-post to.
     *          ["fb_comments"]             boolean     Allows Facebook comments on the campaign (also force-enables the Campaign Archive toolbar).
     *                                                  Defaults to true.
     *      "optional_settings"              array       associative array of optional/conditional campaign options.
     *          ["variate_settings"]        array       Required if type "variate" is set. The settings specific to variate campaigns.
     *               ["winner_criteria"]    string      Required is variate. Possible Values: opens,clicks,manual, total_revenue
     *               ["wait_time"]          int         The number of minutes to wait before choosing the winning campaign.
     *                                                  The value of wait_time must be greater than 0 and in whole hours, specified in minutes.
     *               ["test_size"]          int         The percentage of recipients to send the test combinations to, must be a value between 10 and 100.
     *               ["subject_lines"]      array       The possible subject lines to test. If no subject lines are provided, settings.subject_line will be used.
     *               ["send_times"]         array       The possible send times to test. The times provided should be in the format YYYY-MM-DD HH:MM:SS. If send_times are provided to test, the test_size will be set to 100% and winner_criteria will be ignored.
     *               ["from_names"]         array       The possible from names. The number of from_names provided must match the number of reply_to_addresses. If no from_names are provided, settings.from_name will be used.
     *               ["reply_to_addresses"] array       The possible reply-to addresses. The number of reply_to_addresses provided must match the number of from_names. If no reply_to_addresses are provided, settings.reply_to will be used.
     *          ["rss_opts"]                array       Required if type "rss" is set. The settings specific to rss campaigns.
     *               ["feed_url"]           string      Required for rss. The URL for the RSS feed.
     *               ["frequency"]          string      Required for rss. The frequency of the RSS Campaign. Possible Values: daily,weekly,monthly
     *               ["schedule"]           array       The schedule for sending the RSS Campaign.
     *                  ["hour"]            int         The hour to send the campaign in local time. Acceptable hours are 0-23. For example, ‘4’ would be 4am in your account’s default time zone.
     *                  ["daily_send"]      array       The days of the week to send a daily RSS Campaign.
     *                      ["sunday"]      boolean
     *                      ["monday"]      boolean
     *                      ["tuesday"]     boolean
     *                      ["wednesday"]   boolean
     *                      ["thursday"]    boolean
     *                      ["friday"]      boolean
     *                      ["saturday"]    boolean
     *                  ["weekly_send_day"] string      The day of the week to send a weekly RSS Campaign.
     *                                                  Possible Values:sunday,monday,tuesday,wednesday,thursday,friday,saturday
     *                  ["monthly_send_date"]   number   The day of the month to send a monthly RSS Campaign.
     *                                                  Acceptable days are 1-31, where ‘0’ is always the last day of a month
     *               ["constrain_rss_img"]  boolean     Whether to add CSS to images in the RSS feed to constrain their width in campaigns.
     *          ["tracking"]                array       Required if type variate is set. The settings specific to variate campaigns.
     *               ["opens"]              boolean
     *               ["html_clicks"]        boolean
     *               ["text_clicks"]        boolean
     *               ["goal_tracking"]      boolean
     *               ["ecomm360"]           boolean
     *               ["google_analytics"]   string      The custom slug for Google Analytics tracking (max of 50 bytes).
     *               ["clicktale"]          string      The custom slug for ClickTale tracking (max of 50 bytes).
     *               ["salesforce"]         array       Salesforce tracking options for a campaign.
     *                                                  Must be using MailChimp’s built-in Salesforce integration.
     *                    ["campaign"]      boolean     Create a campaign in a connected Salesforce account.
     *                    ["notes"]         boolean     Update contact notes for a campaign based on subscriber email addresses.
     *               ["highrise"]           array
     *                    ["campaign"]      boolean     Create a campaign in a connected Highrise account.
     *                    ["notes"]         boolean     Update contact notes for a campaign based on subscriber email addresses.
     *               ["capsule"]            array
     *                    ["notes"]         boolean     Update contact notes for a campaign based on subscriber email addresses.
     *          ["social_cards"]            array       Required if type variate is set. The settings specific to rss campaigns.
     *               ["image_url"]          string      The url for the header image for the card.
     *               ["description"]        string      A short summary of the campaign to display.
     *               ["title"]              string      The title for the card.
     * @param array $type       Required
     * @param array $recipients Required
     * @param array $settings   Required
     * @param array $optional_settings (See possible values above)
     * @return object
     */
    public function createCampaign($type, array $recipients = [], array $settings = [], array $optional_settings = null )
    {
        $optional_fields = ["tracking", "social_card", "variate_settings", "rss_opts"];

        $data = [
            "type" => $type,
            "recipients" => $recipients,
            "settings" => $settings
        ];

        // If the optional fields are passed, process them against the list of optional fields.
        if (isset($optional_settings)) {
            $data = array_merge($data, self::optionalFields($optional_fields, $optional_settings));
        }

        return self::execute("POST", "campaigns", $data);
    }

    /**
     * Update a Campaign
     *
     * @param string $campaign_id for the campaign instance
     * @param array $data
     * @return object
     */
    public function updateCampaign($campaign_id, array $data = [])
    {
        return self::execute("PATCH", "campaigns/{$campaign_id}", $data);
    }

    /**
     * Pause an RSS-Driven campaign
     *
     * @param string $campaign_id for the campaign instance
     */
    public function pauseRSSCampaign($campaign_id)
    {
        return self::execute("POST", "campaigns/{$campaign_id}/actions/pause");
    }

    /**
     * Resume an RSS-Driven campaign
     *
     * @param string $campaign_id for the campaign instance
     */
    public function resumeRSSCampaign($campaign_id)
    {
        return self::execute("POST", "campaigns/{$campaign_id}/actions/resume");
    }

    /**
     * Replicate a campaign
     *
     * @param string $campaign_id for the campaign instance
     */
    public function replicateCampaign($campaign_id)
    {
        return self::execute("POST", "campaigns/{$campaign_id}/actions/replicate");
    }


    /**
     * Cancel a campaign
     *
     * @param string $campaign_id for the campaign instance
     */
    public function cancelCampaign($campaign_id)
    {
        return self::execute("POST", "campaigns/{$campaign_id}/actions/cancel-send");
    }

    /**
     * Schedule a campaign
     *
     * @param string $campaign_id for the campaign instance
     */
    public function scheduleCampaign($campaign_id)
    {
        return self::execute("POST", "campaigns/{$campaign_id}/actions/schedule");
    }

    /**
     * Unschedule a campaign
     *
     * @param string $campaign_id for the campaign instance
     */
    public function unscheduleCampaign($campaign_id)
    {
        return self::execute("POST", "campaigns/{$campaign_id}/actions/unschedule");
    }

    /**
     * Send a test email
     *
     * @param string $campaign_id for the campaign instance
     */
    public function sendCampaignTest($campaign_id)
    {
        return self::execute("POST", "campaigns/{$campaign_id}/actions/test");
    }

    /**
     * Send a campaign
     *
     * @param string $campaign_id for the campaign instance
     */
    public function sendCampaign($campaign_id)
    {
        return self::execute("POST", "campaigns/{$campaign_id}/actions/send");
    }

    /**
     * Delete a campaign
     *
     * @param string $campaign_id for the campaign instance
     */
    public function deleteCampaign($campaign_id)
    {
        return self::execute("DELETE", "campaigns/{$campaign_id}/");
    }

    /**
     *  Instantiates the Content class.
     *
     */
     public function content()
     {
         return new Content;
     }

     /**
      *  Instantiates the Feedback class.
      *
      */
      public function feedback()
      {
          return new Feedback;
      }




}
