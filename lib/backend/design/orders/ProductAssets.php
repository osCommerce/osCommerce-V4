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

namespace backend\design\orders;


use Yii;
use yii\base\Widget;

class ProductAssets extends Widget {

    public $product;
    public $manager;

    public function init(){
        parent::init();
    }

    public function run(){
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('ProductAssets' ,'allowed')){
            if ($this->manager->isInstance()){
                return $ext::renderOrderProductAsset($this->product['orders_products_id']);
            }
        }
    }
}
