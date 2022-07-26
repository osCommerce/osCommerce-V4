<?php

/* 
 * This file is part of osCommerce ecommerce platform.
 * osCommerce the ecommerce
 * 
 * @link https://www.oscommerce.com
 * @copyright Copyright (c) 2000-2022 osCommerce LTD
 * 
 * Released under the GNU General Public License
 * For the full copyright and license information, please view the LICENSE.TXT file that was distributed with this source code.
 */


namespace frontend\design\boxes;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;


class TrustPilotReviews extends Widget
{

  public $params;
  public $settings;

  public function init()
  {
    parent::init();
  }

  public function run()
  {
    if (\common\helpers\Acl::checkExtensionAllowed('Trustpilot', 'allowed')) {
      $client = new \common\extensions\Trustpilot\Trustpilot();
      if ($data = $client->getReviewsSummary(\common\classes\platform::currentId())) {
          return IncludeTpl::widget(['file' => 'boxes/trustpilot-summary.tpl', 'params' => [
            'identifying' => $data['summary']->name->identifying,
            'score' => $data['summary']->trustScore,
            'qty' => $data['summary']->numberOfReviews->usedForTrustScoreCalculation,
            'starsURL' => $data['stars']->image130x24->url,
            'starsLabel' => $data['starsLabel']->string,
            'tpLogo' => $data['logos']['icons']->image40x40->url,
          ]])
              //. "<pre>". print_r($data , true)
              ;
      }
    }
    return '';

  }
  
}