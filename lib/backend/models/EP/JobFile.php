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

class JobFile extends Job
{

    public function delete()
    {
        $filename = $this->getFileSystemName();
        if ( is_file($filename) ){
            @unlink($filename);
        }
        return parent::delete();
    }

    public function getFileSystemName()
    {
        if ( strpos($this->file_name, 'php://')===0 ) return $this->file_name;
        $directory = $this->getDirectory();
        $ep_files_dir = $directory->filesRoot();
        return $ep_files_dir.(empty($this->file_name_internal)?$this->file_name:$this->file_name_internal);
    }

    public function getFullFilename()
    {
        if ( strpos($this->file_name, 'php://')===0 ) return $this->file_name;
        $directory = $this->getDirectory();
        $ep_files_dir = $directory->filesRoot();
        return $ep_files_dir.$this->file_name;
    }

    public function getFileInfo()
    {
        $filename = $this->getFileSystemName();
        return [
            'pathFilename' => $this->getFullFilename(),
            'fileSystemName' => $filename,
            'filename' => $this->file_name,
            'fileSize' => is_file($filename)?filesize($filename):false,
            'fileTime' => is_file($filename)?filemtime($filename):0,
        ];
    }

    public function canRemove()
    {
        if ( $this->direction=='export' ) return true;

        $jobFilename = $this->getFileSystemName();

        return /*is_writeable($jobFilename) && */is_writeable(dirname($jobFilename));
    }

    public function watchFileChanges()
    {
        clearstatcache();

        $fileInfo = $this->getFileInfo();
        $touchData = [
            'file_time' => $fileInfo['fileTime'],
            'file_size' => $fileInfo['fileSize'],
        ];
        if ( $touchData['file_size']==$this->file_size /*&& $touchData['file_time']==$this->file_time*/ ) {
            if ( $this->job_state==self::STATE_UPLOAD_IN_PROGRESS ) {
                $touchData['job_state'] = self::STATE_UPLOADED;

                if ( empty($this->file_name_internal) ) {
                    $file_name_internal = md5(time() . '' . $this->file_name);
                    $directory = $this->getDirectory();
                    if (rename($directory->filesRoot() . $this->file_name, $directory->filesRoot() . $file_name_internal)) {
                        $touchData['file_name_internal'] = $file_name_internal;
                    }
                }
            }
        }else {
            $touchData['job_state'] = self::STATE_UPLOAD_IN_PROGRESS;
        }

        if ( $this->job_id ) {
            tep_db_perform(TABLE_EP_JOB, $touchData, 'update', "job_id='" . $this->job_id . "'");
        }
        foreach($touchData as $key=>$val) {
            $this->{$key} = $val;
        }
    }

    public function checkUploadFinish()
    {
        clearstatcache();

        $fileInfo = $this->getFileInfo();
        $touchData = [
            'file_time' => $fileInfo['fileTime'],
            'file_size' => $fileInfo['fileSize'],
        ];
        if ( $touchData['file_size']==$this->file_size /*&& $touchData['file_time']==$this->file_time*/ ) {
            $touchData['job_state'] = self::STATE_UPLOADED;

            $file_name_internal = md5(time().''.$this->file_name);
            $directory = $this->getDirectory();
            if ( rename( $directory->filesRoot().$this->file_name, $directory->filesRoot().$file_name_internal ) ) {
                $touchData['file_name_internal'] = $file_name_internal;
            }
        }
        if ( $this->job_id ) {
            tep_db_perform(TABLE_EP_JOB, $touchData, 'update', "job_id='" . $this->job_id . "'");
        }
        foreach($touchData as $key=>$val) {
            $this->{$key} = $val;
        }

        return $this->job_state==self::STATE_UPLOADED;
    }

    protected function getProvider()
    {
        $jobConstructParam = ['job_configure'=>$this->job_configure];
        if ( is_array($this->job_configure) ) $jobConstructParam = $this->job_configure;
        $directory = $this->getDirectory();
        if ( is_object($directory) ) {
            $jobConstructParam['directoryObj'] = $directory;
        }
        $providers = $this->getProviders();
        $providerObj = $providers->getProviderInstance($this->job_provider, $jobConstructParam);

        return $providerObj;
    }

