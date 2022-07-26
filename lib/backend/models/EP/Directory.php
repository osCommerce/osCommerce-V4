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
use yii\base\BaseObject;
use yii\helpers\FileHelper;


class Directory extends BaseObject
{
    const TYPE_IMPORT = 'import';
    const TYPE_EXPORT = 'export';
    const TYPE_IMAGES = 'images';
    const TYPE_DATASOURCE = 'datasource';
    const TYPE_PROCESSED = 'processed';
    
    public $directory_id;
    public $parent_id;
    public $removable;
    public $cron_enabled;
    public $directory_type = 'import';
    public $name;
    public $directory;
    public $directory_config;

    protected $parentDirectory;
    
    function __construct($config) {
        parent::__construct($config);
        $language_id = isset($_SESSION['language_id'])?$_SESSION['language_id']:\common\classes\language::defaultId();
        $name_value = \common\helpers\Translation::getTranslationValue($this->name, 'admin/easypopulate', $language_id);
        $this->name = $name_value;
        if ( is_string($this->directory_config) ) {
            $this->directory_config = json_decode($this->directory_config,true);
        }
        if ( !is_array($this->directory_config) ) $this->directory_config = array();
    }

    public function canConfigure()
    {
        return $this->cron_enabled && in_array($this->directory_type, [self::TYPE_IMPORT, self::TYPE_PROCESSED, self::TYPE_DATASOURCE]);
    }

    public function canConfigureDatasource()
    {
        return $this->cron_enabled && in_array($this->directory_type, [self::TYPE_DATASOURCE]);
    }

    public function canRemove()
    {
        return $this->parent_id!=0 && $this->removable && !empty($this->directory_id) && !in_array($this->directory_type, [self::TYPE_IMAGES, self::TYPE_PROCESSED]);
    }

    public function filesRoot($type = '')
    {
        if (empty($type)) $type = $this->directory_type;
        $filesRoot = '';
        $globalRoot = Yii::getAlias('@ep_files/');
        if ( $this->parent_id ) {
            $globalRoot = self::loadById($this->parent_id)->filesRoot($this->directory_type);
        }
        if ( $type==self::TYPE_PROCESSED ) {
            if ( $this->directory_type==self::TYPE_PROCESSED ) {
                $filesRoot = $globalRoot;
            }else {
                $filesRoot = $globalRoot . $this->directory . '/processed/';
            }
        }elseif ( $type==self::TYPE_IMAGES ) {
            $filesRoot = $globalRoot.$this->directory.'/images/';
        }else{
            $filesRoot = $globalRoot.$this->directory.'/';
        }
        if ( !is_dir($filesRoot) ) {
            try{
                \yii\helpers\FileHelper::createDirectory($filesRoot, 0777, true);
            }catch(\Exception $ex){
                
            }
        }else{
            @chmod($filesRoot,0777);
        }

        return $filesRoot;
    }

    static function loadById($id)
    {
        return new self(tep_db_fetch_array(tep_db_query("SELECT * FROM ".TABLE_EP_DIRECTORIES." WHERE directory_id='".$id."'")));
    }

    public function delete()
    {
        if ( $this->parent_id==5 ) {
            DataSources::remove($this->directory);
        }
        foreach($this->getSubdirectories(true) as $directory){
            $directory->delete();
        }
        $get_dir_job_r = tep_db_query("SELECT job_id FROM ".TABLE_EP_JOB." WHERE directory_id='".(int)$this->directory_id."'");
        if ( tep_db_num_rows($get_dir_job_r)>0 ) {
            while($_job = tep_db_fetch_array($get_dir_job_r)){
                $job = Job::loadById($_job['job_id']);
                $job->delete();
            }
        }
        try {
            FileHelper::removeDirectory($this->filesRoot());
        }catch (\Exception $ex){

        }
        tep_db_query("DELETE FROM ".TABLE_EP_DIRECTORIES." WHERE directory_id='".$this->directory_id."' AND removable=1");
        self::getAll(true);

        return true;
    }

    /**
     * @param bool $recursive
     * @return static[]
     */
    public function getSubdirectories($recursive=false)
    {
        $subdirectories = [];
        $get_db_link_r = tep_db_query(
            "SELECT * ".
            "FROM ".TABLE_EP_DIRECTORIES." ".
            "WHERE parent_id='".$this->directory_id."'"
        );
        if ( tep_db_num_rows($get_db_link_r)>0 ) {
            while( $dir_data = tep_db_fetch_array($get_db_link_r) ) {
                $subdirectory = new self($dir_data);
                $subdirectories[] = $subdirectory;
                if ( $recursive ) {
                    $subdirs = $subdirectory->getSubdirectories($recursive);
                    if ( is_array($subdirs) && count($subdirs)>0 ){
                        $subdirectories = array_merge($subdirectories, $subdirs);
                    }
                }
            }
        }
        return $subdirectories;
    }

