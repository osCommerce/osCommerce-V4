<?php
/*
 * Copyright 2014 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations under
 * the License.
 */

namespace Google\Service\CloudAlloyDBAdmin;

class Cluster extends \Google\Model
{
  /**
   * @var string[]
   */
  public $annotations;
  /**
   * @var AutomatedBackupPolicy
   */
  public $automatedBackupPolicy;
  protected $automatedBackupPolicyType = AutomatedBackupPolicy::class;
  protected $automatedBackupPolicyDataType = '';
  /**
   * @var BackupSource
   */
  public $backupSource;
  protected $backupSourceType = BackupSource::class;
  protected $backupSourceDataType = '';
  /**
   * @var string
   */
  public $clusterType;
  /**
   * @var ContinuousBackupConfig
   */
  public $continuousBackupConfig;
  protected $continuousBackupConfigType = ContinuousBackupConfig::class;
  protected $continuousBackupConfigDataType = '';
  /**
   * @var ContinuousBackupInfo
   */
  public $continuousBackupInfo;
  protected $continuousBackupInfoType = ContinuousBackupInfo::class;
  protected $continuousBackupInfoDataType = '';
  /**
   * @var string
   */
  public $createTime;
  /**
   * @var string
   */
  public $databaseVersion;
  /**
   * @var string
   */
  public $deleteTime;
  /**
   * @var string
   */
  public $displayName;
  /**
   * @var EncryptionConfig
   */
  public $encryptionConfig;
  protected $encryptionConfigType = EncryptionConfig::class;
  protected $encryptionConfigDataType = '';
  /**
   * @var EncryptionInfo
   */
  public $encryptionInfo;
  protected $encryptionInfoType = EncryptionInfo::class;
  protected $encryptionInfoDataType = '';
  /**
   * @var string
   */
  public $etag;
  /**
   * @var UserPassword
   */
  public $initialUser;
  protected $initialUserType = UserPassword::class;
  protected $initialUserDataType = '';
  /**
   * @var string[]
   */
  public $labels;
  /**
   * @var MigrationSource
   */
  public $migrationSource;
  protected $migrationSourceType = MigrationSource::class;
  protected $migrationSourceDataType = '';
  /**
   * @var string
   */
  public $name;
  /**
   * @var string
   */
  public $network;
  /**
   * @var NetworkConfig
   */
  public $networkConfig;
  protected $networkConfigType = NetworkConfig::class;
  protected $networkConfigDataType = '';
  /**
   * @var PrimaryConfig
   */
  public $primaryConfig;
  protected $primaryConfigType = PrimaryConfig::class;
  protected $primaryConfigDataType = '';
  /**
   * @var bool
   */
  public $reconciling;
  /**
   * @var bool
   */
  public $satisfiesPzs;
  /**
   * @var SecondaryConfig
   */
  public $secondaryConfig;
  protected $secondaryConfigType = SecondaryConfig::class;
  protected $secondaryConfigDataType = '';
  /**
   * @var SslConfig
   */
  public $sslConfig;
  protected $sslConfigType = SslConfig::class;
  protected $sslConfigDataType = '';
  /**
   * @var string
   */
  public $state;
  /**
   * @var string
   */
  public $uid;
  /**
   * @var string
   */
  public $updateTime;

