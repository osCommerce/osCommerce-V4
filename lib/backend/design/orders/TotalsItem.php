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

class TotalsItem extends Widget {

    public $order;
    public $manager;

    public function init(){
        parent::init();
    }

    public function run(){

        $pData = false;
        if(\common\helpers\Acl::checkExtensionAllowed('CollectionPoints') && $this->order->info['pointto'] > 0){
            $pData = \common\extensions\CollectionPoints\models\CollectionPoints::findOne($this->order->info['pointto']);
        }

        $parent = false;
        if ($this->order && method_exists($this->order, 'getParent'))
            $parent = $this->order->getParent();

        $totalItem = 0;
        foreach ($this->order->products as $opRecord) {
            $totalItem += \common\helpers\Product::getVirtualItemQuantity($opRecord['id'], $opRecord['qty']);
        }

        return $this->render('totals-item', [
            'items' => $totalItem,
            'order' => $this->order,
            'shipping_weight' => $this->order->info['shipping_weight'],
            'parent' => $parent,
            'pData' => $pData,
        ]);
    }
}