    public function getProcessedDirectory()
    {
        foreach($this->getSubdirectories() as $subDir){
            if ( $subDir->directory_type==self::TYPE_PROCESSED ) {
                return $subDir;
            }
        }
        if ( $this->cron_enabled ) {
            $processedRoot = $this->filesRoot(self::TYPE_PROCESSED);
            if ( is_dir($processedRoot) ) {
                tep_db_perform(TABLE_EP_DIRECTORIES, array(
                    'removable' => 1,
                    'cron_enabled' => $this->cron_enabled,
                    'parent_id' => $this->directory_id,
                    'name' => '--',
                    'directory_type' => self::TYPE_PROCESSED,
                    'directory' => basename($processedRoot),
                ));
                $newDirectoryId = tep_db_insert_id();
                return Directory::findById($newDirectoryId);
            }
        }
        return false;
    }

    public function getParent()
    {
        if ( is_null($this->parentDirectory) ) {
            $this->parentDirectory = false;
        }
        if ( $this->parent_id ) {
            $this->parentDirectory = self::findById($this->parent_id);
        }
        return $this->parentDirectory;
    }

    public function applyDirectoryConfig()
    {
        if($this->directory_type==self::TYPE_PROCESSED ) {
            $this->cleanDir();
        }elseif($this->directory_type==self::TYPE_DATASOURCE){
            if ( $this->cron_enabled ) {
                foreach ($this->directory_config as $directory_config) {
                    if ($job = $this->findJobByFilename($directory_config['filename_pattern'])) {
                        $update_array = [];
                        $update_array['run_frequency'] = $directory_config['run_frequency'];
                        $update_array['run_time'] = $directory_config['run_time'];
                        $update_array['job_provider'] = $directory_config['job_provider'];
                        tep_db_perform(TABLE_EP_JOB, $update_array, 'update', "job_id='" . $job->job_id . "'");
                    } else {
                        $data_array = array(
                            'directory_id' => $this->directory_id,
                            'direction' => $this->directory_type,
                            'file_name' => $directory_config['filename_pattern'],
                            'file_time' => 0,
                            'file_size' => 0,
                            'job_state' => 'configured',
                            'job_provider' => $directory_config['job_provider'],
                            'run_frequency' => $directory_config['run_frequency'],
                            'run_time' => $directory_config['run_time'],
                        );
                        tep_db_perform(TABLE_EP_JOB, $data_array);
                    }
                }
            }
            $get_db_files_r = tep_db_query(
                "SELECT job_id, file_name " .
                "FROM " . TABLE_EP_JOB . " " .
                "WHERE directory_id='" . $this->directory_id . "' AND direction='" . $this->directory_type . "' "
            );
            if (tep_db_num_rows($get_db_files_r) > 0) {
                while ($db_file = tep_db_fetch_array($get_db_files_r)) {
                    if ($config = $this->findConfigByFileName($db_file['file_name'])) {
                        $update_array = [];
                        $update_array['run_frequency'] = $config['run_frequency'];
                        $update_array['run_time'] = $config['run_time'];
                        $update_array['job_provider'] = $config['job_provider'];
                        tep_db_perform(TABLE_EP_JOB, $update_array, 'update', "job_id='" . $db_file['job_id'] . "'");
                    }
                }
            }
        }elseif($this->directory_type==self::TYPE_IMPORT){
            if ( $this->cron_enabled ) {
                foreach ($this->directory_config as $directory_config) {
                    if ($directory_config['file_format'] == 'BrightPearl') {
                        if ($job = $this->findJobByFilename($directory_config['filename_pattern'])){
                            $update_array = [];
                            $update_array['run_frequency'] = $directory_config['run_frequency'];
                            $update_array['run_time'] = $directory_config['run_time'];
                            $update_array['job_provider'] = $directory_config['job_provider'];
                            tep_db_perform(TABLE_EP_JOB, $update_array, 'update', "job_id='" . $job->job_id . "'");
                        }else{
                            $data_array = array(
                                'directory_id' => $this->directory_id,
                                'direction' => $this->directory_type,
                                'file_name' => $directory_config['filename_pattern'],
                                'file_time' => 0,
                                'file_size' => 0,
                                'job_state' => 'configured',
                                'job_provider' => $directory_config['job_provider'],
                                'run_frequency' => $directory_config['run_frequency'],
                                'run_time' => $directory_config['run_time'],
                            );
                            tep_db_perform(TABLE_EP_JOB, $data_array);
                        }
                    }
                }
            }
            $get_db_files_r = tep_db_query(
                "SELECT job_id, file_name " .
                "FROM " . TABLE_EP_JOB . " " .
                "WHERE directory_id='" . $this->directory_id . "' AND direction='" . $this->directory_type . "' "
            );
            if (tep_db_num_rows($get_db_files_r) > 0) {
                while ($db_file = tep_db_fetch_array($get_db_files_r)) {
                    if ($config = $this->findConfigByFileName($db_file['file_name'])) {
                        $update_array = [];
                        $update_array['run_frequency'] = $config['run_frequency'];
                        $update_array['run_time'] = $config['run_time'];
                        $update_array['job_provider'] = $config['job_provider'];
                        tep_db_perform(TABLE_EP_JOB, $update_array, 'update', "job_id='" . $db_file['job_id'] . "'");
                    }
                }
            }
        }
    }

