<?php

namespace MailChimp\Conversations;

use MailChimp\MailChimp as MailChimp;

class Conversations extends MailChimp
{

    /**
     * Get a list of conversations for the account.
     *
     * Available query fields:
     * array["fields"]              array       list of strings of response fields to return
     * array["exclude_fields"]      array       list of strings of response fields to exclude (not to be used with "fields")
     * array["count"]               int         number of records to return
     * array["offset"]              int         number of records from a collection to skip.
     * array["has_unread_messages"] string      Whether the conversation has any unread messages.
     * array["list_id"]             string      The unique id for the list.
     * array["campaign_id"]         string      The unique id for the camapign.
     *
     * @param array $query (See Above) OPTIONAL associative array of query parameters.
     * @return object
     */
    public function getConversations(array $query = [])
    {
        return self::execute("GET", "conversations", $query);
    }

    /**
     * Get details about an individual conversation.
     *
     * Available query fields:
     * array["fields"]              array       list of strings of response fields to return
     * array["exclude_fields"]      array       list of strings of response fields to exclude (not to be used with "fields")
     *
     * @param string $conversation_id
     * @param array $query (See Above) OPTIONAL associative array of query parameters.
     * @return object
     */
    public function getConversation($conversation_id, array $query = [])
    {
        return self::execute("GET", "conversations/{$conversation_id}", $query);
    }

    /**
     * Get messages from a specific conversation.
     *
     * Available query fields:
     * array["fields"]              array       list of strings of response fields to return
     * array["exclude_fields"]      array       list of strings of response fields to exclude (not to be used with "fields")
     * array["is_read"]             string      Whether a conversation message has been marked as read.
     * array["before_timestamp"]    string      Restrict the response to messages created before the set time.
     *                                          ISO 8601 time format: 2015-10-21T15:41:36+00:00.
     * array["since_timestamp"]     string      Restrict the response to messages created after the set time.
     *                                          ISO 8601 time format: 2015-10-21T15:41:36+00:00.
     *
     * @param string $conversation_id
     * @param array $query (See Above) OPTIONAL associative array of query parameters.
     * @return object
     */
    public function getConversationMessages($conversation_id, array $query = [])
    {
        return self::execute("GET", "conversations/{$conversation_id}/messages", $query);
    }

    /**
     * Get an individual message in a conversation.
     *
     * Available query fields:
     * array["fields"]              array       list of strings of response fields to return
     * array["exclude_fields"]      array       list of strings of response fields to exclude (not to be used with "fields")
     *
     * @param string $conversation_id
     * @param string $message_id
     * @param array $query (See Above) OPTIONAL associative array of query parameters.
     * @return object
     */
    public function getConversationMessage($conversation_id, $message_id, array $query = [])
    {
        return self::execute("GET", "conversations/{$conversation_id}/messages/{$message_id}", $query);
    }

    /**
     * Post a new message to a conversation
     *
     * @param string $conversation_id
     * @param string $from_email
     * @param boolean  $read
     * @param array    $optional_settings
     * @return object
     */
    public function postMessage($conversation_id, $from_email, $read = false, array $optional_settings = null)
    {
        $optional_fields = ["subject", "message"];
        $data = [
            "from_email" => $from_email,
            "read" => $read
        ];

        // If the optional fields are passed, process them against the list of optional fields.
        if (isset($optional_settings)) {
            $data = array_merge($data, self::optionalFields($optional_fields, $optional_settings));
        }

        return self::execute("POST", "conversations/{$conversation_id}/messages", $data);
    }

}
