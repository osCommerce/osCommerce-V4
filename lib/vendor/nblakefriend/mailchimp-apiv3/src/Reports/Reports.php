<?php

namespace MailChimp\Reports;

use MailChimp\MailChimp as MailChimp;

class Reports extends MailChimp
{

    /**
     * Get a list of templates for the account
     *
     * Available query fields:
     * array["fields"]              array       list of strings of response fields to return
     * array["exclude_fields"]      array       list of strings of response fields to exclude (not to be used with "fields")
     * array["count"]               int         number of records to return
     * array["offset"]              int         number of records from a collection to skip.
     * array["folder_id"]           string      Filter results by a specific campaign folder.
     * array["type"]                string      The campaign type.
     *                                          Possible values: regular,plaintext,absplit,rss,variate
     * array["before_send_time"]    string      Restrict the response to campaigns sent before the set time.
     *                                          ISO 8601 time format: 2015-10-21T15:41:36+00:00.
     * array["since_send_time"]     string      Restrict the response to campaigns sent after the set time.
     *                                          ISO 8601 time format: 2015-10-21T15:41:36+00:00.
     *
     * @param array $query (See Above) OPTIONAL associative array of query parameters.
     * @return object
     */
    public function getCamapaignReports(array $query = [])
    {
        return self::execute("GET", "reports", $query);
    }

    /**
     * Get a list of campaigns for the account
     *
     * Available query fields:
     * array["fields"]              array       list of strings of response fields to return
     * array["exclude_fields"]      array       list of strings of response fields to exclude (not to be used with "fields")
     *
     * @param string $campaign_id
     * @param array $query (See Above) OPTIONAL associative array of query parameters.
     * @return object
     */
    public function getCampaignReport($campaign_id, array $query = [])
    {
        return self::execute("GET", "reports/{$campaign_id}", $query);
    }

    /**
     * Get a list of abuse complaints for a specific campaign.
     *
     * Available query fields:
     * array["fields"]              array       list of strings of response fields to return
     * array["exclude_fields"]      array       list of strings of response fields to exclude (not to be used with "fields")
     *
     * @param string $campaign_id
     * @param array $query (See Above) OPTIONAL associative array of query parameters.
     * @return object
     */
    public function getAbuseReports($campaign_id, array $query = [])
    {
        return self::execute("GET", "reports/{$campaign_id}/abuse-reports", $query);
    }

    /**
     * Get information about a specific abuse report for a campaign.
     *
     * Available query fields:
     * array["fields"]              array       list of strings of response fields to return
     * array["exclude_fields"]      array       list of strings of response fields to exclude (not to be used with "fields")
     *
     * @param string $campaign_id
     * @param string $report_id
     * @param array $query (See Above) OPTIONAL associative array of query parameters.
     * @return object
     */
    public function getAbuseReport($campaign_id, $report_id, array $query = [])
    {
        return self::execute("GET", "reports/{$campaign_id}/abuse-reports/{$report_id}", $query);
    }

    /**
     * Get feedback based on a campaign’s statistics.
     *
     * Available query fields:
     * array["fields"]              array       list of strings of response fields to return
     * array["exclude_fields"]      array       list of strings of response fields to exclude (not to be used with "fields")
     *
     * @param string $campaign_id
     * @param array $query (See Above) OPTIONAL associative array of query parameters.
     * @return object
     */
    public function getAdvice($campaign_id, array $query = [])
    {
        return self::execute("GET", "reports/{$campaign_id}/advice", $query);
    }

    /**
     * Get information about clicks on specific links in your MailChimp campaigns.
     *
     * Available query fields:
     * array["fields"]              array       list of strings of response fields to return
     * array["exclude_fields"]      array       list of strings of response fields to exclude (not to be used with "fields")
     * array["count"]               int         number of records to return
     * array["offset"]              int         number of records from a collection to skip.
     *
     * @param string $campaign_id
     * @param array $query (See Above) OPTIONAL associative array of query parameters.
     * @return object
     */
    public function getClickReports($campaign_id, array $query = [])
    {
        return self::execute("GET", "reports/{$campaign_id}/click-details", $query);
    }

