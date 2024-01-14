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

namespace Google\Service;

use Google\Client;

/**
 * Service definition for HangoutsChat (v1).
 *
 * <p>
 * Enables apps to fetch information and perform actions in Google Chat.
 * Authentication is a prerequisite for using the Google Chat REST API.</p>
 *
 * <p>
 * For more information about this service, see the API
 * <a href="https://developers.google.com/hangouts/chat" target="_blank">Documentation</a>
 * </p>
 *
 * @author Google, Inc.
 */
class HangoutsChat extends \Google\Service
{
  /** Private Service: https://www.googleapis.com/auth/chat.bot. */
  const CHAT_BOT =
      "https://www.googleapis.com/auth/chat.bot";
  /** Delete conversations and spaces & remove access to associated files in Google Chat. */
  const CHAT_DELETE =
      "https://www.googleapis.com/auth/chat.delete";
  /** Import spaces, messages, and memberships into Google Chat.. */
  const CHAT_IMPORT =
      "https://www.googleapis.com/auth/chat.import";
  /** View, add, and remove members from conversations in Google Chat. */
  const CHAT_MEMBERSHIPS =
      "https://www.googleapis.com/auth/chat.memberships";
  /** Add and remove itself from conversations in Google Chat. */
  const CHAT_MEMBERSHIPS_APP =
      "https://www.googleapis.com/auth/chat.memberships.app";
  /** View members in Google Chat conversations.. */
  const CHAT_MEMBERSHIPS_READONLY =
      "https://www.googleapis.com/auth/chat.memberships.readonly";
  /** View, compose, send, update, and delete messages, and add, view, and delete reactions to messages.. */
  const CHAT_MESSAGES =
      "https://www.googleapis.com/auth/chat.messages";
  /** Compose and send messages in Google Chat. */
  const CHAT_MESSAGES_CREATE =
      "https://www.googleapis.com/auth/chat.messages.create";
  /** View, add, and delete reactions to messages in Google Chat. */
  const CHAT_MESSAGES_REACTIONS =
      "https://www.googleapis.com/auth/chat.messages.reactions";
  /** Add reactions to messages in Google Chat. */
  const CHAT_MESSAGES_REACTIONS_CREATE =
      "https://www.googleapis.com/auth/chat.messages.reactions.create";
  /** View reactions to messages in Google Chat. */
  const CHAT_MESSAGES_REACTIONS_READONLY =
      "https://www.googleapis.com/auth/chat.messages.reactions.readonly";
  /** View messages and reactions in Google Chat. */
  const CHAT_MESSAGES_READONLY =
      "https://www.googleapis.com/auth/chat.messages.readonly";
  /** Create conversations and spaces and view or update metadata (including history settings) in Google Chat. */
  const CHAT_SPACES =
      "https://www.googleapis.com/auth/chat.spaces";
  /** Create new conversations in Google Chat. */
  const CHAT_SPACES_CREATE =
      "https://www.googleapis.com/auth/chat.spaces.create";
  /** View chat and spaces in Google Chat. */
  const CHAT_SPACES_READONLY =
      "https://www.googleapis.com/auth/chat.spaces.readonly";

  public $media;
  public $spaces;
  public $spaces_members;
  public $spaces_messages;
  public $spaces_messages_attachments;
  public $spaces_messages_reactions;

