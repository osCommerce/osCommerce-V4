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

namespace frontend\design\boxes\cart;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;

class BonusPoints extends Widget
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
        if (!\common\helpers\Acl::checkExtensionAllowed('BonusActions')) {
            return '';
        }
        $groupId = (int) \Yii::$app->storage->get('customer_groups_id');
        /** @var \common\services\OrderManager $manager */
        $manager = $this->params['manager'];
        $bonus_points = $manager->getBonusesDetails();
        return IncludeTpl::widget(['file' => 'boxes/cart/bonus-points.tpl', 'params' => [
            'bonus_points' => $bonus_points,
            'id' => $this->id,
            'groupId' => $groupId
        ]]);
    }
}
