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

class GeostoreCityJsonProto extends \Google\Collection
{
  protected $collection_key = 'vertexXyz';
  /**
   * @var GeostoreCityJsonProtoCityObject[]
   */
  public $cityObjects;
  protected $cityObjectsType = GeostoreCityJsonProtoCityObject::class;
  protected $cityObjectsDataType = 'array';
  /**
   * @var GeostoreCityJsonProtoTransform
   */
  public $transform;
  protected $transformType = GeostoreCityJsonProtoTransform::class;
  protected $transformDataType = '';
  /**
   * @var int[]
   */
  public $vertexXyz;

  /**
   * @param GeostoreCityJsonProtoCityObject[]
   */
  public function setCityObjects($cityObjects)
  {
    $this->cityObjects = $cityObjects;
  }
  /**
   * @return GeostoreCityJsonProtoCityObject[]
   */
  public function getCityObjects()
  {
    return $this->cityObjects;
  }
  /**
   * @param GeostoreCityJsonProtoTransform
   */
  public function setTransform(GeostoreCityJsonProtoTransform $transform)
  {
    $this->transform = $transform;
  }
  /**
   * @return GeostoreCityJsonProtoTransform
   */
  public function getTransform()
  {
    return $this->transform;
  }
  /**
   * @param int[]
   */
  public function setVertexXyz($vertexXyz)
  {
    $this->vertexXyz = $vertexXyz;
  }
  /**
   * @return int[]
   */
  public function getVertexXyz()
  {
    return $this->vertexXyz;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GeostoreCityJsonProto::class, 'Google_Service_Contentwarehouse_GeostoreCityJsonProto');
