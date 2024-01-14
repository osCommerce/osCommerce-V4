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

class PipelineRun extends \Google\Collection
{
  protected $collection_key = 'workspaces';
  /**
   * @var string[]
   */
  public $annotations;
  /**
   * @var ChildStatusReference[]
   */
  public $childReferences;
  protected $childReferencesType = ChildStatusReference::class;
  protected $childReferencesDataType = 'array';
  /**
   * @var string
   */
  public $completionTime;
  /**
   * @var GoogleDevtoolsCloudbuildV2Condition[]
   */
  public $conditions;
  protected $conditionsType = GoogleDevtoolsCloudbuildV2Condition::class;
  protected $conditionsDataType = 'array';
  /**
   * @var string
   */
  public $createTime;
  /**
   * @var string
   */
  public $etag;
  /**
   * @var string
   */
  public $finallyStartTime;
  /**
   * @var string
   */
  public $name;
  /**
   * @var Param[]
   */
  public $params;
  protected $paramsType = Param::class;
  protected $paramsDataType = 'array';
  /**
   * @var PipelineRef
   */
  public $pipelineRef;
  protected $pipelineRefType = PipelineRef::class;
  protected $pipelineRefDataType = '';
  /**
   * @var string
   */
  public $pipelineRunStatus;
  /**
   * @var PipelineSpec
   */
  public $pipelineSpec;
  protected $pipelineSpecType = PipelineSpec::class;
  protected $pipelineSpecDataType = '';
  /**
   * @var PipelineSpec
   */
  public $resolvedPipelineSpec;
  protected $resolvedPipelineSpecType = PipelineSpec::class;
  protected $resolvedPipelineSpecDataType = '';
  /**
   * @var string
   */
  public $serviceAccount;
  /**
   * @var SkippedTask[]
   */
  public $skippedTasks;
  protected $skippedTasksType = SkippedTask::class;
  protected $skippedTasksDataType = 'array';
  /**
   * @var string
   */
  public $startTime;
  /**
   * @var TimeoutFields
   */
  public $timeouts;
  protected $timeoutsType = TimeoutFields::class;
  protected $timeoutsDataType = '';
  /**
   * @var string
   */
  public $uid;
  /**
   * @var string
   */
  public $updateTime;
  /**
   * @var string
   */
  public $workerPool;
  /**
   * @var string
   */
  public $workflow;
  /**
   * @var WorkspaceBinding[]
   */
  public $workspaces;
  protected $workspacesType = WorkspaceBinding::class;
  protected $workspacesDataType = 'array';

