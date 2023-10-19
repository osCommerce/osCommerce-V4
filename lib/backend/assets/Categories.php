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
use yii\web\View;

class Categories extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'themes/basic/css/categories.css',
        'plugins/nestable/nestable.css',
    ];
    public $js = [
        'plugins/nestable/jquery.nestable.min.js',
        'plugins/jquery.jcarousel.min.js',
    ];
    public $jsOptions = [
        'position' => View::POS_HEAD,
    ];
}
