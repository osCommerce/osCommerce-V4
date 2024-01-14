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

namespace Google\Service\DiscoveryEngine\Resource;

use Google\Service\DiscoveryEngine\GoogleCloudDiscoveryengineV1betaConversation;
use Google\Service\DiscoveryEngine\GoogleCloudDiscoveryengineV1betaConverseConversationRequest;
use Google\Service\DiscoveryEngine\GoogleCloudDiscoveryengineV1betaConverseConversationResponse;
use Google\Service\DiscoveryEngine\GoogleCloudDiscoveryengineV1betaListConversationsResponse;
use Google\Service\DiscoveryEngine\GoogleProtobufEmpty;

/**
 * The "conversations" collection of methods.
 * Typical usage is:
 *  <code>
 *   $discoveryengineService = new Google\Service\DiscoveryEngine(...);
 *   $conversations = $discoveryengineService->projects_locations_collections_engines_conversations;
 *  </code>
 */
class ProjectsLocationsCollectionsEnginesConversations extends \Google\Service\Resource
{
  /**
   * Converses a conversation. (conversations.converse)
   *
   * @param string $name Required. The resource name of the Conversation to get.
   * Format: `projects/{project_number}/locations/{location_id}/collections/{colle
   * ction}/dataStores/{data_store_id}/conversations/{conversation_id}`. Use `proj
   * ects/{project_number}/locations/{location_id}/collections/{collection}/dataSt
   * ores/{data_store_id}/conversations/-` to activate auto session mode, which
   * automatically creates a new conversation inside a ConverseConversation
   * session.
   * @param GoogleCloudDiscoveryengineV1betaConverseConversationRequest $postBody
   * @param array $optParams Optional parameters.
   * @return GoogleCloudDiscoveryengineV1betaConverseConversationResponse
   */
  public function converse($name, GoogleCloudDiscoveryengineV1betaConverseConversationRequest $postBody, $optParams = [])
  {
    $params = ['name' => $name, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('converse', [$params], GoogleCloudDiscoveryengineV1betaConverseConversationResponse::class);
  }
  /**
   * Creates a Conversation. If the Conversation to create already exists, an
   * ALREADY_EXISTS error is returned. (conversations.create)
   *
   * @param string $parent Required. Full resource name of parent data store.
   * Format: `projects/{project_number}/locations/{location_id}/collections/{colle
   * ction}/dataStores/{data_store_id}`
   * @param GoogleCloudDiscoveryengineV1betaConversation $postBody
   * @param array $optParams Optional parameters.
   * @return GoogleCloudDiscoveryengineV1betaConversation
   */
  public function create($parent, GoogleCloudDiscoveryengineV1betaConversation $postBody, $optParams = [])
  {
    $params = ['parent' => $parent, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('create', [$params], GoogleCloudDiscoveryengineV1betaConversation::class);
  }
  /**
   * Deletes a Conversation. If the Conversation to delete does not exist, a
   * NOT_FOUND error is returned. (conversations.delete)
   *
   * @param string $name Required. The resource name of the Conversation to
   * delete. Format: `projects/{project_number}/locations/{location_id}/collection
   * s/{collection}/dataStores/{data_store_id}/conversations/{conversation_id}`
   * @param array $optParams Optional parameters.
   * @return GoogleProtobufEmpty
   */
  public function delete($name, $optParams = [])
  {
    $params = ['name' => $name];
    $params = array_merge($params, $optParams);
    return $this->call('delete', [$params], GoogleProtobufEmpty::class);
  }
  /**
   * Gets a Conversation. (conversations.get)
   *
   * @param string $name Required. The resource name of the Conversation to get.
   * Format: `projects/{project_number}/locations/{location_id}/collections/{colle
   * ction}/dataStores/{data_store_id}/conversations/{conversation_id}`
   * @param array $optParams Optional parameters.
   * @return GoogleCloudDiscoveryengineV1betaConversation
   */
  public function get($name, $optParams = [])
  {
    $params = ['name' => $name];
    $params = array_merge($params, $optParams);
    return $this->call('get', [$params], GoogleCloudDiscoveryengineV1betaConversation::class);
  }
  /**
   * Lists all Conversations by their parent DataStore.
   * (conversations.listProjectsLocationsCollectionsEnginesConversations)
   *
   * @param string $parent Required. The data store resource name. Format: `projec
   * ts/{project_number}/locations/{location_id}/collections/{collection}/dataStor
   * es/{data_store_id}`
   * @param array $optParams Optional parameters.
   *
   * @opt_param string filter A filter to apply on the list results. The supported
   * features are: user_pseudo_id, state. Example: "user_pseudo_id = some_id"
   * @opt_param string orderBy A comma-separated list of fields to order by,
   * sorted in ascending order. Use "desc" after a field name for descending.
   * Supported fields: * `update_time` * `create_time` * `conversation_name`
   * Example: "update_time desc" "create_time"
   * @opt_param int pageSize Maximum number of results to return. If unspecified,
   * defaults to 50. Max allowed value is 1000.
   * @opt_param string pageToken A page token, received from a previous
   * `ListConversations` call. Provide this to retrieve the subsequent page.
   * @return GoogleCloudDiscoveryengineV1betaListConversationsResponse
   */
  public function listProjectsLocationsCollectionsEnginesConversations($parent, $optParams = [])
  {
    $params = ['parent' => $parent];
    $params = array_merge($params, $optParams);
    return $this->call('list', [$params], GoogleCloudDiscoveryengineV1betaListConversationsResponse::class);
  }
  /**
   * Updates a Conversation. Conversation action type cannot be changed. If the
   * Conversation to update does not exist, a NOT_FOUND error is returned.
   * (conversations.patch)
   *
   * @param string $name Immutable. Fully qualified name
   * `project/locations/global/collections/{collection}/dataStore/conversations`
   * or `project/locations/global/collections/{collection}/engines/conversations`.
   * @param GoogleCloudDiscoveryengineV1betaConversation $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param string updateMask Indicates which fields in the provided
   * Conversation to update. The following are NOT supported: * conversation.name
   * If not set or empty, all supported fields are updated.
   * @return GoogleCloudDiscoveryengineV1betaConversation
   */
  public function patch($name, GoogleCloudDiscoveryengineV1betaConversation $postBody, $optParams = [])
  {
    $params = ['name' => $name, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('patch', [$params], GoogleCloudDiscoveryengineV1betaConversation::class);
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(ProjectsLocationsCollectionsEnginesConversations::class, 'Google_Service_DiscoveryEngine_Resource_ProjectsLocationsCollectionsEnginesConversations');
