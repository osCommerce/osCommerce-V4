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

class GeostoreGeopoliticalProto extends \Google\Collection
{
  protected $collection_key = 'regionSpecificName';
  /**
   * @var string
   */
  public $conveysAttributionTo;
  /**
   * @var GeostoreRegionSpecificNameProto[]
   */
  public $regionSpecificName;
  protected $regionSpecificNameType = GeostoreRegionSpecificNameProto::class;
  protected $regionSpecificNameDataType = 'array';

  /**
   * @param string
   */
  public function setConveysAttributionTo($conveysAttributionTo)
  {
    $this->conveysAttributionTo = $conveysAttributionTo;
  }
  /**
   * @return string
   */
  public function getConveysAttributionTo()
  {
    return $this->conveysAttributionTo;
  }
  /**
   * @param GeostoreRegionSpecificNameProto[]
   */
  public function setRegionSpecificName($regionSpecificName)
  {
    $this->regionSpecificName = $regionSpecificName;
  }
  /**
   * @return GeostoreRegionSpecificNameProto[]
   */
  public function getRegionSpecificName()
  {
    return $this->regionSpecificName;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GeostoreGeopoliticalProto::class, 'Google_Service_Contentwarehouse_GeostoreGeopoliticalProto');
