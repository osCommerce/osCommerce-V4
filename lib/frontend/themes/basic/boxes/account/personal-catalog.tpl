{use class="frontend\design\Info"}
{use class = "yii\helpers\Html"}
{use class = "yii\helpers\Url"}
{if $lists}
<div class="list-switcher">
{if $lists['personal']}
    {Html::a($lists['personal']->list_name, Url::to(['account/index', 'page_name' => $page_name, 'show' => 'personal', 'list' => $lists['personal']->list_id]), ['class' => $lists['personal']->className])}
{/if}
{if $lists['wishlist']}
    {Html::a($lists['wishlist']['d']->list_name, Url::to(['account/index', 'page_name' => $page_name, 'show' => 'wishlist', 'list' => $lists['wishlist']['d']->list_id]), ['class' => $lists['wishlist']['d']->className])}
    {if is_array($lists['wishlist']['o'])}
        {foreach $lists['wishlist']['o'] as $oList}
          <a href="{Url::to(['account/index', 'page_name' => $page_name, 'show' => 'wishlist', 'list' => $oList->list_id])}" class="{$oList->className|escape}">
            {$oList->list_name}
            {if $oList->isEmpty}
              <span class="btn-del delete-list" data-href="{Url::to(['personal-catalog/delete-list', 'list' => $oList->list_id])}" data-name="{$oList->list_name|escape}"></span>
            {/if}
          </a>
        {/foreach}
    {/if}
{/if}
{Html::a(TEXT_ADD_NEW_LIST, '', ['class' => 'add-new-list'])}
</div>
{/if}
{function bActions product=$product}
    <div class="check-box">
        <label class="checkbox"><input type="checkbox" class="check-product"><span></span></label>
    </div>
    <div class="remove">
        <a class="remove-btn pc-delete-item" data-reload="1" data-id="{$product.products_id}" href="{Url::to(['personal-catalog/confirm-delete', 'list_id' => $currentList->list_id])}"></a>
    </div>
    {if $service->getModule()->getInternalActions()}
    <div class="list-row-action">
        {if $service->getModule()->isAllowedInternalMove()}
        <div class="move">
            <ul>
                <li class="title">{$smarty.const.TEXT_MOVE_TO}</li>
            {foreach $lists['all'] as $list}
                {if $list->list_id != $product['list_id']}
                    <li>{Html::a($list->list_name, Url::to(['personal-catalog/move-to', 'list_id' => $list->list_id, 'leave' => 0]), ['class' => 'move-to', 'data-id' => $product['products_id']])}</li>
                {/if}
            {/foreach}
            </ul>
        </div>
        {/if}
        {if $service->getModule()->isAllowedInternalCopy()}
        <div class="copy">
            <ul>
                <li class="title">{$smarty.const.TEXT_COPY_TO}</li>
            {foreach $lists['all'] as $list}
                {if $list->list_id != $product['list_id']}
                    <li>{Html::a($list->list_name, Url::to(['personal-catalog/move-to', 'list_id' => $list->list_id, 'leave' => 1]), ['class' => 'move-to copy-to', 'data-id' => $product['products_id']])}</li>
                {/if}
            {/foreach}
            </ul>
        </div>
        {/if}
    </div>
    {/if}
{/function}

