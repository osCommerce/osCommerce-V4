<?php

/*
 * This file is part of osCommerce ecommerce platform.
 * osCommerce the ecommerce
 * 
 * @link https://www.oscommerce.com
 * @copyright Copyright (c) 2005 Holbi Group Ltd
 * 
 * Released under the GNU General Public License
 * For the full copyright and license information, please view the LICENSE.TXT file that was distributed with this source code.
 */

namespace common\helpers;

use Yii;

class Thumb {

    public static function setProductPagination(\yii\web\Session $storage, $view, $currentProductsId) {
        $view->product_next = $view->product_prev = 0;
        
        if ($storage->has('products_query_raw')) {
            $products_query_raw_real = $storage->get('products_query_raw');
            if (strpos($products_query_raw_real, 'limit')) {
                $products_query_raw_real = preg_replace("/(.*)limit(.*)/", "$1 limit 100", $products_query_raw_real);
            }
            $products_query_raw_real = preg_replace("/\*/", "p.products_id", $products_query_raw_real);
            $products_query_raw_current = "select (@i:=@i+1) as iter, cur.products_id from ({$products_query_raw_real}) cur";
            
            $view->product_prev = $view->product_next = 0;
            Yii::$app->db->createCommand("set @i:=-1;")->execute();
            $allProds = \yii\helpers\ArrayHelper::map(Yii::$app->db->createCommand($products_query_raw_current)->queryAll(), 'iter', 'products_id');
            $allProdsRev = array_flip($allProds);
            $allProdsIter = new \ArrayIterator($allProds);
            $curKey = $allProdsRev[(int)$currentProductsId] ?? null;
            try{
                $allProdsIter->seek($curKey);
                if ($allProdsIter->offsetExists($curKey - 1)) {
                    $allProdsIter->seek($curKey - 1);
                    $view->product_prev = $allProdsIter->current();
                }
                $allProdsIter->seek($curKey);
                if ($allProdsIter->offsetExists($curKey + 1)) {
                    $allProdsIter->seek($curKey + 1);
                    $view->product_next = $allProdsIter->current();
                }
            } catch (\Exception $ex) {
                Yii::info('Incorrect product thumbing', 'thumb');
            }
            
            if ($view->product_next)
                $view->product_next_name = \common\helpers\Product::get_backend_products_name($view->product_next);
            if ($view->product_prev)
                $view->product_prev_name = \common\helpers\Product::get_backend_products_name($view->product_prev);
        }
    }

}
