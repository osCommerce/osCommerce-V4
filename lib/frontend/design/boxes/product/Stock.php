<?php
namespace frontend\design\boxes\product;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use frontend\design\Info;
use common\helpers\Product;
use common\classes\StockIndication;

class Stock extends Widget
{

  public $file;
  public $params;
  public $settings;

  public function init()
  {
    parent::init();
  }

  public function run()
  {
    $customer_groups_id = (int) \Yii::$app->storage->get('customer_groups_id');
    $params = Yii::$app->request->get();
    $post = Yii::$app->request->post();

    if ($ext = \common\helpers\Acl::checkExtensionAllowed('BusinessToBusiness', 'allowed')) {
        if (!$ext::checkShowStock($customer_groups_id)) {
            return '';
        }
    }
    if (!empty($this->settings[0]['with_details']) && $this->settings[0]['with_details']=='pre-order') {
      $file = 'boxes/product/stock-details.tpl';
    } else {
      $file = 'boxes/product/stock.tpl';
    }

    if ($params['products_id']) {

      $products = Yii::$container->get('products');
      $product = $products->getProduct($params['products_id']);
      if (!$product->checkAttachedDetails($products::TYPE_STOCK)){
          $products_quantity = Product::get_products_stock($params['products_id']);          
          $stock_info = StockIndication::product_info(array(
              'products_id' => $params['products_id'],
              'products_quantity' => $products_quantity,
            ));
          $product->attachDetails([$products::TYPE_STOCK => $stock_info]);
      } else {
          $stock_info = $product[$products::TYPE_STOCK];
      }    
      //$products_quantity = \common\helpers\Product::get_products_stock($params['products_id']);
    
      return IncludeTpl::widget(['file' => $file, 'params' => [
        'stock_indicator' => $stock_info
          /*\common\classes\StockIndication::product_info(array(
          'products_id' => $params['products_id'],
          'products_quantity' => $products_quantity,
          //'stock_indication_id' => (isset($products_arr['stock_indication_id'])?$products_arr['stock_indication_id']:null),
        )),*/
      ]]);
    } else {
      return '';
    }
  }
}