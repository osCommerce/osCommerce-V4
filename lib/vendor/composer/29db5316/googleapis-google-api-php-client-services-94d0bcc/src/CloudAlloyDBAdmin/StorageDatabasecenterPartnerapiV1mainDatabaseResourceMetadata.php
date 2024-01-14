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

class StorageDatabasecenterPartnerapiV1mainDatabaseResourceMetadata extends \Google\Model
{
  /**
   * @var StorageDatabasecenterPartnerapiV1mainAvailabilityConfiguration
   */
  public $availabilityConfiguration;
  protected $availabilityConfigurationType = StorageDatabasecenterPartnerapiV1mainAvailabilityConfiguration::class;
  protected $availabilityConfigurationDataType = '';
  /**
   * @var StorageDatabasecenterPartnerapiV1mainBackupConfiguration
   */
  public $backupConfiguration;
  protected $backupConfigurationType = StorageDatabasecenterPartnerapiV1mainBackupConfiguration::class;
  protected $backupConfigurationDataType = '';
  /**
   * @var StorageDatabasecenterPartnerapiV1mainBackupRun
   */
  public $backupRun;
  protected $backupRunType = StorageDatabasecenterPartnerapiV1mainBackupRun::class;
  protected $backupRunDataType = '';
  /**
   * @var string
   */
  public $creationTime;
  /**
   * @var string
   */
  public $currentState;
  /**
   * @var array[]
   */
  public $customMetadata;
  /**
   * @var string
   */
  public $expectedState;
  /**
   * @var StorageDatabasecenterPartnerapiV1mainDatabaseResourceId
   */
  public $id;
  protected $idType = StorageDatabasecenterPartnerapiV1mainDatabaseResourceId::class;
  protected $idDataType = '';
  /**
   * @var string
   */
  public $instanceType;
  /**
   * @var string
   */
  public $location;
  /**
   * @var StorageDatabasecenterPartnerapiV1mainDatabaseResourceId
   */
  public $primaryResourceId;
  protected $primaryResourceIdType = StorageDatabasecenterPartnerapiV1mainDatabaseResourceId::class;
  protected $primaryResourceIdDataType = '';
  /**
   * @var StorageDatabasecenterProtoCommonProduct
   */
  public $product;
  protected $productType = StorageDatabasecenterProtoCommonProduct::class;
  protected $productDataType = '';
  /**
   * @var string
   */
  public $resourceContainer;
  /**
   * @var string
   */
  public $resourceName;
  /**
   * @var string
   */
  public $updationTime;
  /**
   * @var string[]
   */
  public $userLabels;

