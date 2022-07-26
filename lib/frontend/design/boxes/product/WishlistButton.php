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

namespace frontend\design\boxes\product;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;

class WishlistButton extends Widget
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
        return '';
        global $wish_list;
        $params = Yii::$app->request->get();

        if ($params['products_id'] && !GROUPS_DISABLE_CART && !Yii::$app->user->isGuest) {

            return IncludeTpl::widget(['file' => 'boxes/product/wishlist-button.tpl', 'params' => [
                'id' => $this->id,
                'in_wish_list' => $wish_list->in_wish_list($params['products_id'])
            ]]);
        } else {
            return '';
        }
    }
}