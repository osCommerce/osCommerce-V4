<?php
namespace MailChimp\Lists;

class Segments extends Lists
{

    /**
     * Get a list of campaigns for the account
     * Available query fields:
     * array["fields"]              array       list of strings of response fields to return
     * array["exclude_fields"]      array       list of strings of response fields to exclude (not to be used with "fields")
     * array["count"]               int         number of records to return
     * array["offset"]              int         number of records from a collection to skip.
     * array["type"]                string      The campaign type.
     *                                          Possible values: saved,static,fuzzy
     * array["since_created_at"]    string      Restrict the response to campaigns created after the set time.
     *                                          ISO 8601 time format: 2015-10-21T15:41:36+00:00.
     * array["before_created_at"]   string      Restrict the response to segments created before the set time.
     *                                          ISO 8601 time format: 2015-10-21T15:41:36+00:00.
     * array["since_updated_at"]   string       Restrict the response to segments updated after the set time.
     *                                          ISO 8601 time format: 2015-10-21T15:41:36+00:00.
     * array["before_updated_at"]   string      Restrict the response to segments updated after the set time.
     *                                          ISO 8601 time format: 2015-10-21T15:41:36+00:00.
     * @param string $list_id
     * @param array $query (See Above) OPTIONAL associative array of query parameters.
     * @return object
     */
    public function getListSegments($list_id, array $query = [])
    {
        return self::execute("GET", "lists/{$list_id}/segments", $query);
    }

    /**
     * Get information about a specific segment.
     * Available query fields:
     * array["fields"]              array       list of strings of response fields to return
     * array["exclude_fields"]      array       list of strings of response fields to exclude (not to be used with "fields")
     * @param string $list_id
     * @param string $segment_id
     * @param array $query (See Above) OPTIONAL associative array of query parameters.
     * @return object
     */
     public function getListSegment($list_id, $segment_id, array $query = [])
     {
         return self::execute("GET", "lists/{$list_id}/segments/{$segment_id}", $query);
     }

     /**
      * Get information about members in a saved segment.
      * Available query fields:
      * array["fields"]              array       list of strings of response fields to return
      * array["exclude_fields"]      array       list of strings of response fields to exclude (not to be used with "fields")
      * array["count"]               int         number of records to return
      * array["offset"]              int         number of records from a collection to skip.
      * @param string $list_id
      * @param string $segment_id
      * @param array $query (See Above) OPTIONAL associative array of query parameters.
      * @return object
      */
      public function getListSegmentMembers($list_id, $segment_id, array $query = [])
      {
          return self::execute("GET", "lists/{$list_id}/segments/{$segment_id}/members", $query);
      }


     /**
      * Create a new segment in a specific list.
      * array["data"]
      *      ["name"]               string     required
      *      ["static_segment"]     array       An array of emails to be used for a static segment.
      *                                         Any emails provided that are not present on the list will be ignored.
      *                                         Passing an empty array will create a static segment without any subscribers.
      *                                         This field cannot be provided with the options field.
      *     ["options"]             array       The conditions of the segment. Static and fuzzy segments don’t have conditions.
      *         ["match"]           string      Match Type Possible Values: any, all
      *         ["conditions"]      array       An array of segment conditions.
      *               Structure depends on segment http://developer.mailchimp.com/documentation/mailchimp/reference/lists/segments/#
      * @param string $list_id
      * @param array $data
      * @return object
      */
     public function createListSegment($list_id, array $data =[])
     {
         return self::execute("POST", "lists/{$list_id}/segments", $data);
     }

     /**
      * Update a specific segment in a list.
      * Structure depends on segment http://developer.mailchimp.com/documentation/mailchimp/reference/lists/segments/#
      * @param string $list_id
      * @param array $data
      * @return object
      */
     public function updateListSegment($list_id, $segment_id, array $data = null)
     {
         return self::execute("PATCH", "lists/{$list_id}/segments/{$segment_id}", $data);
     }

     /**
     * Add a member to a static segment.
     * array["data"]
     *      ["email_address"]      string     required
      * @param string $list_id
      * @param array $data
      * @return object
      */
     public function addListSegmentMember($list_id, $segment_id, $email_address, array $data =[])
     {
         return self::execute("POST", "lists/{$list_id}/segments/{$segment_id}/members", $data);
     }

     /**
     * Batch add/remover members to a static segment.
     * array["emails"]
     *      ["add"]      array
     *      ["remove"]      array
      * @param string $list_id
      * @param string $segment_id
      * @param array $emails
      * @return object
      */
     public function batchAddRemoveSegmentMembers($list_id, $segment_id, array $emails = [])
     {
         if (isset($emails["add"])) {
             $data["members_to_add"] = $emails["add"];
         }

         if (isset($emails["remove"])) {
            $data["members_to_remove"] = $emails["remove"];
        }
        // print_r ($data);
         return self::execute("POST", "lists/{$list_id}/segments/{$segment_id}", $data);
     }


     /**
      * Remove a member from the specified static segment.
      * @param string $list_id
      * @param string $segment_id
      * @param string $email_address
      */
      public function removeListSegmentMember($list_id, $segment_id, $email_address)
      {
          $hash = self::getMemberHash($email_address);
          return self::execute("DELETE", "lists/{$list_id}/segments/{$segment_id}/members/{$hash}");
      }

     /**
      * Delete a specific segment in a list.
      * @param string $list_id
      * @param string $segment_id
      */
      public function deleteListSegment($list_id, $segment_id)
      {
          return self::execute("DELETE", "lists/{$list_id}/segments/{$segment_id}");
      }

}
