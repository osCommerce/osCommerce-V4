{use class="frontend\design\Info"}
<form name="filters" action="{$filters_url}" method="get">
{$filters_hiddens}
<div class="filter-widget{if $settings[0].align_position == 'horizontal'} filter-widget-horizontal{/if}">
    <div class="head-filter-widget"><span class="head-span">{if {$filters_array|@count} > 0}{$smarty.const.TEXT_REFINE_SEARCH}{/if}</span><a class="mobileCollapse" href="#">&nbsp;</a></div>
    <div class="content-filter-widget">
{if {$filters_array|@count} > 0}
  {foreach $filters_array as $filter}
    {if {$filter.type == 'input'}}
        <div class="filter-box filter-box-ul" id="fil-{$filter.name}">
            <div class="filter-box-head"><span class="title"></span>{$filter.title}</div>
            <div class="filter-box-content"><input type="text" value="{$filter.params}" name="{$filter.name}" /></div>
						{*<div class="filter_buttons">
            <input type="Submit" class="btn" value="{$smarty.const.SUBMIT}">
            <input type="Reset" onclick="window.location.href='{$filters_url}'" class="btn" value="Reset">
						</div>*}
        </div>
    {/if}
    {if {$filter.type == 'boxes'}}
        <div class="filter-box filter-box-ul" id="fil-{$filter.name}">
            <div class="filter-box-head"><span></span>{$filter.title}</div>
            <div class="filter-box-content">
                <ul>
        {foreach $filter.values as $id => $value}
                    <li><label><input type="checkbox" value="{$value.id}" name="{$filter.name}[]" {if {is_array($filter.params) && in_array($value.id, $filter.params)}} checked{/if} /><span></span>{$value.text} ({$value.count})</label></li>
        {/foreach}
        {if {$filter.values|@count} > 5}
                    <li class="view_items"><a class="view_more">{$smarty.const.TEXT_MORE}</a></li>
        {/if}
                </ul>
            </div>
        </div>
    {/if}
    {if {$filter.type == 'slider'}}
        <div class="filter-box filter-box-ul" id="fil-{$filter.name}">
            <div class="filter-box-head"><span></span>{$filter.title}</div>
            <div class="filter-box-content">
                <div id="slider-{$filter.name}"></div>
                <div class="fsl_handle after">
                    <div><span class="handle_tit">{$smarty.const.TEXT_FROM}</span><input type="text" name="{$filter.name}from" value="{$filter.paramfrom}" size="5" id="min_{$filter.name}" placeholder="{$filter.min}"></div><div><span class="handle_tit">{$smarty.const.TEXT_TO}</span><input type="text" name="{$filter.name}to" value="{$filter.paramto}" size="5" id="max_{$filter.name}" placeholder="{$filter.max}"></div>
                </div>
            </div>
        </div>
<script>
  tl(function(){
    $('head').append('<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.min.css"/>')
  });

  tl('{Info::themeFile('/js/jquery-ui.min.js')}', function(){
    $('#slider-{$filter.name}').slider({
      change: function( event, ui ) {
        $(this).parents('form').trigger('change')
      },
      step: {$filter.step},
      range: true,
      min: {$filter.min},
      max: {$filter.max},
      values: [{if {$filter.paramfrom} > 0} {$filter.paramfrom} {else} {$filter.min} {/if}, {if {$filter.paramto} > 0} {$filter.paramto} {else} {$filter.max} {/if}],
      slide:function(e, ui){
        if (ui.values[0] > $("#min_{$filter.name}").attr('placeholder')) {
          $("#min_{$filter.name}").val(ui.values[0]);
        } else {
          $("#min_{$filter.name}").val('');
        }
        if (ui.values[1] < $("#max_{$filter.name}").attr('placeholder')) {
          $("#max_{$filter.name}").val(ui.values[1]);
        } else {
          $("#max_{$filter.name}").val('');
        }
      }
    });
    $('.mobileCollapse').click(function(){
      $(this).parent().next().slideToggle();
      $(this).toggleClass('active');
      return false;
    });
    $("#min_{$filter.name}, #max_{$filter.name}").change(function () {
      if ($("#min_{$filter.name}").val() > 0) {
        min_val = $("#min_{$filter.name}").val();
      } else {
        min_val = {$filter.min};
        $("#min_{$filter.name}").val('');
      }
      if ($("#max_{$filter.name}").val() > 0) {
        max_val = $("#max_{$filter.name}").val();
      } else {
        max_val = {$filter.max};
        $("#max_{$filter.name}").val('');
      }
      $("#slider-{$filter.name}").slider({
        values : [min_val, max_val]
      })
    });
  })
