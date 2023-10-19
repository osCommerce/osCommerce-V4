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

namespace frontend\design\boxes\quote;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use frontend\forms\registration\CustomerRegistration;

class FastOrder extends Widget
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
        /** @var \common\extensions\Quotations\Quotations $ext */
        if ( ($ext = \common\helpers\Extensions::isAllowed('Quotations')) && $ext::optionUseQuoteFastOrder() ){
            return IncludeTpl::widget(['file' => 'boxes/quote/fast-order.tpl', 'params' => array_merge($this->params, [
                'settings' => $this->settings,
                'id' => $this->id,
                'fastModel' => $this->params['enterModels']['fast']
            ])]);
        }
    }
}