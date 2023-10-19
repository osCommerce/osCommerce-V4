{use class="Yii"}
{use class="frontend\design\boxes\checkout\ShippingList"}
{use class="frontend\design\Info"}
{use class = "yii\helpers\Html"}
{use class="frontend\design\Block"}
<script type="text/javascript" src="{Info::themeFile('/js/checkout.js')}"></script>
{if $payment_error && $payment_error.title }
  <p><strong>{$payment_error.title}</strong><br>{$payment_error.error}</p>
{/if}
{if $message != ''}
  <p>{$message}</p>
{/if}
{\frontend\design\Info::addBoxToCss('multi-page-checkout')}
{\frontend\design\Info::addBoxToCss('form')}
{\frontend\design\Info::addBoxToCss('switch')}
{\frontend\design\Info::addBoxToCss('autocomplete')}
{\frontend\design\Info::addBoxToCss('info')}
{\frontend\design\Info::addBoxToCss('select-suggest')}

{if $noShipping}{$noShipping = '_no_shipping'}{/if}

<script type="text/javascript">
    var $frmCheckout;
    var submitter = 0;
    
    var checkout;

    tl([
      '{Info::themeFile('/js/main.js')}',
    ], function(){
        
        checkout = new checkout('{$worker}');

        $frmCheckout = $('#frmCheckout');
      
        $frmCheckout.append('<input type="hidden" name="xwidth" value="'+screen.width+'">').append('<input type="hidden" name="xheight" value="'+screen.height+'">');

        if ( typeof window.check_form == 'function' ) {
          $frmCheckout.on('submit',function(){
            return window.check_form();
          });
        }
        
        //checkCountryVatState();
 
        $('.js_discount_apply').on('click',function() {
          checkout.data_changed('credit_class', [{
            name:'coupon_apply',value:'y'
          }]);
          return false;
        });
    })

  </script>

<div class="multi-page-checkout main-width">




        {Html::beginForm($checkout_process_link, 'post', ['id' => 'frmCheckout', 'name' => 'one_page_checkout'])}
        <div class="checkout-step active shipping-step" id="shipping-step">
            <div class="checkout-heading">
                <span class="edit">{$smarty.const.EDIT}</span>
                <span class="count">1</span>
                {Block::widget(['name' => 'checkout_delivery_title'|cat:$noShipping, 'params' => ['type' => 'checkout', 'params' => $params]])}
            </div>
            <div class="checkout-content">

                {Block::widget(['name' => 'checkout_delivery'|cat:$noShipping, 'params' => ['type' => 'checkout', 'params' => $params]])}

            </div>
        </div>



        <div class="checkout-step payment-step" id="payment-step">
            <div class="checkout-heading">
                <span class="edit">{$smarty.const.EDIT}</span>
                <span class="count">2</span>
                {Block::widget(['name' => 'checkout_payment_title'|cat:$noShipping, 'params' => ['type' => 'checkout', 'params' => $params]])}
            </div>
            <div class="checkout-content" style="display: none">

                {Block::widget(['name' => 'checkout_payment'|cat:$noShipping, 'params' => ['type' => 'checkout', 'params' => $params]])}

            </div>
        </div>
        {Html::endForm()}


        <div class="checkout-step" id="confirmation-step">
            <div class="checkout-heading"><span class="count">3</span>
                {Block::widget(['name' => 'checkout_confirmation_title'|cat:$noShipping, 'params' => ['type' => 'confirmation', 'params' => $params]])}
            </div>
            <div class="checkout-content" style="display: none"></div>
        </div>

        <div class="checkout-products" id="products-totals">
            <div class="checkout-content">
            {Block::widget(['name' => 'checkout_step_bottom'|cat:$noShipping, 'params' => ['type' => 'checkout', 'params' => $params]])}
            </div>
        </div>




</div>

