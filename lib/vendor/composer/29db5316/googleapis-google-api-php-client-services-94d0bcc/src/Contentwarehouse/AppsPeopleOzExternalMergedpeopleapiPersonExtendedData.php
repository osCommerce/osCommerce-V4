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

class AppsPeopleOzExternalMergedpeopleapiPersonExtendedData extends \Google\Collection
{
  protected $collection_key = 'domainName';
  /**
   * @var AppsPeopleOzExternalMergedpeopleapiAboutMeExtendedData
   */
  public $aboutMeExtendedData;
  protected $aboutMeExtendedDataType = AppsPeopleOzExternalMergedpeopleapiAboutMeExtendedData::class;
  protected $aboutMeExtendedDataDataType = '';
  /**
   * @var SocialGraphWireProtoPeopleapiExtensionAppsWaldoExtendedData
   */
  public $appsWaldoExtendedData;
  protected $appsWaldoExtendedDataType = SocialGraphWireProtoPeopleapiExtensionAppsWaldoExtendedData::class;
  protected $appsWaldoExtendedDataDataType = '';
  /**
   * @var AppsPeopleOzExternalMergedpeopleapiCallerIdExtendedData
   */
  public $callerIdExtendedData;
  protected $callerIdExtendedDataType = AppsPeopleOzExternalMergedpeopleapiCallerIdExtendedData::class;
  protected $callerIdExtendedDataDataType = '';
  /**
   * @var AppsPeopleOzExternalMergedpeopleapiWebContactsExtendedData
   */
  public $contactsExtendedData;
  protected $contactsExtendedDataType = AppsPeopleOzExternalMergedpeopleapiWebContactsExtendedData::class;
  protected $contactsExtendedDataDataType = '';
  /**
   * @var string[]
   */
  public $domainName;
  /**
   * @var SocialGraphWireProtoPeopleapiExtensionDynamiteExtendedData
   */
  public $dynamiteExtendedData;
  protected $dynamiteExtendedDataType = SocialGraphWireProtoPeopleapiExtensionDynamiteExtendedData::class;
  protected $dynamiteExtendedDataDataType = '';
  /**
   * @var AppsPeopleOzExternalMergedpeopleapiGPayExtendedData
   */
  public $gpayExtendedData;
  protected $gpayExtendedDataType = AppsPeopleOzExternalMergedpeopleapiGPayExtendedData::class;
  protected $gpayExtendedDataDataType = '';
  /**
   * @var AppsPeopleOzExternalMergedpeopleapiGplusExtendedData
   */
  public $gplusExtendedData;
  protected $gplusExtendedDataType = AppsPeopleOzExternalMergedpeopleapiGplusExtendedData::class;
  protected $gplusExtendedDataDataType = '';
  /**
   * @var AppsPeopleOzExternalMergedpeopleapiHangoutsExtendedData
   */
  public $hangoutsExtendedData;
  protected $hangoutsExtendedDataType = AppsPeopleOzExternalMergedpeopleapiHangoutsExtendedData::class;
  protected $hangoutsExtendedDataDataType = '';
  /**
   * @var bool
   */
  public $isPlaceholder;
  /**
   * @var AppsPeopleOzExternalMergedpeopleapiMapsExtendedData
   */
  public $mapsExtendedData;
  protected $mapsExtendedDataType = AppsPeopleOzExternalMergedpeopleapiMapsExtendedData::class;
  protected $mapsExtendedDataDataType = '';
  /**
   * @var SocialGraphWireProtoPeopleapiExtensionPaisaExtendedData
   */
  public $paisaExtendedData;
  protected $paisaExtendedDataType = SocialGraphWireProtoPeopleapiExtensionPaisaExtendedData::class;
  protected $paisaExtendedDataDataType = '';
  /**
   * @var SocialGraphWireProtoPeopleapiExtensionPeopleStackExtendedData
   */
  public $peopleStackExtendedData;
  protected $peopleStackExtendedDataType = SocialGraphWireProtoPeopleapiExtensionPeopleStackExtendedData::class;
  protected $peopleStackExtendedDataDataType = '';
  /**
   * @var SocialGraphWireProtoPeopleapiExtensionPeopleStackPersonExtendedData
   */
  public $peopleStackPersonExtendedData;
  protected $peopleStackPersonExtendedDataType = SocialGraphWireProtoPeopleapiExtensionPeopleStackPersonExtendedData::class;
  protected $peopleStackPersonExtendedDataDataType = '';
  /**
   * @var AppsPeopleOzExternalMergedpeopleapiPlayGamesExtendedData
   */
  public $playGamesExtendedData;
  protected $playGamesExtendedDataType = AppsPeopleOzExternalMergedpeopleapiPlayGamesExtendedData::class;
  protected $playGamesExtendedDataDataType = '';
  /**
   * @var bool
   */
  public $tlsIsPlaceholder;
  /**
   * @var AppsPeopleOzExternalMergedpeopleapiYoutubeExtendedData
   */
  public $youtubeExtendedData;
  protected $youtubeExtendedDataType = AppsPeopleOzExternalMergedpeopleapiYoutubeExtendedData::class;
  protected $youtubeExtendedDataDataType = '';