    public function findConfigByFileName($filename)
    {
        $configData = false;
        foreach( $this->directory_config as $directory_job_config ){
            if ( isset($directory_job_config['filename_pattern']) && fnmatch($directory_job_config['filename_pattern'],$filename,FNM_PERIOD) ) {
                $configData = $directory_job_config;
                break;
            }
        }
        return $configData;
    }

    public function isCronImportJobDirectory()
    {
        return ($this->directory_type==self::TYPE_IMPORT && $this->parent_id==4);
    }

    public function process($cronAsk=false)
    {
        if ( $this->directory_type==self::TYPE_IMPORT ) {
            $this->synchronizeFiles($cronAsk);

            foreach($this->getSubdirectories(true) as $checkDir) {
                if ( $checkDir->directory_type==self::TYPE_PROCESSED ) {
                    $checkDir->cleanDir();
                }
            }
        }elseif($this->directory_type==self::TYPE_DATASOURCE ) {
            foreach ($this->getSubdirectories(true) as $checkDir) {
                if ($checkDir->directory_type == self::TYPE_PROCESSED) {
                    $checkDir->cleanDir();
                } elseif ($checkDir->directory_type == self::TYPE_DATASOURCE) {
                    $checkDir->watchIdleHang();
                }
            }
        }elseif($this->directory_type==self::TYPE_PROCESSED ) {
            $this->cleanDir();
        }
    }

    public function watchIdleHang()
    {
        foreach ($this->getJobs() as $epJob){
            /**
             * @var $epJob JobDatasource
             */
            if ($epJob instanceof JobDatasource && $epJob->isHangJob()) {
                $epJob->moveToProcessed();
            }
        }
    }

    public function cleanDir()
    {
        if ( isset($this->directory_config['cleaning_term']) && $this->directory_config['cleaning_term']!=-1 ){
            $removeOlderThen = strtotime('-'.$this->directory_config['cleaning_term']);
            if ($this->getParent()->directory_type==self::TYPE_DATASOURCE) {
                // fast remove - skip any temporary files
                tep_db_query(
                    "DELETE target FROM " . TABLE_EP_LOG_MESSAGES . " target ".
                    " INNER JOIN ".TABLE_EP_JOB." job_table ON target.job_id=job_table.job_id ".
                    "WHERE job_table.directory_id='" . $this->directory_id . "' ".
                    "  AND job_table.last_cron_run<='" . date('Y-m-d H:i:s', $removeOlderThen) . "'"
                );
                tep_db_query(
                    "DELETE job_table FROM " . TABLE_EP_JOB . " job_table ".
                    "WHERE job_table.directory_id='" . $this->directory_id . "' ".
                    "  AND job_table.last_cron_run<='" . date('Y-m-d H:i:s', $removeOlderThen) . "'"
                );
            } else {
                $get_job_remove_r = tep_db_query(
                    "SELECT job_id " .
                    "FROM " . TABLE_EP_JOB . " " .
                    "WHERE directory_id='" . $this->directory_id . "' " .
                    " AND last_cron_run<='" . date('Y-m-d H:i:s', $removeOlderThen) . "'"
                );
                if (tep_db_num_rows($get_job_remove_r) > 0) {
                    while ($job_remove = tep_db_fetch_array($get_job_remove_r)) {
                        Job::loadById($job_remove['job_id'])->delete();
                    }
                }
            }
        }
    }

