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

class Cron
{
    static public function init()
    {
        global $languages_id;
        $languages_id = \common\classes\language::defaultId();

        $configuration_query = tep_db_query('select configuration_key as cfgKey, configuration_value as cfgValue from ' . TABLE_PLATFORMS_CONFIGURATION . ' where platform_id = "0" and configuration_key like "%\_EXTENSION\_%"');
        while ($configuration = tep_db_fetch_array($configuration_query)) {
            if (!defined($configuration['cfgKey'])) {
                define($configuration['cfgKey'], $configuration['cfgValue']);
            }
        }

    }

    static public function runExport()
    {
        self::init();

        // find cronned export jobs
        $get_job_r = tep_db_query(
            "SELECT ej.job_id ".
            "FROM ".TABLE_EP_JOB." ej ".
            "  INNER JOIN ".TABLE_EP_DIRECTORIES." ed ON ed.directory_id = ej.directory_id ".
            "WHERE ed.cron_enabled=1 AND ed.directory_type='export' ".
            " AND ej.run_frequency>=0 "
        );
        if ( tep_db_num_rows($get_job_r)>0 ) {
            while($get_job = tep_db_fetch_array($get_job_r)){
                $job_id = $get_job['job_id'];
                $job = Job::loadById($job_id);

                $now = strtotime('now');  
                
                $run_job_now = false;
                if ( $job->run_frequency==0 ) {

                    $need_run_mktime = strtotime($job->run_time);
                    $allow_frame_sec = 5*60; 

                    if ( !empty($job->last_cron_run) ) {
                        $runned_today = date('Ymd',strtotime($job->last_cron_run))==date('Ymd',$now);
                    }else{
                        $runned_today = false;
                    }

                    $exact_time = date('dHi',$need_run_mktime)==date('dHi',$now);
                    $missed_run = ($now>$need_run_mktime) && ($now<($need_run_mktime+$allow_frame_sec));

                    $run_job_now = !$runned_today && ($exact_time || $missed_run);
                }else{
                    $run_job_now = empty($job->last_cron_run) || strtotime('- '.$job->run_frequency.' minutes')>=strtotime($job->last_cron_run);
                }
                if ( $run_job_now ) {
                    tep_db_query("UPDATE ".TABLE_EP_JOB." SET last_cron_run='".date('Y-m-d H:i:s',$now)."' WHERE job_id='".$job->job_id."'");
                    
                    $messages = new \backend\models\EP\Messages([
                        'job_id' => $job_id,
                        'output' => 'console',
                    ]);

                    try{
                        echo "#{$job->job_id} {$job->file_name}\n";
                        $job->run($messages);
                    }catch(\Exception $ex){
                        $messages->info($ex->getMessage()); 
                    }
                }
                
            }
        }
    }

    static public function runImport()
    {
        self::init();

        $autoImportRoot = Directory::loadById(4);
        $autoImportRoot->process(true);

        // find cronned import jobs
        $get_job_r = tep_db_query(
            "SELECT ej.job_id ".
            "FROM ".TABLE_EP_JOB." ej ".
            "  INNER JOIN ".TABLE_EP_DIRECTORIES." ed ON ed.directory_id = ej.directory_id ".
            "WHERE ed.cron_enabled=1 AND ed.directory_type IN('import','import_zip','import_sheets') AND ej.job_state='configured' ".
            " AND ej.run_frequency>=0 "
        );
        if ( tep_db_num_rows($get_job_r)>0 ) {
            while($get_job = tep_db_fetch_array($get_job_r)){
                $job_id = $get_job['job_id'];
                $job = Job::loadById($job_id);

                if ( $job->run_frequency==0 || $job->run_frequency==1 ) {
                    // once run
                    // if ( $job->last_cron_run ) continue;
                }

                $now = strtotime('now');  

                $run_job_now = false;
                if ( $job->run_frequency==0 ) {

                    $need_run_mktime = strtotime($job->run_time);
                    $allow_frame_sec = 5*60; 

                    if ( !empty($job->last_cron_run) ) {
                        $runned_today = date('Ymd',strtotime($job->last_cron_run))==date('Ymd',$now);
                    }else{
                        $runned_today = false;
                    }

                    $exact_time = date('dHi',$need_run_mktime)==date('dHi',$now);
                    $missed_run = ($now>$need_run_mktime) && ($now<($need_run_mktime+$allow_frame_sec));

                    $run_job_now = !$runned_today && ($exact_time || $missed_run);
                }else{
                    $run_job_now = empty($job->last_cron_run) || strtotime('- '.$job->run_frequency.' minutes')>=strtotime($job->last_cron_run);
                }

                if ( $run_job_now ) {
                    tep_db_query("UPDATE ".TABLE_EP_JOB." SET last_cron_run='".date('Y-m-d H:i:s',$now)."' WHERE job_id='".$job->job_id."'");

                    $messages = new \backend\models\EP\Messages([
                        'job_id' => $job_id,
                        'output' => 'console',
                    ]);
                    try{
                        echo "#{$job->job_id} {$job->file_name}\n";
                        $messages->info('Cron start run at '.\common\helpers\Date::formatDateTime(date('Y-m-d H:i:s')));
                        $job->run($messages);
                        // TODO: this same as JobDatasource::jobFinished - do refactor
                        $job->job_state = 'processed';
                        $job->last_cron_run = date('Y-m-d H:i:s',$now);
                        tep_db_query(
                            "UPDATE ".TABLE_EP_JOB." ".
                            "SET job_state='processed', last_cron_run='".$job->last_cron_run."' ".
                            "WHERE job_id='".$job->job_id."'"
                        );
                        $job->moveToProcessed();
                        //

                    }catch(\Exception $ex){
                        $messages->info($ex->getMessage());
                    }
                    $messages->info('Cron finished at '.\common\helpers\Date::formatDateTime(date('Y-m-d H:i:s')));
                }
            }
        }
    }