  /**
   * Constructs the internal representation of the HangoutsChat service.
   *
   * @param Client|array $clientOrConfig The client used to deliver requests, or a
   *                                     config array to pass to a new Client instance.
   * @param string $rootUrl The root URL used for requests to the service.
   */
  public function __construct($clientOrConfig = [], $rootUrl = null)
  {
    parent::__construct($clientOrConfig);
    $this->rootUrl = $rootUrl ?: 'https://chat.googleapis.com/';
    $this->servicePath = '';
    $this->batchPath = 'batch';
    $this->version = 'v1';
    $this->serviceName = 'chat';

    $this->media = new HangoutsChat\Resource\Media(
        $this,
        $this->serviceName,
        'media',
        [
          'methods' => [
            'download' => [
              'path' => 'v1/media/{+resourceName}',
              'httpMethod' => 'GET',
              'parameters' => [
                'resourceName' => [
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ],
              ],
            ],'upload' => [
              'path' => 'v1/{+parent}/attachments:upload',
              'httpMethod' => 'POST',
              'parameters' => [
                'parent' => [
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ],
              ],
            ],
          ]
        ]
    );
    $this->spaces = new HangoutsChat\Resource\Spaces(
        $this,
        $this->serviceName,
        'spaces',
        [
          'methods' => [
            'create' => [
              'path' => 'v1/spaces',
              'httpMethod' => 'POST',
              'parameters' => [
                'requestId' => [
                  'location' => 'query',
                  'type' => 'string',
                ],
              ],
            ],'delete' => [
              'path' => 'v1/{+name}',
              'httpMethod' => 'DELETE',
              'parameters' => [
                'name' => [
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ],
              ],
            ],'findDirectMessage' => [
              'path' => 'v1/spaces:findDirectMessage',
              'httpMethod' => 'GET',
              'parameters' => [
                'name' => [
                  'location' => 'query',
                  'type' => 'string',
                ],
              ],
            ],'get' => [
              'path' => 'v1/{+name}',
              'httpMethod' => 'GET',
              'parameters' => [
                'name' => [
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ],
              ],
            ],'list' => [
              'path' => 'v1/spaces',
              'httpMethod' => 'GET',
              'parameters' => [
                'filter' => [
                  'location' => 'query',
                  'type' => 'string',
                ],
                'pageSize' => [
                  'location' => 'query',
                  'type' => 'integer',
                ],
                'pageToken' => [
                  'location' => 'query',
                  'type' => 'string',
                ],
              ],
            ],'patch' => [
              'path' => 'v1/{+name}',
              'httpMethod' => 'PATCH',
              'parameters' => [
                'name' => [
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ],
                'updateMask' => [
                  'location' => 'query',
                  'type' => 'string',
                ],
              ],
            ],'setup' => [
              'path' => 'v1/spaces:setup',
              'httpMethod' => 'POST',
              'parameters' => [],
            ],
          ]
        ]
    );
    $this->spaces_members = new HangoutsChat\Resource\SpacesMembers(
        $this,
        $this->serviceName,
        'members',
        [
          'methods' => [
            'create' => [
              'path' => 'v1/{+parent}/members',
              'httpMethod' => 'POST',
              'parameters' => [
                'parent' => [
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ],
              ],
            ],'delete' => [
              'path' => 'v1/{+name}',
              'httpMethod' => 'DELETE',
              'parameters' => [
                'name' => [
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ],
              ],
            ],'get' => [
              'path' => 'v1/{+name}',
              'httpMethod' => 'GET',
              'parameters' => [
                'name' => [
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ],
              ],
            ],'list' => [
              'path' => 'v1/{+parent}/members',
              'httpMethod' => 'GET',
              'parameters' => [
                'parent' => [
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ],
                'filter' => [
                  'location' => 'query',
                  'type' => 'string',
                ],
                'pageSize' => [
                  'location' => 'query',
                  'type' => 'integer',
                ],
                'pageToken' => [
                  'location' => 'query',
                  'type' => 'string',
                ],
                'showGroups' => [
                  'location' => 'query',
                  'type' => 'boolean',
                ],
                'showInvited' => [
                  'location' => 'query',
                  'type' => 'boolean',
                ],
              ],
            ],
          ]
        ]
    );
    $this->spaces_messages = new HangoutsChat\Resource\SpacesMessages(
        $this,
        $this->serviceName,
        'messages',
        [
          'methods' => [
            'create' => [
              'path' => 'v1/{+parent}/messages',
              'httpMethod' => 'POST',
              'parameters' => [
                'parent' => [
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ],
                'messageId' => [
                  'location' => 'query',
                  'type' => 'string',
                ],
                'messageReplyOption' => [
                  'location' => 'query',
                  'type' => 'string',
                ],
                'requestId' => [
                  'location' => 'query',
                  'type' => 'string',
                ],
                'threadKey' => [
                  'location' => 'query',
                  'type' => 'string',
                ],
              ],
            ],'delete' => [
              'path' => 'v1/{+name}',
              'httpMethod' => 'DELETE',
              'parameters' => [
                'name' => [
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ],
                'force' => [
                  'location' => 'query',
                  'type' => 'boolean',
                ],
              ],
            ],'get' => [
              'path' => 'v1/{+name}',
              'httpMethod' => 'GET',
              'parameters' => [
                'name' => [
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ],
              ],
            ],'list' => [
              'path' => 'v1/{+parent}/messages',
              'httpMethod' => 'GET',
              'parameters' => [
                'parent' => [
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ],
                'filter' => [
                  'location' => 'query',
                  'type' => 'string',
                ],
                'orderBy' => [
                  'location' => 'query',
                  'type' => 'string',
                ],
                'pageSize' => [
                  'location' => 'query',
                  'type' => 'integer',
                ],
                'pageToken' => [
                  'location' => 'query',
                  'type' => 'string',
                ],
                'showDeleted' => [
                  'location' => 'query',
                  'type' => 'boolean',
                ],
              ],
            ],'patch' => [
              'path' => 'v1/{+name}',
              'httpMethod' => 'PATCH',
              'parameters' => [
                'name' => [
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ],
                'allowMissing' => [
                  'location' => 'query',
                  'type' => 'boolean',
                ],
                'updateMask' => [
                  'location' => 'query',
                  'type' => 'string',
                ],
              ],
            ],'update' => [
              'path' => 'v1/{+name}',
              'httpMethod' => 'PUT',
              'parameters' => [
                'name' => [
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ],
                'allowMissing' => [
                  'location' => 'query',
                  'type' => 'boolean',
                ],
                'updateMask' => [
                  'location' => 'query',
                  'type' => 'string',
                ],
              ],
            ],
          ]
        ]
    );
    $this->spaces_messages_attachments = new HangoutsChat\Resource\SpacesMessagesAttachments(
        $this,
        $this->serviceName,
        'attachments',
        [
          'methods' => [
            'get' => [
              'path' => 'v1/{+name}',
              'httpMethod' => 'GET',
              'parameters' => [
                'name' => [
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ],
              ],
            ],
          ]
        ]
    );
    $this->spaces_messages_reactions = new HangoutsChat\Resource\SpacesMessagesReactions(
        $this,
        $this->serviceName,
        'reactions',
        [
          'methods' => [
            'create' => [
              'path' => 'v1/{+parent}/reactions',
              'httpMethod' => 'POST',
              'parameters' => [
                'parent' => [
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ],
              ],
            ],'delete' => [
              'path' => 'v1/{+name}',
              'httpMethod' => 'DELETE',
              'parameters' => [
                'name' => [
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ],
              ],
            ],'list' => [
              'path' => 'v1/{+parent}/reactions',
              'httpMethod' => 'GET',
              'parameters' => [
                'parent' => [
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ],
                'filter' => [
                  'location' => 'query',
                  'type' => 'string',
                ],
                'pageSize' => [
                  'location' => 'query',
                  'type' => 'integer',
                ],
                'pageToken' => [
                  'location' => 'query',
                  'type' => 'string',
                ],
              ],
            ],
          ]
        ]
    );
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(HangoutsChat::class, 'Google_Service_HangoutsChat');
