<?php

namespace common\modules\email;

class Mandrill implements MailerInterface {
    
    public $apikey;
    public $ch;
    public $root = 'https://mandrillapp.com/api/1.0';
    public $debug = false;

    public static $error_map = array(
        "ValidationError" => "Mandrill_ValidationError",
        "Invalid_Key" => "Mandrill_Invalid_Key",
        "PaymentRequired" => "Mandrill_PaymentRequired",
        "Unknown_Subaccount" => "Mandrill_Unknown_Subaccount",
        "Unknown_Template" => "Mandrill_Unknown_Template",
        "ServiceUnavailable" => "Mandrill_ServiceUnavailable",
        "Unknown_Message" => "Mandrill_Unknown_Message",
        "Invalid_Tag_Name" => "Mandrill_Invalid_Tag_Name",
        "Invalid_Reject" => "Mandrill_Invalid_Reject",
        "Unknown_Sender" => "Mandrill_Unknown_Sender",
        "Unknown_Url" => "Mandrill_Unknown_Url",
        "Unknown_TrackingDomain" => "Mandrill_Unknown_TrackingDomain",
        "Invalid_Template" => "Mandrill_Invalid_Template",
        "Unknown_Webhook" => "Mandrill_Unknown_Webhook",
        "Unknown_InboundDomain" => "Mandrill_Unknown_InboundDomain",
        "Unknown_InboundRoute" => "Mandrill_Unknown_InboundRoute",
        "Unknown_Export" => "Mandrill_Unknown_Export",
        "IP_ProvisionLimit" => "Mandrill_IP_ProvisionLimit",
        "Unknown_Pool" => "Mandrill_Unknown_Pool",
        "NoSendingHistory" => "Mandrill_NoSendingHistory",
        "PoorReputation" => "Mandrill_PoorReputation",
        "Unknown_IP" => "Mandrill_Unknown_IP",
        "Invalid_EmptyDefaultPool" => "Mandrill_Invalid_EmptyDefaultPool",
        "Invalid_DeleteDefaultPool" => "Mandrill_Invalid_DeleteDefaultPool",
        "Invalid_DeleteNonEmptyPool" => "Mandrill_Invalid_DeleteNonEmptyPool",
        "Invalid_CustomDNS" => "Mandrill_Invalid_CustomDNS",
        "Invalid_CustomDNSPending" => "Mandrill_Invalid_CustomDNSPending",
        "Metadata_FieldLimit" => "Mandrill_Metadata_FieldLimit",
        "Unknown_MetadataField" => "Mandrill_Unknown_MetadataField"
    );

    public function __construct($apikey=null) {
        if(!$apikey) $apikey = getenv('MANDRILL_APIKEY');
        if(!$apikey) $apikey = $this->readConfigs();
        //if(!$apikey) throw new Mandrill_Error('You must provide a Mandrill API key');
        $this->apikey = $apikey;

        $this->ch = curl_init();
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($this->ch, CURLOPT_USERAGENT, 'Mandrill-PHP/1.0.54');
        curl_setopt($this->ch, CURLOPT_POST, true);
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->ch, CURLOPT_HEADER, false);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($this->ch, CURLOPT_TIMEOUT, 600);

        $this->root = rtrim($this->root, '/') . '/';
        
