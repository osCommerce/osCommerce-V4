<?php
namespace MailChimp\Lists;

class Webhooks extends Lists
{
    /**
     * Get a list of webhooks
     * @param string $list_id
     * @param array $query (See Above) OPTIONAL associative array of query parameters.
     * @return object
    */
    public function getWebhooks($list_id, array $query = [])
    {
        return self::execute("GET", "lists/{$list_id}/webhooks", $query);
    }

    /**
     * Get a single webhook
     *
     * @param string $list_id
     * @param string $webhook_id
     * @param array $query (See Above) OPTIONAL associative array of query parameters.
     * @return object
    */
    public function getWebhook($list_id, $webhook_id, array $query = [])
    {
        return self::execute("GET", "lists/{$list_id}/webhooks/{$webhook_id}", $query);
    }

    /**
     * Create a new webhook
     *
     * array["data"]
     *      ["url"]             string      A valid URL for the Webhook.
     *      ["events"]          array      The events that can trigger the webhook and whether they are enabled.
     *          "subscribe"     boolean
     *          "unsubscribe"   boolean
     *          "profile"       boolean
     *          "cleaned"       boolean
     *          "upemail"       boolean
     *          "campaign"      boolean
     *      ["sources"]         array      The possible sources of any events that can trigger the webhook and whether they are enabled.
     *          "user"          boolean
     *          "admin"         boolean
     *          "api"           boolean
     *
     * @param string $list_id
     * @param array $data
     * @return object
    */
    public function createWebhook($list_id, array $data = [])
    {
        return self::execute("POST", "lists/{$list_id}/webhooks", $data);
    }

    /**
     * Delete a webhook
     *
     * @param string $list_id
     * @param string $webhook_id
    */
    public function deleteWebhook($list_id, $webhook_id)
    {
        return self::execute("DELETE", "lists/{$list_id}/webhooks/{$webhook_id}");
    }

}
