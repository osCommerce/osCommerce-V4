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

namespace frontend\assets;

use yii\web\AssetBundle;

class AppAsset extends AssetBundle
{
    public $sourcePath = '@app/themes/basic/js/boxes';
    public $baseUrl = '@web/themes/basic/js';
    public $js = [
        'edit-blocks.js',
    ];

    public function init()
    {
        parent::init();

        foreach (\frontend\design\Block::$widgetsList as $name) {
            $this->js[] = $name . '.js';
        }
    }
}
