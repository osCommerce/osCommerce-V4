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


use backend\models\EP\Provider\DatasourceInterface;

class JobDatasource extends Job
{
    public function canConfigureExport()
    {
        return false;
    }

    public function getFileSystemName()
    {
        if ( strpos($this->file_name, 'php://')===0 ) return $this->file_name;
        $directory = $this->getDirectory();
        $ep_files_dir = $directory->filesRoot();
        return $ep_files_dir.(empty($this->file_name_internal)?$this->file_name:$this->file_name_internal);
    }
    
    public function canConfigureImport()
    {
        $can = false;
        try {
            $job = $this->getJobInstance();
            if ( method_exists($job,'importOptions') ) {
                $can = !empty($job->importOptions());
            }
        } catch (\Exception $ex) {
          \Yii::warning(" #### " . print_r($ex->getMessage(), true), 'TLDEBUG');
        }
        return $can;
    }

    public function canRun()
    {
        $this->checkIdle();
        if ($this->job_state == self::PROCESS_STATE_CONFIGURED || $this->job_state == self::PROCESS_STATE_IDLE){
            return true;
        }
        return false;
    }

    /**
     * Timeout for job restart
     *
     * @return int
     */
    public function maximumHangTimeMinutes()
    {
        return 12*60;
    }

    /**
     * Check job too long running.
     * If last_cron_run time touched time more then maximumHangTimeMinutes and in running state
     *
     * @return bool
     */
    public function isHangJob()
    {
        if ($activityState = $this->jobActivityState()){
            if ( in_array($activityState['job_state'], [Job::PROCESS_STATE_IN_PROGRESS, Job::PROCESS_STATE_IDLE]) ){
                $dbTime = strtotime($activityState['db_time']);
                $lastPingSeconds = $dbTime - strtotime($activityState['last_cron_run']);
                if ( $lastPingSeconds>=$this->maximumHangTimeMinutes()*60 ){
                    \Yii::info(
                        'Hang job #'.$this->job_id.' '.$this->file_name.
                        " state: {$activityState['job_state']};".
                        " last_ping: {$activityState['last_cron_run']};".
                        " allow minutes:".$this->maximumHangTimeMinutes().
                        "  (".date('Y-m-d H:i:s', strtotime('-'.$this->maximumHangTimeMinutes().'minutes',$dbTime))."); ",
                        'datasource');
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Get job state from db
     *
     * @return array|false
     */
    protected function jobActivityState()
    {
        return tep_db_fetch_array(tep_db_query(
            "select last_cron_run, now() as db_time, job_state from ".TABLE_EP_JOB." ".
            "WHERE job_id='".intval($this->job_id)."'"
        ));
    }

    public function checkIdle(){
        
        $idle = tep_db_fetch_array(tep_db_query(
            "select (MINUTE(TIMEDIFF(last_cron_run, now()))) as minutes from ".TABLE_EP_JOB." ".
            "WHERE job_id='".intval($this->job_id)."' and job_state='in_progress'"
        ));
       
        if (($idle['minutes']??null) > 10){
            $this->job_state = self::PROCESS_STATE_IDLE;
        }
        return;
    }

    public function canRunInBrowser()
    {
        $can = false;
        try {
            $job = $this->getJobInstance();
            if ( method_exists($job,'allowRunInPopup') ) {
                $can = $job->allowRunInPopup();
            }
        }catch (\Exception $ex){}
        return $can;
    }

    public function runASAP()
    {
        $this->run_frequency = 1;
        tep_db_query(
            "UPDATE ".TABLE_EP_JOB." ".
            "SET run_frequency=1 ".
            "WHERE job_id='".intval($this->job_id)."'"
        );
    }
    
    public function getProviders(){
        return new Providers();
    }
    
    protected function getDataSourceByName(Directory $directory){
        return DataSources::getByName($directory->directory);
    }

    protected function getJobInstance()
    {
        $directory = Directory::loadById($this->directory_id);
        if ( !is_object($directory) ) {
            throw new Exception('Not found job directory.');
        }
        $datasource = $this->getDataSourceByName($directory);
        if ( !is_object($datasource) ) {
            throw new Exception('Not found job datasource object.');
        }

        $datasourceProviderConfig = $datasource->getJobConfig();
        $datasourceProviderConfig['workingDirectory'] = $directory->filesRoot();
        $datasourceProviderConfig['directoryId'] = $this->directory_id;
        if ( is_array($this->job_configure) && !empty($this->job_configure) ) {
            $datasourceProviderConfig['job_configure'] = $this->job_configure;
        }

        $providers = $this->getProviders();

        return $providers->getProviderInstance($this->job_provider, $datasourceProviderConfig);

    }

    public function run(Messages $messages)
    {
        try {
            $providerObj = $this->getJobInstance();
            if (property_exists($providerObj, 'job_id')){
                $providerObj->job_id = $this->job_id;
            }
        }catch (Exception $ex){
            $messages->info($ex->getMessage().' Exit job.');
        }

        $messages->command('start');

        try{

            if ( $providerObj instanceof DatasourceInterface ) {

                $messages->progress(0);

                $started = time();
                $idlePing = $started;
                $rowCounter = 0;
                $progressRowInform = 100;
                $lastInfoSayTime = $started;
                $lastProgress = 0;
                set_time_limit(0);

                $providerObj->prepareProcess($messages);

                while ($providerObj->processRow( $messages)) {
                    echo '.';

                    $rowCounter++;
                    $currentTime = time();
                    $percentProgress = $providerObj->getProgress();
                    if ( ((int)$percentProgress-$lastProgress)>1 || ($rowCounter % $progressRowInform)==0 || ($currentTime-$lastInfoSayTime)>60 ) {
                        $lastProgress = (int)$percentProgress;
                        if ( $percentProgress==0 ) {
                            $secondsForJob = round(($currentTime - $started) * 100 / 0.0001);
                        }else{
                            $secondsForJob = round(($currentTime - $started) * 100 / $percentProgress);
                        }
                        $timeLeft = 'Time left: '.gmdate('H:i:s',max(0,$secondsForJob - ($currentTime-$started)) );
                        if ( $currentTime!=$started ) {
                            $timeLeft .= ' ' . number_format($rowCounter / ($currentTime - $started), 1, '.', '') . ' Lines per second';
                        }
                        if ( $this->isAlive()===false ) {
                            // job removed;
                            // hmm.. postprocess or not?
                            echo "\nJob removed. Exit\n";
                            break;
                        }

                        $messages->progress($percentProgress, $timeLeft);

                        $idlePing = $currentTime;

                        set_time_limit(0);
                        $lastInfoSayTime = $currentTime;
                    }elseif( $this->job_id && $currentTime-$idlePing>60 ){
                        // workaround for idle state
                        tep_db_perform(TABLE_EP_JOB,array(
                            'last_cron_run' => date('Y-m-d H:i:s',$currentTime),
                            'job_state' => Job::PROCESS_STATE_IN_PROGRESS,
                        ), 'update', "job_id='".$this->job_id."'");
                        $idlePing = $currentTime;
                    }
                }

                $messages->progress(100);

                $providerObj->postProcess($messages);

            }
        }catch (\Exception $ex){
            //$messages->info($ex->getMessage());
            \Yii::error("Job exception: ".$ex->getMessage()."\n".$ex->getTraceAsString(),'datasource');
            throw $ex;
        }
    }

    public function jobFinished()
    {
        parent::jobFinished();

        $this->moveToProcessed();
    }


    public function moveToProcessed()
    {
        $new_job_directory_id = $this->directory_id;

        if (!parent::moveToProcessed()){
            \Yii::error("Move ".$this->file_name." to processed failed - renew skip",'datasource');
            return;
        }

        if ( is_array($this->job_configure) && isset($this->job_configure['oneTimeJob']) && $this->job_configure['oneTimeJob']===true ){
            // on time job
            return;
        }
        if ( !$this->isAlive() ) {
            \Yii::error("Move ".$this->file_name." to processed failed - current job not in db",'datasource');
            return;
        }

        $data_array = array(
            'directory_id' => $new_job_directory_id,
            'direction' => $this->direction,
            'file_name' => $this->file_name,
            'file_time' => 0,
            'file_size' => 0,
            'job_state' => 'configured',
            'job_provider' => $this->job_provider,
            'run_frequency' => $this->run_frequency,
            'run_time' => $this->run_time,
            'job_configure' => (!empty($this->job_configure)?json_encode($this->job_configure):'null'),
            'last_cron_run' => 'now()', //$this->last_cron_run,
        );
        // {{ restore time if run by admin request - Immediately or and custom job time modification
        $directory = Directory::loadById($this->directory_id);
        if ( is_object($directory) && $directory->directory_type==Directory::TYPE_PROCESSED ){
            $directory = $directory->getParent();
        }
        if ( is_object($directory) ) {
            $jobConfig = $directory->findConfigByFileName($this->file_name);
            if ( is_array($jobConfig) ) {
                if ( array_key_exists('run_frequency', $jobConfig) ) {
                    $data_array['run_frequency'] = $jobConfig['run_frequency'];
                }
                if ( array_key_exists('run_time', $jobConfig) ) {
                    $data_array['run_time'] = $jobConfig['run_time'];
                }
            }
        }
        // }} restore time

        \Yii::info("[EP_CRON] Dir {$data_array['directory_id']} re-add processed job {$data_array['file_name']}", 'datasource');
        $this_job_scheduled_count = \Yii::$app->getDb()->createCommand(
            "SELECT COUNT(*) ".
            "FROM ".TABLE_EP_JOB." ".
            "WHERE directory_id=:dir_id AND file_name=:job_name",
            [':dir_id' => (int)$data_array['directory_id'], ':job_name'=>(string)$data_array['file_name']]
        )->queryScalar();
        if ( is_numeric($this_job_scheduled_count) && $this_job_scheduled_count>0 ){
            \Yii::info("[EP_CRON] [CRITICAL] Job {$data_array['directory_id']} {$data_array['file_name']} already exist", 'datasource');
        }else{
            tep_db_perform(TABLE_EP_JOB, $data_array);
        }

    }

}