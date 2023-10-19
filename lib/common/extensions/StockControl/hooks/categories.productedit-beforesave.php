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

/**
 * @var $productModel common\models\Products
 * @var $TabAccess \backend\models\ProductEdit\TabAccess
 */

if ( ($ext =\common\helpers\Extensions::isAllowed('StockControl')) && $TabAccess->tabDataSave('TEXT_MAIN_DETAILS') && ($productModel->returnProductType() == 'product' )) {
    $ext::saveProduct($productModel);
}