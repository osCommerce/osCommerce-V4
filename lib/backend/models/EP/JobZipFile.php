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
use yii\helpers\FileHelper;

class JobZipFile extends JobFile
{

    public function delete()
    {
        $file = $this->getFileSystemName();
        $extractDir = dirname($file).'/'.pathinfo($this->file_name,PATHINFO_FILENAME).'/';
        Directory::findById($this->directory_id);
        FileHelper::removeDirectory($extractDir);

        return parent::delete();
    }

    public function canConfigureExport()
    {
        return false;
    }

    public function canConfigureImport()
    {
        return true;
    }

    public function getArchivedFileColumns()
    {
        $result = [];
        $fileSystemName = $this->getFileSystemName();
        if ( preg_match('/\.zip$/i',$this->file_name ) ){
            $reader = new Reader\ZIP([
                'filename' => $fileSystemName,
            ]);

            while($fileInfo = $reader->read()){
                if ( preg_match('/\.(csv|txt)$/i', $fileInfo['filename'] ) ){
                    $fileSystemName = tempnam(sys_get_temp_dir(), 'ep_test_archived_feed');
                    $writeStream = fopen($fileSystemName,'wb');
                    $limitExtractBlock = 16; // extract only 256k
                    while( $data = fread($fileInfo['stream'],16*1024) ) {
                        fwrite($writeStream, $data);
                        $limitExtractBlock--;
                        if ( $limitExtractBlock<=0 ) break;
                    }
                    fclose($writeStream);

                    $nestedReader = new Reader\CSV([
                        'filename' => $fileSystemName,
                    ]);
                    $fileColumns = $nestedReader->readColumns();
                    @unlink($fileSystemName);

                    $result[$fileInfo['filename']] = [
                        'columns' => $fileColumns,
                    ];
                }elseif( preg_match('/\.(xml)$/i', $fileInfo['filename'] ) ){
                    $fileSystemName = tempnam(sys_get_temp_dir(), 'ep_test_archived_feed');
                    //$writeStream = fopen($fileSystemName,'wb');
                    $headChunk = '';
                    $limitExtractBlock = 16; // extract only 256k
                    while( $data = fread($fileInfo['stream'],16*1024) ) {
                        //fwrite($writeStream, $data);
                        $headChunk .= $data;
                        $limitExtractBlock--;
                        if ( $limitExtractBlock<=0 ) break;
                    }
                    //fclose($writeStream);
                    $result[$fileInfo['filename']] = [
                        'headChunk' => $headChunk,
                    ];
                }
            }
            unset($reader);
        }

        return $result;
    }

