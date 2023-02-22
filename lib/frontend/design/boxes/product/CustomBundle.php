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

class CustomBundle extends Widget
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
        $params = Yii::$app->request->get();
        $params['box_id'] = $this->id;

        $details = \common\helpers\CustomBundles::getDetails($params);

        if (isset($details['all_products']) && count($details['all_products']) > 0) {
            return IncludeTpl::widget(['file' => 'boxes/product/custom-bundle.tpl',
                'params' => [
                    'products' => $details['all_products'],
                    'chosenProducts' => $details['custom_bundle_products'],
                    'old' => $details['custom_bundle_full_price'],
                    'price' => $details['custom_bundle_full_price'],
                    'isAjax' => false,
                    'id' => $this->id
                ]
            ]);
        } else {
            return '';
        }
    }
}