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

namespace frontend\design\boxes\sample;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use common\classes\Images;
use frontend\design\CartDecorator;

class Products extends Widget
{

    public $type;
    public $settings;
    public $params;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        global $sample, $languages_id;
        
        $sampleDecorator = new CartDecorator($sample);
        $sampleDecorator->setContorllerDispatch('sample-cart');
        $sampleDecorator->setRemoveAction('remove_sample');
        
        if (Yii::$app->controller->id == 'checkout'
            || Yii::$app->controller->id == 'sample-checkout'
            || Yii::$app->controller->id == 'quote-checkout' ) {
            $this->type = 2;
        }
        
        if ($sample->count_contents() > 0) {
            return IncludeTpl::widget(['file' => 'boxes/sample/products' . ($this->type ? '-' . $this->type : '') . '.tpl', 'params' => [
              'products' => $sampleDecorator->getProducts(),
              'allow_checkout' => true,
            ]]);
        } else {
            return '<div class="empty">' . SAMPLES_CART_EMPTY . '</div>';
        }
    }
}