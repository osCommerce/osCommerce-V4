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

namespace Google\Service\Aiplatform;

class GoogleCloudAiplatformV1IndexDatapoint extends \Google\Collection
{
  protected $collection_key = 'restricts';
  /**
   * @var GoogleCloudAiplatformV1IndexDatapointCrowdingTag
   */
  public $crowdingTag;
  protected $crowdingTagType = GoogleCloudAiplatformV1IndexDatapointCrowdingTag::class;
  protected $crowdingTagDataType = '';
  /**
   * @var string
   */
  public $datapointId;
  /**
   * @var float[]
   */
  public $featureVector;
  /**
   * @var GoogleCloudAiplatformV1IndexDatapointNumericRestriction[]
   */
  public $numericRestricts;
  protected $numericRestrictsType = GoogleCloudAiplatformV1IndexDatapointNumericRestriction::class;
  protected $numericRestrictsDataType = 'array';
  /**
   * @var GoogleCloudAiplatformV1IndexDatapointRestriction[]
   */
  public $restricts;
  protected $restrictsType = GoogleCloudAiplatformV1IndexDatapointRestriction::class;
  protected $restrictsDataType = 'array';

  /**
   * @param GoogleCloudAiplatformV1IndexDatapointCrowdingTag
   */
  public function setCrowdingTag(GoogleCloudAiplatformV1IndexDatapointCrowdingTag $crowdingTag)
  {
    $this->crowdingTag = $crowdingTag;
  }
  /**
   * @return GoogleCloudAiplatformV1IndexDatapointCrowdingTag
   */
  public function getCrowdingTag()
  {
    return $this->crowdingTag;
  }
  /**
   * @param string
   */
  public function setDatapointId($datapointId)
  {
    $this->datapointId = $datapointId;
  }
  /**
   * @return string
   */
  public function getDatapointId()
  {
    return $this->datapointId;
  }
  /**
   * @param float[]
   */
  public function setFeatureVector($featureVector)
  {
    $this->featureVector = $featureVector;
  }
  /**
   * @return float[]
   */
  public function getFeatureVector()
  {
    return $this->featureVector;
  }
  /**
   * @param GoogleCloudAiplatformV1IndexDatapointNumericRestriction[]
   */
  public function setNumericRestricts($numericRestricts)
  {
    $this->numericRestricts = $numericRestricts;
  }
  /**
   * @return GoogleCloudAiplatformV1IndexDatapointNumericRestriction[]
   */
  public function getNumericRestricts()
  {
    return $this->numericRestricts;
  }
  /**
   * @param GoogleCloudAiplatformV1IndexDatapointRestriction[]
   */
  public function setRestricts($restricts)
  {
    $this->restricts = $restricts;
  }
  /**
   * @return GoogleCloudAiplatformV1IndexDatapointRestriction[]
   */
  public function getRestricts()
  {
    return $this->restricts;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleCloudAiplatformV1IndexDatapoint::class, 'Google_Service_Aiplatform_GoogleCloudAiplatformV1IndexDatapoint');
