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

namespace Google\Service\HangoutsChat\Resource;

use Google\Service\HangoutsChat\ChatEmpty;
use Google\Service\HangoutsChat\ListMessagesResponse;
use Google\Service\HangoutsChat\Message;

/**
 * The "messages" collection of methods.
 * Typical usage is:
 *  <code>
 *   $chatService = new Google\Service\HangoutsChat(...);
 *   $messages = $chatService->spaces_messages;
 *  </code>
 */
class SpacesMessages extends \Google\Service\Resource
{
  /**
   * Creates a message in a Google Chat space. For an example, see [Create a
   * message](https://developers.google.com/chat/api/guides/v1/messages/create).
   * Calling this method requires
   * [authentication](https://developers.google.com/chat/api/guides/auth) and
   * supports the following authentication types: - For text messages, user
   * authentication or app authentication are supported. - For card messages, only
   * app authentication is supported. (Only Chat apps can create card messages.)
   * (messages.create)
   *
   * @param string $parent Required. The resource name of the space in which to
   * create a message. Format: `spaces/{space}`
   * @param Message $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param string messageId Optional. A custom name for a Chat message
   * assigned at creation. Must start with `client-` and contain only lowercase
   * letters, numbers, and hyphens up to 63 characters in length. Specify this
   * field to get, update, or delete the message with the specified value.
   * Assigning a custom name lets a a Chat app recall the message without saving
   * the message `name` from the [response
   * body](/chat/api/reference/rest/v1/spaces.messages/get#response-body) returned
   * when creating the message. Assigning a custom name doesn't replace the
   * generated `name` field, the message's resource name. Instead, it sets the
   * custom name as the `clientAssignedMessageId` field, which you can reference
   * while processing later operations, like updating or deleting the message. For
   * example usage, see [Name a created message](https://developers.google.com/cha
   * t/api/guides/v1/messages/create#name_a_created_message).
   * @opt_param string messageReplyOption Optional. Specifies whether a message
   * starts a thread or replies to one. Only supported in named spaces.
   * @opt_param string requestId Optional. A unique request ID for this message.
   * Specifying an existing request ID returns the message created with that ID
   * instead of creating a new message.
   * @opt_param string threadKey Optional. Deprecated: Use thread.thread_key
   * instead. ID for the thread. Supports up to 4000 characters. To start or add
   * to a thread, create a message and specify a `threadKey` or the thread.name.
   * For example usage, see [Start or reply to a message thread](https://developer
   * s.google.com/chat/api/guides/v1/messages/create#create-message-thread).
   * @return Message
   */
  public function create($parent, Message $postBody, $optParams = [])
  {
    $params = ['parent' => $parent, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('create', [$params], Message::class);
  }
  /**
   * Deletes a message. For an example, see [Delete a
   * message](https://developers.google.com/chat/api/guides/v1/messages/delete).
   * Requires
   * [authentication](https://developers.google.com/chat/api/guides/auth).
   * Supports [app
   * authentication](https://developers.google.com/chat/api/guides/auth/service-
   * accounts) and [user
   * authentication](https://developers.google.com/chat/api/guides/auth/users).
   * When using app authentication, requests can only delete messages created by
   * the calling Chat app. (messages.delete)
   *
   * @param string $name Required. Resource name of the message that you want to
   * delete, in the form `spaces/messages` Example:
   * `spaces/AAAAAAAAAAA/messages/BBBBBBBBBBB.BBBBBBBBBBB`
   * @param array $optParams Optional parameters.
   *
   * @opt_param bool force When `true`, deleting a message also deletes its
   * threaded replies. When `false`, if a message has threaded replies, deletion
   * fails. Only applies when [authenticating as a
   * user](https://developers.google.com/chat/api/guides/auth/users). Has no
   * effect when [authenticating as a Chat app]
   * (https://developers.google.com/chat/api/guides/auth/service-accounts).
   * @return ChatEmpty
   */
  public function delete($name, $optParams = [])
  {
    $params = ['name' => $name];
    $params = array_merge($params, $optParams);
    return $this->call('delete', [$params], ChatEmpty::class);
  }
  /**
   * Returns details about a message. For an example, see [Read a
   * message](https://developers.google.com/chat/api/guides/v1/messages/get).
   * Requires
   * [authentication](https://developers.google.com/chat/api/guides/auth).
   * Supports [app
   * authentication](https://developers.google.com/chat/api/guides/auth/service-
   * accounts) and [user
   * authentication](https://developers.google.com/chat/api/guides/auth/users).
   * Note: Might return a message from a blocked member or space. (messages.get)
   *
   * @param string $name Required. Resource name of the message to retrieve.
   * Format: `spaces/{space}/messages/{message}` If the message begins with
   * `client-`, then it has a custom name assigned by a Chat app that created it
   * with the Chat REST API. That Chat app (but not others) can pass the custom
   * name to get, update, or delete the message. To learn more, see [create and
   * name a message] (https://developers.google.com/chat/api/guides/v1/messages/cr
   * eate#name_a_created_message).
   * @param array $optParams Optional parameters.
   * @return Message
   */
  public function get($name, $optParams = [])
  {
    $params = ['name' => $name];
    $params = array_merge($params, $optParams);
    return $this->call('get', [$params], Message::class);
  }
  /**
   * Lists messages in a space that the caller is a member of, including messages
   * from blocked members and spaces. For an example, see [List
   * messages](/chat/api/guides/v1/messages/list). Requires [user
   * authentication](https://developers.google.com/chat/api/guides/auth/users).
   * (messages.listSpacesMessages)
   *
   * @param string $parent Required. The resource name of the space to list
   * messages from. Format: `spaces/{space}`
   * @param array $optParams Optional parameters.
   *
   * @opt_param string filter A query filter. You can filter messages by date
   * (`create_time`) and thread (`thread.name`). To filter messages by the date
   * they were created, specify the `create_time` with a timestamp in
   * [RFC-3339](https://www.rfc-editor.org/rfc/rfc3339) format and double
   * quotation marks. For example, `"2023-04-21T11:30:00-04:00"`. You can use the
   * greater than operator `>` to list messages that were created after a
   * timestamp, or the less than operator `<` to list messages that were created
   * before a timestamp. To filter messages within a time interval, use the `AND`
   * operator between two timestamps. To filter by thread, specify the
   * `thread.name`, formatted as `spaces/{space}/threads/{thread}`. You can only
   * specify one `thread.name` per query. To filter by both thread and date, use
   * the `AND` operator in your query. For example, the following queries are
   * valid: ``` create_time > "2012-04-21T11:30:00-04:00" create_time >
   * "2012-04-21T11:30:00-04:00" AND thread.name = spaces/AAAAAAAAAAA/threads/123
   * create_time > "2012-04-21T11:30:00+00:00" AND create_time <
   * "2013-01-01T00:00:00+00:00" AND thread.name = spaces/AAAAAAAAAAA/threads/123
   * thread.name = spaces/AAAAAAAAAAA/threads/123 ``` Invalid queries are rejected
   * by the server with an `INVALID_ARGUMENT` error.
   * @opt_param string orderBy Optional, if resuming from a previous query. How
   * the list of messages is ordered. Specify a value to order by an ordering
   * operation. Valid ordering operation values are as follows: - `ASC` for
   * ascending. - `DESC` for descending. The default ordering is `create_time
   * ASC`.
   * @opt_param int pageSize The maximum number of messages returned. The service
   * might return fewer messages than this value. If unspecified, at most 25 are
   * returned. The maximum value is 1,000. If you use a value more than 1,000,
   * it's automatically changed to 1,000. Negative values return an
   * `INVALID_ARGUMENT` error.
   * @opt_param string pageToken Optional, if resuming from a previous query. A
   * page token received from a previous list messages call. Provide this
   * parameter to retrieve the subsequent page. When paginating, all other
   * parameters provided should match the call that provided the page token.
   * Passing different values to the other parameters might lead to unexpected
   * results.
   * @opt_param bool showDeleted Whether to include deleted messages. Deleted
   * messages include deleted time and metadata about their deletion, but message
   * content is unavailable.
   * @return ListMessagesResponse
   */
  public function listSpacesMessages($parent, $optParams = [])
  {
    $params = ['parent' => $parent];
    $params = array_merge($params, $optParams);
    return $this->call('list', [$params], ListMessagesResponse::class);
  }
  /**
   * Updates a message. There's a difference between the `patch` and `update`
   * methods. The `patch` method uses a `patch` request while the `update` method
   * uses a `put` request. We recommend using the `patch` method. For an example,
   * see [Update a
   * message](https://developers.google.com/chat/api/guides/v1/messages/update).
   * Requires
   * [authentication](https://developers.google.com/chat/api/guides/auth).
   * Supports [app
   * authentication](https://developers.google.com/chat/api/guides/auth/service-
   * accounts) and [user
   * authentication](https://developers.google.com/chat/api/guides/auth/users).
   * When using app authentication, requests can only update messages created by
   * the calling Chat app. (messages.patch)
   *
   * @param string $name Resource name in the form `spaces/messages`. Example:
   * `spaces/AAAAAAAAAAA/messages/BBBBBBBBBBB.BBBBBBBBBBB`
   * @param Message $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param bool allowMissing Optional. If `true` and the message isn't found,
   * a new message is created and `updateMask` is ignored. The specified message
   * ID must be [client-assigned](https://developers.google.com/chat/api/guides/v1
   * /messages/create#name_a_created_message) or the request fails.
   * @opt_param string updateMask Required. The field paths to update. Separate
   * multiple values with commas. Currently supported field paths: - `text` -
   * `attachment` - `cards` (Requires [app
   * authentication](/chat/api/guides/auth/service-accounts).) - `cards_v2`
   * (Requires [app authentication](/chat/api/guides/auth/service-accounts).)
   * @return Message
   */
  public function patch($name, Message $postBody, $optParams = [])
  {
    $params = ['name' => $name, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('patch', [$params], Message::class);
  }
  /**
   * Updates a message. There's a difference between the `patch` and `update`
   * methods. The `patch` method uses a `patch` request while the `update` method
   * uses a `put` request. We recommend using the `patch` method. For an example,
   * see [Update a
   * message](https://developers.google.com/chat/api/guides/v1/messages/update).
   * Requires
   * [authentication](https://developers.google.com/chat/api/guides/auth).
   * Supports [app
   * authentication](https://developers.google.com/chat/api/guides/auth/service-
   * accounts) and [user
   * authentication](https://developers.google.com/chat/api/guides/auth/users).
   * When using app authentication, requests can only update messages created by
   * the calling Chat app. (messages.update)
   *
   * @param string $name Resource name in the form `spaces/messages`. Example:
   * `spaces/AAAAAAAAAAA/messages/BBBBBBBBBBB.BBBBBBBBBBB`
   * @param Message $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param bool allowMissing Optional. If `true` and the message isn't found,
   * a new message is created and `updateMask` is ignored. The specified message
   * ID must be [client-assigned](https://developers.google.com/chat/api/guides/v1
   * /messages/create#name_a_created_message) or the request fails.
   * @opt_param string updateMask Required. The field paths to update. Separate
   * multiple values with commas. Currently supported field paths: - `text` -
   * `attachment` - `cards` (Requires [app
   * authentication](/chat/api/guides/auth/service-accounts).) - `cards_v2`
   * (Requires [app authentication](/chat/api/guides/auth/service-accounts).)
   * @return Message
   */
  public function update($name, Message $postBody, $optParams = [])
  {
    $params = ['name' => $name, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('update', [$params], Message::class);
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(SpacesMessages::class, 'Google_Service_HangoutsChat_Resource_SpacesMessages');