    public function tryAutoConfigure($selectedProvider='')
    {
        $possibleProviders = [];
        if ( !empty($selectedProvider) ) {
            $this->job_provider = $selectedProvider;
            $this->job_configure = [];
            $this->saveConfigureState();
        }
        if ( empty($this->job_provider) || $this->job_provider=='auto' || !empty($selectedProvider) ) {

            $providers = $this->getProviders();

            $fileSystemName = $this->getFileSystemName();

            if ( fnmatch('*.xml',$this->file_name) ) {
                $possible = $providers->getAvailableProviders('Import', function ($providerKey, $providerInfo) use($selectedProvider) {
                    if ((empty($selectedProvider) || $selectedProvider==$providerKey) && isset($providerInfo['export']) && isset($providerInfo['export']['allow_format'])) {
                        return count(preg_grep('/xml/i', $providerInfo['export']['allow_format'])) > 0;
                    }
                    return false;
                });

                $f = fopen($fileSystemName, 'r');
                $firstCut = 2048;
                $shCut = '';
                do {
                    $shCut .= fread($f, $firstCut);
                    if (stripos($shCut, '<header>') === false) {
                        break;
                    } else {
                        if (stripos($shCut, '</header>') !== false) {
                            break;
                        }
                    }
                } while (true);
                fclose($f);

                $header = false;
                if ($shCut && ($h0 = stripos($shCut, '<header>')) !== false && ($h1 = stripos($shCut, '</header>')) !== false && $h1 > $h0) {
                    $xmlObj = new \SimpleXMLElement(substr($shCut, $h0, $h1 - $h0 + 9));
                    $header = json_decode(json_encode($xmlObj), true);
                }
//                if (preg_match( '#<header>(.*)</header>#m',$shCut, $matchHeader) ) {
//                    $xmlObj = new \SimpleXMLElement($matchHeader[0]);
//                    $header = json_decode(json_encode($xmlObj),true);
//                }

                if (is_array($header) && count($header) > 0) {
                    foreach ($possible as $possibleProviderInfo) {
                        $providerObj = $providers->getProviderInstance($possibleProviderInfo['key']);
                        if (!method_exists($providerObj, 'exchangeXml')) continue;
                        foreach ($providerObj->exchangeXml() as $versionInfo) {
                            if (isset($versionInfo['Header']) && $versionInfo['Header']['type'] == $header['type']) {
                                $xmlReader = preg_grep('/xml/i', $possibleProviderInfo['export']['allow_format']);
                                if ( isset($header['projectCode']) ) {
                                    $versionInfo['projectCode'] = $header['projectCode'];
                                }
                                $this->job_configure['import'] = $versionInfo;
                                $this->job_configure['import']['format'] = current($xmlReader);
                                $this->job_state = self::STATE_CONFIGURED;
                                $this->job_provider = $possibleProviderInfo['key'];
                                $possibleProviders[$this->job_provider] = 1;
                                break;
                            }else
                            if (isset($versionInfo['header']) && $versionInfo['header'] == $header) {
                                $xmlReader = preg_grep('/xml/i', $possibleProviderInfo['export']['allow_format']);
                                $this->job_configure['import'] = $versionInfo;
                                $this->job_configure['import']['format'] = current($xmlReader);
                                $this->job_state = self::STATE_CONFIGURED;
                                $this->job_provider = $possibleProviderInfo['key'];
                                $possibleProviders[$this->job_provider] = 1;
                                break;
                            }
                        }
                    }
                }

                if (!is_array($header) && $this->job_state != self::STATE_CONFIGURED && strpos($shCut, '<Orders>') !== false) {
                    $this->job_configure['import']['format'] = 'XML_orders_new';
                    $this->job_state = self::STATE_CONFIGURED;
                    $this->job_provider = 'orders\orders';
                    $possibleProviders[$this->job_provider] = 1;
                }
                if ($this->job_id && $this->job_state == self::STATE_CONFIGURED) {
                    $this->saveConfigureState();
                }
                return $possibleProviders;
            }

            $n = strrpos($fileSystemName, '.');
            if ($n !== false) {
              $ext = substr($fileSystemName, $n+1);
              $ns = '\\backend\\models\\EP\\Reader\\';
              $classname = '';
              if (class_exists($ns . strtoupper($ext))) {
                $classname = $ns . strtoupper($ext);
              }elseif (class_exists($ns . ucfirst($ext))) {
                $classname = $ns . ucfirst($ext);
              }

              if ($classname !='') {
                $reader = new $classname(['filename' => $fileSystemName,]);
                $reader->filename = $fileSystemName;
              }
            }

            if (!is_object($reader)) {
              $reader = new Reader\CSV([
                  'filename' => $fileSystemName,
              ]);
            };

            $fileColumns = $reader->readColumns();
            if ( is_array($fileColumns) && count($fileColumns)>0 ) {
                $possibleProviders = $providers->bestMatch($fileColumns);
                if ( !empty($selectedProvider) ) {
                    if ( isset($possibleProviders[$selectedProvider]) ) {
                        $possibleProviders = [
                            $selectedProvider => $possibleProviders[$selectedProvider],
                        ];
                    }else {
                        $possibleProviders = [];
                    }
                }
                reset($possibleProviders);
                if (current($possibleProviders) == 1 || (!empty($selectedProvider) && isset($possibleProviders[$selectedProvider]))) {
                    $fileProvider = current(array_keys($possibleProviders));
                    $this->job_state = self::STATE_CONFIGURED;
                    $this->job_provider = $fileProvider;
                    $this->saveConfigureState();
                } else {
                    /// search file in history, For CSV auto import
                    if (!empty($this->job_id) && $this->direction == 'import' && $reader instanceof Reader\CSV) {
                        $q = tep_db_query("select job_configure, run_frequency, job_provider FROM " . TABLE_EP_JOB . " "
                            . " WHERE "
                            //in 'processed' :(  . " directory_id='" . $this->directory_id . "' and "
                            . " direction='" . $this->direction . "' and job_state='processed' and "
                            . " file_name='" . tep_db_input($this->file_name) . "' and run_frequency>0"
                            . " ORDER BY job_id desc LIMIT 1");
                        if ($_details = tep_db_fetch_array($q)) {
                            //from saveConfigureState + run_frequency.
                            $this->job_state = self::STATE_CONFIGURED;
                            $this->job_provider = $_details['job_provider'];
                            $this->run_frequency = $_details['run_frequency'];
                            $this->job_configure = json_decode($_details['job_configure'], true);
                            tep_db_query(
                                "UPDATE " . TABLE_EP_JOB . " " .
                                "SET job_state='" . tep_db_input($this->job_state) . "', job_provider='" . tep_db_input($this->job_provider) . "', " .
                                " job_configure='" . tep_db_input(json_encode($this->job_configure)) . "' " .
                                ", run_frequency='" . (int)$this->run_frequency . "' " .
                                "WHERE job_id='" . $this->job_id . "' "
                            );
                            
                        }
                    }
                }
            }
        }else{
            if ( $this->job_state != self::STATE_CONFIGURED ){
                $this->job_state = self::STATE_CONFIGURED;
                $this->saveConfigureState();
            }
        }
        return $possibleProviders;
    }