    public function tryAutoConfigure($selectedProvider='')
    {
        $detectedProviders = [];
        if ( empty($this->job_provider) || $this->job_provider=='auto' ) {
            $providers = new Providers();

            $possibleXml = $providers->getAvailableProviders('Import', function ($providerKey, $providerInfo) use($selectedProvider) {
                if ((empty($selectedProvider) || $selectedProvider==$providerKey) && isset($providerInfo['export']) && isset($providerInfo['export']['allow_format'])) {
                    return count(preg_grep('/xml/i', $providerInfo['export']['allow_format'])) > 0;
                }
                return false;
            });

            $fullAutoConfigure = null;

            $archivedFileColumns = $this->getArchivedFileColumns();

            $job_configure = [
                'containerFilesSetting' => [],
            ];

            $job_provider = '';

            $containerProviderType = [];

            if ( isset($archivedFileColumns['process_sequence.csv']) ) {
                $reader = new Reader\ZIP([
                    'filename' => $this->getFileSystemName(),
                ]);
                while($fileInfo = $reader->read()){
                    if ($fileInfo['filename']=='process_sequence.csv'){
                        $fileSystemName = tempnam(sys_get_temp_dir(), 'ep_test_archived_feed');
                        $writeStream = fopen($fileSystemName,'wb');
                        while( $data = fread($fileInfo['stream'],16*1024) ) {
                            fwrite($writeStream, $data);
                        }
                        fclose($writeStream);

                        $nestedReader = new Reader\CSV([
                            'filename' => $fileSystemName,
                        ]);
                        while($feedData = $nestedReader->read()){
                            if ( !empty($feedData['Feed Type']) ) {
                                $containerProviderType[$feedData['Feed Process Queue']] = $feedData['Feed Type'];
                            }
                        }
                        unset($nestedReader);
                        unlink($fileSystemName);
                    }
                }
            }

            foreach ($archivedFileColumns as $archivedFile => $fileInfo) {
                if ($archivedFile == 'process_sequence.csv') {
                    $job_provider = 'product\catalog';
                }
                if (preg_match('/\.(csv|txt)$/', $archivedFile)) {
                    $fileColumns = $fileInfo['columns'];
                    if (is_array($fileColumns) && count($fileColumns) > 0) {
                        $possibleProviders = $providers->bestMatch($fileColumns);
                        reset($possibleProviders);
                        $__fileProviderList = array_keys($possibleProviders);
                        if ( count($__fileProviderList)>0 ) {
                            if ( $job_provider != 'product\catalog' ) {
                                $job_provider = $__fileProviderList[0];
                                if ( isset($containerProviderType[$archivedFile]) && !empty($containerProviderType[$archivedFile]) ) {
                                    if ( array_search($containerProviderType[$archivedFile],$__fileProviderList)!==false ) {
                                        $job_provider = $containerProviderType[$archivedFile];
                                    }
                                }
                            }else{
                                continue;
                            }
                            $job_configure['containerFilesSetting'][$archivedFile] = [
                                'job_provider' =>  $job_provider,
                            ];
                        }

                        //$autoConfigured[$archivedFile] = ['provider'=>current($possibleProviders)];
                        if (current($possibleProviders) == 1) {
                            $fileProvider = current(array_keys($possibleProviders));

                            $detectedProviders[] = $fileProvider;
                        } else {
                            $fullAutoConfigure = false;
                        }
                    }
                }elseif ( preg_match('/\.(xml)$/i', $archivedFile) ) {
                    $shCut = $fileInfo['headChunk'];
                    $header = false;
                    if ($shCut && ($h0 = stripos($shCut, '<header>')) !== false && ($h1 = stripos($shCut, '</header>')) !== false && $h1 > $h0) {
                        $xmlObj = new \SimpleXMLElement(substr($shCut, $h0, $h1 - $h0 + 9));
                        $header = json_decode(json_encode($xmlObj), true);
                    }

                    if (is_array($header) && count($header) > 0) {
                        foreach ($possibleXml as $possibleProviderInfo) {
                            $providerObj = $providers->getProviderInstance($possibleProviderInfo['key']);
                            if (!method_exists($providerObj, 'exchangeXml')) continue;
                            $feedSettings = [];
                            foreach ($providerObj->exchangeXml() as $versionInfo) {
                                if (isset($versionInfo['Header']) && $versionInfo['Header']['type'] == $header['type']) {
                                    $xmlReader = preg_grep('/xml/i', $possibleProviderInfo['export']['allow_format']);
                                    if ( isset($header['projectCode']) ) {
                                        $versionInfo['projectCode'] = $header['projectCode'];
                                    }
                                    $feedSettings['job_configure'] = [];
                                    $feedSettings['job_configure']['import'] = $versionInfo;
                                    $feedSettings['job_configure']['import']['format'] = current($xmlReader);
                                    $feedSettings['job_state'] = self::STATE_CONFIGURED;
                                    $feedSettings['job_provider'] = $possibleProviderInfo['key'];
                                    $detectedProviders[] = $feedSettings['job_provider'];
                                    break;
                                }elseif (isset($versionInfo['header']) && $versionInfo['header'] == $header) {
                                    $xmlReader = preg_grep('/xml/i', $possibleProviderInfo['export']['allow_format']);
                                    $feedSettings['job_configure'] = [];
                                    $feedSettings['job_configure']['import'] = $versionInfo;
                                    $feedSettings['job_configure']['import']['format'] = current($xmlReader);
                                    $feedSettings['job_state'] = self::STATE_CONFIGURED;
                                    $feedSettings['job_provider'] = $possibleProviderInfo['key'];
                                    $detectedProviders[] = $feedSettings['job_provider'];
                                    break;
                                }
                            }
                            if ( count($feedSettings)>0 ) {
                                $job_configure['containerFilesSetting'][$archivedFile] = $feedSettings;
                            }
                        }
                    }
                    if ( count($detectedProviders)>0 ) {
                        $job_provider = current($detectedProviders);
                    }
                }
            }

            if ($job_provider) {
                $this->job_state = self::STATE_CONFIGURED;
                $this->job_provider = $job_provider;
                $this->job_configure = $job_configure;
                if ($this->job_id) {
                    tep_db_query(
                        "UPDATE " . TABLE_EP_JOB . " " .
                        "SET job_state='" . tep_db_input($this->job_state) . "', job_provider='" . tep_db_input($this->job_provider) . "', " .
                        " job_configure='".tep_db_input(json_encode($this->job_configure))."' ".
                        "WHERE job_id='" . $this->job_id . "' "
                    );
                }
            }
        }
        if ($this->job_state != self::STATE_CONFIGURED) {
            $this->job_state = self::STATE_CONFIGURED;
            tep_db_query(
                "UPDATE " . TABLE_EP_JOB . " " .
                "SET job_state='" . tep_db_input($this->job_state) . "' " .
                "WHERE job_id='" . $this->job_id . "' "
            );
        }
        return $detectedProviders;
    }