    static public function runDatasource($runNow = false)
    {
        self::init();

        $autoImportRoot = Directory::loadById(5);
        $autoImportRoot->process(true);

        // find cronned import jobs
        $get_job_r = tep_db_query(
            "SELECT ej.job_id ".
            "FROM ".TABLE_EP_JOB." ej ".
            "  INNER JOIN ".TABLE_EP_DIRECTORIES." ed ON ed.directory_id = ej.directory_id ".
            "WHERE ed.cron_enabled=1 AND ed.directory_type='datasource' AND ej.job_state='configured' ".
            " AND ej.run_frequency>=0 "
        );
        if ( tep_db_num_rows($get_job_r)>0 ) {
            \Yii::info("[EP_CRON] job poll count ".tep_db_num_rows($get_job_r), 'datasource');
            while($get_job = tep_db_fetch_array($get_job_r)){
                $job_id = $get_job['job_id'];
                $job = Job::loadById($job_id);
                if ( $job ) {
                    \Yii::info("[EP_CRON] job {$job_id} {$job->file_name} {$job->job_state}", 'datasource');
                }else{
                    \Yii::info("[EP_CRON] job {$job_id} ?? ".var_export($job), 'datasource');
                }
                if ( $job==false || $job->job_state!='configured' ) {
                    continue;
                }

                if ( $job->run_frequency==0 || $job->run_frequency==1 ) {
                    // once run
                    // if ( $job->last_cron_run ) continue;
                }

                $now = strtotime('now');

                $run_job_now = false;
                if ( $runNow ) {
                    $run_job_now = true;
                }else
                if ( $job->run_frequency==0 ) {

                    $need_run_mktime = strtotime($job->run_time);
                    $allow_frame_sec = 5*60;

                    if ( !empty($job->last_cron_run) ) {
                        $runned_today = date('Ymd',strtotime($job->last_cron_run))==date('Ymd',$now);
                    }else{
                        $runned_today = false;
                    }

                    $exact_time = date('dHi',$need_run_mktime)==date('dHi',$now);
                    $missed_run = ($now>$need_run_mktime) && ($now<($need_run_mktime+$allow_frame_sec));

                    $run_job_now = !$runned_today && ($exact_time || $missed_run);
                }else{
                    $run_job_now = empty($job->last_cron_run) || strtotime('- '.$job->run_frequency.' minutes')>=strtotime($job->last_cron_run);
                }

                if ( $run_job_now ) {
                    tep_db_query(
                        "UPDATE ".TABLE_EP_JOB." ".
                        "SET job_state = '".Job::PROCESS_STATE_IN_PROGRESS."' ".
                        "WHERE job_id='".$job->job_id."' AND job_state = '".Job::PROCESS_STATE_CONFIGURED."' "
                    );

                    if (!tep_db_affected_rows()){
                        $switch_fail_data = tep_db_fetch_array(tep_db_query("SELECT * FROM ".TABLE_EP_JOB." WHERE job_id='".$job->job_id."'"));
                        \Yii::error('[!!!!] Can\'t switch job #'.$job->job_id.' to progress state '.var_export($switch_fail_data, true),'datasource');
                        continue;
                    }

                    $job->setJobStartTime($now);

                    tep_db_query("UPDATE ".TABLE_EP_JOB." SET last_cron_run='".date('Y-m-d H:i:s',$now)."' WHERE job_id='".$job->job_id."'");

                    $messages = new \backend\models\EP\Messages([
                        'job_id' => $job->job_id,
                        'output' => 'console',
                    ]);
                    try{
                        echo "#{$job->job_id} {$job->file_name}\n";
                        $messages->info('Cron start run at '.\common\helpers\Date::formatDateTime(date('Y-m-d H:i:s')));
                        $job->run($messages);
                    }catch(\Exception $ex){
                        $messages->info($ex->getMessage());
                        (new \backend\models\AdminNotifier)->addNotification($messages, $ex->getMessage(), 'danger');
                        echo $ex->getFile().':'.$ex->getLine()."\n";
                    }

                    $job->jobFinished();

                    $messages->info('Cron finished at '.\common\helpers\Date::formatDateTime(date('Y-m-d H:i:s')));

                }
            }
        }
    }
    
}