    public function run(Messages $messages)
    {

        if ( $this->direction=='import' ) {
            $this->runImport($messages);
        }elseif( $this->direction=='export' ) {
            $this->runExport($messages);
        }
    }


    public function runExport($messages)
    {
        $selected_columns = false;
        $filter = [];
        if ( isset($this->job_configure['export']) && is_array($this->job_configure['export']) ) {
            if ( isset($this->job_configure['export']['columns']) ) {
                $selected_columns = $this->job_configure['export']['columns'];
            }
            if ( isset($this->job_configure['export']['filter']) && is_array($this->job_configure['export']['filter']) ) {
                $filter = $this->job_configure['export']['filter'];
            }
        }

        $exportProviderObj = $this->getProvider();
        if ( !is_object($exportProviderObj) ) {
            die;
        }
        $writerConfigure = [];

        if ( stripos($this->job_configure['export']['format'],'XML')!==false && method_exists($exportProviderObj,'exchangeXml') ) {
            $exchangeConvention = $exportProviderObj->exchangeXml();
            if ( count($exchangeConvention)>0 ) {
                $exchangeConvention = end($exchangeConvention);
                if ( is_array($exchangeConvention) ) {
                    $writerConfigure = array_merge($writerConfigure, $exchangeConvention);
                }
            }
        }elseif(isset($this->job_configure['export']['write_config']) && is_array($this->job_configure['export']['write_config'])){
            $writerConfigure = array_merge($writerConfigure, $this->job_configure['export']['write_config']);
        }

        if ( isset($this->job_configure['export']['feed']) ) {
            $writerConfigure = array_merge([
                'class' => 'backend\\models\\EP\\Writer\\' . $this->job_configure['export']['format'],
                'filename' => $this->getFileSystemName(),
                'feed' => isset($this->job_configure['export']['feed']) ? $this->job_configure['export']['feed'] : [],
            ],$writerConfigure);
        }else {
            $writerConfigure = array_merge([
                'class' => 'backend\\models\\EP\\Writer\\' . $this->job_configure['export']['format'],
                'filename' => $this->getFileSystemName(),
            ],$writerConfigure);

        }
        $writerConfigure['class'] = 'backend\\models\\EP\\Writer\\' . $this->job_configure['export']['format'];
        $writer = Yii::createObject($writerConfigure);

        $exportColumns = $exportProviderObj->getColumns();

        if ( is_array($selected_columns) && count($selected_columns)>0 ) {
            $_selected = array();
            foreach($selected_columns as $selected_column){
                if ( !isset($exportColumns[$selected_column]) ) continue;
                $_selected[$selected_column] = $exportColumns[$selected_column];
            }
            $exportColumns = $_selected;
        }

        //add import instruction to XLSX export files - set before setColumns (it writes out columns to file)
        if (method_exists($writer, 'setDescription') && method_exists($exportProviderObj, 'getExportDescription')) {
          $writer->setDescription($exportProviderObj->getExportDescription());
        }

        $writer->setColumns($exportColumns);
        $exportProviderObj->setColumns($exportColumns);
        $exportProviderObj->setFormat($this->job_configure['export']['format']);

        $exportProviderObj->prepareExport(array_keys($exportColumns), $filter);
        while(is_array($providerData = $exportProviderObj->exportRow())){
            $writer->write($providerData);
        }
        $writer->close();
    }

