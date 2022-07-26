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

namespace backend\models\ProductEdit;

use yii;
use common\models\Products;

class SavePromotions
{

    protected $product;

    public function __construct(Products $product)
    {
        $this->product = $product;
    }

    public function save()
    {
        if ( empty($this->product->products_id) || !\common\helpers\Acl::checkExtensionAllowed('Promotions')) return;

        $assigned_promotions_sort_order = Yii::$app->request->post('assigned_promotions_sort_order');
        if ( !empty($assigned_promotions_sort_order) ){
            parse_str($assigned_promotions_sort_order, $sort_order_array);
            if (isset($sort_order_array['promo']) && is_array($sort_order_array['promo'])) {
                $sort_order_array = $sort_order_array['promo'];
                $sort_order_array = array_flip($sort_order_array);
            }
            if ( count($sort_order_array)>0 ) {
                $dbCollection = \common\models\promotions\PromotionsProductSort::find()
                    ->where(['products_id'=>$this->product->products_id])
                    ->all();
                $dbCollection = yii\helpers\ArrayHelper::index($dbCollection, 'promo_id');
                foreach ($sort_order_array as $promo_id=>$sort_order){
                    if ( !isset($dbCollection[$promo_id]) ){
                        $sortModel = new \common\models\promotions\PromotionsProductSort([
                            'products_id' => $this->product->products_id,
                            'promo_id' => (int)$promo_id,
                        ]);
                        $sortModel->loadDefaultValues();
                    }else{
                        $sortModel = $dbCollection[$promo_id];
                        unset($dbCollection[$promo_id]);
                    }
                    $sortModel->setAttributes([
                        'sort_order' => (int)$sort_order,
                    ], false);
                    $sortModel->save(false);
                }
                foreach ($dbCollection as $notUpdated){
                    $notUpdated->delete();
                }
            }
        }
    }

}