    public function run(Messages $messages)
    {

        $this->runZip($messages);

    }

    public function runZip(Messages $messages)
    {
        $file = $this->getFileSystemName();
        $extractDir = dirname($file).'/'.pathinfo($this->file_name,PATHINFO_FILENAME).'/';

        FileHelper::createDirectory($extractDir,0777);

        $zip = new \ZipArchive();
        $zip->open($file);

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            $stream = $zip->getStream($filename);
            $extractFilename = $extractDir.$filename;
            if ( !is_dir(dirname($extractFilename)) ) {
                FileHelper::createDirectory(dirname($extractFilename),0777, true);
            }
            if ( preg_match('#[/|\\\]$#',$filename) ) continue; // skip directory

            $writeStream = fopen($extractFilename,'wb');
            while( $data = fread($stream,16*1024) ) {
                fwrite($writeStream, $data);
            }
            fclose($stream);
            fclose($writeStream);
            chmod($extractFilename, 0666);
        }

        $zip->close();

        $this->getDirectory()->synchronizeDirectories(false);

        if ( $this->job_provider!='' && $this->job_provider!='auto' ) {
            /**
             * @var $processSubDir Directory
             */
            $processSubDir = false;
            foreach( $this->getDirectory()->getSubdirectories(false) as $subDir ){
                if ($subDir->directory == basename($extractDir)) {
                    $processSubDir = $subDir;
                    break;
                }
            }

            $providers = new \backend\models\EP\Providers();

            if ( $processSubDir ) {
                $messages->setEpFileId($this->job_id);
                $messages->command('start_import');
                // {{ patch auto configured
                if ( is_array($this->job_configure) && isset($this->job_configure['containerFilesSetting']) ) {
                    foreach( $this->job_configure['containerFilesSetting'] as $subfilename=>$file_configure ) {
                        if ( empty($file_configure['job_provider']) ) continue;
                        $subJob_record = $processSubDir->findJobByFilename($subfilename);
                        if ( $subJob_record ) {
                            $subJob_record->job_provider = $file_configure['job_provider'];
                            if ( $file_configure['remap_columns'] ?? null) {
                                $subJob_record->job_configure['remap_columns'] = $file_configure['remap_columns'];
                            }

                            tep_db_query(
                                "UPDATE ".TABLE_EP_JOB." ".
                                "SET job_provider='".tep_db_input($subJob_record->job_provider)."', ".
                                " job_configure='".tep_db_input(json_encode($subJob_record->job_configure))."' ".
                                "WHERE job_id='".$subJob_record->job_id."'"
                            );
                        }
                    }
                }
                // }} patch auto configured

                $providerObj = $providers->getProviderInstance($this->job_provider);

                $job_record = $processSubDir->findJobByFilename('process_sequence.csv');
                if ( $job_record ) {
                    $job_record->job_provider = 'product\catalog';
                    $messages->info('<b>Process "'.$job_record->file_name.'"</b>');
                    try {
                        $job_record->run($messages);
                    }catch (\Exception $ex){
                        $messages->info($ex->getMessage());
                        \Yii::error($ex->getMessage().(YII_DEBUG ? "\n".$ex->getTraceAsString() : ''));
                    }
                }else{
                    foreach ($processSubDir->getJobs() as $directoryJob) {
                        $messages->command('persist_messages',true);
                        /**
                         * @var $directoryJob Job
                         */
                        if ( $directoryJob->job_provider=='' || $directoryJob->job_provider=='auto' ) continue;
                        $messages->info('<b>Process "' . $directoryJob->file_name . '"</b>');
                        try {
                            $directoryJob->run($messages);
                        } catch (\Exception $ex) {
                            $messages->info($ex->getMessage());
                            \Yii::error($ex->getMessage().(YII_DEBUG ? "\n".$ex->getTraceAsString() : ''));
                        }
                    }
                    $messages->command('persist_messages',false);
                }

                FileHelper::removeDirectory($extractDir);

                $this->moveToProcessed();

                $this->getDirectory()->synchronizeDirectories(false);

                return;
            }
        }

        Directory::getAll(true);
    }

}