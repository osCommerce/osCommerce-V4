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

namespace frontend\design\boxes\promotions;
use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;

class PromoTrigger extends Widget {
    
    public $file;
    public $params;
    public $settings;
    public $isAjax;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        if(\common\helpers\Acl::checkExtensionAllowed('BonusActions') && defined('BONUS_ACTION_PROGRAM_STATUS') && BONUS_ACTION_PROGRAM_STATUS == 'true'){
            
            return IncludeTpl::widget(['file' => 'boxes/promotions/trigger.tpl', 'params' => [
            ]]);
        }
    }
    
}