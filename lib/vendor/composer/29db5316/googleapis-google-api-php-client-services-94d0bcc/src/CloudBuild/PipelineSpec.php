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

namespace Google\Service\CloudBuild;

class PipelineSpec extends \Google\Collection
{
  protected $collection_key = 'workspaces';
  /**
   * @var PipelineTask[]
   */
  public $finallyTasks;
  protected $finallyTasksType = PipelineTask::class;
  protected $finallyTasksDataType = 'array';
  /**
   * @var string
   */
  public $generatedYaml;
  /**
   * @var ParamSpec[]
   */
  public $params;
  protected $paramsType = ParamSpec::class;
  protected $paramsDataType = 'array';
  /**
   * @var PipelineTask[]
   */
  public $tasks;
  protected $tasksType = PipelineTask::class;
  protected $tasksDataType = 'array';
  /**
   * @var PipelineWorkspaceDeclaration[]
   */
  public $workspaces;
  protected $workspacesType = PipelineWorkspaceDeclaration::class;
  protected $workspacesDataType = 'array';

  /**
   * @param PipelineTask[]
   */
  public function setFinallyTasks($finallyTasks)
  {
    $this->finallyTasks = $finallyTasks;
  }
  /**
   * @return PipelineTask[]
   */
  public function getFinallyTasks()
  {
    return $this->finallyTasks;
  }
  /**
   * @param string
   */
  public function setGeneratedYaml($generatedYaml)
  {
    $this->generatedYaml = $generatedYaml;
  }
  /**
   * @return string
   */
  public function getGeneratedYaml()
  {
    return $this->generatedYaml;
  }
  /**
   * @param ParamSpec[]
   */
  public function setParams($params)
  {
    $this->params = $params;
  }
  /**
   * @return ParamSpec[]
   */
  public function getParams()
  {
    return $this->params;
  }
  /**
   * @param PipelineTask[]
   */
  public function setTasks($tasks)
  {
    $this->tasks = $tasks;
  }
  /**
   * @return PipelineTask[]
   */
  public function getTasks()
  {
    return $this->tasks;
  }
  /**
   * @param PipelineWorkspaceDeclaration[]
   */
  public function setWorkspaces($workspaces)
  {
    $this->workspaces = $workspaces;
  }
  /**
   * @return PipelineWorkspaceDeclaration[]
   */
  public function getWorkspaces()
  {
    return $this->workspaces;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(PipelineSpec::class, 'Google_Service_CloudBuild_PipelineSpec');