    public function runImport(Messages $messages)
    {

        if( (empty($this->job_provider) || $this->job_provider=='auto' ) ){
            throw new Exception('Need select job type');
        }

        $providerObj = $this->getProvider();

        //$messages->setEpFileId($this->job_id);
//        $messages = new EP\Messages([
//            'job_id' => $job_record->job_id,
//        ]);
        $messages->command('start_import');
        //$dir = rtrim(\Yii::getAlias($this->ep_work_dir), '/');
        $filename = $this->getFileSystemName();
        try {
            $readerClass = 'CSV';
            if ( preg_match('/\.zip$/i',$filename) ) $readerClass = 'ZIP';

            if ( isset($this->job_configure['import']) && !empty($this->job_configure['import']['format']) ){
                $readerClass = $this->job_configure['import']['format'];
            }
            elseif (preg_match('/\.xls$/i',$filename) ){
              $readerClass = 'XLS';
            }
            elseif (preg_match('/\.xlsx$/i',$filename) ){
              $readerClass = 'XLSX';
            }
            $readerConfig = array_merge([
                'class' => 'backend\\models\\EP\\Reader\\' . $readerClass,
                'filename' => $filename,
            ], (isset($this->job_configure['import']) && is_array($this->job_configure['import'])?$this->job_configure['import']:[]));

            $reader = Yii::createObject($readerConfig);

            $providerObj->setFormat($readerClass);
            $transform = new Transform();
            $transform->setProviderColumns($providerObj->getColumns());
            if (isset($this->job_configure['remap_columns']) && is_array($this->job_configure['remap_columns'])) {
                $transform->setTransformMap($this->job_configure['remap_columns']);
            }

            $started = time();
            $progressRowInform = 100;
            $rowCounter = 0;
            while ($data = $reader->read()) {
                if ($readerClass!='XML') {
                    $data = $transform->transform($data);
                }
                set_time_limit(300);
                $providerObj->importRow($data, $messages);
                $rowCounter++;
                if (($rowCounter % $progressRowInform)==0) {
                    $percentProgress = $reader->getProgress();
                    $currentTime = time();
                    if ( $percentProgress==0 ) {
                        $secondsForJob = round(($currentTime - $started) * 100 / 0.0001);
                    }else{
                        $secondsForJob = round(($currentTime - $started) * 100 / $percentProgress);
                    }
                    $timeLeft = 'Time left: '.gmdate('H:i:s',max(0,$secondsForJob - ($currentTime-$started)) );
                    if ( $currentTime!=$started ) {
                        $timeLeft .= ' ' . number_format($rowCounter / ($currentTime - $started), 1, '.', '') . ' Lines per second';
                    }

                    $messages->progress($percentProgress, $timeLeft);

                    set_time_limit(300);
                }
            }
            $messages->progress(100);

            $providerObj->postProcess($messages);

            $this->jobFinished();
        }catch (\Exception $ex){
            //$messages->info($ex->getMessage());
            $this->jobFinished();
            throw $ex;
        }
    }

    public function moveToProcessed()
    {
        $directory = $this->getDirectory();

        $processedDirectory = $directory->getProcessedDirectory();

        if ( !$processedDirectory ) return false;

        $processedDir = $processedDirectory->filesRoot();

        if ( $this->file_name_internal ) {
            $new_name = $this->file_name_internal;
            $new_file_name = $this->file_name;
        }else {
            $file_pathinfo = pathinfo($this->file_name);
            $new_name = $file_pathinfo['filename'] . '_' . date('YmdHis') . (isset($file_pathinfo['extension']) ? '.' . $file_pathinfo['extension'] : '');
            $new_file_name = $new_name;
        }

        if ( rename( $this->getFileSystemName(), $processedDir.$new_name ) ) {
            @chmod($processedDir.$new_name,0666);
            $this->job_state = self::STATE_PROCESSED;
            $this->file_name = $new_file_name;
            $this->directory_id = $processedDirectory->directory_id;

            tep_db_query(
                "UPDATE ".TABLE_EP_JOB." ".
                "SET job_state='".tep_db_input($this->job_state)."', file_name='".tep_db_input($this->file_name)."', directory_id='".intval($this->directory_id)."' ".
                "WHERE job_id='".intval($this->job_id)."'"
            );
        }
    }
}