  /**
   * @param string[]
   */
  public function setAnnotations($annotations)
  {
    $this->annotations = $annotations;
  }
  /**
   * @return string[]
   */
  public function getAnnotations()
  {
    return $this->annotations;
  }
  /**
   * @param AutomatedBackupPolicy
   */
  public function setAutomatedBackupPolicy(AutomatedBackupPolicy $automatedBackupPolicy)
  {
    $this->automatedBackupPolicy = $automatedBackupPolicy;
  }
  /**
   * @return AutomatedBackupPolicy
   */
  public function getAutomatedBackupPolicy()
  {
    return $this->automatedBackupPolicy;
  }
  /**
   * @param BackupSource
   */
  public function setBackupSource(BackupSource $backupSource)
  {
    $this->backupSource = $backupSource;
  }
  /**
   * @return BackupSource
   */
  public function getBackupSource()
  {
    return $this->backupSource;
  }
  /**
   * @param string
   */
  public function setClusterType($clusterType)
  {
    $this->clusterType = $clusterType;
  }
  /**
   * @return string
   */
  public function getClusterType()
  {
    return $this->clusterType;
  }
  /**
   * @param ContinuousBackupConfig
   */
  public function setContinuousBackupConfig(ContinuousBackupConfig $continuousBackupConfig)
  {
    $this->continuousBackupConfig = $continuousBackupConfig;
  }
  /**
   * @return ContinuousBackupConfig
   */
  public function getContinuousBackupConfig()
  {
    return $this->continuousBackupConfig;
  }
  /**
   * @param ContinuousBackupInfo
   */
  public function setContinuousBackupInfo(ContinuousBackupInfo $continuousBackupInfo)
  {
    $this->continuousBackupInfo = $continuousBackupInfo;
  }
  /**
   * @return ContinuousBackupInfo
   */
  public function getContinuousBackupInfo()
  {
    return $this->continuousBackupInfo;
  }
  /**
   * @param string
   */
  public function setCreateTime($createTime)
  {
    $this->createTime = $createTime;
  }
  /**
   * @return string
   */
  public function getCreateTime()
  {
    return $this->createTime;
  }
  /**
   * @param string
   */
  public function setDatabaseVersion($databaseVersion)
  {
    $this->databaseVersion = $databaseVersion;
  }
  /**
   * @return string
   */
  public function getDatabaseVersion()
  {
    return $this->databaseVersion;
  }
  /**
   * @param string
   */
  public function setDeleteTime($deleteTime)
  {
    $this->deleteTime = $deleteTime;
  }
  /**
   * @return string
   */
  public function getDeleteTime()
  {
    return $this->deleteTime;
  }
  /**
   * @param string
   */
  public function setDisplayName($displayName)
  {
    $this->displayName = $displayName;
  }
  /**
   * @return string
   */
  public function getDisplayName()
  {
    return $this->displayName;
  }
  /**
   * @param EncryptionConfig
   */
  public function setEncryptionConfig(EncryptionConfig $encryptionConfig)
  {
    $this->encryptionConfig = $encryptionConfig;
  }
  /**
   * @return EncryptionConfig
   */
  public function getEncryptionConfig()
  {
    return $this->encryptionConfig;
  }
  /**
   * @param EncryptionInfo
   */
  public function setEncryptionInfo(EncryptionInfo $encryptionInfo)
  {
    $this->encryptionInfo = $encryptionInfo;
  }
  /**
   * @return EncryptionInfo
   */
  public function getEncryptionInfo()
  {
    return $this->encryptionInfo;
  }
  /**
   * @param string
   */
  public function setEtag($etag)
  {
    $this->etag = $etag;
  }
  /**
   * @return string
   */
  public function getEtag()
  {
    return $this->etag;
  }
  /**
   * @param UserPassword
   */
  public function setInitialUser(UserPassword $initialUser)
  {
    $this->initialUser = $initialUser;
  }
  /**
   * @return UserPassword
   */
  public function getInitialUser()
  {
    return $this->initialUser;
  }
  /**
   * @param string[]
   */
  public function setLabels($labels)
  {
    $this->labels = $labels;
  }
  /**
   * @return string[]
   */
  public function getLabels()
  {
    return $this->labels;
  }
  /**
   * @param MigrationSource
   */
  public function setMigrationSource(MigrationSource $migrationSource)
  {
    $this->migrationSource = $migrationSource;
  }
  /**
   * @return MigrationSource
   */
  public function getMigrationSource()
  {
    return $this->migrationSource;
  }
  /**
   * @param string
   */
  public function setName($name)
  {
    $this->name = $name;
  }
  /**
   * @return string
   */
  public function getName()
  {
    return $this->name;
  }
  /**
   * @param string
   */
  public function setNetwork($network)
  {
    $this->network = $network;
  }
  /**
   * @return string
   */
  public function getNetwork()
  {
    return $this->network;
  }
  /**
   * @param NetworkConfig
   */
  public function setNetworkConfig(NetworkConfig $networkConfig)
  {
    $this->networkConfig = $networkConfig;
  }
  /**
   * @return NetworkConfig
   */
  public function getNetworkConfig()
  {
    return $this->networkConfig;
  }
  /**
   * @param PrimaryConfig
   */
  public function setPrimaryConfig(PrimaryConfig $primaryConfig)
  {
    $this->primaryConfig = $primaryConfig;
  }
  /**
   * @return PrimaryConfig
   */
  public function getPrimaryConfig()
  {
    return $this->primaryConfig;
  }
  /**
   * @param bool
   */
  public function setReconciling($reconciling)
  {
    $this->reconciling = $reconciling;
  }
  /**
   * @return bool
   */
  public function getReconciling()
  {
    return $this->reconciling;
  }
  /**
   * @param bool
   */
  public function setSatisfiesPzs($satisfiesPzs)
  {
    $this->satisfiesPzs = $satisfiesPzs;
  }
  /**
   * @return bool
   */
  public function getSatisfiesPzs()
  {
    return $this->satisfiesPzs;
  }
  /**
   * @param SecondaryConfig
   */
  public function setSecondaryConfig(SecondaryConfig $secondaryConfig)
  {
    $this->secondaryConfig = $secondaryConfig;
  }
  /**
   * @return SecondaryConfig
   */
  public function getSecondaryConfig()
  {
    return $this->secondaryConfig;
  }
  /**
   * @param SslConfig
   */
  public function setSslConfig(SslConfig $sslConfig)
  {
    $this->sslConfig = $sslConfig;
  }
  /**
   * @return SslConfig
   */
  public function getSslConfig()
  {
    return $this->sslConfig;
  }
  /**
   * @param string
   */
  public function setState($state)
  {
    $this->state = $state;
  }
  /**
   * @return string
   */
  public function getState()
  {
    return $this->state;
  }
  /**
   * @param string
   */
  public function setUid($uid)
  {
    $this->uid = $uid;
  }
  /**
   * @return string
   */
  public function getUid()
  {
    return $this->uid;
  }
  /**
   * @param string
   */
  public function setUpdateTime($updateTime)
  {
    $this->updateTime = $updateTime;
  }
  /**
   * @return string
   */
  public function getUpdateTime()
  {
    return $this->updateTime;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(Cluster::class, 'Google_Service_CloudAlloyDBAdmin_Cluster');
