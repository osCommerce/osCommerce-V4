{use class="frontend\design\Info"}
{capture name="video"}
  {\frontend\design\Info::addBoxToCss('product-images')}
  <div class="frame-video">
    {if !$video[0].type}
        {if $video[0].code}
    <iframe
            width="{if $settings[0].video_width}{$settings[0].video_width}{else}560{/if}"
            height="{if $settings[0].video_height}{$settings[0].video_height}{else}315{/if}"
            src="https://www.youtube.com/embed/{$video[0].code}?rel={if $settings[0].rel}0{else}1{/if}&controls={if $settings[0].controls}0{else}1{/if}&showinfo={if $settings[0].showinfo}0{else}1{/if}"
            frameborder="0"
            allowfullscreen></iframe>
        {/if}
    {else}
      <video class="video-js" width="{if $settings[0].video_width}{$settings[0].video_width}{else}560{/if}px" height="{if $settings[0].video_height}{$settings[0].video_height}{else}315{/if}px" controls>
        <source src="{$video[0].src}">
      </video>
    {/if}
  </div>
{/capture}

<div class="video-box{if $settings[0].align_position == 'horizontal'} additional-horizontal{elseif $video|@count > 1} additional-vertical{/if}">
  {if $settings[0].align_position == 'horizontal'}
    {$smarty.capture.video}
  {/if}


  {if $video|@count > 1}
  <div class="additional-videos">
    {foreach $video as $item}
      <div class="js-product-image" data-image-id="{$image_id}">
        <div class="item">
          <div>
              {if $item.type == 0}
                <img src="https://img.youtube.com/vi/{$item.code}/0.jpg" alt="" data-type="0" data-code="{$item.code}" class="add-video">
              {else}
                <video width="150px" data-type="1" class="add-video">
                  <source src="{$item.src}">
                </video>
              {/if}
          </div>
        </div>
      </div>
    {/foreach}
  </div>
  {/if}


  {if !$settings[0].align_position}
    {$smarty.capture.video}
  {/if}

</div>


<script type="text/javascript">
  tl('{Info::themeFile('/js/slick.min.js')}', function(){

    {Info::addBoxToCss('slick')}
    $('.additional-videos').slick({
      {if !$settings[0].align_position}
      vertical: true,
      rows: 3,
      {else}
      slidesToShow: 3,
      {/if}
      infinite: false
    });

    $('.additional-videos .item:first').addClass('active');
    $('.add-video').on('click', function(){
      var code = $(this).data('code');
      var type = $(this).data('type');
      $(this).closest('.additional-videos').find('.active').removeClass('active');
      $(this).closest('.item').addClass('active');
      if (type == '0') {
          $('.frame-video').html('<iframe width="{if $settings[0].video_width}{$settings[0].video_width}{else}560{/if}" height="{if $settings[0].video_height}{$settings[0].video_height}{else}315{/if}" src="https://www.youtube.com/embed/' + code + '?autoplay=1&rel={if $settings[0].rel}0{else}1{/if}&controls={if $settings[0].controls}0{else}1{/if}&showinfo={if $settings[0].showinfo}0{else}1{/if}" frameborder="0" allowfullscreen></iframe>')
      } else {
          $('.frame-video').html('');
          var $video = $(this).clone();
          $video.attr('width', {if $settings[0].video_width}{$settings[0].video_width}{else}560{/if});
          $video.attr('height', {if $settings[0].video_height}{$settings[0].video_height}{else}315{/if});
          $video.attr('controls', 'controls');
          $('.frame-video').append($video)
      }
    })
  })
</script>