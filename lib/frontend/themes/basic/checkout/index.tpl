{use class="Yii"}
{use class="frontend\design\Info"}
{use class="frontend\design\Block"}
{use class = "yii\helpers\Html"}
{\frontend\design\Info::addBoxToCss('info')}
{\frontend\design\Info::addBoxToCss('select-suggest')}
{\frontend\design\Info::addBoxToCss('autocomplete')}
{\frontend\design\Info::addBoxToCss('preloader')}

<script type="text/javascript" src="{Info::themeFile('/js/checkout.js')}"></script>
<script type="text/javascript">
    var $frmCheckout;
    var submitter = 0;   
    
    var checkout;
    
    function checkCountryVatState() {
        var selected = $('select[name="country"]').val();
        if (selected == '{$smarty.const.STORE_COUNTRY}') {
            $('.company_vat_box').hide();
        } else {
            $('.company_vat_box').show();
        }
    }

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
 
        /*$('.js_discount_apply').on('click',function() {
          checkout.data_changed('credit_class', [{
            name:'coupon_apply',value:'y'
          }]);
          return false;
        });*/
    })

  </script>
{if $payment_error && $payment_error.title }
    <div class="" id="payment_error-box" style="display:none;">
        <div class="" style="padding: 20px">
            <strong>{$payment_error.title}</strong><br>
            {$payment_error.error}
            <div>{$smarty.const.TEXT_PAYMENT_ERROR_TRY_OTHER_METHOD}</div>
        </div>
    </div>
    <script>
        tl('{Info::themeFile('/js/main.js')}', function(){
            $('<a href="#payment_error-box"></a>').popUp().trigger('click')
        });
    </script>
{/if}


{if $message != ''}
    <div class="" id="messages-box" style="display:none;"><div class="" style="padding: 20px">{$message}</div></div>
    <script>
        tl('{Info::themeFile('/js/main.js')}', function(){
            $('<a href="#messages-box"></a>').popUp().trigger('click')
        });
    </script>
{/if}


{Html::beginForm('', 'post', ['id' => 'frmCheckout', 'name' => 'one_page_checkout'])}
  {\frontend\design\Info::addBoxToCss('form')}


    {Block::widget(['name' => $page_name, 'params' => ['type' => 'checkout', 'params' => $params]])}


{Html::endForm()}

{$payment_javascript_validation}


<script type="text/javascript">
    tl('{Info::themeFile('/js/main.js')}', function(){
        $('.order-summary').scrollBox();

        $('.closeable-box').closeable();
    })

    tl(function(){
        var prevent_submit_checkout_form = $('form[name=one_page_checkout]');
        //var prevent_submit_checkout_button = $('.w-checkout-continue-btn button[type=submit]');
        var prevent_submit_checkout_button = $('.w-checkout-continue-btn');
//double click
        prevent_submit_checkout_form && prevent_submit_checkout_form.submit(function(){
            var $hidePage = $('<div class="hide-page" style="align-items: center;background-color: rgba(255, 255, 255, 0.7);height:100%; justify-content: center; left:0; position: fixed; top:0; width:100%; z-index: 100000; display:flex;"><div class="preloader"></div></div>')
            var $fakeInput = $('<div class="fake-input" style="height:0; overflow: hidden;"><input type="text"></div>')
            $('body').append($hidePage);
            $('.main-content').append($fakeInput);
            $('input', $fakeInput).focus()
            $('.w-checkout-continue-btn button[type=submit]').prop('disabled', true);
            setTimeout(removeDisabled, 7000);
            setTimeout(function(){
                if ($('.required-error', prevent_submit_checkout_form).length) {
                    removeDisabled()
                }
            }, 100);
            function removeDisabled() {
                $('.w-checkout-continue-btn button[type=submit]').prop('disabled', false);
                $hidePage.remove()
                $fakeInput.remove()
            }
        });

//ajax in progress
        var ajaxCount = 0;
        $( document ).ajaxSend(function(e,x,p) {
            if (p.url.indexOf('/worker')!==-1) ajaxCount++;
            if ( ajaxCount>0 ) {
                //prevent_submit_checkout_form.css('opacity','0.3');
                prevent_submit_checkout_button.css('opacity','0.2');
                prevent_submit_checkout_form.addClass('disable-checkout');
            }
        });
        $(document).ajaxComplete(function (e, x, p) {
            if (p.url.indexOf('/worker') !== -1)
                ajaxCount--;
            if (ajaxCount == 0) {
                //prevent_submit_checkout_form.css('opacity', '1');
                prevent_submit_checkout_button.css('opacity', '1');
                prevent_submit_checkout_form.removeClass('disable-checkout');
                try {
                    $(window).trigger('checkout_worker_complete');
                } catch ( e ) {  }
            }
        });
        prevent_submit_checkout_form.on('submit', function () {
            if (prevent_submit_checkout_form.hasClass('disable-checkout')) {
                //alert('request in progress. Please wait.'); 
                return false;
            }
        });
   });


</script>