</script>
    {/if}
  {/foreach}
{/if}
{*<div class="filter_buttons">
<input type="Submit" class="btn" value="{$smarty.const.SUBMIT}">
<input type="Reset" onclick="window.location.href='{$filters_url}'" class="btn" value="Reset">
</div>*}
    </div>
</div>
</form>
<script>
  tl('{Info::themeFile('/js/main.js')}', function(){

    $('.filter-box-head').click(function() {
      $(this).toggleClass('clouse');
      $(this).parent().next().slideToggle();
    });

    var closed = {if $settings[0].open_filter == 'closed'}true{else}false{/if};
    var c_data = $.cookie('closed_data');
    if (!c_data) c_data = '';
    var closed_data = c_data.split('|');
    $('.filter-box').each(function(){
      var head = $('.filter-box-head', this);
      var content = $('.filter-box-content', this);
      var icon = $('> span', head);
      var _id = $(this).attr('id');
      var in_closed_data = closed_data.indexOf(_id);
      if (in_closed_data == -1) {
        in_closed_data = false;
      } else {
        in_closed_data = true;
      }
      if (closed && !in_closed_data || !closed && in_closed_data){
        icon.addClass('close');
        content.hide()
      }
      head.off('click').on('click', function(){
        if (icon.hasClass('close')){
          icon.removeClass('close');
          content.show();
          if (closed && !in_closed_data){
            $.cookie('closed_data', c_data + _id + '|', $.extend(cookieConfig || { }, { 'path':'' }));
          } else {
            $.cookie('closed_data', c_data.replace(_id + '|', ''), $.extend(cookieConfig || { }, { 'path':'' }))
          }
        } else {
          icon.addClass('close');
          content.hide();
          if (!closed && !in_closed_data){
            $.cookie('closed_data', c_data + _id + '|', $.extend(cookieConfig || { }, { 'path':'' }))
          } else {
            $.cookie('closed_data', c_data.replace(_id + '|', ''), $.extend(cookieConfig || { }, { 'path':'' }))
          }
        }
      })
    });

    var true_click = true;
    $('.view_more').click(function() {
      if(true_click) {
        $(this).parents('ul').find('li:nth-child(5)~li').show();
        true_click = false;
        $(this).html('less');
      } else {
        $(this).parents('ul').find('li:nth-child(5)~li').hide();
        true_click = true;
        $(this).html('{$smarty.const.TEXT_MORE}');
      }
      return false;
    });

    $('.filter_buttons').hide();
    $('form[name="filters"]').on('change', function(){
      $('.filter-listing-preloader').remove();
      $('.filter-listing-loader').remove();
      var listing = $('.products-listing');
      var pos = listing.offset();
      var width = listing.width();
      var height = listing.height();
      $('body')
              .append('<div class="filter-listing-loader"></div>')
              .append('<div class="filter-listing-preloader preloader"></div>');
      var preloader = $('.filter-listing-preloader');
      var loader = $('.filter-listing-loader');
      loader.css({
        left: pos.left,
        top: pos.top,
        width: width,
        height: height
      });
      var top = $(window).scrollTop() + ($(window).height() / 2 - 20);
      if (top < pos.top + 20) top = pos.top + 20;
      preloader.css({
        left: pos.left + (width/2 - 20),
        top: top
      });
      $.get($(this).attr('action'), $(this).serializeArray(), function(d){
        window.compare_key = 0;
        $('.main-content').html(d);
        loader.remove();
        preloader.remove()
      })
    })
  });
</script>