<?php
namespace MailChimp\Lists;

class Members extends Lists
{
    /**
     * Get a list of list members
     * @param string $list_id
     * @param array $query (See Above) OPTIONAL associative array of query parameters.
     * @return object
     */
    public function getListMembers($list_id, array $query = [])
    {
        return self::execute("GET", "lists/{$list_id}/members", $query);
    }

    /**
     * Get a single list members
     * @param string $list_id
     * @param string $email_address
     * @param array $query (See Above) OPTIONAL associative array of query parameters.
     * @return object
     */
    public function getListMember($list_id, $email_address, array $query = [])
    {
        $hash = self::getMemberHash($email_address);
        return self::execute("GET", "lists/{$list_id}/members/{$hash}", $query);
    }

    /**
     * Get the last 50 events of a member’s activity on a specific list, including opens, clicks, and unsubscribes.
     * Available query fields:
     * array["fields"]              array       list of strings of response fields to return
     * array["exclude_fields"]      array       list of strings of response fields to exclude (not to be used with "fields")
     * @param string $list_id
     * @param string $email_address
     * @param array $query (See Above) OPTIONAL associative array of query parameters.
     * @return object
     */
    public function getMemberActiity($list_id, $email_address, array $query = [])
    {
        $hash = self::getMemberHash($email_address);
        return self::execute("GET", "lists/{$list_id}/members/{$hash}/activity", $query);
    }

    /**
     * Get the last 50 Goal events for a member on a specific list
     * Available query fields:
     * array["fields"]              array       list of strings of response fields to return
     * array["exclude_fields"]      array       list of strings of response fields to exclude (not to be used with "fields")
     * @param string $list_id
     * @param string $email_address
     * @param array $query (See Above) OPTIONAL associative array of query parameters.
     * @return object
     */
    public function getMemberGoals($list_id, $email_address, array $query = [])
    {
        $hash = self::getMemberHash($email_address);
        return self::execute("GET", "lists/{$list_id}/members/{$hash}/goals", $query);
    }

    /**
     * Get recent notes for a specific list member
     * Available query fields:
     * array["fields"]              array       list of strings of response fields to return
     * array["exclude_fields"]      array       list of strings of response fields to exclude (not to be used with "fields")
     * array["count"]                   int         number of records to return
     * array["offset"]                  int         number of records from a collection to skip.
     * @param string $list_id
     * @param string $email_address
     * @param array $query (See Above) OPTIONAL associative array of query parameters.
     * @return object
     */
    public function getMemberNotes($list_id, $email_address, array $query = [])
    {
        $hash = self::getMemberHash($email_address);
        return self::execute("GET", "lists/{$list_id}/members/{$hash}/notes", $query);
    }

    /**
     * Get a specific note for a specific list member.
     * Available query fields:
     * array["fields"]              array       list of strings of response fields to return
     * array["exclude_fields"]      array       list of strings of response fields to exclude (not to be used with "fields")
     * @param string $list_id
     * @param string $email_address
     * @param array $query (See Above) OPTIONAL associative array of query parameters.
     * @return object
     */
    public function getMemberNote($list_id, $email_address, $note_id, array $query = [])
    {
        $hash = self::getMemberHash($email_address);
        return self::execute("GET", "lists/{$list_id}/members/{$hash}/notes/{$note_id}", $query);
    }


    /**
     * Add List Member
     * "email_address"       string      required
     * "status"              string      required
     *                                   Possible Values: subscribed,unsubscribed,cleaned,pending
     * array["optional_settings"]
     * @param string $list_id
     * @param string $email_address
     * @param string $status
     * @param array $optional_settings
     * @return object
     */
    public function addListMember($list_id, $email_address, $status, array $optional_settings = null)
    {
        $optional_fields = ["email_type", "merge_fields", "interests", "language", "vip", "location"];
        $data = [
            "email_address" => $email_address,
            "status" => $status
        ];

        if (isset($optional_settings)) {
            $data = array_merge($data, self::optionalFields($optional_fields, $optional_settings));
        }

        return self::execute("POST", "lists/{$list_id}/members", $data);
    }

    /**
     * Add or Update List Member
     * array["data"]
     *      ["status"]              string      required
     *                                          Possible Values: subscribed,unsubscribed,cleaned,pending
     * @param string $list_id
     * @param string $email_address
     * @param array subscriber data
     * @return object
     */
    public function upsertListMember($list_id, $email_address, array $data = [])
    {
        $hash = self::getMemberHash($email_address);
        return self::execute("PUT", "lists/{$list_id}/members/{$hash}", $data);
    }

    /**
     * Update List Member
     * array["data"]
     *      ["email_address"]       string      required
     *      ["status"]              string      required
     *                                          Possible Values: subscribed,unsubscribed,cleaned,pending
     * @param string $list_id
     * @param array subscriber data
     * @return object
     */
    public function updateListMember($list_id, $email_address, array $data = [])
    {
        $hash = self::getMemberHash($email_address);
        return self::execute("PATCH", "lists/{$list_id}/members/{$hash}", $data);
    }

    /**
     * Add a new note for a specific subscriber
     * array["data"]
     *      ["note"]       string       The content of the note.
     * @param string $list_id
     * @param string $email_address
     * @param array $data (See Above) OPTIONAL associative array of query parameters.
     * @return object
     */
    public function addMemberNote($list_id, $email_address, array $data = [])
    {
        $hash = self::getMemberHash($email_address);
        return self::execute("POST", "lists/{$list_id}/members/{$hash}/notes", $data);
    }

    /**
     * Update a specific note for a specific list member.
     * array["data"]
     *      ["note"]       string       The content of the note.
     * @param string $list_id
     * @param string $email_address
     * @param int $noteId
     * @param array $data (See Above) OPTIONAL associative array of query parameters.
     * @return object
     */
    public function updateMemberNote($list_id, $email_address, $note_id, array $data = [])
    {
        $hash = self::getMemberHash($email_address);
        return self::execute("PATCH", "lists/{$list_id}/members/{$hash}/notes/{$note_id}", $data);
    }

    /**
     * Delete a specific note for a specific list member.
     * @param string $list_id
     * @param string $email_address
     * @param int $noteId
     */
    public function deleteMemberNote($list_id, $email_address, $note_id)
    {
        $hash = self::getMemberHash($email_address);
        return self::execute("DELETE", "lists/{$list_id}/members/{$hash}/notes/{$note_id}");
    }

    /**
     * Delete a subscriber
     * @param string $list_id
     * @param string email address
     */
    public function deleteListMember($list_id, $email_address)
    {
        $hash = self::getMemberHash($email_address);
        return self::execute("DELETE", "lists/{$list_id}/members/{$hash}");
    }

}
