<div class="search-ico"></div>
<div class="search suggest-js">
  <form action="{$link}" method="get">
    <input class="search-input" type="text" name="keywords" placeholder="{$smarty.const.ENTER_YOUR_KEYWORDS}" value="{$keywords}" />
{if $smarty.const.SEARCH_IN_DESCRIPTION == 'True'}
    <input type="hidden" name="search_in_description" value="1" />
{/if}
    <button class="button-search" type="submit"></button>
    {$extra_form_fields}
  </form>
</div>
<script type="text/javascript">
  tl(function(){

      var box = $('#box-{$id}');
      var searchCloseKey = true;
      var closeSearch = function(){
          setTimeout(function(){
              if (searchCloseKey) {
                  $('.search', box).removeClass('opened');
                  $('body').off('click', closeSearch)
              }
              searchCloseKey = true;
          }, 100)
      };

      $('.search', box).on('click', function(){
          if (!$(this).hasClass('opened')) {
              $(this).addClass('opened');

              setTimeout(function(){
                $('body').on('click', closeSearch)
              }, 100)
          }
      });
      $('form', box).on('click', function(){
          searchCloseKey = false
      });

    var input_s = $('.suggest-js input');
    input_s.attr({
      autocomplete:"off"
    });

    var ssTimeout = null;
    input_s.keyup(function(e){
      $('.suggest').addClass('loading');
      if (ssTimeout != null) {
        clearTimeout(ssTimeout);
      }
      ssTimeout = setTimeout(function() {
        ssTimeout = null;
        if ($(input_s).val().length>1) {
          jQuery.get('{$searchSuggest}', {
              keywords: $(input_s).val()
             }, function(data){
                 $('.suggest').remove();
                 $('.suggest-js').append('<div class="suggest">'+data+'</div>')
               });
        };
      }, 400 );
    });
    input_s.blur(function(){
      setTimeout(function(){
        $('.suggest').hide()
      }, 200)
    });
    input_s.focus(function(){
      $('.suggest').show()
    })

    $('.search-ico', box).on('click', function(){
      $('.suggest-js', box).toggleClass('opened')
    })
  })
</script>