        $this->templates = new Mandrill\Templates($this);
        $this->exports = new Mandrill\Exports($this);
        $this->users = new Mandrill\Users($this);
        $this->rejects = new Mandrill\Rejects($this);
        $this->inbound = new Mandrill\Inbound($this);
        $this->tags = new Mandrill\Tags($this);
        $this->messages = new Mandrill\Messages($this);
        $this->whitelists = new Mandrill\Whitelists($this);
        $this->ips = new Mandrill\Ips($this);
        $this->internal = new Mandrill\Internal($this);
        $this->subaccounts = new Mandrill\Subaccounts($this);
        $this->urls = new Mandrill\Urls($this);
        $this->webhooks = new Mandrill\Webhooks($this);
        $this->senders = new Mandrill\Senders($this);
        $this->metadata = new Mandrill\Metadata($this);
    }

    public function __destruct() {
        curl_close($this->ch);
    }

    public function call($url, $params) {
        $params['key'] = $this->apikey;         
        $params = json_encode($params);
        $ch = $this->ch;

        curl_setopt($ch, CURLOPT_URL, $this->root . $url . '.json');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_VERBOSE, $this->debug);

        $start = microtime(true);
        $this->log('Call to ' . $this->root . $url . '.json: ' . $params);
        if($this->debug) {
            $curl_buffer = fopen('php://memory', 'w+');
            curl_setopt($ch, CURLOPT_STDERR, $curl_buffer);
        }

        $response_body = curl_exec($ch);
        $info = curl_getinfo($ch);
        $time = microtime(true) - $start;
        if($this->debug) {
            rewind($curl_buffer);
            $this->log(stream_get_contents($curl_buffer));
            fclose($curl_buffer);
        }
        $this->log('Completed in ' . number_format($time * 1000, 2) . 'ms');
        $this->log('Got response: ' . $response_body);

        if(curl_error($ch)) {
            throw new Mandrill\HttpError("API call to $url failed: " . curl_error($ch));
        }
        $result = json_decode($response_body, true);
        if($result === null) throw new Mandrill\Error('We were unable to decode the JSON response from the Mandrill API: ' . $response_body);
        
        if(floor($info['http_code'] / 100) >= 4) {
            throw $this->castError($result);
        }

        return $result;
    }

    public function readConfigs() {
        $paths = array('~/.mandrill.key', '/etc/mandrill.key');
        foreach($paths as $path) {
            if(file_exists($path)) {
                $apikey = trim(file_get_contents($path));
                if($apikey) return $apikey;
            }
        }
        return false;
    }

    public function castError($result) {
        if($result['status'] !== 'error' || !$result['name']) throw new Mandrill\Error('We received an unexpected error: ' . json_encode($result));

        $class = (isset(self::$error_map[$result['name']])) ? self::$error_map[$result['name']] : 'Mandrill_Error';
        return new $class($result['message'], $result['code']);
    }

    public function log($msg) {
        if($this->debug) error_log($msg);
    }
    
    public function ready(){
        if (defined('MANDRILL_APIKEY')){
            $this->apikey = MANDRILL_APIKEY;
        }
        return !empty($this->apikey);
    }
    
    private $message = [];
    public function add_html($email_text, $text){
        $this->message['html'] = $email_text;
        $this->add_text($text);
    }
    
    public function add_text($text){
        $this->message['text'] = $text;
    }
    
    public function add_attachment($file, $name){
        if (!is_array($this->message['attachments'])) $this->message['attachments'] = [];
        
        try {
            $fInfo = new \finfo(FILEINFO_MIME);
        
            $this->message['attachments'][] = [
                'type' => $fInfo->buffer($file),
                'name' => $name,
                'content' => base64_encode($file)
            ];
        } catch (\Exception $ex) {
            \Yii::warning($ex->getMessage());
        }
        
    }
    
    public function build_message(){
        
    }
    
    public function addBcc($bcc){
        $this->message['bcc_address'] = $bcc;
    }
    
    public function send($to_name, $to_email_address, $from_email_name, $from_email_address, $email_subject, $headers){
        $this->message['subject'] = $email_subject;        
        $this->message['from_email'] = $from_email_address;
        $this->message['from_name'] = $from_email_name;
        if ($headers){
            $this->message['headers'] = (!is_array($headers) ? [$headers]: $headers);
        }
        $this->message['to'] = [
                    [
                        'email' => $to_email_address,
                        'name' => $to_name,
                        'type' => 'to',
                    ]
                ];
        return $this->messages->send($this->message);
    }
    
}


