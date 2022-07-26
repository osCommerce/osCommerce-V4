<?php
namespace MailChimp\Campaigns;

use MailChimp\MailChimp as MailChimp;

class Feedback extends MailChimp
{

    /**
     * Get feedback about a campaign
     *
     * array["fields"]              array       list of strings of response fields to return
     * array["exclude_fields"]      array       list of strings of response fields to exclude (not to be used with "fields")
     *
     * @param string $campaign_id for the campaign instance
     * @param array $query (See Above) OPTIONAL associative array of query parameters.
     * @return object
     */
    public function getFeedback($campaign_id, array $query = [])
    {
        return self::execute("GET", "campaigns/{$campaign_id}/feedback", $query);
    }

    /**
     * Get a specific feedback message
     *
     * array["fields"]              array       list of strings of response fields to return
     * array["exclude_fields"]      array       list of strings of response fields to exclude (not to be used with "fields")
     *
     * @param string $campaign_id for the campaign instance
     * @param string $feedback_id
     * @param array $query (See Above) OPTIONAL associative array of query parameters.
     * @return object
     */
    public function getFeedbackMessage($campaign_id, $feedback_id, array $query = [])
    {
        return self::execute("GET", "campaigns/{$campaign_id}/feedback/{$feedback_id}", $query);
    }

    /**
     * Update a campaign feedback message
     *     *
     * @param string $campaign_id for the campaign instance
     * @param string $feedback_id
     * @param array $data
     * @return object
     */
    public function createFeedbackMessage($campaign_id, $feedback_id, $message, array $optional_settings = null)
    {
        $optional_fields = ["block_id", "is_complete"];
        $data = ["message" => $message];

        // If the optional fields are passed, process them against the list of optional fields.
        if (isset($optional_settings)) {
            $data = array_merge($data, self::optionalFields($optional_fields, $optional_settings));
        }

        return self::execute("PATCH", "campaigns/{$campaign_id}/feedback/{$feedback_id}", $data);
    }

    /**
     * Update a campaign feedback message
     *     *
     * @param string $campaign_id for the campaign instance
     * @param string $feedback_id
     * @param array $data
     * @return object
     */
    public function updateFeedbackMessage($campaign_id, $feedback_id, array $data = [])
    {
        return self::execute("PATCH", "campaigns/{$campaign_id}/feedback/{$feedback_id}", $data);
    }

    /**
     * Delete a campaign feedback message
     *
     * @param string $campaign_id for the campaign instance
     * @param string $feedback_id
     */
    public function deleteFeedbackMessage($campaign_id, $feedback_id)
    {
        return self::execute("DELETE", "campaigns/{$campaign_id}/feedback/{$feedback_id}");
    }


}
