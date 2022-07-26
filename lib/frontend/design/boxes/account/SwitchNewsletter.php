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

class SwitchNewsletter extends Widget
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
        if (\common\helpers\Acl::checkExtensionAllowed('Subscribers', 'allowed') && (!defined('ENABLE_CUSTOMERS_NEWSLETTER') || ENABLE_CUSTOMERS_NEWSLETTER != 'true') && !$this->settings[0]['hide_parents']) {
            return '';
        }
        return IncludeTpl::widget(['file' => 'boxes/account/switch-newsletter.tpl', 'params' => [
            'settings' => $this->settings,
            'params' => $this->params,
            'id' => $this->id,
        ]]);
    }
}