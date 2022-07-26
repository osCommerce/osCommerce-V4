<?php
namespace MailChimp\Batches;

use MailChimp\MailChimp as MailChimp;

class Batches extends MailChimp
{

    /**
     * Get a summary of batch requests that have been made.
     *
     * Available query fields:
     * array["fields"]                  array       list of strings of response fields to return
     * array["exclude_fields"]          array       list of strings of response fields to exclude (not to be used with "fields")
     * array["count"]                   int         number of records to return
     * array["offset"]                  int         number of records from a collection to skip.
     * @param array $query (See Above) OPTIONAL associative array of query parameters.
     * @return object
     */
    public function getBatches (array $query = [])
    {
        return self::execute("GET", "batches", $query);
    }

    /**
     * Get the status of a batch request.
     *
     * Available query fields:
     * array["fields"]                  array       list of strings of response fields to return
     * array["exclude_fields"]          array       list of strings of response fields to exclude (not to be used with "fields")
     * @param string $batch_id
     * @param array $query (See Above) OPTIONAL associative array of query parameters.
     * @return object
     */
    public function getBatch ($batch_id, array $query = [])
    {
        return self::execute("GET", "batches/{$batch_id}", $query);
    }

    /**
     * Begin processing a batch operations request.
     *
     * @param array $data
     * @return object
     */
    public function createBatch ($data = array(["method" => "", "path" => "", "params" => null, "body"=> null, "operation_id" => null])  )
    {
        $operation = [];

        foreach ($data as $d) {
            $op = [];
            $op["method"] = $d["method"];
            $op["path"] = $d["path"];

            if ($d["method"] == "GET" && !empty($d["params"])) {
                $op["params"] = $d["params"];
            } elseif ($d["method"] == "POST" || $d["method"] == "PATCH" || $d["method"] == "PUT") {
                $op["body"] = $d["body"];
            }

            if (isset($d["operation_id"])) {
                $op["operation_id"] = $d["operation_id"];
            }

            $operation[] = $op;
        }

        $operations = [ "operations" => $operation ];
        return self::execute("POST", "batches", $operations);
    }

    /**
     * Delete a batch request and stop if from processing further.
     *
     * @param string $batch_id
     */
    public function deleteBatch ($batch_id)
    {
        return self::execute("DELETE", "batches/{$batch_id}");
    }

}
