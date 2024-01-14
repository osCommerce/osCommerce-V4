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

class GoogleCloudAiplatformV1GenerateContentRequest extends \Google\Collection
{
  protected $collection_key = 'tools';
  /**
   * @var GoogleCloudAiplatformV1Content[]
   */
  public $contents;
  protected $contentsType = GoogleCloudAiplatformV1Content::class;
  protected $contentsDataType = 'array';
  /**
   * @var string
   */
  public $endpoint;
  /**
   * @var GoogleCloudAiplatformV1GenerationConfig
   */
  public $generationConfig;
  protected $generationConfigType = GoogleCloudAiplatformV1GenerationConfig::class;
  protected $generationConfigDataType = '';
  /**
   * @var GoogleCloudAiplatformV1SafetySetting[]
   */
  public $safetySettings;
  protected $safetySettingsType = GoogleCloudAiplatformV1SafetySetting::class;
  protected $safetySettingsDataType = 'array';
  /**
   * @var GoogleCloudAiplatformV1Tool[]
   */
  public $tools;
  protected $toolsType = GoogleCloudAiplatformV1Tool::class;
  protected $toolsDataType = 'array';

  /**
   * @param GoogleCloudAiplatformV1Content[]
   */
  public function setContents($contents)
  {
    $this->contents = $contents;
  }
  /**
   * @return GoogleCloudAiplatformV1Content[]
   */
  public function getContents()
  {
    return $this->contents;
  }
  /**
   * @param string
   */
  public function setEndpoint($endpoint)
  {
    $this->endpoint = $endpoint;
  }
  /**
   * @return string
   */
  public function getEndpoint()
  {
    return $this->endpoint;
  }
  /**
   * @param GoogleCloudAiplatformV1GenerationConfig
   */
  public function setGenerationConfig(GoogleCloudAiplatformV1GenerationConfig $generationConfig)
  {
    $this->generationConfig = $generationConfig;
  }
  /**
   * @return GoogleCloudAiplatformV1GenerationConfig
   */
  public function getGenerationConfig()
  {
    return $this->generationConfig;
  }
  /**
   * @param GoogleCloudAiplatformV1SafetySetting[]
   */
  public function setSafetySettings($safetySettings)
  {
    $this->safetySettings = $safetySettings;
  }
  /**
   * @return GoogleCloudAiplatformV1SafetySetting[]
   */
  public function getSafetySettings()
  {
    return $this->safetySettings;
  }
  /**
   * @param GoogleCloudAiplatformV1Tool[]
   */
  public function setTools($tools)
  {
    $this->tools = $tools;
  }
  /**
   * @return GoogleCloudAiplatformV1Tool[]
   */
  public function getTools()
  {
    return $this->tools;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleCloudAiplatformV1GenerateContentRequest::class, 'Google_Service_Aiplatform_GoogleCloudAiplatformV1GenerateContentRequest');