    public function synchronizeDirectories($cronAsk)
    {
        if (self::TYPE_PROCESSED==$this->directory_type) return;

        $db_link_check = [];

        $get_db_link_r = tep_db_query(
            "SELECT directory_id, directory ".
            "FROM ".TABLE_EP_DIRECTORIES." ".
            "WHERE parent_id='".$this->directory_id."'"
        );
        if ( tep_db_num_rows($get_db_link_r)>0 ) {
            while( $_db_link = tep_db_fetch_array($get_db_link_r) ) {
                $db_link_check[ $_db_link['directory'] ] = $_db_link['directory_id'];
            }
        }
        
        $directoryRoot = $this->filesRoot($this->directory_type);

        foreach(glob($directoryRoot.'*',GLOB_ONLYDIR) as $checkDir){
            $checkDirectoryName = basename($checkDir);

            //if (preg_match('/^(images|processed)$/', $checkDirectoryName)) continue;
            if (preg_match('/^(images)$/', $checkDirectoryName)) continue;

            if ( !isset($db_link_check[$checkDirectoryName]) ) {
                tep_db_perform(TABLE_EP_DIRECTORIES, array(
                    'removable' => 1,
                    'cron_enabled' => $this->cron_enabled,
                    'parent_id' => $this->directory_id,
                    'name' => '--',
                    'directory_type' => ($checkDirectoryName==self::TYPE_PROCESSED?self::TYPE_PROCESSED:$this->directory_type),
                    'directory' => $checkDirectoryName,
                ));
                $newDirectoryId = tep_db_insert_id();

                $newDirectory = self::loadById($newDirectoryId);
                if ( $newDirectory->isCronImportJobDirectory() ) {
                    // touch dir
                    $newDirectory->filesRoot(self::TYPE_PROCESSED);
                }
                $newDirectory->synchronizeFiles($cronAsk);
            }else{
                $oldDirectoryId = $db_link_check[$checkDirectoryName];
                unset($db_link_check[$checkDirectoryName]);

                $oldDirectory = self::loadById($oldDirectoryId);
                if ( $oldDirectory->isCronImportJobDirectory() ) {
                    // touch dir
                    $oldDirectory->filesRoot(self::TYPE_PROCESSED);
                }
                $oldDirectory->synchronizeFiles($cronAsk);
            }
        }
        // remove not found
        if ( count($db_link_check)>0 ) {
            tep_db_query("DELETE FROM ".TABLE_EP_JOB." WHERE directory_id IN ('".implode("','", $db_link_check)."')");
            tep_db_query("DELETE FROM ".TABLE_EP_DIRECTORIES." WHERE directory_id IN ('".implode("','", $db_link_check)."') AND removable=1");
        }
    }

    public function synchronizeFiles($cronAsk=false)
    {
        if ( $this->directory_type!=self::TYPE_IMPORT ) return;

        $dirScan = $this->filesRoot();
        if ( !is_dir($dirScan) ) return;

        if ( $this->cron_enabled || $this->directory_type=='import' ) {
            $this->synchronizeDirectories($cronAsk);
        }

        $dirScan = FileHelper::normalizePath($dirScan, '/').'/';
        $dir_files = FileHelper::findFiles($dirScan,[
            'recursive' => false,
            'except' => ['.svn', 'images', 'processed'],
            //'only' => ['*.*'],
        ]);
        $dir_files = array_map(function($val) { return FileHelper::normalizePath($val, '/'); }, $dir_files);

        $get_db_files_r = tep_db_query(
            "SELECT job_id, IF(LENGTH(file_name_internal)>0, file_name_internal, file_name) AS file_name ".
            "FROM ".TABLE_EP_JOB." ".
            "WHERE directory_id='".$this->directory_id."' /*AND direction='".$this->directory_type."'*/ "
        );
        $db_files = array();
        if ( tep_db_num_rows($get_db_files_r)>0 ) {
            while($_db_file = tep_db_fetch_array($get_db_files_r)){
                $checkFSJob = Job::loadById($_db_file['job_id']);
                if ( $checkFSJob instanceof JobFile ) {
                    $db_files[$_db_file['file_name']] = $_db_file['job_id'];
                }
            }
        }

        foreach( $dir_files as $fsFilename ) {
            $fsFilename = str_replace($dirScan, '', $fsFilename);
            if ( isset($db_files[$fsFilename]) ) {
                unset($db_files[$fsFilename]);
            }

            $job = $this->findJobByFilename($fsFilename);
            if ($cronAsk && $this->cron_enabled) {
                if ($job && $job instanceof JobFile) {
                    $job->watchFileChanges();
                    if ($job->job_state == Job::STATE_UPLOADED) {
                        $job->tryAutoConfigure();
                    }
                    /*
                    //$this->touchImportJob($fsFilename,'upload','auto');
                    if ($job->checkUploadFinish()) {
                        $job->tryAutoConfigure();
                    }
                    */
                } else {
                    $this->touchImportJob($fsFilename, 'upload', 'auto');
                }
            } elseif (!$this->cron_enabled) {
                if (!$job) {
                    $job_id = $this->touchImportJob($fsFilename, 'uploaded', 'auto');
                    $job = Job::loadById($job_id);
                    if ($job && $job instanceof JobFile) {
                        $job->tryAutoConfigure();
                    }
                }
            }
        }

        if ( count($db_files)>0 ) {
            tep_db_query(
                "DELETE FROM ".TABLE_EP_JOB." ".
                "WHERE job_id IN('".implode("','",$db_files)."') AND directory_id='".$this->directory_id."'"
            );
        }
    }
    
