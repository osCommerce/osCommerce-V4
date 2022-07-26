<?php
/**
 * This file is part of osCommerce ecommerce platform.
 * osCommerce the ecommerce
 *
 * @link https://www.oscommerce.com
 * @copyright Copyright (c) 2000-2022 osCommerce LTD
 *
 * Released under the GNU General Public License
 * For the full copyright and license information, please view the LICENSE.TXT file that was distributed with this source code.
 */

namespace frontend\design\boxes\account;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use common\extensions\Subscribers\models\SubscribersLists;
use common\extensions\Subscribers\models\CustomersToLists;

class Subscription extends Widget
{

    public $file;
    public $params;
    public $settings;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
      global $navigation;

      if ( Yii::$app->user->isGuest ) {
          $navigation->set_snapshot();
          tep_redirect(tep_href_link('account/login','','SSL'));
      }
      $user = Yii::$app->user->getIdentity();

      $customer_id = Yii::$app->user->getId();
      $languages_id = \Yii::$app->settings->get('languages_id');

      
      $q = SubscribersLists::find()->alias('l')
          ->leftJoin(['s2l' => CustomersToLists::tableName()], 'l.subscribers_lists_id=s2l.subscribers_lists_id and s2l.customers_id=:customer_id', ['customer_id' => $customer_id])
          ->andWhere([
            'l.platform_id' => \common\classes\platform::currentId(),
            'l.language_id' => $languages_id,
            'l.status' => 1,
          ])
          ->addOrderBy('l.sort_order, l.name')
          ->select([
                'id' => 'l.subscribers_lists_id',
                'title' => 'l.name',
                'description' => 'l.description',
                'yes' => 's2l.subscribers_lists_id',
            ])
          ;
        $variants = $q->asArray()->all();

        return IncludeTpl::widget(['file' => 'boxes/account/subscription.tpl', 'params' => [
            'settings' => $this->settings,
            'params' => $this->params,
            'id' => $this->id,
            'newsletter' => $user->customers_newsletter,
            'variants' => $variants,
        ]]);
    }
}