<div class="customers-activity">
    <div class="close"></div>
  <div class="content">
      <div class="text">
    {if $purchasing>0}
      <span class="message other-purchasing">{sprintf($smarty.const.PRODUCT_OTHER_PURCHASING, $purchasing)}</span>
    {/if}
    {if $viewing>0}
      <span class="message other-viewing">{sprintf($smarty.const.PRODUCT_OTHER_VIEWING, $viewing)}</span>
    {/if}
      </div>

      <div class="buttons" style="display: none">
          <button type="submit" class="btn add-to-cart">
              {$smarty.const.ADD_TO_CART}
          </button>
      </div>
  </div>
</div>

<script type="text/javascript">
    tl(function(){
        var box = $('#box-{$id}');

        var customersActivity = $('.customers-activity', box);
        var buttons = $('.buttons', box);
        $('.close', box).on('click', function(){
            customersActivity.addClass('closed')
        });

        checkAndShowButton();
        setInterval(checkAndShowButton, 1000);

        function checkAndShowButton(){
            if ($('#btn-cart .add-to-cart:visible').length) {
                buttons.show();
            } else {
                buttons.hide()
            }
        }

        setInterval(function(){
            $.get("{$widgetUrl}", {
                id: "{$id}",
                action: "catalog/product"
            }, function(d){
                $('.text', box).html($('.text', d))
            })
        }, 60000)
    })
</script>
<style type="text/css">
@keyframes customers-activity-show {
    from {
        bottom: -1000px;
    }
    50% {
        bottom: -1000px;
    }
    to {
        bottom: 0;
    }
}
@keyframes customers-activity-hide {
    from {
        bottom: 0;
    }
    to {
        bottom: -1000px;
    }
}
</style>