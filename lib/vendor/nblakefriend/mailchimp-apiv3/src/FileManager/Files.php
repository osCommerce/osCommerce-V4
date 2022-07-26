<?php

namespace MailChimp\FileManager;

use MailChimp\MailChimp as MailChimp;

class Files extends MailChimp
{

    /**
     * Get a list of available images and files stored in the File Manager for the account.
     *
     * Available query fields:
     * array["fields"]              array       list of strings of response fields to return
     * array["exclude_fields"]      array       list of strings of response fields to exclude (not to be used with "fields")
     * array["count"]               int         number of records to return
     * array["offset"]              int         number of records from a collection to skip.
     * array["folder_id"]           int         Filter results by a specific campaign folder.
     * array["created_by"]          string      The MailChimp account user who created the File Manager file.
     * array["type"]                string      The file type for the File Manager file
     *                                          Possible values: image,file
     * array["before_created_at"]   string      Restrict the response to files created before the set date
     *                                          ISO 8601 time format: 2015-10-21T15:41:36+00:00.
     * array["since_created_at"]    string      Restrict the response to files created after the set date.
     *                                          ISO 8601 time format: 2015-10-21T15:41:36+00:00.
     * array["sort_field"]          string      Returns files sorted by the specified field.
     * array["sort_dir"]            string      Determines the order direction for sorted results.
     *
     * @param array $query (See Above) OPTIONAL associative array of query parameters.
     * @return object
     */
    public function getFiles(array $query = [])
    {
        return self::execute("GET", "file-manager/files", $query);
    }

    /**
     * Get information about a specific file in the File Manager.
     *
     * Available query fields:
     * array["fields"]              array       list of strings of response fields to return
     * array["exclude_fields"]      array       list of strings of response fields to exclude (not to be used with "fields")
     *
     * @param int file_id
     * @param array $query (See Above) OPTIONAL associative array of query parameters.
     * @return object
     */
    public function getFile($file_id, array $query = [])
    {
        return self::execute("GET", "file-manager/files/{$file_id}", $query);
    }

    /**
     * Upload a new image or file to the File Manager.
     *
     * @param string $name
     * @param string $file_data
     * @param int   (optional) $folder_id
     * @return object
     */
    public function uploadFile($name, $file_data, $folder_id = null)
    {
        $data = [
            "name" => $name,
            "file_data" => $file_data
        ];

        if ($folder_id) {
            $data["folder_id"] = $folder_id;
        }

        return self::execute("POST", "file-manager/files", $data);
    }

    /**
     * Update a file in the File Manager.
     *
     * @param string file_id
     * @param array $data
     */
    public function updateFile($file_id, array $data = [])
    {
        return self::execute("PATCH", "file-manager/files/{$file_id}", $data);
    }

    /**
     * Remove a specific file from the File Manager.
     *
     * @param int file_id
     */
    public function deleteFile($file_id)
    {
        return self::execute("DELETE", "file-manager/files/{$file_id}");
    }

}