  /**
   * @param AppsPeopleOzExternalMergedpeopleapiAboutMeExtendedData
   */
  public function setAboutMeExtendedData(AppsPeopleOzExternalMergedpeopleapiAboutMeExtendedData $aboutMeExtendedData)
  {
    $this->aboutMeExtendedData = $aboutMeExtendedData;
  }
  /**
   * @return AppsPeopleOzExternalMergedpeopleapiAboutMeExtendedData
   */
  public function getAboutMeExtendedData()
  {
    return $this->aboutMeExtendedData;
  }
  /**
   * @param SocialGraphWireProtoPeopleapiExtensionAppsWaldoExtendedData
   */
  public function setAppsWaldoExtendedData(SocialGraphWireProtoPeopleapiExtensionAppsWaldoExtendedData $appsWaldoExtendedData)
  {
    $this->appsWaldoExtendedData = $appsWaldoExtendedData;
  }
  /**
   * @return SocialGraphWireProtoPeopleapiExtensionAppsWaldoExtendedData
   */
  public function getAppsWaldoExtendedData()
  {
    return $this->appsWaldoExtendedData;
  }
  /**
   * @param AppsPeopleOzExternalMergedpeopleapiCallerIdExtendedData
   */
  public function setCallerIdExtendedData(AppsPeopleOzExternalMergedpeopleapiCallerIdExtendedData $callerIdExtendedData)
  {
    $this->callerIdExtendedData = $callerIdExtendedData;
  }
  /**
   * @return AppsPeopleOzExternalMergedpeopleapiCallerIdExtendedData
   */
  public function getCallerIdExtendedData()
  {
    return $this->callerIdExtendedData;
  }
  /**
   * @param AppsPeopleOzExternalMergedpeopleapiWebContactsExtendedData
   */
  public function setContactsExtendedData(AppsPeopleOzExternalMergedpeopleapiWebContactsExtendedData $contactsExtendedData)
  {
    $this->contactsExtendedData = $contactsExtendedData;
  }
  /**
   * @return AppsPeopleOzExternalMergedpeopleapiWebContactsExtendedData
   */
  public function getContactsExtendedData()
  {
    return $this->contactsExtendedData;
  }
  /**
   * @param string[]
   */
  public function setDomainName($domainName)
  {
    $this->domainName = $domainName;
  }
  /**
   * @return string[]
   */
  public function getDomainName()
  {
    return $this->domainName;
  }
  /**
   * @param SocialGraphWireProtoPeopleapiExtensionDynamiteExtendedData
   */
  public function setDynamiteExtendedData(SocialGraphWireProtoPeopleapiExtensionDynamiteExtendedData $dynamiteExtendedData)
  {
    $this->dynamiteExtendedData = $dynamiteExtendedData;
  }
  /**
   * @return SocialGraphWireProtoPeopleapiExtensionDynamiteExtendedData
   */
  public function getDynamiteExtendedData()
  {
    return $this->dynamiteExtendedData;
  }
  /**
   * @param AppsPeopleOzExternalMergedpeopleapiGPayExtendedData
   */
  public function setGpayExtendedData(AppsPeopleOzExternalMergedpeopleapiGPayExtendedData $gpayExtendedData)
  {
    $this->gpayExtendedData = $gpayExtendedData;
  }
  /**
   * @return AppsPeopleOzExternalMergedpeopleapiGPayExtendedData
   */
  public function getGpayExtendedData()
  {
    return $this->gpayExtendedData;
  }
  /**
   * @param AppsPeopleOzExternalMergedpeopleapiGplusExtendedData
   */
  public function setGplusExtendedData(AppsPeopleOzExternalMergedpeopleapiGplusExtendedData $gplusExtendedData)
  {
    $this->gplusExtendedData = $gplusExtendedData;
  }
  /**
   * @return AppsPeopleOzExternalMergedpeopleapiGplusExtendedData
   */
  public function getGplusExtendedData()
  {
    return $this->gplusExtendedData;
  }
  /**
   * @param AppsPeopleOzExternalMergedpeopleapiHangoutsExtendedData
   */
  public function setHangoutsExtendedData(AppsPeopleOzExternalMergedpeopleapiHangoutsExtendedData $hangoutsExtendedData)
  {
    $this->hangoutsExtendedData = $hangoutsExtendedData;
  }
  /**
   * @return AppsPeopleOzExternalMergedpeopleapiHangoutsExtendedData
   */
  public function getHangoutsExtendedData()
  {
    return $this->hangoutsExtendedData;
  }
  /**
   * @param bool
   */
  public function setIsPlaceholder($isPlaceholder)
  {
    $this->isPlaceholder = $isPlaceholder;
  }
  /**
   * @return bool
   */
  public function getIsPlaceholder()
  {
    return $this->isPlaceholder;
  }
  /**
   * @param AppsPeopleOzExternalMergedpeopleapiMapsExtendedData
   */
  public function setMapsExtendedData(AppsPeopleOzExternalMergedpeopleapiMapsExtendedData $mapsExtendedData)
  {
    $this->mapsExtendedData = $mapsExtendedData;
  }
  /**
   * @return AppsPeopleOzExternalMergedpeopleapiMapsExtendedData
   */
  public function getMapsExtendedData()
  {
    return $this->mapsExtendedData;
  }
  /**
   * @param SocialGraphWireProtoPeopleapiExtensionPaisaExtendedData
   */
  public function setPaisaExtendedData(SocialGraphWireProtoPeopleapiExtensionPaisaExtendedData $paisaExtendedData)
  {
    $this->paisaExtendedData = $paisaExtendedData;
  }
  /**
   * @return SocialGraphWireProtoPeopleapiExtensionPaisaExtendedData
   */
  public function getPaisaExtendedData()
  {
    return $this->paisaExtendedData;
  }
  /**
   * @param SocialGraphWireProtoPeopleapiExtensionPeopleStackExtendedData
   */
  public function setPeopleStackExtendedData(SocialGraphWireProtoPeopleapiExtensionPeopleStackExtendedData $peopleStackExtendedData)
  {
    $this->peopleStackExtendedData = $peopleStackExtendedData;
  }
  /**
   * @return SocialGraphWireProtoPeopleapiExtensionPeopleStackExtendedData
   */
  public function getPeopleStackExtendedData()
  {
    return $this->peopleStackExtendedData;
  }
  /**
   * @param SocialGraphWireProtoPeopleapiExtensionPeopleStackPersonExtendedData
   */
  public function setPeopleStackPersonExtendedData(SocialGraphWireProtoPeopleapiExtensionPeopleStackPersonExtendedData $peopleStackPersonExtendedData)
  {
    $this->peopleStackPersonExtendedData = $peopleStackPersonExtendedData;
  }
  /**
   * @return SocialGraphWireProtoPeopleapiExtensionPeopleStackPersonExtendedData
   */
  public function getPeopleStackPersonExtendedData()
  {
    return $this->peopleStackPersonExtendedData;
  }
  /**
   * @param AppsPeopleOzExternalMergedpeopleapiPlayGamesExtendedData
   */
  public function setPlayGamesExtendedData(AppsPeopleOzExternalMergedpeopleapiPlayGamesExtendedData $playGamesExtendedData)
  {
    $this->playGamesExtendedData = $playGamesExtendedData;
  }
  /**
   * @return AppsPeopleOzExternalMergedpeopleapiPlayGamesExtendedData
   */
  public function getPlayGamesExtendedData()
  {
    return $this->playGamesExtendedData;
  }
  /**
   * @param bool
   */
  public function setTlsIsPlaceholder($tlsIsPlaceholder)
  {
    $this->tlsIsPlaceholder = $tlsIsPlaceholder;
  }
  /**
   * @return bool
   */
  public function getTlsIsPlaceholder()
  {
    return $this->tlsIsPlaceholder;
  }
  /**
   * @param AppsPeopleOzExternalMergedpeopleapiYoutubeExtendedData
   */
  public function setYoutubeExtendedData(AppsPeopleOzExternalMergedpeopleapiYoutubeExtendedData $youtubeExtendedData)
  {
    $this->youtubeExtendedData = $youtubeExtendedData;
  }
  /**
   * @return AppsPeopleOzExternalMergedpeopleapiYoutubeExtendedData
   */
  public function getYoutubeExtendedData()
  {
    return $this->youtubeExtendedData;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(AppsPeopleOzExternalMergedpeopleapiPersonExtendedData::class, 'Google_Service_Contentwarehouse_AppsPeopleOzExternalMergedpeopleapiPersonExtendedData');
