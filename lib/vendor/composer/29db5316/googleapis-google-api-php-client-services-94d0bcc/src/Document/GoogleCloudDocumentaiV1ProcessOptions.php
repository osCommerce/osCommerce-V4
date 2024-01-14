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

namespace Google\Service\Document;

class GoogleCloudDocumentaiV1ProcessOptions extends \Google\Model
{
  /**
   * @var int
   */
  public $fromEnd;
  /**
   * @var int
   */
  public $fromStart;
  /**
   * @var GoogleCloudDocumentaiV1ProcessOptionsIndividualPageSelector
   */
  public $individualPageSelector;
  protected $individualPageSelectorType = GoogleCloudDocumentaiV1ProcessOptionsIndividualPageSelector::class;
  protected $individualPageSelectorDataType = '';
  /**
   * @var GoogleCloudDocumentaiV1OcrConfig
   */
  public $ocrConfig;
  protected $ocrConfigType = GoogleCloudDocumentaiV1OcrConfig::class;
  protected $ocrConfigDataType = '';

  /**
   * @param int
   */
  public function setFromEnd($fromEnd)
  {
    $this->fromEnd = $fromEnd;
  }
  /**
   * @return int
   */
  public function getFromEnd()
  {
    return $this->fromEnd;
  }
  /**
   * @param int
   */
  public function setFromStart($fromStart)
  {
    $this->fromStart = $fromStart;
  }
  /**
   * @return int
   */
  public function getFromStart()
  {
    return $this->fromStart;
  }
  /**
   * @param GoogleCloudDocumentaiV1ProcessOptionsIndividualPageSelector
   */
  public function setIndividualPageSelector(GoogleCloudDocumentaiV1ProcessOptionsIndividualPageSelector $individualPageSelector)
  {
    $this->individualPageSelector = $individualPageSelector;
  }
  /**
   * @return GoogleCloudDocumentaiV1ProcessOptionsIndividualPageSelector
   */
  public function getIndividualPageSelector()
  {
    return $this->individualPageSelector;
  }
  /**
   * @param GoogleCloudDocumentaiV1OcrConfig
   */
  public function setOcrConfig(GoogleCloudDocumentaiV1OcrConfig $ocrConfig)
  {
    $this->ocrConfig = $ocrConfig;
  }
  /**
   * @return GoogleCloudDocumentaiV1OcrConfig
   */
  public function getOcrConfig()
  {
    return $this->ocrConfig;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleCloudDocumentaiV1ProcessOptions::class, 'Google_Service_Document_GoogleCloudDocumentaiV1ProcessOptions');