    static public function getAll($renewCache=false)
    {
        static $directoriesList = false;
        if ( $renewCache ) $directoriesList = false;
        if ( !is_array($directoriesList) ) {
            $directoriesList = [];
            $get_all_r = tep_db_query("SELECT * FROM ".TABLE_EP_DIRECTORIES." WHERE 1 ORDER BY directory_id");
            if ( tep_db_num_rows($get_all_r)>0 ) {
                while($data = tep_db_fetch_array($get_all_r)){
                    $directoriesList[] = new static($data);
                }
            }
        }
        return $directoriesList;
    }

    static public function getAllRoots()
    {
        $roots = [];
        foreach (self::getAll() as $directory) {
            if ( !empty($directory->parent_id) ) continue;
            if ( $directory->directory_id==5 && count(DataSources::getAvailableList())==0 ) continue;
            $roots[] = $directory;
        }
        return $roots;
    }
    
    /**
     * 
     * @param id $id
     * @return self
     */
    static public function findById($id)
    {
        $result = false;

        foreach(self::getAll() as $directory){
            if ( $directory->directory_id==$id ) {
                $result = $directory;
                break;
            }
        }
        if ( $result===false ) {
            $result = self::loadById($id);
            if ( is_object($result) ) self::getAll(true);
        }
        return $result;
    }
    
    public function findJobByFilename($filename)
    {
        $get_db_file_r = tep_db_query(
            "SELECT job_id ".
            "FROM ".TABLE_EP_JOB." ".
            "WHERE directory_id='".$this->directory_id."' /*AND direction = '".tep_db_input($this->directory_type)."'*/ ".
            "  AND (BINARY file_name = BINARY '".tep_db_input($filename)."' OR file_name_internal='".tep_db_input($filename)."') "
        );
        if ( tep_db_num_rows($get_db_file_r) ) {
            $job_data = tep_db_fetch_array($get_db_file_r);
            return Job::loadById($job_data['job_id']);
        }
        return false;
    }

    public function getJobs()
    {
        $jobList = [];
        $get_db_file_r = tep_db_query(
            "SELECT job_id ".
            "FROM ".TABLE_EP_JOB." ".
            "WHERE directory_id='".$this->directory_id."'".
            ""
        );
        if ( tep_db_num_rows($get_db_file_r) ) {
            while($job_data = tep_db_fetch_array($get_db_file_r)) {
                $jobList[] = Job::loadById($job_data['job_id']);
            }
        }
        return $jobList;
    }

    public function getJobConfigTemplate($jobId)
    {
        $jobObj = Job::loadById($jobId);
        if (!$jobObj) return [];

        $directoryConfig = [];
        foreach ($this->directory_config as $idx => $jobConfig) {
            if (!($jobObj->job_provider == $jobConfig['job_provider'] && $jobObj->file_name == $jobConfig['filename_pattern'])) continue;
            $directoryConfig[$idx] = $jobConfig;
        }
        return $directoryConfig;
    }

