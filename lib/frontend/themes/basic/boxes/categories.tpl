{use class="frontend\design\Info"}
<div class="categories items-list{if isset($settings) && isset($settings[0].view_as) && $settings[0].view_as == 'carousel'} carousel{/if}">
  {foreach $categories as $category}
      <a class="item category-link" href="{$category.link}">
          {if !($settings[0].hide_images|default:null)}
              {$category.img}
          {/if}
          <h2 class="name">
              {if $category.categories_h2_tag}
                  {$category.categories_h2_tag}
              {else}
                  {$category.categories_name}
              {/if}
          </h2>
      </a>
  {/foreach}
</div>

{if isset($settings) && isset($settings[0].view_as) && $settings[0].view_as == 'carousel'}
    <script>
        tl('{Info::themeFile('/js/slick.min.js')}', function(){

            var box = $('#box-{$id}');

            var show = {if $settings[0].col_in_row|default:null}{$settings[0].col_in_row}{else}4{/if};

            $('.carousel', box).slick({
                slidesToShow: show,
                slidesToScroll: show,
                infinite: false,
                dots: true,
                responsive: [
                    {foreach $settings.colInRowCarousel as $width => $val}
                    {
                        breakpoint: {$width},
                        settings: {
                            slidesToShow: {$val},
                            slidesToScroll: {$val}
                        }
                    },
                    {/foreach}
                ]
            });

        })
    </script>
{/if}