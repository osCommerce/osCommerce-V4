<?php
namespace MailChimp\Lists;

class Interests extends Lists
{

    /**
     * Get information about a list’s interest categories.
     *
     * Available query fields:
     * array["fields"]              array       list of strings of response fields to return
     * array["exclude_fields"]      array       list of strings of response fields to exclude (not to be used with "fields")
     * array["count"]               int         number of records to return
     * array["offset"]              int         number of records from a collection to skip.
     * array["type"]                string      Restrict results a type of interest group
     * @param string $list_id
     * @param array $query (See Above) OPTIONAL associative array of query parameters.
     * @return object
     */
    public function getInterestCategories($list_id, array $query = [])
    {
        return self::execute("GET", "lists/{$list_id}/interest-categories", $query);
    }

    /**
     * Get information about a specific interest category.
     *
     * Available query fields:
     * array["fields"]              array       list of strings of response fields to return
     * array["exclude_fields"]      array       list of strings of response fields to exclude (not to be used with "fields")
     * @param string $list_id
     * @param string $interest_category_id
     * @param array $query (See Above) OPTIONAL associative array of query parameters.
     * @return object
     */
    public function getInterestCategory($list_id, $interest_category_id, array $query = [])
    {
        return self::execute("GET", "lists/{$list_id}/interest-categories/{$interest_category_id}", $query);
    }

    /**
     * Create a new interest category
     *
     * array["data"]
     *      ["title"]           string      The text description of this category.
     *      ["display_order"]   int         The order that the categories are displayed in the list. Lower numbers display first.
     *      ["type"]            string      Determines how this category’s interests are displayed on signup forms.
     *                                      Possible Values: checkboxes,dropdown,radio,hidden
     * @param string $list_id
     * @param array $data
     * @return object
     */
     public function createInterestCategory($list_id, $title, $type, $display_order = null)
     {
        $data = [
            "title" => $title,
            "type" => $type
        ];

        if ($display_order) {
            $data["display_order"] = $display_order;
        }
        return self::execute("POST", "lists/{$list_id}/interest-categories", $data);
     }

     /**
      * Update a specific interest category.
      *
      * array["data"]
      *      ["title"]           string      The text description of this category.
      *      ["display_order"]   int         The order that the categories are displayed in the list. Lower numbers display first.
      *      ["type"]            string      Determines how this category’s interests are displayed on signup forms.
      *                                      Possible Values: checkboxes,dropdown,radio,hidden
      * @param string $list_id
      * @param string $interest_category_id
      * @param array $data
      * @return object
      */
      public function updateInterestCategory($list_id, $interest_category_id, array $data = [])
      {
         return self::execute("PATCH", "lists/{$list_id}/interest-categories/{$interest_category_id}", $data);
      }

    /**
     * Delete a specific interest category.
     *
     * @param string $list_id
     * @param string $interest_category_id
     */
    public function deleteInterestCategory($list_id, $interest_category_id)
    {
        return self::execute("DELETE", "lists/{$list_id}/interest-categories/{$interest_category_id}");
    }

    /**
     * Get a list of this category’s interests.
     *
     * Available query fields:
     * array["fields"]              array       list of strings of response fields to return
     * array["exclude_fields"]      array       list of strings of response fields to exclude (not to be used with "fields")
     * array["count"]               int         number of records to return
     * array["offset"]              int         number of records from a collection to skip.
     * @param string $list_id
     * @param array $query (See Above) OPTIONAL associative array of query parameters.
     * @return object
     */
    public function getInterests($list_id, $interest_category_id, array $query = [])
    {
        return self::execute("GET", "lists/{$list_id}/interest-categories/{$interest_category_id}/interests", $query);
    }

    /**
     * Get interests or ‘group names’ for a specific category.
     *
     * Available query fields:
     * array["fields"]              array       list of strings of response fields to return
     * array["exclude_fields"]      array       list of strings of response fields to exclude (not to be used with "fields")
     * @param string $list_id
     * @param string $interest_category_id
     * @param string $interest_id
     * @param array $query (See Above) OPTIONAL associative array of query parameters.
     * @return object
     */
    public function getInterest($list_id, $interest_category_id, $interest_id, array $query = [])
    {
        return self::execute("GET", "lists/{$list_id}/interest-categories/{$interest_category_id}/interests/{$interest_id}", $query);
    }

    /**
     * Create a new interest or ‘group name’ for a specific category.
     *
     * array["data"]
     *      ["name"]           string       The name of the interest.
     * @param string $list_id
     * @param string $interest_category_id
     * @param array $data (See Above) OPTIONAL associative array of query parameters.
     * @return object
     */
    public function createInterest($list_id, $interest_category_id, $name, $display_order = null)
    {
        $data = [
            "name" => $name
        ];

        if ($display_order !== null) {
            $data["display_order"] = $display_order;
        }
        return self::execute("POST", "lists/{$list_id}/interest-categories/{$interest_category_id}/interests/", $data);
    }

    /**
     * Update interests or ‘group names’ for a specific category.
     *
     * array["data"]
     *      ["name"]           string       The name of the interest.
     * @param string $list_id
     * @param string $interest_category_id
     * @param string $interest_id
     * @param array $data (See Above) OPTIONAL associative array of query parameters.
     * @return object
     */
    public function updateInterest($list_id, $interest_category_id, $interest_id, array $data = [])
    {
        return self::execute("PATCH", "lists/{$list_id}/interest-categories/{$interest_category_id}/interests/{$interest_id}", $data);
    }


    /**
     * Delete interests or group names in a specific category.
     *
     * @param string $list_id
     * @param string $interest_category_id
     * @param string $interest_id
     * @return object
     */
    public function deleteInterest($list_id, $interest_category_id, $interest_id)
    {
        return self::execute("DELETE", "lists/{$list_id}/interest-categories/{$interest_category_id}/interests/{$interest_id}");
    }


}
