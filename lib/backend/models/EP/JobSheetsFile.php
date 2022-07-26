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

class JobSheetsFile extends JobFile {

  public function delete() {
    /*        $file = $this->getFileSystemName();
      $extractDir = dirname($file).'/'.pathinfo($this->file_name,PATHINFO_FILENAME).'/';
      Directory::findById($this->directory_id);
      FileHelper::removeDirectory($extractDir);
     */
    return parent::delete();
  }

  public function canConfigureExport() {
    return false;
  }

  public function canConfigureImport() {
    return true;
  }

  public function getArchivedFileColumns() {
    $result = [];
    $fileSystemName = $this->getFileSystemName();

    if (preg_match('/\.xlsx$/i', $fileSystemName)) {
      $reader = new Reader\XLSX([
        'filename' => $fileSystemName,
      ]);
      $reader->filename = $fileSystemName;
      $result = $reader->readSheets();

      unset($reader);
    }

    return $result;
  }

  public function tryAutoConfigure($selectedProvider = '') {
    $detectedProviders = [];
    if (empty($this->job_provider) || $this->job_provider == 'auto') {
      $providers = new Providers();
      $fullAutoConfigure = null;

      $archivedFileColumns = $this->getArchivedFileColumns();

      $job_configure = [
        'containerFilesSetting' => [],
      ];

      $job_provider = '';

      $containerProviderType = [];
      /*
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
       */
      foreach ($archivedFileColumns as $archivedFile => $fileInfo) {
        $fileColumns = $fileInfo['columns'];
        if (is_array($fileColumns) && count($fileColumns) > 0) {
          $possibleProviders = $providers->bestMatch($fileColumns);
          reset($possibleProviders);
          $__fileProviderList = array_keys($possibleProviders);
          if (count($__fileProviderList) > 0) {
            if ($job_provider != 'product\catalog') {
              $job_provider = $__fileProviderList[0];
              if (isset($containerProviderType[$archivedFile]) && !empty($containerProviderType[$archivedFile])) {
                if (array_search($containerProviderType[$archivedFile], $__fileProviderList) !== false) {
                  $job_provider = $containerProviderType[$archivedFile];
                }
              }
            } else {
              continue;
            }
            $job_configure['containerFilesSetting'][$archivedFile] = [
              'job_provider' => $job_provider,
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
      }

      if ($job_provider) {
        $this->job_state = self::STATE_CONFIGURED;
        $this->job_provider = $job_provider;
        $this->job_configure = $job_configure;
        if ($this->job_id) {
          tep_db_query(
              "UPDATE " . TABLE_EP_JOB . " " .
              "SET job_state='" . tep_db_input($this->job_state) . "', job_provider='" . tep_db_input($this->job_provider) . "', " .
              " job_configure='" . tep_db_input(json_encode($this->job_configure)) . "' " .
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

  public function run(Messages $messages) {
    $this->runSheets($messages);
  }

  public function runSheets(Messages $messages) {

    if ($this->job_provider != '' && $this->job_provider != 'auto') {
      //$filename = $this->file_name;
      $filename = $this->getFileSystemName();
      $readerClass = false;
      if (isset($this->job_configure['import']) && !empty($this->job_configure['import']['format'])) {
        $readerClass = $this->job_configure['import']['format'];
      } elseif (preg_match('/\.xls$/i', $filename)) {
        $readerClass = 'XLS';
      } elseif (preg_match('/\.xlsx$/i', $filename)) {
        $readerClass = 'XLSX';
      }
      /**
       * @var $processSubDir Directory
       */
      $providers = new \backend\models\EP\Providers();
      $processSubDir = $this->getDirectory();

      if ($processSubDir) {
        $messages->setEpFileId($this->job_id);
        $messages->command('start_import');
        $messages->command('persist_messages', true);
        
        if (is_array($this->job_configure) && isset($this->job_configure['containerFilesSetting'])) {

          foreach ($this->job_configure['containerFilesSetting'] as $subfilename => $file_configure) {
            
            if (empty($file_configure['job_provider']) || empty($readerClass)) {
              continue;
            }
            $messages->info('<b>Process "' . $subfilename . '"</b>');

            try {
              $ext = 'backend\\models\\EP\\Reader\\' . $readerClass;
              $reader = new $ext ([
                'filename' => $filename,
              ]);
              $reader->filename = $filename;
              $reader->sheet_name = $subfilename;

              $providerObj = $providers->getProviderInstance($file_configure['job_provider'], $file_configure);

              $providerObj->setFormat($readerClass);
              $transform = new Transform();
              $transform->setProviderColumns($providerObj->getColumns());
              if ($file_configure['remap_columns']) {
                $transform->setTransformMap($file_configure['remap_columns']);
              }

              $started = time();
              $progressRowInform = 100;
              $rowCounter = 0;
              while ($data = $reader->read()) {
                if ($readerClass != 'XML') {
                  $data = $transform->transform($data);
                }
                set_time_limit(300);
                $providerObj->importRow($data, $messages);
                $rowCounter++;
                if (($rowCounter % $progressRowInform) == 0) {
                  $percentProgress = $reader->getProgress();
                  $currentTime = time();
                  if ($percentProgress == 0) {
                    $secondsForJob = round(($currentTime - $started) * 100 / 0.0001);
                  } else {
                    $secondsForJob = round(($currentTime - $started) * 100 / $percentProgress);
                  }
                  $timeLeft = 'Time left: ' . gmdate('H:i:s', max(0, $secondsForJob - ($currentTime - $started)));
                  if ($currentTime != $started) {
                    $timeLeft .= ' ' . number_format($rowCounter / ($currentTime - $started), 1, '.', '') . ' Lines per second';
                  }

                  $messages->progress($percentProgress, $timeLeft);

                  set_time_limit(300);
                }
              }
              $messages->progress(100);

              $providerObj->postProcess($messages);
            } catch (\Exception $ex) {
              //$messages->info($ex->getMessage());
              $messages->command('persist_messages', false);
              $this->jobFinished();
              throw $ex;
            }
          }
          $this->jobFinished();
        }
        $messages->command('persist_messages', false);

        return;
      }
    }
  }

}
