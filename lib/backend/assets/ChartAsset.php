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

namespace backend\assets;

use yii\web\AssetBundle;

class ChartAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        //'themes/basic/plugins/multiple-select/multiple-select.css',
    ];
    public $js = [
        'plugins/sparkline/jquery.sparkline.min.js',
        'plugins/flot/jquery.flot.min.js',
        'plugins/flot/jquery.flot.tooltip.min.js',
        'plugins/flot/jquery.flot.resize.min.js',
        'plugins/flot/jquery.flot.time.min.js',
        'plugins/flot/jquery.flot.growraf.min.js',
        'plugins/flot/jquery.flot.dashes.js',
        'plugins/chart-js-master/Chart.js',
    ];
}