  /**
   * @param StorageDatabasecenterPartnerapiV1mainAvailabilityConfiguration
   */
  public function setAvailabilityConfiguration(StorageDatabasecenterPartnerapiV1mainAvailabilityConfiguration $availabilityConfiguration)
  {
    $this->availabilityConfiguration = $availabilityConfiguration;
  }
  /**
   * @return StorageDatabasecenterPartnerapiV1mainAvailabilityConfiguration
   */
  public function getAvailabilityConfiguration()
  {
    return $this->availabilityConfiguration;
  }
  /**
   * @param StorageDatabasecenterPartnerapiV1mainBackupConfiguration
   */
  public function setBackupConfiguration(StorageDatabasecenterPartnerapiV1mainBackupConfiguration $backupConfiguration)
  {
    $this->backupConfiguration = $backupConfiguration;
  }
  /**
   * @return StorageDatabasecenterPartnerapiV1mainBackupConfiguration
   */
  public function getBackupConfiguration()
  {
    return $this->backupConfiguration;
  }
  /**
   * @param StorageDatabasecenterPartnerapiV1mainBackupRun
   */
  public function setBackupRun(StorageDatabasecenterPartnerapiV1mainBackupRun $backupRun)
  {
    $this->backupRun = $backupRun;
  }
  /**
   * @return StorageDatabasecenterPartnerapiV1mainBackupRun
   */
  public function getBackupRun()
  {
    return $this->backupRun;
  }
  /**
   * @param string
   */
  public function setCreationTime($creationTime)
  {
    $this->creationTime = $creationTime;
  }
  /**
   * @return string
   */
  public function getCreationTime()
  {
    return $this->creationTime;
  }
  /**
   * @param string
   */
  public function setCurrentState($currentState)
  {
    $this->currentState = $currentState;
  }
  /**
   * @return string
   */
  public function getCurrentState()
  {
    return $this->currentState;
  }
  /**
   * @param array[]
   */
  public function setCustomMetadata($customMetadata)
  {
    $this->customMetadata = $customMetadata;
  }
  /**
   * @return array[]
   */
  public function getCustomMetadata()
  {
    return $this->customMetadata;
  }
  /**
   * @param string
   */
  public function setExpectedState($expectedState)
  {
    $this->expectedState = $expectedState;
  }
  /**
   * @return string
   */
  public function getExpectedState()
  {
    return $this->expectedState;
  }
  /**
   * @param StorageDatabasecenterPartnerapiV1mainDatabaseResourceId
   */
  public function setId(StorageDatabasecenterPartnerapiV1mainDatabaseResourceId $id)
  {
    $this->id = $id;
  }
  /**
   * @return StorageDatabasecenterPartnerapiV1mainDatabaseResourceId
   */
  public function getId()
  {
    return $this->id;
  }
  /**
   * @param string
   */
  public function setInstanceType($instanceType)
  {
    $this->instanceType = $instanceType;
  }
  /**
   * @return string
   */
  public function getInstanceType()
  {
    return $this->instanceType;
  }
  /**
   * @param string
   */
  public function setLocation($location)
  {
    $this->location = $location;
  }
  /**
   * @return string
   */
  public function getLocation()
  {
    return $this->location;
  }
  /**
   * @param StorageDatabasecenterPartnerapiV1mainDatabaseResourceId
   */
  public function setPrimaryResourceId(StorageDatabasecenterPartnerapiV1mainDatabaseResourceId $primaryResourceId)
  {
    $this->primaryResourceId = $primaryResourceId;
  }
  /**
   * @return StorageDatabasecenterPartnerapiV1mainDatabaseResourceId
   */
  public function getPrimaryResourceId()
  {
    return $this->primaryResourceId;
  }
  /**
   * @param StorageDatabasecenterProtoCommonProduct
   */
  public function setProduct(StorageDatabasecenterProtoCommonProduct $product)
  {
    $this->product = $product;
  }
  /**
   * @return StorageDatabasecenterProtoCommonProduct
   */
  public function getProduct()
  {
    return $this->product;
  }
  /**
   * @param string
   */
  public function setResourceContainer($resourceContainer)
  {
    $this->resourceContainer = $resourceContainer;
  }
  /**
   * @return string
   */
  public function getResourceContainer()
  {
    return $this->resourceContainer;
  }
  /**
   * @param string
   */
  public function setResourceName($resourceName)
  {
    $this->resourceName = $resourceName;
  }
  /**
   * @return string
   */
  public function getResourceName()
  {
    return $this->resourceName;
  }
  /**
   * @param string
   */
  public function setUpdationTime($updationTime)
  {
    $this->updationTime = $updationTime;
  }
  /**
   * @return string
   */
  public function getUpdationTime()
  {
    return $this->updationTime;
  }
  /**
   * @param string[]
   */
  public function setUserLabels($userLabels)
  {
    $this->userLabels = $userLabels;
  }
  /**
   * @return string[]
   */
  public function getUserLabels()
  {
    return $this->userLabels;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(StorageDatabasecenterPartnerapiV1mainDatabaseResourceMetadata::class, 'Google_Service_CloudAlloyDBAdmin_StorageDatabasecenterPartnerapiV1mainDatabaseResourceMetadata');
