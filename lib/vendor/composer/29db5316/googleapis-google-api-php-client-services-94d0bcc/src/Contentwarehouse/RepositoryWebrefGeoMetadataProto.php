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

class RepositoryWebrefGeoMetadataProto extends \Google\Collection
{
  protected $collection_key = 'wpLocation';
  /**
   * @var GeostoreAddressProto
   */
  public $address;
  protected $addressType = GeostoreAddressProto::class;
  protected $addressDataType = '';
  /**
   * @var RepositoryWebrefGeoMetadataProtoAddressSynonym[]
   */
  public $addressSynonyms;
  protected $addressSynonymsType = RepositoryWebrefGeoMetadataProtoAddressSynonym::class;
  protected $addressSynonymsDataType = 'array';
  public $areaKm2;
  /**
   * @var GeostoreRectProto
   */
  public $bound;
  protected $boundType = GeostoreRectProto::class;
  protected $boundDataType = '';
  /**
   * @var string
   */
  public $countryCode;
  /**
   * @var GeostorePointProto
   */
  public $location;
  protected $locationType = GeostorePointProto::class;
  protected $locationDataType = '';
  /**
   * @var GeostoreNameProto
   */
  public $name;
  protected $nameType = GeostoreNameProto::class;
  protected $nameDataType = '';
  /**
   * @var GeostoreFeatureIdProto
   */
  public $oysterId;
  protected $oysterIdType = GeostoreFeatureIdProto::class;
  protected $oysterIdDataType = '';
  /**
   * @var int
   */
  public $stableIntegerCountryCode;
  /**
   * @var string
   */
  public $timezone;
  /**
   * @var RepositoryWebrefWikipediaGeocode[]
   */
  public $wpLocation;
  protected $wpLocationType = RepositoryWebrefWikipediaGeocode::class;
  protected $wpLocationDataType = 'array';

  /**
   * @param GeostoreAddressProto
   */
  public function setAddress(GeostoreAddressProto $address)
  {
    $this->address = $address;
  }
  /**
   * @return GeostoreAddressProto
   */
  public function getAddress()
  {
    return $this->address;
  }
  /**
   * @param RepositoryWebrefGeoMetadataProtoAddressSynonym[]
   */
  public function setAddressSynonyms($addressSynonyms)
  {
    $this->addressSynonyms = $addressSynonyms;
  }
  /**
   * @return RepositoryWebrefGeoMetadataProtoAddressSynonym[]
   */
  public function getAddressSynonyms()
  {
    return $this->addressSynonyms;
  }
  public function setAreaKm2($areaKm2)
  {
    $this->areaKm2 = $areaKm2;
  }
  public function getAreaKm2()
  {
    return $this->areaKm2;
  }
  /**
   * @param GeostoreRectProto
   */
  public function setBound(GeostoreRectProto $bound)
  {
    $this->bound = $bound;
  }
  /**
   * @return GeostoreRectProto
   */
  public function getBound()
  {
    return $this->bound;
  }
  /**
   * @param string
   */
  public function setCountryCode($countryCode)
  {
    $this->countryCode = $countryCode;
  }
  /**
   * @return string
   */
  public function getCountryCode()
  {
    return $this->countryCode;
  }
  /**
   * @param GeostorePointProto
   */
  public function setLocation(GeostorePointProto $location)
  {
    $this->location = $location;
  }
  /**
   * @return GeostorePointProto
   */
  public function getLocation()
  {
    return $this->location;
  }
  /**
   * @param GeostoreNameProto
   */
  public function setName(GeostoreNameProto $name)
  {
    $this->name = $name;
  }
  /**
   * @return GeostoreNameProto
   */
  public function getName()
  {
    return $this->name;
  }
  /**
   * @param GeostoreFeatureIdProto
   */
  public function setOysterId(GeostoreFeatureIdProto $oysterId)
  {
    $this->oysterId = $oysterId;
  }
  /**
   * @return GeostoreFeatureIdProto
   */
  public function getOysterId()
  {
    return $this->oysterId;
  }
  /**
   * @param int
   */
  public function setStableIntegerCountryCode($stableIntegerCountryCode)
  {
    $this->stableIntegerCountryCode = $stableIntegerCountryCode;
  }
  /**
   * @return int
   */
  public function getStableIntegerCountryCode()
  {
    return $this->stableIntegerCountryCode;
  }
  /**
   * @param string
   */
  public function setTimezone($timezone)
  {
    $this->timezone = $timezone;
  }
  /**
   * @return string
   */
  public function getTimezone()
  {
    return $this->timezone;
  }
  /**
   * @param RepositoryWebrefWikipediaGeocode[]
   */
  public function setWpLocation($wpLocation)
  {
    $this->wpLocation = $wpLocation;
  }
  /**
   * @return RepositoryWebrefWikipediaGeocode[]
   */
  public function getWpLocation()
  {
    return $this->wpLocation;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(RepositoryWebrefGeoMetadataProto::class, 'Google_Service_Contentwarehouse_RepositoryWebrefGeoMetadataProto');
