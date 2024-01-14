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

namespace Google\Service\TrafficDirectorService;

class ClientConfig extends \Google\Collection
{
  protected $collection_key = 'xdsConfig';
  /**
   * @var GenericXdsConfig[]
   */
  public $genericXdsConfigs;
  protected $genericXdsConfigsType = GenericXdsConfig::class;
  protected $genericXdsConfigsDataType = 'array';
  /**
   * @var Node
   */
  public $node;
  protected $nodeType = Node::class;
  protected $nodeDataType = '';
  /**
   * @var PerXdsConfig[]
   */
  public $xdsConfig;
  protected $xdsConfigType = PerXdsConfig::class;
  protected $xdsConfigDataType = 'array';

  /**
   * @param GenericXdsConfig[]
   */
  public function setGenericXdsConfigs($genericXdsConfigs)
  {
    $this->genericXdsConfigs = $genericXdsConfigs;
  }
  /**
   * @return GenericXdsConfig[]
   */
  public function getGenericXdsConfigs()
  {
    return $this->genericXdsConfigs;
  }
  /**
   * @param Node
   */
  public function setNode(Node $node)
  {
    $this->node = $node;
  }
  /**
   * @return Node
   */
  public function getNode()
  {
    return $this->node;
  }
  /**
   * @param PerXdsConfig[]
   */
  public function setXdsConfig($xdsConfig)
  {
    $this->xdsConfig = $xdsConfig;
  }
  /**
   * @return PerXdsConfig[]
   */
  public function getXdsConfig()
  {
    return $this->xdsConfig;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(ClientConfig::class, 'Google_Service_TrafficDirectorService_ClientConfig');
