{use class="frontend\design\Info"}
{\frontend\design\Info::addBoxToCss('rating')}
{$message_review}
{$id = rand()}

<div class="review-{$id}">
  <div class="stars-default"
       {for $i=1 to 5} data-text{$i}="{if defined("TEXT_RATE_$i")}{"TEXT_RATE_$i"|constant|escape:'html'}{/if}"{/for}
       >
    <input type="hidden" name="rating" class="rating" value="{$review_rate|escape:'html'}"/>
  </div>

  <div class="">
    <textarea name="review" cols="30" rows="5" style="width: 100%" class="review-text">{$review_text|escape:'html'}</textarea>
    <div style="padding-bottom: 10px">{$smarty.const.TEXT_NO_HTML}</div>
  </div>

  <div class="buttons">
    <div class="left-buttons"><span class="btn btn-cancel">{$smarty.const.CANCEL}</span></div>
    <div class="right-buttons"><span class="btn btn-submit">{$smarty.const.SEND_REVIEW}</span></div>
  </div>
</div>

<script type="text/javascript">
    tl('{Info::themeFile('/js/main.js')}' , function(){

        var box = $('.review-{$id}');

        $(".stars-default", box).rating();

        $('.btn-cancel', box).on('click', function(){
            $.get('{$link_cancel}', function(d){
                $('.product-reviews').html(d)
            })
        });

        $('.btn-submit', box).on('click', function(){
            $.post('{$link_write}', {
                action: 'process',
                rating: $('.rating', box).val(),
                review: $('.review-text', box).val(),
                _csrf: '{Yii::$app->request->getCsrfToken()}',
                products_id: '{$products_id}'
            }, function(d){
                $('.product-reviews').html(d);
            })
        })
    });
</script>
