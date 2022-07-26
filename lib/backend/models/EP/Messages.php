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

namespace backend\models\EP;

class Messages extends \yii\base\BaseObject implements \backend\models\NotificationInterface {

    public $job_id;
    public $output = 'www';
    public $log_message_id;
    
    public function __construct($config = array()) {
        parent::__construct($config);
        $this->setEpFileId( $this->job_id );
    }
    
    public function setEpFileId($id)
    {
        $this->job_id = $id;
        if ( $this->job_id ) {
            tep_db_query("DELETE FROM ".TABLE_EP_LOG_MESSAGES." WHERE job_id='".(int)$this->job_id."'");
        }
    }
    
    public function info($text){
        if ( $this->output=='null' ) {
            return;
        }
        if ( $this->output=='www' ) {
            echo '<script>window.parent.uploader(\'message\', '.json_encode($text).')</script>';
            echo str_repeat(' ',2048); echo "\n";
            ob_flush();
            flush();
        }elseif( $this->output=='console' ){
            echo "$text\n";
        }
        if ( $this->job_id ) {
            tep_db_perform(TABLE_EP_LOG_MESSAGES, array(
                'job_id' => $this->job_id,
                'message_time' => 'now()',
                'message_text' => $text,
            ));
            $this->log_message_id = tep_db_insert_id();
            tep_db_perform(TABLE_EP_JOB,array(
                'last_cron_run' => date('Y-m-d H:i:s',strtotime('now')),
            ), 'update', "job_id='".$this->job_id."' AND job_state = '".Job::PROCESS_STATE_IN_PROGRESS."'");
        }
    }

    public function progress($percentDone, $timeString='')
    {
        if ( $this->output=='null' ) {
            return;
        }
        if ( $this->output=='www' ) {
            if( empty($timeString) ) {
                echo '<script>window.parent.uploader(\'progress\', ' . json_encode(round($percentDone)) . ')</script>';
            }else{
                echo '<script>window.parent.uploader(\'progress\', ' . json_encode(round($percentDone)) . ', '.json_encode($timeString).')</script>';
            }
            echo str_repeat(' ',1024*8); echo "\n";
            ob_flush();
            flush();
        }elseif($this->output=='console'){
            if( empty($timeString) ) {
                echo " =>".round($percentDone). "%\n";
            }else{
                echo " =>".round($percentDone). "% {$timeString}\n";
            }
        }
        if ( $this->job_id ) {
            tep_db_perform(TABLE_EP_JOB,array(
                'process_progress' => round($percentDone),
                'last_cron_run' => date('Y-m-d H:i:s',strtotime('now')),
                'job_state' => Job::PROCESS_STATE_IN_PROGRESS,
            ), 'update', "job_id='".$this->job_id."'");
        }
    }

    public function command($command)
    {
        if ( $this->output=='www' ) {
            if (func_num_args()>1){
                $arg = func_get_args();
                array_shift($arg);
                echo '<script>window.parent.uploader(\'' . $command . '\',' .json_encode($arg). ')</script>';
            }else {
                echo '<script>window.parent.uploader(\'' . $command . '\')</script>';
            }
            echo str_repeat(' ',2048); echo "\n";
            ob_flush();
            flush();
        }
    }

    public function getMessages()
    {
        $messages = [];
        if ( $this->job_id ) {
            $get_messages_r = tep_db_query(
                "SELECT `message_text` ".
                "FROM " . TABLE_EP_LOG_MESSAGES . " ".
                "WHERE job_id='" . $this->job_id . "' ".
                "ORDER BY ep_log_message_id"
            );
            if ( tep_db_num_rows($get_messages_r)>0 ) {
                while($get_message = tep_db_fetch_array($get_messages_r) ){
                    $messages[] = $get_message['message_text'];
                }
            }
        }
        return $messages;
    }
    
    public function prepareAdminMessage($message = null){
        return base64_encode(serialize([
            'ep_log_message_id' => $this->log_message_id,
        ]));
    }
    
    public function getAdminMessage($message = null){
        $_message = unserialize(base64_decode($message));
        if (is_array($_message)){
            
            if (isset($_message['ep_log_message_id'])){
                $log_message = tep_db_fetch_array(tep_db_query(
                    "SELECT `message_text`, job_id ".
                    "FROM " . TABLE_EP_LOG_MESSAGES . " ".
                    "WHERE ep_log_message_id='" . (int)$_message['ep_log_message_id'] . "' "
                ));
                if ($log_message){
                    $job = Job::loadById($log_message['job_id']);
                    return $job->job_provider . ", {$log_message['message_text']}" ;
                }

            }
        }
        return '';
    }
    
}