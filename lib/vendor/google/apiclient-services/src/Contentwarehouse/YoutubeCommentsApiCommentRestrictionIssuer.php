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

class YoutubeCommentsApiCommentRestrictionIssuer extends \Google\Model
{
  /**
   * @var YoutubeCommentsApiCommentRestrictionIssuerChannelModeratorDetails
   */
  public $channelModeratorDetails;
  protected $channelModeratorDetailsType = YoutubeCommentsApiCommentRestrictionIssuerChannelModeratorDetails::class;
  protected $channelModeratorDetailsDataType = '';
  /**
   * @var YoutubeCommentsApiCommentRestrictionIssuerChannelOwnerDetails
   */
  public $channelOwnerDetails;
  protected $channelOwnerDetailsType = YoutubeCommentsApiCommentRestrictionIssuerChannelOwnerDetails::class;
  protected $channelOwnerDetailsDataType = '';
  /**
   * @var string
   */
  public $issuer;

  /**
   * @param YoutubeCommentsApiCommentRestrictionIssuerChannelModeratorDetails
   */
  public function setChannelModeratorDetails(YoutubeCommentsApiCommentRestrictionIssuerChannelModeratorDetails $channelModeratorDetails)
  {
    $this->channelModeratorDetails = $channelModeratorDetails;
  }
  /**
   * @return YoutubeCommentsApiCommentRestrictionIssuerChannelModeratorDetails
   */
  public function getChannelModeratorDetails()
  {
    return $this->channelModeratorDetails;
  }
  /**
   * @param YoutubeCommentsApiCommentRestrictionIssuerChannelOwnerDetails
   */
  public function setChannelOwnerDetails(YoutubeCommentsApiCommentRestrictionIssuerChannelOwnerDetails $channelOwnerDetails)
  {
    $this->channelOwnerDetails = $channelOwnerDetails;
  }
  /**
   * @return YoutubeCommentsApiCommentRestrictionIssuerChannelOwnerDetails
   */
  public function getChannelOwnerDetails()
  {
    return $this->channelOwnerDetails;
  }
  /**
   * @param string
   */
  public function setIssuer($issuer)
  {
    $this->issuer = $issuer;
  }
  /**
   * @return string
   */
  public function getIssuer()
  {
    return $this->issuer;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(YoutubeCommentsApiCommentRestrictionIssuer::class, 'Google_Service_Contentwarehouse_YoutubeCommentsApiCommentRestrictionIssuer');
