function checkout_payment_changed(){
    var cObject = {

        after_payment_changed_callbacks: [], // array of callbacks which will be called on (radio) "payment" change (payment block is replaced by several ajax calls here)
        get: function(  ) {
            return this.after_payment_changed_callbacks;
        },

        set: function( addCallback ){
            var add = true;
            addCallback = addCallback.replace(/^window\./g, '');
            for (let existing of this.after_payment_changed_callbacks) {
                if (addCallback == existing) {
                    add = false;
                    break;
                };
            }
            if (add) {
                this.after_payment_changed_callbacks.push(addCallback);
            }
        },
    }
    return cObject;
};
var checkout_payment_changed = new checkout_payment_changed();

function checkout($url){
    var cObject = {
        shipping_choice: function(_choice, $holder){
            $.get($url, {
                'choice' : _choice,
                'subaction': 'shipping_choice'
            }, function(data){
                if (data.hasOwnProperty('page')){
                    if (data.page.hasOwnProperty('blocks')){
                        $.each(data.page.blocks, function(id, e){
                            var _box = $('#'+id + ' .checkout-content');
                            $('.block.checkout', _box).replaceWith(e);
                        });
                    } else if (data.page.hasOwnProperty('widgets')){
                        $.each(data.page.widgets, function(selector, e){
                            $(selector + ' input, ' + selector + ' select').trigger('remove-validate');
                            $(selector).html(e);
                        });
                    } else {
                        $holder.replaceWith(data.page);
                    }
                }
            }, 'json');
        },
        get_address_list: function(type){
            $.get($url, {
                'type': type,
                'subaction': 'get_address_list'
            }, function(data){
                $('#'+type+'-addresses').replaceWith(data);
            }, 'json');
        },
        get_address_list_popup: function(type){
            $('<a href="' + $url + '"></a>').popUp({
                data: {
                    'type': type,
                    'subaction': 'get_address_list'
                },
                dataType: 'json',
                box_class: 'edit-address-popup',
                opened: function(){
                    $('.addresses input').one('change', function(){
                        $('.popup-box-wrap').remove()
                    })
                }
            }).trigger('click')
        },
        change_address_list: function(type, value, $holder){
            $.get($url, {
                'type': type,
                'value' : value,
                'subaction': 'change_address_list'
            }, function(data){
                if (data.hasOwnProperty('page')){
                    if (data.hasOwnProperty('page')){
                        if (data.page.hasOwnProperty('blocks')){
                            $.each(data.page.blocks, function(id, e){
                                var _box = $('#'+id + ' .checkout-content');
                                $('.block.checkout', _box).replaceWith(e);
                            });
                        } else if (data.page.hasOwnProperty('widgets')){
                            $.each(data.page.widgets, function(selector, e){
                                $(selector + ' input, ' + selector + ' select').trigger('remove-validate');
                                $(selector).html(e);
                            });
                        } else {
                            $holder.replaceWith(data.page);
                        }
                    }
                }
            }, 'json');
        },
        set_bill_as_ship: function(){
            $.get($url, {
                'subaction': 'set_bill_as_ship'
            }, function(data){
                if (data.hasOwnProperty('address')){
                    $('.billing-addresses').replaceWith(data.address);
                    $('.billing-addresses').hide()
                }
                if (data.hasOwnProperty('order_totals')){
                    $('.order_totals').replaceWith(data.order_totals);
                }
                if (data.hasOwnProperty('payments')){
                    $('#payment_method').replaceWith(data.payments);
                }
                
            }, 'json');
        },

        set_ship_as_bill: function(){
            $.get($url, {
                'subaction': 'set_ship_as_bill'
            }, function(data){
                if (data.hasOwnProperty('address')){
                    $('.shipping-addresses').replaceWith(data.address);
                    $('.shipping-addresses').hide()
                }
                if (data.hasOwnProperty('order_totals')){
                    $('.order_totals').replaceWith(data.order_totals);
                }
                if (data.hasOwnProperty('shipping')){
                    $('#shipping_method').replaceWith(data.shipping);
                }

            }, 'json');
        },
        copy_address: function(event){
            if ($('input[name=ship_as_bill]').prop('checked') ||
                $('input[name=bill_as_ship]').prop('checked')){
            //if (!$('.hide-billing-address').is(':hidden') || ($('.multi-page-checkout').is('div') && $('.checkout-step.active').attr('id') == 'shipping-step' )){
                var box = $('#' + event.data.address_box);
                var form = box.closest('form');
                $('input:visible, select:visible', box).each(function(){
                    var id = $(this).attr('id');
                    if (id){
                        id = id.replace(event.data.address_prefix, '');
                        var analog = $('.addresses:not(#'+event.data.address_box+') [id *='+id+']');
                        if ( $(this).is('input') && analog.is('input')){
                            analog.val($(this).val());
                        } else if ($(this).is('select') && analog.is('select')){
                            analog.val($(this).val());
                        } 
                    } else {
                        if ($(this).is(':radio')){
                            var checked = $(this).filter(':checked');
                            if (checked.is('input')){
                                var analog = $('.addresses:not(#'+event.data.address_box+') [value='+checked.val()+']:radio');
                                if (analog.is('input')){
                                    analog.prop('checked', true);
                                }
                            }
                        }
                        
                    }

                })
            }
        },
        edit_address: function(type, ab_id){
            $.get($url, {
                'type': type,
                'ab_id': ab_id,
                'subaction': 'edit_address'
            }, function(data){
                if (data.hasOwnProperty('address')){
                    $('#'+type+'-addresses').replaceWith(data.address);
                }
            }, 'json');
        },
        edit_address_popup: function(type, ab_id, drop_ship){
            $.get($url, {
                'type': type,
                'ab_id': ab_id,
                'drop_ship': drop_ship,
                'subaction': 'edit_address'
            }, function(data){
                if (data.hasOwnProperty('address')){
                    $('.pop-up-content').html(data.address);
                }
            }, 'json');
        },
        save_address: function(type, $holder){
            var popUpData = $('.pop-up-content input, .pop-up-content select').serializeArray();
            var formData = $frmCheckout.serializeArray();
            formData = formData.concat(popUpData);
            $.post($url+'?subaction=save_address&type='+type,
                formData, function(data){
                    $('.messageBox').hide();
                    if (data.hasOwnProperty('error')){
                        alertMessage(data.error);
                    } else if (data.hasOwnProperty('page')){
                        if (data.page.hasOwnProperty('blocks')){
                            $.each(data.page.blocks, function(id, e){
                                var _box = $('.'+id + ' .checkout-content');
                                $('.block.checkout', _box).replaceWith(e);
                            });
                        } else if (data.page.hasOwnProperty('widgets')){
                            $.each(data.page.widgets, function(selector, e){
                                $(selector + ' input, ' + selector + ' select').trigger('remove-validate');
                                $(selector).html(e);
                            });
                        } else {
                            $holder.replaceWith(data.page);
                        }
                        //$('.block.checkout').replaceWith(data.page);
                        window.history.replaceState({ }, '', window.location.href);
                    }
            }, 'json');
        },
        data_changed: function(subaction, extra_post) {
            return new Promise(function(resolve, reject){
                var popUpData = $('.pop-up-content input, .pop-up-content select').serializeArray();
                var $xhr,
                    $post_data = $frmCheckout.serializeArray();
                $post_data = $post_data.concat(popUpData);
                
                if ( extra_post && $.isArray(extra_post) ) {
                    for(var _i=0; _i<extra_post.length; _i++){
                        $post_data.push(extra_post[_i]);
                    }
                }
                if($xhr && $xhr.readyState != 4) {
                    $xhr.abort();
                }
                $xhr = $.ajax({
                    url:$url+'?subaction='+subaction,
                    data: $post_data,
                    method:'post',
                    dataType:'json',
                    success: function(data) {
                        if (data.hasOwnProperty('shipping')){
                            $('#shipping_method').replaceWith(data.shipping);
                        }
                        if (data.hasOwnProperty('payments')){
                            $('#payment_method').replaceWith(data.payments);
                        }
                        if (data.hasOwnProperty('order_totals')){
                            $('.order_totals').replaceWith(data.order_totals);
                        }
                        if (data.hasOwnProperty('products')){
                            $('.cart-listing').replaceWith(data.products);
                        }
                        if (data.hasOwnProperty('page') && data.page.hasOwnProperty('widgets')) {
                            $.each(data.page.widgets, function(selector, e){
                                $(selector + ' input, ' + selector + ' select').trigger('remove-validate');
                                $(selector).html(e);
                            });
                        }

                        if (data.hasOwnProperty('field')){
                            var inputExists = $('#'+data.field);
                            if (inputExists){
                                var errStatusContainer = data.field.replace(/(shipping)|(billing)_address\-/g, '');
                                inputExists.parent().find('i.' + errStatusContainer + '_status').text(data[errStatusContainer + '_status']);
                            }
                        }

                        if(data.hasOwnProperty('credit_modules')){
                            $('#credit_modules_message').html('');
                            $.each(data.credit_modules, function (i, e){
                                $('.discount-box.'+i).find('#credit_modules_message')
                                    .removeClass('error')
                                    .addClass(e.error ? 'error' : '')
                                    .text(e.message);
                                $('.w-checkout-credit-amount .amount-val').html(e.amount)
                            })
                        }

                        resolve();
                        // checkCountryVatState();
                    }
                });
            });
        },
        switch_update: function(object, state){
            if (object){
              if (state){
                $(object).removeClass('semi_disabled');
                $(object).find('input, button').removeAttr('disabled').removeAttr('readonly');
              }else{
                $(object).addClass('semi_disabled');
                $(object).find('input, button').attr({
                  disabled:'disabled',
                  readonly:'readonly'
                });
              }
            }
        }
    };
    return cObject;
}