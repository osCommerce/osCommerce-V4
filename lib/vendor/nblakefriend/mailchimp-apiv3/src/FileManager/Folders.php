<?php

namespace MailChimp\FileManager;

use MailChimp\MailChimp as MailChimp;

class Folders extends MailChimp
{

    /**
     * Get a list of all folders in the File Manager.
     *
     * Available query fields:
     * array["fields"]              array       list of strings of response fields to return
     * array["exclude_fields"]      array       list of strings of response fields to exclude (not to be used with "fields")
     * array["count"]               int         number of records to return
     * array["offset"]              int         number of records from a collection to skip.
     * array["folder_id"]           int         Filter results by a specific campaign folder.
     * array["created_by"]          string      The MailChimp account user who created the File Manager file.
     * array["before_created_at"]   string      Restrict the response to files created before the set date
     *                                          ISO 8601 time format: 2015-10-21T15:41:36+00:00.
     * array["since_created_at"]    string      Restrict the response to files created after the set date.
     *                                          ISO 8601 time format: 2015-10-21T15:41:36+00:00.
     *
     * @param array $query (See Above) OPTIONAL associative array of query parameters.
     * @return object
     */
    public function getFolders(array $query = [])
    {
        return self::execute("GET", "file-manager/folders", $query);
    }

    /**
     * Get information about a specific folder in the File Manager.
     *
     * Available query fields:
     * array["fields"]              array       list of strings of response fields to return
     * array["exclude_fields"]      array       list of strings of response fields to exclude (not to be used with "fields")
     *
     * @param int file_id
     * @param array $query (See Above) OPTIONAL associative array of query parameters.
     * @return object
     */
    public function getFolder($folder_id, array $query = [])
    {
        return self::execute("GET", "file-manager/folders/{$folder_id}", $query);
    }

    /**
     * Create a new folder in the File Manager.
     *
     * @param string $name
     * @return object
     */
    public function createFolder($name)
    {
        $data = [
            "name" => $name,
        ];

        return self::execute("POST", "file-manager/folders", $data);
    }

    /**
     * Update a File Manager Folder.
     *
     * @param string file_id
     * @param array $data
     */
    public function updateFolder($folder_id, $name)
    {
        $data = [
            "name" => $name,
        ];

        return self::execute("PATCH", "file-manager/folders/{$folder_id}", $data);
    }

    /**
     * Delete a specific folder in the File Manager.
     *
     * @param int $folder_id
     */
    public function deleteFolder($folder_id)
    {
        return self::execute("DELETE", "file-manager/folders/{$folder_id}");
    }

}
