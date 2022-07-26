{use class="yii\widgets\LinkPager"}
{use class="frontend\design\Info"}
{use class = "Yii"}
{use class = "yii\helpers\Html"}
{\frontend\design\Info::addBlockToWidgetsList('list-type-1')}
{\frontend\design\Info::addBoxToCss('products-listing')}
{\frontend\design\Info::addBoxToCss('wedding-list')}

{if $search}
    <span>{$search}</span>
{/if}
{Html::beginForm([''], 'get', [])}
   <div class="wr-search">
     {Html::input('text', 'product', $search, ['class' => '', 'placeholder' => 'Search'])}
       <button type="submit">{$smarty.const.BOX_HEADING_SEARCH}</button>
   </div>
{Html::endForm()}
{if $products}
    <div class="heading-2">{$smarty.const.REGISTRY_ITEMS}</div>


    <div class="products-listing list-type-1 w-list-type-1 wedding-list">

        {foreach $products as $product}
            <div class="item">
                <div class="item-holder">
                    <div class="image">
                        {if $product.status}
                            <a href="{$product.link}"><img src="{$product.image}" alt="{$product.name|escape:'html'}"
                                                           title="{$product.name|escape:'html'}"></a>
                        {else}
                            <img src="{$product.image}" alt="{$product.name|escape:'html'}"
                                 title="{$product.name|escape:'html'}">
                        {/if}
                    </div>
                    <div class="stock">
                        <span class="{$product.stock_info.text_stock_code}"><span class="{$product.stock_info.stock_code}-icon">&nbsp;</span>{$product.stock_info.stock_indicator_text}</span>
                    </div>


                    <div class="name">
                        <div class="title">
                            {if $product.status}
                                <a href="{$product.link}">{$product.name}</a>
                            {else}
                                {$product.name}
                            {/if}
                        </div>
                        <div class="model">{$smarty.const.TEXT_MODEL}: {$product.model}</div>
                        <div class="attributes">
                            {foreach $product.attr as $attr}
                                <div class="">
                                    <strong>{$attr.products_options_name}:</strong>
                                    <span>{$attr.products_options_values_name}</span>
                                </div>
                            {/foreach}
                        </div>
                    </div>

                    <div class="add-height">
                        <div class="price">
                            {if $product.stock_info.flags.request_for_quote}
                                {$smarty.const.HEADING_REQUEST_FOR_QUOTE}
                            {else}
                                {$product.final_price_formatted}
                            {/if}
                        </div>
                        <div class="rating">
                            {if $product.ordered_qty}{$ordered_qty = $product.ordered_qty}{else}{$ordered_qty = '0'}{/if}
                            {sprintf($smarty.const.ORDERED_OF_QTY_PURCHASED, $ordered_qty, $product.qty)}
                        </div>
                    </div>


                    <div class="remove"><a class="remove-btn wr-delete-item" data-id="{$product.id}"
                                           href="javascript:void(0)" title="{$smarty.const.TEXT_REMOVE_CART}"></a></div>

                    {if $product.stock_info.products_quantity > $product.qty}
                        <div class="qty-input">
                            <input type="text" name="qty" value="{$product.qty}" class="qty-inp"
                               data-max="{$product.stock_info.products_quantity}"
                               data-min = "{if $product.ordered_qty > 0}{$product.ordered_qty}{else}1{/if}"
                               data-step = "1"
                               data-product = "{$product.id}"
                            />
                        <input type="hidden" name="products_id" value="{$product.id}"/>
                    </div>
                    {/if}

                </div>
            </div>
        {/foreach}
    </div>
{/if}
{Html::beginForm([], 'post', [])}
{Html::endForm()}

{LinkPager::widget(['pagination' => $pages])}
{\frontend\design\Info::addBoxToCss('quantity')}
<script>
    tl('{Info::themeFile('/js/main.js')}', function(){
        $('.products-listing').inRow(['.image', '.name', '.price', '.buttons', '.qty-input', '.buy-button', '.add-height'], 4)

        $('input.qty-inp').quantity();

        $('body').on('click', '.qty-box .smaller', function () {
            changeQty.call(this, -1);
        })

        $('body').on('click', '.qty-box .bigger', function () {
            changeQty.call(this, 1);
        })

        var  changeQty = function(qty) {
            var product_id = $(this).closest('.qty-box').find('input.qty-inp').attr('data-product'),
                valueInput = $(this).closest('.qty-box').find('input.qty-inp'),
                value = parseInt(valueInput.val());
                $.post('{Yii::$app->urlManager->createUrl(['wedding-registry/change-qty'])}',
                       { product_id : product_id,
                        _csrf : $('input[name=_csrf]').val(),
                         qty: value
                       },
                       function(data){
                            if(!data.success){
                                valueInput.val(value - qty);
                            }
                       }
                       ,"JSON"
                )




        }


    })
</script>


