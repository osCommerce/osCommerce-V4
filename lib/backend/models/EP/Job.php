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

use Yii;
use backend\models\EP\Providers;

class Job extends \yii\base\BaseObject
{
    public $job_id;
    public $directory_id;
    public $file_name;
    public $file_name_internal;
    public $file_time;
    public $file_size = false; // for check upload complete -- auto import
    public $direction = 'import';
    public $run_frequency = -1;
    public $run_time = '00:00';
    public $last_cron_run;
    public $process_progress = 0;
    public $job_provider;
    public $job_state;
    public $job_configure;

    const STATE_UPLOAD_IN_PROGRESS = 'upload';
    const STATE_UPLOADED = 'uploaded';
    const STATE_NOT_CONFIGURED = 'not_configured';
    const STATE_CONFIGURED = 'configured';
    const STATE_PROCESSED = 'processed';

    const PROCESS_STATE_PENDING = 'pending';
    const PROCESS_STATE_CONFIGURED = 'configured';
    const PROCESS_STATE_IDLE = 'idle';
    const PROCESS_STATE_IN_PROGRESS = 'in_progress';
    const PROCESS_STATE_COMPLETE = 'complete';

    protected $job_start_time;

    protected $providers;

    /**
     *
     * @param type $id
     * @return boolean|\self
     */
    static public function loadById($id)
    {
        if ( empty($id) ) return false;
        $job_lookup_r = tep_db_query("SELECT * FROM " . TABLE_EP_JOB . " WHERE job_id='" . (int)$id . "' ");
        if (tep_db_num_rows($job_lookup_r) > 0) {
            $job_record = tep_db_fetch_array($job_lookup_r);
            if (!empty($job_record['job_configure'])) {
                $job_record['job_configure'] = json_decode($job_record['job_configure'], true);
            }
            if (!is_array($job_record['job_configure'])) $job_record['job_configure'] = array();
            if ($job_record['direction']=='datasource') {
                $datasource = null;
                if(!empty($job_record['file_name'])){
                    $path = explode("_", $job_record['file_name']);
                    array_pop($path);
                    $class = implode("\\", $path);
                    if (class_exists($class)){
                        $datasource = new $class($job_record);
                    }
                }
                if (!$datasource){
                    $datasource = new JobDatasource($job_record);
                }
                return $datasource;
            }elseif ($job_record['direction']=='import_zip'){
                return new JobZipFile($job_record);
            }elseif ($job_record['direction']=='import_sheets'){
                return new JobSheetsFile($job_record);
            } else {
                return new JobFile($job_record);
            }
            //return new self($job_record);
        }
        return false;
    }

    public function delete()
    {
        tep_db_query("DELETE FROM " . TABLE_EP_JOB . " WHERE job_id='" . $this->job_id . "'");
        tep_db_query("DELETE FROM " . TABLE_EP_LOG_MESSAGES . " WHERE job_id='" . $this->job_id . "'");
        return true;
    }

    public function saveConfigureState()
    {
        if ( !$this->job_id ) return;
        tep_db_query(
            "UPDATE " . TABLE_EP_JOB . " " .
            "SET job_state='" . tep_db_input($this->job_state) . "', job_provider='" . tep_db_input($this->job_provider) . "', " .
            " job_configure='" . tep_db_input(json_encode($this->job_configure)) . "' " .
            "WHERE job_id='" . $this->job_id . "' "
        );
    }

    public function setProviders(Providers $providers)
    {
        $this->providers = $providers;
    }

    public function getProviders()
    {
        if (!is_object($this->providers)){
            $this->providers = new Providers();
        }
        return $this->providers;
    }

    /**
     * @return Directory
     */
    public function getDirectory()
    {
        return Directory::findById($this->directory_id);
    }

    public function checkRequirements()
    {

    }

    public function canRemove()
    {
        return true;
    }

    public function canSetupRunFrequency()
    {
        if ($this->job_state == self::STATE_UPLOAD_IN_PROGRESS) return false;
        $directory = $this->getDirectory();
        return $directory->cron_enabled && (in_array($directory->directory_type, ['import','export','datasource']) );
    }

    public function canRun()
    {
        $directory = $this->getDirectory();
        if ($directory->cron_enabled) {
            return false;
        }
        return !($this->job_state == self::STATE_NOT_CONFIGURED || $directory->directory_type != 'import');
    }

    public function canConfigureExport()
    {
        $directory = $this->getDirectory();
        return $directory->directory_type == 'export' && in_array($this->job_state, [self::STATE_CONFIGURED, self::STATE_NOT_CONFIGURED, self::STATE_PROCESSED]);
    }

    public function canConfigureImport()
    {
        $directory = $this->getDirectory();
        return $directory->directory_type == 'import' && in_array($this->job_state, [self::STATE_CONFIGURED, self::STATE_NOT_CONFIGURED, self::STATE_UPLOADED, self::STATE_PROCESSED]);
    }

    public function run(Messages $messages)
    {

    }

    /**
     * @param mixed $job_start_time
     */
    public function setJobStartTime($job_start_time)
    {
        $this->job_start_time = $job_start_time;
    }

    public function jobFinished()
    {
        if ( $this->job_id ) {
            $this->job_state = 'processed';
            $this->last_cron_run = date('Y-m-d H:i:s',$this->job_start_time);
            tep_db_query(
                "UPDATE ".TABLE_EP_JOB." ".
                "SET job_state='processed', last_cron_run='".$this->last_cron_run."' ".
                "WHERE job_id='".$this->job_id."'"
            );
            $directory = $this->getDirectory();
            if ( $directory->directory_type==Directory::TYPE_IMPORT && $directory->cron_enabled ) {
                $this->moveToProcessed();
            }
        }
    }

    public function isAlive()
    {
        if ( empty($this->job_id) ) return true;
        $aliveStatus = null;

        $check_table_r = Yii::$app->get('db')->createCommand(
            "SELECT COUNT(*) AS c FROM ".TABLE_EP_JOB." WHERE job_id='" . $this->job_id . "'"
        )->queryAll();
        if ( count($check_table_r)>0 ) {
            $check_table = reset($check_table_r);
            $aliveStatus = $check_table['c']>0;
        }
        return $aliveStatus;
    }

    public function haveMessages()
    {
        $haveMessages = false;
        if ($this->job_id) {
            $check = tep_db_fetch_array(tep_db_query(
                "SELECT COUNT(*) AS c " .
                "FROM " . TABLE_EP_LOG_MESSAGES . " " .
                "WHERE job_id='" . $this->job_id . "'"
            ));
            $haveMessages = $check['c'] > 0;
        }
        return $haveMessages;
    }

    public function moveToProcessed()
    {
        $moveStatus = true;

        $directory = $this->getDirectory();

        $processedDirectory = $directory->getProcessedDirectory();

        if ( !$processedDirectory ) return false;

        $this->job_state = self::STATE_PROCESSED;
        if ( intval($this->directory_id) == intval($processedDirectory->directory_id) ) {
            $moveStatus = false;
        }
        $this->directory_id = $processedDirectory->directory_id;

        try {
            Yii::$app->get('db')->createCommand(
                "UPDATE " . TABLE_EP_JOB . " " .
                "SET job_state='" . tep_db_input($this->job_state) . "', directory_id='" . intval($this->directory_id) . "' " .
                "WHERE job_id='" . intval($this->job_id) . "'"
            )->execute();
        }catch (\yii\db\Exception $ex){
            Yii::error("Fail move job to processed : ".$ex->getMessage(),'datasource');
            $moveStatus = false;
        }

        return $moveStatus;
    }

}