{$payment_javascript_validation}

  <script type="text/javascript">
    function checkCountryVatState() {
        var selected = $('select[name="country"]').val();
        if (selected == {$smarty.const.STORE_COUNTRY}) {
            $('.company_vat_box').hide();
        } else {
            $('.company_vat_box').show();
        }
    }    

    tl([
      '{Info::themeFile('/js/main.js')}',
    ], function(){
        {if Info::isAdmin()}
        $('#payment-step .checkout-content').show();
        {/if}
        
        var timeSlide = 500;
        var shippingStep = $('#shipping-step');
        var paymentStep = $('#payment-step');
        var confirmationStep = $('#confirmation-step');        
        var confirmationStepContent = $('.checkout-content', confirmationStep);
        $('body').on('click', '#shipping-step .btn-next', function(e){
            e.preventDefault();
            $('input, select', shippingStep).trigger('check');
            if ($('.required-error', shippingStep).length == 0) {
                shippingStep.removeClass('active').addClass('past');
                $('.checkout-content', shippingStep).slideUp(timeSlide);
                paymentStep.addClass('active');
                $('.checkout-content', paymentStep).slideDown(timeSlide);
                $("html, body").stop().animate({ scrollTop: shippingStep.offset().top}, timeSlide);
            }
        })        
        $('body').on('click', '#shipping-step .checkout-heading .edit', function(){
            shippingStep.addClass('active').removeClass('past');
            paymentStep.removeClass('past');
            $('.checkout-content', shippingStep).slideDown(400);
            paymentStep.removeClass('active');
            confirmationStep.removeClass('active');
            $('.checkout-content', paymentStep).slideUp(timeSlide);
            confirmationStepContent.slideUp(timeSlide);
            $('.checkout-products').slideDown(timeSlide);
        })

        var confirmationResponse = function(d, a, xhr){
            var ct = xhr.getResponseHeader("content-type") || "";
            if (ct.indexOf('html') > -1) {
                confirmationStepContent.stop();
                confirmationStepContent.css('height', confirmationStepContent.height());
                confirmationStepContent.html(d);
                confirmationStepContent
                        .animate({ height: $('> div', confirmationStepContent).height()+40}, timeSlide)
                        .removeAttr('style');
                $('.checkout-content', paymentStep).slideUp(timeSlide);
            }
            if (ct.indexOf('json') > -1) {
                d = $.parseJSON(d);
                if (d.message.length > 0 || d.payment_error.length > 0) {
                    setTimeout(function(){
                        if (d.message.length > 0) {
                            alertMessage(d.message);
                        }
                        if (d.payment_error.length > 0) {
                            var paymentError = '<div class="messageBox"><strong>' + 
                                    d.payment_error.title + 
                                    '</strong><br>' + 
                                    d.payment_error.error + '</div>';
                            alertMessage(paymentError);
                        }
                    }, timeSlide);
                    if ( d.error_box.indexOf(2) != -1) {
                        $('.checkout-heading .edit', shippingStep).trigger('click');
                    } else {
                        $('.checkout-heading .edit', paymentStep).trigger('click');
                    }
                }
            }
        };

        $('body').on('click', '#payment-step .btn-next', function(e){
            e.preventDefault();
            $('input, select', paymentStep).trigger('check');
            $('#payment-step input, #payment-step select', paymentStep).trigger('check');
            if ($('.required-error', paymentStep).length == 0) {
                confirmationStepContent.html('<div class="preloader"></div>');
                paymentStep.removeClass('active').addClass('past');
                confirmationStep.addClass('active');
                confirmationStepContent.slideDown(timeSlide);
                $('.checkout-products').slideUp(timeSlide);
                $("html, body").stop().animate({ scrollTop: shippingStep.offset().top}, timeSlide);
                var form = paymentStep.closest('form');
                $.ajax({
                    type: "POST",
                    url: form.attr('action'),
                    data: form.serializeArray(),
                    success: confirmationResponse
                });
            }
        });
        $('body').on('click', '#payment-step .checkout-heading .edit', function(){
            paymentStep.addClass('active').removeClass('past');
            $('.checkout-content', paymentStep).slideDown(400);
            confirmationStep.removeClass('active');
            confirmationStepContent.slideUp(timeSlide);
            $('.checkout-products').slideDown(timeSlide);
        });
        
        $('input', shippingStep).validate({ onlyCheck: true});
        $('input', paymentStep).validate({ onlyCheck: true});
               

    })
  
  </script>