<?php
namespace MailChimp\Automations;

use MailChimp\MailChimp as MailChimp;

class Automations extends MailChimp
{

    /**
     * Get a summary of an account’s Automations.
     *
     * array["fields"]              array       list of strings of response fields to return
     * array["exclude_fields"]      array       list of strings of response fields to exclude (not to be used with "fields")
     *
     * @param array $query (See Above) OPTIONAL associative array of query parameters.
     * @return object
     */
    public function getAutomations(array $query = [])
    {
        return self::execute("GET", "automations/", $query);
    }

    /**
     * Get a summary of an individual Automation workflow’s settings and content.
     *
     * array["fields"]              array       list of strings of response fields to return
     * array["exclude_fields"]      array       list of strings of response fields to exclude (not to be used with "fields")
     * @param string $workflow_id
     * @param array $query (See Above) OPTIONAL associative array of query parameters.
     * @return object
     */
    public function getAutomation($workflow_id, array $query = [])
    {
        return self::execute("GET", "automations/{$workflow_id}", $query);
    }

    /**
     * Start all emails in an Automation workflow.
     *
     * @param string $workflow_id
     */
    public function startAutomation($workflow_id)
    {
        return self::execute("POST", "automations/{$workflow_id}/actions/start-all-emails");
    }

    /**
     * Pause all emails in an Automation workflow.
     *
     * @param string $workflow_id
     */
    public function pauseAutomation($workflow_id)
    {
        return self::execute("POST", "automations/{$workflow_id}/actions/pause-all-emails");
    }

    /**
     * Get a list of automated emails in a workflow
     *
     * array["fields"]              array       list of strings of response fields to return
     * array["exclude_fields"]      array       list of strings of response fields to exclude (not to be used with "fields")
     * @param string $workflow_id
     * @param array $query (See Above) OPTIONAL associative array of query parameters.
     * @return object
     */
    public function getWorkflowEmails($workflow_id, array $query = [])
    {
        return self::execute("GET", "automations/{$workflow_id}/emails", $query);
    }

    /**
     * Get information about a specific workflow email
     *
     * array["fields"]              array       list of strings of response fields to return
     * array["exclude_fields"]      array       list of strings of response fields to exclude (not to be used with "fields")
     * @param string $workflow_id
     * @param string $workflow_email_id
     * @param array $query (See Above) OPTIONAL associative array of query parameters.
     * @return object
     */
    public function getWorkflowEmail($workflow_id, $workflow_email_id, array $query = [])
    {
        return self::execute("GET", "automations/{$workflow_id}/emails/{$workflow_email_id}", $query);
    }

    /**
     * Start an automated email
     *
     * @param string $workflow_id
     * @param string $workflow_email_id
     */
    public function startWorkflowEmail($workflow_id,$workflow_email_id)
    {
        return self::execute("POST", "automations/{$workflow_id}/emails/{$workflow_email_id}/actions/start-all-emails");
    }

    /**
     * Pause an automated email
     *
     * @param string $workflow_id
     * @param string $workflow_email_id
     */
    public function pauseWorkflowEmail($workflow_id,$workflow_email_id)
    {
        return self::execute("POST", "automations/{$workflow_id}/emails/{$workflow_email_id}/actions/pause-all-emails");
    }

    /**
     * View queued subscribers for an automated email
     *
     * array["fields"]              array       list of strings of response fields to return
     * array["exclude_fields"]      array       list of strings of response fields to exclude (not to be used with "fields")
     * @param string $workflow_id
     * @param string $workflow_email_id
     * @param array $query (See Above) OPTIONAL associative array of query parameters.
     * @return object
     */
    public function getWorkflowEmailQueue($workflow_id, $workflow_email_id, array $query = [])
    {
        return self::execute("GET", "automations/{$workflow_id}/emails/{$workflow_email_id}/queue", $query);
    }

    /**
     * View specific subscriber in email queue
     *
     * array["fields"]              array       list of strings of response fields to return
     * array["exclude_fields"]      array       list of strings of response fields to exclude (not to be used with "fields")
     * @param string $workflow_id
     * @param string $workflow_email_id
     * @param string $email_address
     * @param array $query (See Above) OPTIONAL associative array of query parameters.
     * @return object
     */
    public function getWorkflowEmailSubscriber($workflow_id, $workflow_email_id, $email_address, array $query = [])
    {
        $hash = self::getMemberHash($email_address);
        return self::execute("GET", "automations/{$workflow_id}/emails/{$workflow_idEmailId}/queue/{$hash}", $query);
    }

    /**
     * Add a subscriber to a workflow email
     *
     * @param string $workflow_id
     * @param string $workflow_email_id
     * @param string $email_address
     */
    public function addWorkflowEmailSubscriber($workflow_id, $workflow_email_id, $email_address)
    {
        $data = [ "email_address" => $email_address];
        return self::execute("POST", "automations/{$workflow_id}/email/{$workflow_email_id}/queue", $data);
    }

    /**
     * View all subscribers removed from a workflow
     *
     * array["fields"]              array       list of strings of response fields to return
     * array["exclude_fields"]      array       list of strings of response fields to exclude (not to be used with "fields")
     * @param string $workflow_id
     */
    public function getRemovedWorkflowSubscribers($workflow_id, array $query = [])
    {
        return self::execute("GET", "automations/{$workflow_id}/removed-subscribers", $query);
    }

    /**
     * Remove subscriber from a workflow
     *
     * @param string $workflow_id
     * @param string $workflow_email_id
     * @param string $email_address
     */
    public function removeWorkflowSubscriber($workflow_id, $email_address)
    {
        $data = ["email_address" => $email_address];
        return self::execute("POST", "automations/{$workflow_id}/removed-subscribers", $data);
    }

}
