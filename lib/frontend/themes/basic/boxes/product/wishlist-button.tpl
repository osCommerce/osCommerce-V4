{use class="Yii"}
{use class="frontend\design\Info"}

<span class="btn add_to_whishlist">{$smarty.const.ADD_TO_WISH_LIST}</span>

<script type="text/javascript">
  tl('{Info::themeFile('/js/main.js')}' , function(){

    var product_form = $('#product-form');

    $('#box-{$id} .add_to_whishlist').on('click', function(){
      product_form.append('<input type="hidden" name="add_to_whishlist" value="1">');
      product_form.append('<input type="hidden" name="popup" value="1">');
      $.post(product_form.attr('action'), product_form.serializeArray(), function(d){
        alertMessage(d);		
				if($(d).filter("#error_wishlist_popup").length > 0){
				}else{
					$('.popup-box').addClass('cart-popup');
				}        
        $('.alert-message').removeClass('alert-message')
      });
      $('input[name="add_to_whishlist"]').remove();
      $('input[name="popup"]').remove()
    })

  });


</script>