  /**
   * @param string[]
   */
  public function setAnnotations($annotations)
  {
    $this->annotations = $annotations;
  }
  /**
   * @return string[]
   */
  public function getAnnotations()
  {
    return $this->annotations;
  }
  /**
   * @param ChildStatusReference[]
   */
  public function setChildReferences($childReferences)
  {
    $this->childReferences = $childReferences;
  }
  /**
   * @return ChildStatusReference[]
   */
  public function getChildReferences()
  {
    return $this->childReferences;
  }
  /**
   * @param string
   */
  public function setCompletionTime($completionTime)
  {
    $this->completionTime = $completionTime;
  }
  /**
   * @return string
   */
  public function getCompletionTime()
  {
    return $this->completionTime;
  }
  /**
   * @param GoogleDevtoolsCloudbuildV2Condition[]
   */
  public function setConditions($conditions)
  {
    $this->conditions = $conditions;
  }
  /**
   * @return GoogleDevtoolsCloudbuildV2Condition[]
   */
  public function getConditions()
  {
    return $this->conditions;
  }
  /**
   * @param string
   */
  public function setCreateTime($createTime)
  {
    $this->createTime = $createTime;
  }
  /**
   * @return string
   */
  public function getCreateTime()
  {
    return $this->createTime;
  }
  /**
   * @param string
   */
  public function setEtag($etag)
  {
    $this->etag = $etag;
  }
  /**
   * @return string
   */
  public function getEtag()
  {
    return $this->etag;
  }
  /**
   * @param string
   */
  public function setFinallyStartTime($finallyStartTime)
  {
    $this->finallyStartTime = $finallyStartTime;
  }
  /**
   * @return string
   */
  public function getFinallyStartTime()
  {
    return $this->finallyStartTime;
  }
  /**
   * @param string
   */
  public function setName($name)
  {
    $this->name = $name;
  }
  /**
   * @return string
   */
  public function getName()
  {
    return $this->name;
  }
  /**
   * @param Param[]
   */
  public function setParams($params)
  {
    $this->params = $params;
  }
  /**
   * @return Param[]
   */
  public function getParams()
  {
    return $this->params;
  }
  /**
   * @param PipelineRef
   */
  public function setPipelineRef(PipelineRef $pipelineRef)
  {
    $this->pipelineRef = $pipelineRef;
  }
  /**
   * @return PipelineRef
   */
  public function getPipelineRef()
  {
    return $this->pipelineRef;
  }
  /**
   * @param string
   */
  public function setPipelineRunStatus($pipelineRunStatus)
  {
    $this->pipelineRunStatus = $pipelineRunStatus;
  }
  /**
   * @return string
   */
  public function getPipelineRunStatus()
  {
    return $this->pipelineRunStatus;
  }
  /**
   * @param PipelineSpec
   */
  public function setPipelineSpec(PipelineSpec $pipelineSpec)
  {
    $this->pipelineSpec = $pipelineSpec;
  }
  /**
   * @return PipelineSpec
   */
  public function getPipelineSpec()
  {
    return $this->pipelineSpec;
  }
  /**
   * @param PipelineSpec
   */
  public function setResolvedPipelineSpec(PipelineSpec $resolvedPipelineSpec)
  {
    $this->resolvedPipelineSpec = $resolvedPipelineSpec;
  }
  /**
   * @return PipelineSpec
   */
  public function getResolvedPipelineSpec()
  {
    return $this->resolvedPipelineSpec;
  }
  /**
   * @param string
   */
  public function setServiceAccount($serviceAccount)
  {
    $this->serviceAccount = $serviceAccount;
  }
  /**
   * @return string
   */
  public function getServiceAccount()
  {
    return $this->serviceAccount;
  }
  /**
   * @param SkippedTask[]
   */
  public function setSkippedTasks($skippedTasks)
  {
    $this->skippedTasks = $skippedTasks;
  }
  /**
   * @return SkippedTask[]
   */
  public function getSkippedTasks()
  {
    return $this->skippedTasks;
  }
  /**
   * @param string
   */
  public function setStartTime($startTime)
  {
    $this->startTime = $startTime;
  }
  /**
   * @return string
   */
  public function getStartTime()
  {
    return $this->startTime;
  }
  /**
   * @param TimeoutFields
   */
  public function setTimeouts(TimeoutFields $timeouts)
  {
    $this->timeouts = $timeouts;
  }
  /**
   * @return TimeoutFields
   */
  public function getTimeouts()
  {
    return $this->timeouts;
  }
  /**
   * @param string
   */
  public function setUid($uid)
  {
    $this->uid = $uid;
  }
  /**
   * @return string
   */
  public function getUid()
  {
    return $this->uid;
  }
  /**
   * @param string
   */
  public function setUpdateTime($updateTime)
  {
    $this->updateTime = $updateTime;
  }
  /**
   * @return string
   */
  public function getUpdateTime()
  {
    return $this->updateTime;
  }
  /**
   * @param string
   */
  public function setWorkerPool($workerPool)
  {
    $this->workerPool = $workerPool;
  }
  /**
   * @return string
   */
  public function getWorkerPool()
  {
    return $this->workerPool;
  }
  /**
   * @param string
   */
  public function setWorkflow($workflow)
  {
    $this->workflow = $workflow;
  }
  /**
   * @return string
   */
  public function getWorkflow()
  {
    return $this->workflow;
  }
  /**
   * @param WorkspaceBinding[]
   */
  public function setWorkspaces($workspaces)
  {
    $this->workspaces = $workspaces;
  }
  /**
   * @return WorkspaceBinding[]
   */
  public function getWorkspaces()
  {
    return $this->workspaces;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(PipelineRun::class, 'Google_Service_CloudBuild_PipelineRun');
