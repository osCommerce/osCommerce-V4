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

namespace frontend\design\boxes;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;

class Breadcrumb extends Widget
{

    public $params;
    public $settings;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        global $breadcrumb;
        $breadcrumb_trail = $breadcrumb->trail();

        $count = 1;
        foreach ($breadcrumb_trail as $item) {
            \frontend\design\JsonLd::addData(['BreadcrumbList' => [
                'itemListElement' => [[
                    '@type' => 'ListItem',
                    'position' => $count,
                    'item' => ['@id' => (isset($item['link']) ? $item['link'] : ''), 'name' => strip_tags($item['name'])]
                ]]
            ]]);
            $count++;
        }

        return IncludeTpl::widget(['file' => 'boxes/breadcrumb.tpl', 'params' => [
            'breadcrumb' => $breadcrumb_trail,
            'settings' => $this->settings
        ]]);
    }
}