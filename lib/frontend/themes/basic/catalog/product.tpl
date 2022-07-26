{use class="frontend\design\Block"}{use class = "yii\helpers\Html"}
{Html::beginForm($action, 'post', ['id' => 'product-form', 'name' => 'cart_quantity'], false)}
  <input type="hidden" name="products_id" value="{$products_prid|escape:'html'}"/>
  <div class="product" {*itemscope itemtype="http://schema.org/Product"*}>
    {Block::widget(['name' => $page_name, 'params' => ['type' => 'product', 'params' => ['message' => $message]]])}
  </div>
{Html::endForm()} 