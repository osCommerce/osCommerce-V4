{use class="frontend\design\IncludeTpl"}

{if $smarty.const.SHOW_SHIPPING_ESTIMATOR=='true'}
  <div id="js-ship-estimate-root">
    {IncludeTpl::widget(['file' => 'shopping-cart/estimate.tpl', 'params' => $params])}
    <script type="text/javascript">
    tl(function(){

      var delay = (function(){
        var timer = 0;
        return function(callback, ms){
          clearTimeout (timer);
          timer = setTimeout(callback, ms);
        };
      })();

      $('#js-ship-estimate-root').off('shipping_estimate_renew').on('shipping_estimate_renew',function() {
        var $xhr,
                $post_data = $('#js-ship-estimate-root').find('input, select').filter('[name^="estimate["]').serializeArray();
        $post_data.push({
          name:'ajax_estimate',
          value:'ajax_estimate',
        });
        
        $post_data.push({
           'name': '_csrf',
           'value': $('input[name=_csrf]').val(),
        });

        delay(function(){
          if($xhr && $xhr.readyState != 4) {
            $xhr.abort();
          }
          $xhr = $.ajax({
            url:'{$estimate_ajax_server_url}',
            data: $post_data,
            method:'post',
            dataType:'json',
            success: function(data) {
              $('#js-ship-estimate-root').replaceWith(data.estimate);
              $('.order-total').replaceWith(data.total);
                try {
                    $(window).trigger('checkout_worker_complete');
                } catch ( e ) {  }
            }
          });
        }, 300 );
      });
      $('#js-ship-estimate-root').off('click').on('click',function (event) {
        if (event.target.className && (event.target.className+' ').indexOf('js-ship-estimate ')!==-1 ) {
          $('#js-ship-estimate-root').trigger('shipping_estimate_renew');
        }
      })
    })
  </script>
  </div>
{/if}