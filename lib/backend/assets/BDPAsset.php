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

class BDPAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        '@web/../plugins/bootstrap-datepicker/bootstrap-datepicker.min.css',
        '@web/../plugins/multiple-select/multiple-select.css',
    ];
    public $js = [
//        '@web/../plugins/bootstrap-datepicker/nonconflict.js',
        '@web/../plugins/bootstrap-datepicker/bootstrap-datepicker.js',
        '@web/../plugins/multiple-select/multiple-select.js',
    ];
}