    /**
     * Get click details for a specific link.
     *
     * Available query fields:
     * array["fields"]              array       list of strings of response fields to return
     * array["exclude_fields"]      array       list of strings of response fields to exclude (not to be used with "fields")
     *
     * @param string $campaign_id
     * @param string $report_id
     * @param array $query (See Above) OPTIONAL associative array of query parameters.
     * @return object
     */
    public function getClickReport($campaign_id, $link_id, array $query = [])
    {
        return self::execute("GET", "reports/{$campaign_id}/click-details/{$link_id}", $query);
    }

    /**
     * Get information about list members who clicked on a specific link in a campaign.
     *
     * Available query fields:
     * array["fields"]              array       list of strings of response fields to return
     * array["exclude_fields"]      array       list of strings of response fields to exclude (not to be used with "fields")
     * array["count"]               int         number of records to return
     * array["offset"]              int         number of records from a collection to skip.
     *
     * @param string $campaign_id
     * @param string $link_id
     * @param array $query (See Above) OPTIONAL associative array of query parameters.
     * @return object
     */
    public function getClickReportMembers($campaign_id, $link_id, array $query = [])
    {
        return self::execute("GET", "reports/{$campaign_id}/click-details/{$link_id}/members", $query);
    }

    /**
     * Get information about a specific subscriber who clicked a link in a specific campaign.
     *
     * Available query fields:
     * array["fields"]              array       list of strings of response fields to return
     * array["exclude_fields"]      array       list of strings of response fields to exclude (not to be used with "fields")
     *
     * @param string $campaign_id
     * @param string $link_id
     * @param string $subscriber_hash
     * @param array $query (See Above) OPTIONAL associative array of query parameters.
     * @return object
     */
    public function getClickReportMember($campaign_id, $link_id, $subscriber_hash, array $query = [])
    {
        return self::execute("GET", "reports/{$campaign_id}/click-details/{$link_id}/members/{$subscriber_hash}", $query);
    }

    /**
     * Get statistics for the top-performing email domains in a campaign.
     *
     * Available query fields:
     * array["fields"]              array       list of strings of response fields to return
     * array["exclude_fields"]      array       list of strings of response fields to exclude (not to be used with "fields")
     *
     * @param string $campaign_id
     * @param array $query (See Above) OPTIONAL associative array of query parameters.
     * @return object
     */
    public function getDomainPerformance($campaign_id, array $query = [])
    {
        return self::execute("GET", "reports/{$campaign_id}/domain-performance", $query);
    }

    /**
     * Get a summary of social activity for the campaign, tracked by EepURL.
     *
     * Available query fields:
     * array["fields"]              array       list of strings of response fields to return
     * array["exclude_fields"]      array       list of strings of response fields to exclude (not to be used with "fields")
     *
     * @param string $campaign_id
     * @param array $query (See Above) OPTIONAL associative array of query parameters.
     * @return object
     */
    public function getSocialActivity($campaign_id, array $query = [])
    {
        return self::execute("GET", "reports/{$campaign_id}/eepurl", $query);
    }

    /**
     * Get a list of member’s subscriber activity in a specific campaign.
     *
     * Available query fields:
     * array["fields"]              array       list of strings of response fields to return
     * array["exclude_fields"]      array       list of strings of response fields to exclude (not to be used with "fields")
     * array["count"]               int         number of records to return
     * array["offset"]              int         number of records from a collection to skip.
     *
     * @param string $campaign_id
     * @param array $query (See Above) OPTIONAL associative array of query parameters.
     * @return object
     */
    public function getEmailActivity($campaign_id, array $query = [])
    {
        return self::execute("GET", "reports/{$campaign_id}/email-activity", $query);
    }

