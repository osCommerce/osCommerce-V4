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

namespace suppliersarea\assets;

use yii\web\AssetBundle;

class AppAsset extends AssetBundle
{
    public $basePath = '@suppliersarea';
    public $baseUrl = '@web/lib/modules/suppliers-area/resources';
    public $css = [
        'css/bootstrap.min.css',
        'css/bootstrap-switch.css',
    ];
    public $js = [
        'js/bootstrap.min.js',
        'js/bootstrap-switch.min.js',
        'js/bootbox.min.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