{if $products }
        <div class="w-cart-listing{\frontend\design\Info::addBlockToWidgetsList('cart-listing')}">
            <div class="headings">
                <div class="head remove">{$smarty.const.TEXT_REMOVE_CART}</div>
                <div class="head image">{$smarty.const.PRODUCTS}</div>
                <div class="head name"></div>
                <div class="head price">{$smarty.const.PRICE}</div>
                <div class="head qty"></div>
            </div>
            {foreach $products as $product}
                <div class="item">
                    {call bActions product=$product}
                    <div class="image">
                        {if $product.products_status}
                            <a href="{$product.link}"><img src="{$product.image}" alt="{$product.products_name|escape:'html'}" title="{$product.products_name|escape:'html'}"></a>
                        {else}
                            <img src="{$product.image}" alt="{$product.products_name|escape:'html'}" title="{$product.products_name|escape:'html'}">
                        {/if}
                    </div>
                    <div class="name">
                        {if $product.products_status}
                            <a href="{$product.link}">{$product.products_name}</a>
                        {else}
                            {$product.products_name}
                        {/if}
                        <div class="attributes">
                            {foreach $product.attributes as $attr}
                                <div class="">
                                    <strong>{$attr.products_options_name}:</strong>
                                    <span>{$attr.products_options_values_name}</span>
                                </div>
                            {/foreach}
                        </div>
                        {if $product.stock_indicator}
                            <div class="{$product.stock_indicator.text_stock_code}"><span class="{$product.stock_indicator.stock_code}-icon">&nbsp;</span>{$product.stock_indicator.stock_indicator_text}</div>
                        {/if}
                    </div>
                    <div class="price">
                        {$product.final_price_formatted}
                    </div>
                    {if $product.products_status && $product.stock_indicator && ($product.stock_indicator.flags.add_to_cart || $product.stock_indicator.flags.request_for_quote) && $service->getModule()->getExternalActions()}
                        <div class="qty-input"{if $product.product_in_cart} style="display: none"{/if}>
                            <input type="hidden" name="popup" value="1"/>
                            <input type="text" name="qty" value="1" class="qty-inp"
                                    {if $product.stock_indicator.max_qty > 0 }
                                        data-max="{$product.stock_indicator.max_qty}"
                                    {/if}
                                    {if \common\helpers\Acl::checkExtensionAllowed('MinimumOrderQty', 'allowed')}{\common\extensions\MinimumOrderQty\MinimumOrderQty::setLimit($product.order_quantity_data)}{/if}
                                    {if \common\helpers\Acl::checkExtensionAllowed('OrderQuantityStep', 'allowed')}{\common\extensions\OrderQuantityStep\OrderQuantityStep::setLimit($product.order_quantity_data)}{/if}
                            />
                        </div>
                    {/if}
                    <div class="buy-box">
                    {*if $show == 'personal'}
                        {if $product.products_status}
                            {if $product.stock_indicator.flags.add_to_cart || $product.stock_indicator.flags.request_for_quote}
                                <a class="view_link pc-send-shop" data-qty="{$product.qty}" data-id="{$product.id}" data-cart="{if $product.stock_indicator.flags.add_to_cart}cart{else}quote{/if}" href="#">{$smarty.const.BOX_WISHLIST_MOVE_TO_CART}</a>
                            {/if}
                        {else}
                            {$smarty.const.TEXT_PRODUCT_DISABLED}
                        {/if}
                    {else*}
                        {if $product.products_status}
                            {if $product.stock_indicator}
                                {if $product.stock_indicator.flags.add_to_cart || $product.stock_indicator.flags.request_for_quote}
                                    {if $service->getModule()->getExternalActions()}

                                    {if $service->getModule()->isAllowedExternalAddToCart()}
                                    <a class="btn-1 btn-buy add-to-cart" data-cart="{if $product.stock_indicator.flags.request_for_quote}quote{else}cart{/if}" data-id="{$product.id}" title="{$smarty.const.ADD_TO_CART}"{if $product.product_in_cart} style="display: none"{/if}>{$smarty.const.ADD_TO_CART}</a>
                                    <a href="{tep_href_link(FILENAME_SHOPPING_CART)}" class=" btn-1 btn-cart in-cart" rel="nofollow" title="{$smarty.const.TEXT_IN_YOUR_CART}"{if !$product.product_in_cart} style="display: none"{/if}>{$smarty.const.TEXT_IN_YOUR_CART}</a>
                                    {/if}
                                    {if $service->getModule()->isAllowedExternalMoveToCart()}
                                    <a href="{Url::to(['personal-catalog/add-to-cart', 'list_id' => $product.list_id])}" data-id="{$product.id}" data-cart="{if $product.stock_indicator.flags.add_to_cart}cart{else}quote{/if}" class="btn  move-to-cart"{if $product.product_in_cart} style="display: none"{/if}>{$smarty.const.TEXT_MOVE_TO_CART_WISHLIST}</a>
                                    {/if}
                                    {/if}
                                {/if}
                          {else}
                            <a href="{$product.move_in_cart}" class="view_link"{if $product.product_in_cart} style="display: none"{/if}>{$smarty.const.TEXT_MOVE_TO_CART_WISHLIST}</a>
                          {/if}
                        {else}
                            {$smarty.const.TEXT_PRODUCT_DISABLED}
                        {/if}
                    {*/if*}
                    </div>
                </div>
            {/foreach}

        </div>
    {if $params.params.listing_split->number_of_pages > 1}
        {\frontend\design\boxes\PagingBar::widget($params)}
    {/if}

    <br><div class="buttons">
        <span class="btn-1 btn-add-selected" style="display: none;">{$smarty.const.ADD_SELECTED_TO_CART}</span>
        <span class="btn-1 btn-del-selected" style="display: none;">{$smarty.const.BUTTON_DELETE_SELECTED}</span>
        <span class="btn-1 btn-add-all">{$smarty.const.ADD_ALL_TO_CART}</span>
    </div>
{else}
    {$empty = true}
{/if}
<script type="text/javascript">
    tl(['{Info::themeFile('/js/main.js')}'], function(){
        var showedPopup = false;

        $('input.qty-inp').quantity();
        var body = $('body');
        body.on('click','.pc-send-shop', function(event) {
            event.preventDefault();
            showedPopup = false;
            actionPersonalCatalog(
                '{Yii::$app->urlManager->createUrl('personal-catalog/add-to-cart')}',
                $(this).attr('data-id'),
                $(this).attr('data-cart'),
                $(this).attr('data-qty'),
            );
        });
        body.on('click', '.pc-delete-item', function (event) {
            event.preventDefault();
            showedPopup = false;
            actionPersonalCatalog(
                $(event.target).attr('href'),
                $(this).attr('data-id'),
                '',
                1,
                1
            );
        });
        body.on('click', '.move-to', function (event) {
            event.preventDefault();
            showedPopup = false;
            actionPersonalCatalog(
                $(event.target).attr('href'),
                $(this).attr('data-id'),
                '',
                1,
                1
            );
        });

        body.on('click', '.delete-list', function (event) {
            event.preventDefault();
            showedPopup = false;
            confirmMessage('<p>{$smarty.const.CONFIRM_DELETE_WISHLIST}<br/>'+$(this).attr('data-name')+'</p>',
            function () {
            $.post($(event.target).attr('data-href'),
                {
                    _csrf: $('meta[name="csrf-token"]').attr('content'),
                    reload: 1,
                }, function(response){
                    if(response.hasOwnProperty('message') && !showedPopup) {
                        showedPopup = true;
                        alertMessage(response.message);
                        //setTimeout(function(){ $('.pop-up-close:last').trigger('click'); }, 2500);
                    }
                    if(response.hasOwnProperty('reload') && response.reload == "1") {
                        setTimeout(window.location.replace("{\Yii::$app->urlManager->createUrl(['account', 'page_name' => 'personal_catalog', 'show' => 'wishlist'])}"), 3000);
                    }
                })
              },
            '{$smarty.const.TEXT_YES|escape}',
            '{$smarty.const.TEXT_NO|escape}'
            );
        });
        
        body.on('click', '.add-to-cart', function(event){
            event.preventDefault();
            showedPopup = false;
            actionPersonalCatalog(
                '{Yii::$app->urlManager->createUrl('personal-catalog/add-to-cart')}',
                $(this).attr('data-id'),
                $(this).attr('data-cart'),
                $(this).prev().find('.qty-inp').val(),
                0,
                0,
                function(data){
                    var box = $(event.target).closest('.price');
                    $.each(box.children(), function(i, e){
                        $(e).hide();
                    })
                    box.find('.in-cart').show();
                }
            );
        })

        body.on('change', '.check-product', function(event){
            if ($('.check-product:checked').length > 0) {
                $('.btn-add-selected').show()
                $('.btn-del-selected').show()
            } else {
                $('.btn-add-selected').hide()
                $('.btn-del-selected').hide()
            }
        })

        body.on('click', '.move-to-cart', function(event){
            showedPopup = false;
            event.preventDefault();
            actionPersonalCatalog(
                '{Yii::$app->urlManager->createUrl('personal-catalog/add-to-cart')}',
                $(this).attr('data-id'),
                $(this).attr('data-cart'),
                1,
                1,
                1
            );
        })

        body.on('click', '.btn-add-selected, .btn-add-all', function(event){
          showedPopup = false;
          var postData = [];
          postData.push({
            name: '_csrf',
            value: $('meta[name="csrf-token"]').attr('content')
          });
          var sel = '';
          if ($(this).hasClass('btn-add-selected')) {
            sel = ':checked';
          }
          $('.check-product'+sel).closest('.item').each(function(){
            $block = $(this);
            var qty = $('.qty-inp', $block).val();
            var uprid = $('.add-to-cart', $block).attr('data-id');
            if (qty && qty>0) {
              postData.push({
                name: 'qty[' + uprid + ']',
                value: qty
              });
            }
          });
          $.post("{\Yii::$app->urlManager->createUrl(['personal-catalog/index', 'action'=>'quick_buy'])}", postData,
            function(response){
/*              if(response.hasOwnProperty('message') && !showedPopup) {
                showedPopup = true;
                alertMessage(response.message);
              }*/
              window.location.href = "{\Yii::$app->urlManager->createUrl(['shopping-cart'])}";
            });
        })

        body.on('click', '.btn-del-selected', function(e){
            e.preventDefault();
            var postData = [], cnt = 0;
            postData.push({
              name: '_csrf',
              value: $('meta[name="csrf-token"]').attr('content')
            });
            $('.check-product:checked').closest('.item').each(function(){
              cnt++;
              $block = $(this);
              var uprid = $('.add-to-cart', $block).attr('data-id');
                postData.push({
                  name: 'products_id[]',
                  value: uprid
                });
            });

            if (cnt>0) {
              var msg = "{$smarty.const.CONFIRM_DELETE_SELECTED_FROM_WISHLIST|escape}";
              confirmMessage('<div class="new-list-box">' + msg.replace('%n', cnt) + '</div>',
              function () {
                $.post("{\Yii::$app->urlManager->createUrl(['personal-catalog/delete', 'listId' => $currentList->list_id])}", postData, function(response){
                      if(response.hasOwnProperty('message') && !showedPopup) {
                          showedPopup = true;
                          alertMessage(response.message);
                          //setTimeout(function(){ $('.pop-up-close:last').trigger('click'); }, 2500);
                      }
                      setTimeout(window.location.reload(), 3000);
                  })
              },
              '{$smarty.const.TEXT_DELETE|escape}',
              '{$smarty.const.CANCEL|escape}'
              );
            }
        })

        $('.add-new-list').click(function(e){
            e.preventDefault();
            confirmMessage('<div class="new-list-box">{Html::textInput('list_name', '', ['class' => 'list-name', 'required' => 'required'])|escape:'javascript'}</div>',
            function () {
            $.post("{\Yii::$app->urlManager->createUrl(['personal-catalog/list'])}",
                {
                    _csrf: $('meta[name="csrf-token"]').attr('content'),
                    list_name: $('input[name="list_name"]').val()
                }, function(response){
                    if(response.hasOwnProperty('message') && !showedPopup) {
                        showedPopup = true;
                        alertMessage(response.message);
                        //setTimeout(function(){ $('.pop-up-close:last').trigger('click'); }, 2500);
                    }
                    setTimeout(window.location.reload(), 3000);
                })
              },
            '{$smarty.const.TEXT_ADD|escape}',
            '{$smarty.const.CANCEL|escape}'
            );
            $("#listName").focus();
        })

        function actionPersonalCatalog(action, id, in_cart, qty, reload, deleteAfter, callback) {
            if (qty === undefined ) {
                qty = 1;
            }
            if (reload === undefined) {
                reload = 0;
            }

            $.post(action,
                {
                    _csrf: $('meta[name="csrf-token"]').attr('content'),
                    products_id:id,
                    pc_in_cart:in_cart,
                    pc_qty: qty,
                    reload: reload,
                    deleteAfter:deleteAfter,
                }
                , function(response){
                    if(response.hasOwnProperty('message') && !showedPopup) {
                        showedPopup = true;
                        alertMessage(response.message);
                        //setTimeout(function(){ $('.pop-up-close:last').trigger('click'); }, 2500);
                    }
                    if(response.hasOwnProperty('reload') && response.reload == "1") {
                        setTimeout(window.location.reload(), 3000);
                    }
                    if (typeof callback == 'function'){
                        callback(response);
                    }
                });
        }
    {if $empty && !Info::isAdmin()}
        {if $settings[0].hide_parents == 1}
        $('#box-{$id}').hide()
        {elseif $settings[0].hide_parents == 2}
        $('#box-{$id}').closest('.box-block').hide()
        {elseif $settings[0].hide_parents == 3}
        $('#box-{$id}').closest('.box-block').closest('.box-block').hide()
        {elseif $settings[0].hide_parents == 4}
        $('#box-{$id}').closest('.box-block').closest('.box-block').closest('.box-block').hide()
        {/if}
    {/if}
    });
</script>