    /**
     * Get a specific list member’s activity in a campaign including opens, clicks, and bounces.
     *
     * Available query fields:
     * array["fields"]              array       list of strings of response fields to return
     * array["exclude_fields"]      array       list of strings of response fields to exclude (not to be used with "fields")
     *
     * @param string $campaign_id
     * @param string $subscriber_hash
     * @param array $query (See Above) OPTIONAL associative array of query parameters.
     * @return object
     */
    public function getMemberActivity($campaign_id, $subscriber_hash, array $query = [])
    {
        return self::execute("GET", "reports/{$campaign_id}/email-activity/{$subscriber_hash}", $query);
    }

    /**
     * Get top open locations for a specific campaign.
     *
     * Available query fields:
     * array["fields"]              array       list of strings of response fields to return
     * array["exclude_fields"]      array       list of strings of response fields to exclude (not to be used with "fields")
     *
     * @param string $campaign_id
     * @param array $query (See Above) OPTIONAL associative array of query parameters.
     * @return object
     */
    public function getTopLocations($campaign_id, array $query = [])
    {
        return self::execute("GET", "reports/{$campaign_id}/locations", $query);
    }

    /**
     * Get information about campaign recipients.
     *
     * Available query fields:
     * array["fields"]              array       list of strings of response fields to return
     * array["exclude_fields"]      array       list of strings of response fields to exclude (not to be used with "fields")
     * array["count"]               int         number of records to return
     * array["offset"]              int         number of records from a collection to skip.
     *
     * @param string $campaign_id
     * @param array $query (See Above) OPTIONAL associative array of query parameters.
     * @return object
     */
    public function getSentToMembers($campaign_id, array $query = [])
    {
        return self::execute("GET", "reports/{$campaign_id}/sent-to", $query);
    }

    /**
     * Get information about a specific campaign recipient.
     *
     * Available query fields:
     * array["fields"]              array       list of strings of response fields to return
     * array["exclude_fields"]      array       list of strings of response fields to exclude (not to be used with "fields")
     *
     * @param string $campaign_id
     * @param string $subscriber_hash
     * @param array $query (See Above) OPTIONAL associative array of query parameters.
     * @return object
     */
    public function getSentToMember($campaign_id, $subscriber_hash, array $query = [])
    {
        return self::execute("GET", "reports/{$campaign_id}/sent-to/{$subscriber_hash}", $query);
    }

    /**
     * Get a list of reports with child campaigns for a specific parent campaign.
     *
     * Available query fields:
     * array["fields"]              array       list of strings of response fields to return
     * array["exclude_fields"]      array       list of strings of response fields to exclude (not to be used with "fields")
     *
     * @param string $campaign_id
     * @param array $query (See Above) OPTIONAL associative array of query parameters.
     * @return object
     */
    public function getSubReports($campaign_id, array $query = [])
    {
        return self::execute("GET", "reports/{$campaign_id}/sub-reports", $query);
    }

    /**
     * Get information about members who have unsubscribed from a specific campaign.
     *
     * Available query fields:
     * array["fields"]              array       list of strings of response fields to return
     * array["exclude_fields"]      array       list of strings of response fields to exclude (not to be used with "fields")
     * array["count"]               int         number of records to return
     * array["offset"]              int         number of records from a collection to skip.
     *
     * @param string $campaign_id
     * @param array $query (See Above) OPTIONAL associative array of query parameters.
     * @return object
     */
    public function getUnsubscribedMembers($campaign_id, array $query = [])
    {
        return self::execute("GET", "reports/{$campaign_id}/unsubscribed", $query);
    }

    /**
     * Get information about a specific list member who unsubscribed from a campaign.
     *
     * Available query fields:
     * array["fields"]              array       list of strings of response fields to return
     * array["exclude_fields"]      array       list of strings of response fields to exclude (not to be used with "fields")
     *
     * @param string $campaign_id
     * @param string $subscriber_hash
     * @param array $query (See Above) OPTIONAL associative array of query parameters.
     * @return object
     */
    public function getUnsubscribedMember($campaign_id, $subscriber_hash, array $query = [])
    {
        return self::execute("GET", "reports/{$campaign_id}/unsubscribed/{$subscriber_hash}", $query);
    }

}
