{use class="Yii"}
{use class="frontend\design\boxes\cart\Products"}
{use class="frontend\design\boxes\cart\GiveAway"}
{use class="frontend\design\Block"}
{use class="frontend\design\Info"}
{use class="\yii\helpers\Html"}

<div class="cart-page" id="cart-page">
  {Html::beginForm($action, 'post', ['id' => 'cart-form'])}  

    {$message_shopping_cart}
    {Block::widget(['name' => 'cart', 'params' => ['type' => 'cart', 'params' => ['manager' => $manager]]])}

  {Html::endForm()}
  {*use class="common\components\google\widgets\GoogleTagmanger}
  {GoogleTagmanger::trigger()}
  {if !$app->request->isAjax}
  {GoogleTagmanger::getJsEvents([[ 'class' => '.w-cart-checkout-btn a', 'action' => 'click' , 'php_action' => 'checkout', 'page' => 'shopping-cart', 'immidiately' => 'true' ]])}
  {/if*}
</div>


<script type="text/javascript">
  tl([
      '{Info::themeFile('/js/main.js')}',
      '{Info::themeFile('/js/bootstrap-switch.js')}'
  ], function(){
      $('.closeable-box').closeable();

      $('.order-summary').scrollBox()

    var form = $('#cart-form');

    {\frontend\design\Info::addBoxToCss('quantity')}
    $('input.qty-inp-s').quantity({
      event: function(){
        form.trigger('cart-change');
      }
    }).on('blur', function(){
      form.trigger('cart-change');
    });

    {\frontend\design\Info::addBoxToCss('switch')}
    $(".check-on-off").bootstrapSwitch({
      offText: '{$smarty.const.TEXT_NO}',
      onText: '{$smarty.const.TEXT_YES}',
      onSwitchChange: function () {
        $(this).closest('form').trigger('cart-change');
      }
    });

    var send = 0;
    form.off('cart-change').on('cart-change', function(){
      send++;
      $.post(form.attr('action'), form.serializeArray(), function(d){
        send--;
        if (send == 0) {
            var inputName = $('input:focus').attr('name');
            $('body > .main-content').html(d);
            var inp = $('input[name="'+inputName+'"]').focus().get(0);

            if (inp && inp.value) {
                var val = inp.value;
                inp.value = '';
                inp.value = val;
            }
        }
        $(window).trigger('cart_change');
      });
    });

    $('.remove-btn').on('click', function(){
      $.get($(this).attr('href'), function(d){
        $('#cart-page').replaceWith(d);
        $(window).trigger('cart_change')
      });
      return false
    });

    $('.input-apple button').on('click', function(){
      $.post(form.attr('action'), form.serializeArray(), function(d){
        $('#cart-page').replaceWith(d);
        $(window).trigger('cart_change')
      });
      return false
    });

	if($('.cart-page .w-cart-products .empty').length) {
		$('.cart-page .order-summary').hide();
		$('.cart-page .closeable-box').hide();
		$('.cart-page .closeable-content').hide();
	}

    $('.addresses input').radioHolder({ holder: '.address-item'});
    $('.shipping-method input').radioHolder();
  })
</script>