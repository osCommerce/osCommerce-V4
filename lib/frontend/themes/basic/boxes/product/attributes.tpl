{use class="Yii"}
{use class="frontend\design\Info"}
{if isset($product) && $product && $product['settings']->show_attributes_quantity|default:null && \common\helpers\Extensions::isAllowed('Inventory')}
    {\frontend\design\boxes\product\MultiInventory::widget(['params' => $params, 'settings' => $settings])}
{else}
    <div id="product-attributes" class="attributes">
        {foreach $attributes as $item}
            {if $item['type'] == 'radio'}
                {include file="`$smarty.current_dir`/attributes/radio.tpl" item=$item}
            {elseif is_array($item['options_2level']) && count($item['options_2level']) > 1}
                {include file="`$smarty.current_dir`/attributes/2level.tpl" item=$item}
            {else}
                {include file="`$smarty.current_dir`/attributes/select.tpl" item=$item}
            {/if}
        {/foreach}

        {if !Yii::$app->request->get('list_b2b')}
            <script type="text/javascript">
                {if !$isAjax && !Yii::$app->request->get('opt')}
                tl(function () {
                    if (document.forms['cart_quantity']) {
                        update_attributes($('form[name="cart_quantity"]:last')[0]);
                    }
                });
                {/if}

                function update_attributes(theForm) {

                    var _data = $(theForm).find('input, select, textarea').filter(function () {
                        return $(this).closest(".item").length == 0;
                    }).serialize();

                    $.get("{Yii::$app->urlManager->createUrl('catalog/product-attributes')}", _data + '&boxId={$boxId}', function (data, status) {
                        if (status == "success") {
                            $('#product-price-old', theForm).html(data.product_price);

                            var $productPriceCurrent = $('#product-price-current', theForm);
                            var $productPriceCurrentEx = $('#product-price-current-ex', theForm);

                            var $incVatTitle = $('.inc-vat-title', $productPriceCurrent);
                            if ($incVatTitle.length) {
                                $productPriceCurrent.html(data.product_price + ' <small class="inc-vat-title"> ' + $incVatTitle.html() + '</small>');
                            } else {
                                $productPriceCurrent.html(data.product_price);
                            }

                            if ($productPriceCurrentEx && data.product_price_ex) {
                                var $exVatTitle = $('.ex-vat-title', $productPriceCurrentEx);
                                if ($exVatTitle.length) {
                                    $productPriceCurrentEx.html(data.product_price_ex + ' <small class="ex-vat-title"> ' + $exVatTitle.html() + '</small>');
                                } else {
                                    $productPriceCurrentEx.html(data.product_price_ex);
                                }
                            }

                            if (data.hasOwnProperty('special_price') && data.special_price.length > 0) {
                                $('#product-price-special', theForm).show().html(data.special_price);
                                if (!$('#product-price-old', theForm).hasClass('old')) $('#product-price-old', theForm).addClass('old');
                                if ($productPriceCurrent.hasClass('price_1')) {
                                    $productPriceCurrent.html(data.special_price);
                                }
                            } else {
                                $('#product-price-old', theForm).removeClass('old');
                                $('#product-price-special', theForm).hide();
                            }
                            if (
                                data.hasOwnProperty('personalCatalogButton') &&
                                data.hasOwnProperty('personalCatalogButtonWrapId') &&
                                data.personalCatalogButton.length > 0
                            ) {
                                $('#personal-button-wrap-' + data.personalCatalogButtonWrapId, theForm).html(data.personalCatalogButton);
                            }
                            $('#product-attributes', theForm).replaceWith(data.product_attributes);
                            if (data.product_valid > 0) {
                                if (data.product_in_cart && !isElementExist(['themeSettings', 'showInCartButton'], entryData)) {
                                    $('.add-to-cart', theForm).hide();
                                    $('.in-cart', theForm).show();
                                    $('.qty-input', theForm).hide()
                                } else {
                                    $('.add-to-cart', theForm).show();
                                    $('.in-cart', theForm).hide();
                                    $('.qty-input', theForm).show()
                                }
                                if (data.stock_indicator) {
                                    var stock_data = data.stock_indicator;
                                    if (stock_data.add_to_cart) {
                                        if (data.add_to_cart_text) {
                                            try {
                                                $('#btn-cart button', theForm)[0].innerHTML = data.add_to_cart_text;
                                            } catch (e) {
                                            }
                                        }
                                        $('#btn-cart', theForm).show();
                                        $('.qty-input', theForm).show();
                                        if (data.product_in_cart && !isElementExist(['themeSettings', 'showInCartButton'], entryData)) {
                                            $('.add-to-cart', theForm).hide();
                                            $('.in-cart', theForm).show();
                                            $('.qty-input', theForm).hide()
                                        } else {
                                            $('.add-to-cart', theForm).show();
                                            $('.in-cart', theForm).hide();
                                            $('.qty-input', theForm).show()
                                        }
                                        $('#btn-cart-none:visible', theForm).hide();
                                    } else {
                                        $('#btn-cart', theForm).hide();
                                        if ($('.qty-input', theForm).length == 1) {
                                            $('.qty-input', theForm).hide();
                                        }
                                        if (data.product_in_cart && !isElementExist(['themeSettings', 'showInCartButton'], entryData)) {
                                            $('.add-to-cart', theForm).hide();
                                            $('.in-cart', theForm).show();
                                            $('.qty-input', theForm).hide()
                                        } else {
                                            $('.add-to-cart', theForm).show();
                                            $('.in-cart', theForm).hide();
                                            $('.qty-input', theForm).show()
                                        }
                                        $('#btn-cart-none:hidden', theForm).show();
                                    }
                                    if (stock_data.request_for_quote) {
                                        $('.btn-rfq, #btn-rfq', theForm).show();
                                        $('#btn-cart-none:visible', theForm).hide();
                                    } else {
                                        $('.btn-rfq, #btn-rfq', theForm).hide();
                                    }
                                    if (stock_data.ask_sample) {
                                        $('.btn-sample, #btn-sample', theForm).show();
                                    } else {
                                        $('.btn-sample, #btn-sample', theForm).hide();
                                    }
                                    if (stock_data.notify_instock) {
                                        $('.btn-notify-prod', theForm).show();
                                        $('#btn-cart-none:visible', theForm).hide();
                                    } else {
                                        $('.btn-notify-prod', theForm).hide();
                                    }
                                    if (stock_data.quantity_max > 0) {
                                        var qty = $('.qty-inp', theForm);
                                        $.each(qty, function (i, e) {
                                            $(e).attr('data-max', stock_data.quantity_max).trigger('changeSettings');
                                            if ($(e).val() > stock_data.quantity_max) {
                                                $(e).val(stock_data.quantity_max);
                                            }
                                        });
                                    }
                                } else {
                                    $('#btn-cart', theForm).hide();
                                    $('#btn-cart-none', theForm).show();
                                    $('.btn-notify-prod', theForm).hide();
                                    $('.qty-input', theForm).hide();
                                }
                            } else {
                                if ($('.qty-input', theForm).length == 1) {
                                    $('.qty-input', theForm).hide();
                                }
                                $('#btn-cart', theForm).hide();
                                $('#btn-cart-none', theForm).show();
                                $('.btn-notify-prod', theForm).hide();
                            }
                            if (typeof data.images != 'undefined') {
                                tl.store.dispatch({
                                    type: 'CHANGE_PRODUCT_IMAGES',
                                    value: {
                                        id: data.productId,
                                        defaultImage: data.defaultImage,
                                        images: data.images,
                                    },
                                    file: 'boxes/product/attributes.tpl'
                                });
                            }
                            if (typeof data.dynamic_prop != 'undefined') {
                                for (var prop_name in data.dynamic_prop) {
                                    if (!data.dynamic_prop.hasOwnProperty(prop_name)) continue;
                                    var _value = data.dynamic_prop[prop_name];
                                    var $value_dest = $('.js_prop-' + prop_name, theForm);
                                    if ($value_dest.length == 0) continue;
                                    $value_dest.html(_value);
                                    $value_dest.parents('.js_prop-block').each(function () {
                                        if (_value == '') {
                                            $(this).addClass('js-hide');
                                        } else {
                                            $(this).removeClass('js-hide');
                                        }
                                    });
                                }
                            }
                            if (typeof data.stock_indicator != 'undefined') {
                                $('.js-stock', theForm).html('<span class="' + data.stock_indicator.text_stock_code + '"><span class="' + data.stock_indicator.stock_code + '-icon">&nbsp;</span>' + data.stock_indicator.stock_indicator_text + '</span>');

                                if (typeof data.stock_indicator.products_date_available != 'undefined') {
                                    $('.js-date-available', theForm).html('<span class="date-available">' + data.stock_indicator.products_date_available + '</span>');
                                }
                            }
                            if ((typeof (data.flexifi_credit_plan_button) != 'undefined') && (data.flexifi_credit_plan_button != '')) {
                                $('div.flexifi-credit-plan-information', theForm).closest('div.box').html(data.flexifi_credit_plan_button);
                            }
                            $('#product-attributes select', theForm).addClass('form-control');
                            $(theForm).trigger('attributes_updated', [data]);
                            {if $ext = \common\helpers\Acl::checkExtensionAllowed('NotifyBackInStockWaitDiscount', 'allowed')}
                            if ( typeof data.notify_when_stock_text != 'undefined' ) {
                                $('#notify_when_stock_text').html(data.notify_when_stock_text);
                            }
                            if ( typeof data.notify_when_stock_description != 'undefined' ) {
                                $('#notify_when_stock_description').html(data.notify_when_stock_description);
                            }
                            {/if}
                            return data;
                        }
                    }, 'json').then(function (data) {
                        if (typeof sProductsReload == 'function') {
                            sProductsReload(data);
                        }
                    });
                }
            </script>
        {/if}
    </div>
{/if}