    public function updateJobDirectoryConfig($jobId, $newConfig)
    {
        if ( !is_array($newConfig) || !$this->directory_id ) return;
        $configs = $this->getJobConfigTemplate($jobId);

        if ( empty($configs) ) return;

        foreach (array_keys($configs) as $idx){
            $this->directory_config[$idx] = array_merge($this->directory_config[$idx], $newConfig);
        }
        tep_db_query(
            "UPDATE ".TABLE_EP_DIRECTORIES." ".
            "SET directory_config='".tep_db_input(json_encode($this->directory_config))."' ".
            "WHERE directory_id='".(int)$this->directory_id."'"
        );
    }

    public function touchImportJob($fileName, $job_state, $job_provider)
    {
        if ( $this->directory_type==self::TYPE_DATASOURCE ) {
            $file_data_array = array(
                'directory_id' => $this->directory_id,
                'direction' => preg_match('/\.zip$/i', $fileName) ? 'import_zip' : $this->directory_type,
                'file_name' => $fileName,
                'file_time' => 0,
                'file_size' => 0,
                'job_state' => $job_state,
            );
        }else {
            clearstatcache();
            $fileDir = $this->filesRoot();
            $filePathName = $fileDir.$fileName;
            if ( !is_file($filePathName) ) return false;

            $direction = preg_match('/\.zip$/i', $fileName)?'import_zip':$this->directory_type;
            if (defined('EP_MULTI_SHEETS') && !empty(EP_MULTI_SHEETS) && $direction != 'import_zip') {
              $multiSheets = explode(',', EP_MULTI_SHEETS);
              if (!empty($multiSheets) && is_array($multiSheets)) {
                foreach ($multiSheets as $fileExt) {
                  $fileExt = strtolower($fileExt);
                  if (preg_match('/\.' . preg_quote($fileExt). '$/i', $fileName)) {
                    $direction = 'import_sheets';
                    break;
                  }
                }
              }
            }

            $file_data_array = array(
                'directory_id' => $this->directory_id,
                'direction' => $direction,
                'file_name' => $fileName,
                'file_time' => filemtime($filePathName),
                'file_size' => filesize($filePathName),
                'job_state' => $job_state,
            );
        }
        if ( !is_null($job_provider) ) {
            $file_data_array['job_provider'] = $job_provider;
        }
        if ( !isset($file_data_array['job_provider']) || empty($file_data_array['job_provider']) || $file_data_array['job_provider']=='auto' ) {
            $config = $this->findConfigByFileName($fileName);
            if ($config) {
                $file_data_array['run_frequency'] = $config['run_frequency'];
                $file_data_array['run_time'] = $config['run_time'];
                $file_data_array['job_provider'] = $config['job_provider'];
            }
        }

        $dbConnection = Yii::$app->get('db');
        /**
         * @var yii\db\Connection $dbConnection
         */
        $getExistingJobId = $dbConnection->createCommand(
            "SELECT job_id ".
            "FROM ".TABLE_EP_JOB." ".
            "WHERE directory_id='".$this->directory_id."' /*AND direction = '".tep_db_input($this->directory_type)."'*/ ".
            "  AND BINARY file_name = BINARY '".tep_db_input($fileName)."' "
        )->queryAll();
        if ( count($getExistingJobId)>0 ) {
            $_db_file = reset($getExistingJobId);
            tep_db_perform(TABLE_EP_JOB, $file_data_array, 'update', "job_id='".(int)$_db_file['job_id']."'");
            $job_id = (int)$_db_file['job_id'];
        }else{
            tep_db_perform(TABLE_EP_JOB, $file_data_array);
            $job_id = tep_db_insert_id();
        }
        return $job_id;
    }

    /**
     * @return bool|DatasourceBase
     */
    public function getDatasource()
    {
        $datasource = false;
        if ( $this->directory_type!=self::TYPE_DATASOURCE ) {
            if ( $this->parent_id ) {
                return $this->getParent()->getDatasource();
            }
            return $datasource;
        }
        $datasource = DataSources::getByName($this->directory);
        return $datasource;
    }

    /**
     * @param $name int
     * @return Directory|bool
     */
    public static function getDatasourceRoot($name)
    {
        $get_created_id_r = tep_db_query(
            "SELECT directory_id FROM " . TABLE_EP_DIRECTORIES . " WHERE directory='" . tep_db_input($name) . "' AND parent_id=5"
        );
        if (tep_db_num_rows($get_created_id_r) > 0) {
            $get_directory_id = tep_db_fetch_array($get_created_id_r);
            return self::findById($get_directory_id['directory_id']);
        }
        return false;
    }
}
