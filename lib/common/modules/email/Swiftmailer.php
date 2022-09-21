<?php
/**
 * This file is part of osCommerce ecommerce platform.
 * osCommerce the ecommerce
 * 
 * @link https://www.oscommerce.com
 * @copyright Copyright (c) 2000-2022 osCommerce LTD
 * 
 * Released under the GNU General Public License
 * For the full copyright and license information, please view the LICENSE.TXT file that was distributed with this source code.
 */

namespace common\modules\email;

use Yii;

class Swiftmailer implements MailerInterface {

    private $mailer;
    private $message;

    public function __construct() {
        
        if (!$this->ready()) {
            return false;
        }
        $transport = (new \Swift_SmtpTransport(SMTP_HOST, SMTP_PORT))
            ->setUsername(SMTP_USERNAME)
            ->setPassword(SMTP_PASSWORD)
            //->registerPlugin($plugin)
            //->setAuthMode($mode)
            //->setExtensionHandlers($handlers)
            //->setAddressEncoder($addressEncoder)
            //->setPipelining($enabled)
            //->setSourceIp($source)
            //->setStreamOptions($options)
          ;
        if (defined('SMTP_ENCRYPTION') && !empty(SMTP_ENCRYPTION)) {
            $transport->setEncryption(SMTP_ENCRYPTION);
        }
        $this->mailer = new \Swift_Mailer($transport);
        $this->message = $this->mailer->createMessage();
        //$this->message = new \Swift_Message();
    }

    public function ready() {
        if (!defined('SMTP_HOST')) {
            return false;
        }
        if (!defined('SMTP_PORT')) {
            return false;
        }
        if (!defined('SMTP_USERNAME')) {
            return false;
        }
        if (!defined('SMTP_PASSWORD')) {
            return false;
        }
        if (empty(SMTP_HOST)) {
            return false;
        }
        if (empty(SMTP_PORT)) {
            return false;
        }
        if (empty(SMTP_USERNAME)) {
            return false;
        }
        if (empty(SMTP_PASSWORD)) {
            return false;
        }
        return true;
    }

    public function add_html($email_text, $text) {
        $this->message->setBody($email_text, 'text/html');
        $this->message->addPart($text, 'text/plain');
    }

    public function add_text($text) {
        $this->message->setBody($text);
    }

    public function add_attachment($file, $name) {
        //$this->message->attachContent($file, ['fileName' => $name, 'contentType' => mime_content_type($name)]);
        $attachment = new \Swift_Attachment($file, $name);
        $this->message->attach($attachment);
    }

    public function build_message() {
        
    }

    public function addBcc($bcc) {
        $this->message->setBcc($bcc);
    }

    protected function parseEmails($email_addresses_string)
    {
        $mail_list = [];
        foreach (preg_split('/,(?=([^\"]*\"[^\"]*\")*[^\"]*$)/', $email_addresses_string, -1, PREG_SPLIT_NO_EMPTY) as $split_mail){
            if (preg_match('/^((.*?)\s+)?([^\s]+)$/', trim($split_mail), $_split_mail)) {
                $_toName = trim($_split_mail[1], '" ');
                $mail_list[trim($_split_mail[3], '< >')] = $_toName?$_toName:null;
            }
        }
        return $mail_list;
    }

    public function send($to_name, $to_email_address, $from_email_name, $from_email_address, $email_subject, $headers) {
        $this->message
            ->setSubject($email_subject)
            ->setFrom([$from_email_address => $from_email_name])
            //->setTo([$to_email_address => $to_name])
            ;

        $mailTo = [];
        if ( empty($to_name) ){
            $mailTo = $this->parseEmails($to_email_address);
        }else{
            $mailTo[trim($to_email_address, '< >')] = $to_name;
        }
        $this->message->setTo($mailTo);

        if ( !empty($headers) && !is_array($headers) ){
            $headers_raw = array_map('trim',preg_split("/\n/",$headers,-1,PREG_SPLIT_NO_EMPTY));
            $headers = [];
            foreach ($headers_raw as $headers_row){
                list($key, $val) = explode(":",$headers_row,2);
                $headers[trim($key)] = trim($val);
            }
        }

        if (is_array($headers)) {
            $messageHeaders = $this->message->getHeaders();
            foreach ($headers as $key => $value) {
                if ( strtolower($key)=='cc' ){
                    $this->message->setCc($this->parseEmails($value));
                }else {
                    $messageHeaders->addTextHeader($key, $value);
                }
            }
        }
        return $this->mailer->send($this->message);
    }

}
