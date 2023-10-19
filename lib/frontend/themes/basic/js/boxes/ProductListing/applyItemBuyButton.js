if (!ProductListing) var ProductListing = {};
ProductListing.applyItemBuyButton = function($item, widgetId){
    var productId = $item.data('id');
    var $buyBox = $('.buyButton', $item);
    var $btnBuy = $('.btn-buy', $buyBox);
    var $btnPreloader = $('.btn-preloader', $buyBox);
    var $btnChooseOptions = $('.btn-choose-options', $buyBox);
    var $btnInCart = $('.btn-in-cart', $buyBox);
    var $loadedQty = $('.loaded-qty', $buyBox);
    var $btnNotify = $('.btn-notify', $buyBox);
    var $btnNotifyForm = $('.btn-notify-form', $buyBox);

    var state = tl.store.getState();
    var product = state.products[productId];
    if (entryData.GROUPS_DISABLE_CHECKOUT){
        $buyBox.hide().html('');
        return '';
    }

    var listingName = state['widgets'][widgetId]['listingName'];
    var hideAttributes = state['widgets'][widgetId]['hideAttributes'];
    var itemElements = state['productListings'][listingName]['itemElements'];
    var hasAttributes = +state['products'][productId]['product_has_attributes'];
    var pcConfigurator = +state['products'][productId]['products_pctemplates_id'];
    var isBundle = +state['products'][productId]['isBundle'];
    if (((!itemElements.attributes || hideAttributes) && hasAttributes) || isBundle || pcConfigurator) {
        $btnChooseOptions.show();
        $btnBuy.hide();
        $btnInCart.hide();
        return '';
    }

    canAddToCart();
    tl.subscribe(['widgets', widgetId, 'products', productId, 'canAddToCart'], function(){
        canAddToCart()
    });

    switchBuyButton();
    tl.subscribe(['widgets', widgetId, 'products', productId, 'productInCart'], function(){
        switchBuyButton()
    });
    tl.subscribe(['products', productId, 'stock_indicator', 'flags'], function(){
        switchBuyButton()
    });

    tl.subscribe(['widgets', widgetId, 'products', productId, 'addingToCart'], function(){
        state = tl.store.getState()
        if (
            isElementExist( ['widgets', widgetId, 'products', productId, 'addingToCart'], state)
            && state['widgets'][widgetId]['products'][productId]['addingToCart']
        ){
            $btnInCart.addClass('hide');
            $btnBuy.addClass('hide');
            $btnPreloader.show();
        } else {
            $btnInCart.removeClass('hide');
            $btnBuy.removeClass('hide');
            $btnPreloader.hide();
        }
    });

    //loadedQty();
    tl.subscribe(['productListings', 'cart', 'products', productId, 'qty'], function(){
        loadedQty()
    });

    $btnBuy.on('click', function(e){
        e.preventDefault();
        var state = tl.store.getState()
        if (
            +product.product_has_attributes &&
            !isElementExist( ['widgets', widgetId, 'products', productId, 'attributes'], state)
        ){
            window.location.href = product.link
        }
        ProductListing.addProductToCart(widgetId, productId)
    });

    $btnNotifyForm.on('click', function(){
        const $form = $(`
                <form>
                    <div class="middle-form">
                        <div class="heading-3">${entryData.tr.BACK_IN_STOCK}</div>
                        <div class="col-full">
                            <label>
                                ${entryData.tr.TEXT_NAME}
                                <input type="text" class="notify-name">
                            </label>
                        </div>
                        <div class="col-full">
                            <label>
                                ${entryData.tr.ENTRY_EMAIL_ADDRESS}
                                <input type="text" class="notify-email">
                            </label>
                        </div>
                        <div class="center-buttons">
                          <button type="submit" class="btn">${entryData.tr.NOTIFY_ME}</button>
                        </div>
                    </div>
                </form>`)
        alertMessage($form, 'notify-form');

        $form.on('submit', function(){
            if ($('.notify-name', $form).val() < entryData.tr.ENTRY_FIRST_NAME_MIN_LENGTH) {
                alertMessage(entryData.tr.NAME_IS_TOO_SHORT.replace('%s', entryData.tr.ENTRY_FIRST_NAME_MIN_LENGTH));
            } else {
                var email = $(".notify-email", $form).val();
                if (!isValidEmailAddress(email)) {
                    alertMessage(entryData.tr.ENTER_VALID_EMAIL);
                } else {
                    $.ajax({
                        url: getMainUrl() + '/catalog/product-notify',
                        data: {
                            name: $('.notify-name', $form).val(),
                            email: email,
                            products_id: productId,
                        },
                        success: function(msg) {
                            $form.html('<div>' + msg + '</div>');
                        }
                    });
                }
            }
            return false;
        })
    })

    function loadedQty(){
        state = tl.store.getState()
        if (
            isElementExist( ['productListings', 'cart', 'products', productId, 'qty'], state)
            && state['productListings']['cart']['products'][productId]['qty'] > 0
        ){
            $loadedQty.show();
            $('span', $loadedQty).html(state['productListings']['cart']['products'][productId]['qty'])
        } else {
            $loadedQty.hide();
        }
    }

    function switchBuyButton(){
        var state = tl.store.getState();
        if (
            isElementExist(['widgets', widgetId, 'products', productId, 'productInCart'], state) &&
            !isElementExist( ['themeSettings', 'showInCartButton'], state)
        ) {
            $btnInCart.show();
            $btnBuy.hide();
        } else {
            $btnInCart.hide();
            if (isElementExist(['products', productId, 'stock_indicator', 'flags', 'add_to_cart'], state)) {
                $btnBuy.show();
            } else {
                $btnBuy.hide();
            }
        }
        if (isElementExist(['products', productId, 'stock_indicator', 'flags', 'notify_instock'], state)) {
            if (hasAttributes) {
                $btnChooseOptions.show();
            } else {
                $btnNotify.show();
            }
        } else {
            $btnNotify.hide();
        }
    }

    function canAddToCart(){
        var state = tl.store.getState();
        if (
            !isElementExist(['widgets', widgetId, 'products', productId, 'canAddToCart'], state)
            && !(+state['products'][productId]['is_virtual'])
        ) {
            $buyBox.hide();
        } else {
            $buyBox.show();
        }
    }
}