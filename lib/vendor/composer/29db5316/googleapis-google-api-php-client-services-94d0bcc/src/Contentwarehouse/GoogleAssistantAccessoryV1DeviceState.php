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

namespace Google\Service\Contentwarehouse;

class GoogleAssistantAccessoryV1DeviceState extends \Google\Collection
{
  protected $collection_key = 'unavailableSettings';
  /**
   * @var GoogleAssistantEmbeddedV1Alarms
   */
  public $alarmState;
  protected $alarmStateType = GoogleAssistantEmbeddedV1Alarms::class;
  protected $alarmStateDataType = '';
  /**
   * @var string
   */
  public $contextParams;
  /**
   * @var string
   */
  public $deviceTime;
  /**
   * @var GoogleTypeTimeZone
   */
  public $deviceTimeZone;
  protected $deviceTimeZoneType = GoogleTypeTimeZone::class;
  protected $deviceTimeZoneDataType = '';
  /**
   * @var bool
   */
  public $doNotDisturb;
  /**
   * @var GoogleAssistantEmbeddedV1FitnessActivities
   */
  public $fitnessActivitiesState;
  protected $fitnessActivitiesStateType = GoogleAssistantEmbeddedV1FitnessActivities::class;
  protected $fitnessActivitiesStateDataType = '';
  /**
   * @var GoogleAssistantAccessoryV1DeviceStateInstalledAppsState
   */
  public $installedApps;
  protected $installedAppsType = GoogleAssistantAccessoryV1DeviceStateInstalledAppsState::class;
  protected $installedAppsDataType = '';
  /**
   * @var string
   */
  public $installedAppsZlib;
  /**
   * @var GoogleAssistantEmbeddedV1Timers
   */
  public $timerState;
  protected $timerStateType = GoogleAssistantEmbeddedV1Timers::class;
  protected $timerStateDataType = '';
  /**
   * @var string[]
   */
  public $unavailableSettings;

  /**
   * @param GoogleAssistantEmbeddedV1Alarms
   */
  public function setAlarmState(GoogleAssistantEmbeddedV1Alarms $alarmState)
  {
    $this->alarmState = $alarmState;
  }
  /**
   * @return GoogleAssistantEmbeddedV1Alarms
   */
  public function getAlarmState()
  {
    return $this->alarmState;
  }
  /**
   * @param string
   */
  public function setContextParams($contextParams)
  {
    $this->contextParams = $contextParams;
  }
  /**
   * @return string
   */
  public function getContextParams()
  {
    return $this->contextParams;
  }
  /**
   * @param string
   */
  public function setDeviceTime($deviceTime)
  {
    $this->deviceTime = $deviceTime;
  }
  /**
   * @return string
   */
  public function getDeviceTime()
  {
    return $this->deviceTime;
  }
  /**
   * @param GoogleTypeTimeZone
   */
  public function setDeviceTimeZone(GoogleTypeTimeZone $deviceTimeZone)
  {
    $this->deviceTimeZone = $deviceTimeZone;
  }
  /**
   * @return GoogleTypeTimeZone
   */
  public function getDeviceTimeZone()
  {
    return $this->deviceTimeZone;
  }
  /**
   * @param bool
   */
  public function setDoNotDisturb($doNotDisturb)
  {
    $this->doNotDisturb = $doNotDisturb;
  }
  /**
   * @return bool
   */
  public function getDoNotDisturb()
  {
    return $this->doNotDisturb;
  }
  /**
   * @param GoogleAssistantEmbeddedV1FitnessActivities
   */
  public function setFitnessActivitiesState(GoogleAssistantEmbeddedV1FitnessActivities $fitnessActivitiesState)
  {
    $this->fitnessActivitiesState = $fitnessActivitiesState;
  }
  /**
   * @return GoogleAssistantEmbeddedV1FitnessActivities
   */
  public function getFitnessActivitiesState()
  {
    return $this->fitnessActivitiesState;
  }
  /**
   * @param GoogleAssistantAccessoryV1DeviceStateInstalledAppsState
   */
  public function setInstalledApps(GoogleAssistantAccessoryV1DeviceStateInstalledAppsState $installedApps)
  {
    $this->installedApps = $installedApps;
  }
  /**
   * @return GoogleAssistantAccessoryV1DeviceStateInstalledAppsState
   */
  public function getInstalledApps()
  {
    return $this->installedApps;
  }
  /**
   * @param string
   */
  public function setInstalledAppsZlib($installedAppsZlib)
  {
    $this->installedAppsZlib = $installedAppsZlib;
  }
  /**
   * @return string
   */
  public function getInstalledAppsZlib()
  {
    return $this->installedAppsZlib;
  }
  /**
   * @param GoogleAssistantEmbeddedV1Timers
   */
  public function setTimerState(GoogleAssistantEmbeddedV1Timers $timerState)
  {
    $this->timerState = $timerState;
  }
  /**
   * @return GoogleAssistantEmbeddedV1Timers
   */
  public function getTimerState()
  {
    return $this->timerState;
  }
  /**
   * @param string[]
   */
  public function setUnavailableSettings($unavailableSettings)
  {
    $this->unavailableSettings = $unavailableSettings;
  }
  /**
   * @return string[]
   */
  public function getUnavailableSettings()
  {
    return $this->unavailableSettings;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleAssistantAccessoryV1DeviceState::class, 'Google_Service_Contentwarehouse_GoogleAssistantAccessoryV1DeviceState');
