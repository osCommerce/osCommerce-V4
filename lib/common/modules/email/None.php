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

class None implements MailerInterface {

    private $mailer;
    /** @prop \Swift_Message */
    private $message;

    public function __construct() {
        
        if (!$this->ready()) {
            return false;
        }
        /**/
        $transport = (new \Swift_NullTransport());
        //$transport = (new \Swift_SendmailTransport());
        //$transport = (new \Swift_SmtpTransport());
        $this->mailer = new \Swift_Mailer($transport);
        $this->message = $this->mailer->createMessage();

        //$this->message = new \Swift_Message();
    }

    public function ready() {
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

    public function send($to_name, $to_email_address, $from_email_name, $from_email_address, $email_subject, $headers) {
        $this->message
            ->setSubject($email_subject)
            ->setFrom([$from_email_address => $from_email_name])
            ->setTo([trim($to_email_address, '<> ') => $to_name])
            //->setBody('test')
            ;
        if (is_array($headers)) {
            $messageHeaders = $this->message->getHeaders();
            foreach ($headers as $key => $value) {
                $messageHeaders->addTextHeader($key, $value);
            }
        }

        $path = Yii::getAlias('@runtime/mail');
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        $file = $path . '/' . $this->generateMessageFileName($to_email_address. ' ' . $email_subject);

        file_put_contents($file, $this->message->toString());
        chmod($file, 0646);

        return true;//$this->mailer->send($this->message);
    }
    
    public function generateMessageFileName($subj = '')
    {
        $time = microtime(true);
        if (!empty($subj)) {
          $subj = preg_replace('/[^0-9A-Za-z]/', '_', $subj) . '-';
        } else {
          $subj = '';
        }

        return date('Ymd-His-', $time) . sprintf('%04d', (int) (($time - (int) $time) * 10000)) . '-' . $subj. sprintf('%04d', mt_rand(0, 10000)) . '.eml';
    }

}
