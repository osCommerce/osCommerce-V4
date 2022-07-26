<?php
namespace MailChimp\Lists;

class MergeFields extends Lists
{

    /**
     * Get a list of all merge fields (formerly merge vars) for a list.
     * Available query fields:
     * array["fields"]                  array       list of strings of response fields to return
     * array["exclude_fields"]          array       list of strings of response fields to exclude (not to be used with "fields")
     * array["count"]                   int         number of records to return
     * array["offset"]                  int         number of records from a collection to skip.
     * array["type"]                    string      The merge field type.
     * array["required"]                boolean     The boolean value if the merge field is required.
     * @param string $list_id
     * @param array $query (See Above) OPTIONAL associative array of query parameters.
     * @return object
     */
    public function getMergeFields($list_id, array $query = [])
    {
        return self::execute("GET", "lists/{$list_id}/merge-fields", $query);
    }

    /**
     * Get information about a specific merge field in a list.
     * Available query fields:
     * array["fields"]                  array       list of strings of response fields to return
     * array["exclude_fields"]          array       list of strings of response fields to exclude (not to be used with "fields")
     * @param string $list_id
     * @param int $merge_id
     * @param array $query (See Above) OPTIONAL associative array of query parameters.
     * @return object
     */
    public function getMergeField($list_id, $merge_id, array $query = [])
    {
        return self::execute("GET", "lists/{$list_id}/merge-fields/{$merge_id}", $query);
    }

    /**
     * Create a new merge field for a specific list
     *  "name"                    string       Required. The name of the merge field
     *  "type"                    string       Required. The type for the merge field
     * array["optional_settings"]
     *      ["tag"]                     string       The tag used in MailChimp campaigns and for the /members endpoint.
     *      ["required"]                boolean      The boolaen value if the merge field is required
     *      ["default_value"]           string       The type for the merge field
     *      ["public"]                  boolean      Whether the merge field is displayed on the signup form.
     *      ["display_order"]           int          The order the merge field displays on the signup form.
     *      ["options"]                 array        Extra option for some merge field tidy_parse_string
     *             ["default_country"]  int          In an address field, the default country code if none supplied
     *             ["phone_format"]     string       In a phone field, the phone number tupe: US or International
     *             ["date_format"]      string       In a date or birthday field, the format of the date
     *             ["choices"]          array        In a radio or dropdown non-group field, the available options.
     *             ["size"]             int          In a text field, the default length fo the text field
     *      ["help_text"]               string        Extra text to help the subscrber fill out the form
     * @param string $list_id
     * @param string $name
     * @param string $type
     * @param array $optional_settings
     * @return object
     */
     public function createMergeField($list_id, $name, $type,  array $optional_settings = [])
     {
        $optional_fields = ["tag", "required", "default_value", "public", "display_order", "options", "help_text"];

        $data = [
            "name" => $name,
            "type" => $type
        ];

        // If the optional fields are passed, process them against the list of optional fields.
        if (isset($optional_settings)) {
            $data = array_merge($data, self::optionalFields($optional_fields, $optional_settings));
        }

        return self::execute("POST", "lists/{$list_id}/merge-fields", $data);
     }

    /**
     * Update a specific merge field in a list
     * @param string $list_id
     * @param string $merge_id
     * @param array $data (See createMergeField() for structure)
     * @return object
     */
     public function updateMergeField($list_id, $merge_id, array $data = [])
     {
        return self::execute("PATCH", "lists/{$list_id}/merge-fields/{$merge_id}", $data);
     }


    /**
     * Delete a specific merge field in a list.
     * @param string $list_id
     * @param int $merge_id
     */
    public function deleteMergeField($list_id, $merge_id)
    {
        return self::execute("DELETE", "lists/{$list_id}/merge-fields/{$merge_id}");
    }

}
