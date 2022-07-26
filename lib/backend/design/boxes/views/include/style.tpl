{use class="\backend\design\Style"}
{$styleHide = Style::hide($settings.data_class)}
{$styleShow = Style::show($settings.data_class)}
<div class="tabbable tabbable-custom box-style-tab">
  <ul class="nav nav-tabs nav-tabs-scroll style-tabs">

    <li class="active"><a href="#main_view" data-toggle="tab">{$smarty.const.BOX_HEADING_MAIN_STYLES}</a></li>
    {if $styleHide.hover !== 1}
    <li><a href="#hover" data-toggle="tab">hover</a></li>
    {/if}
    {if $styleShow.active == 1}
      <li><a href="#active" data-toggle="tab">active</a></li>
    {/if}
    {if $styleHide.responsive !== 1}
    {foreach $settings.media_query as $item}
      <li><a href="#m{$item.id}" data-toggle="tab">{$item.setting_value}</a></li>
    {/foreach}
    {/if}
    {if $styleHide.before !== 1}
      <li><a href="#before" data-toggle="tab">:before</a></li>
    {/if}
    {if $styleHide.after !== 1}
      <li><a href="#after" data-toggle="tab">:after</a></li>
    {/if}

  </ul>
  <div class="tab-content menu-list style-tabs-content">
    <div class="tab-pane active" id="main_view" data-id="main_view" data-name="setting[0]" data-visibility="0">

      {*<div class="demo-box">AaBbCc 1 2 3 4 5</div>*}
      {*$id = 'main_view'}
      {$name = 'setting[0]'}
      {$value = $settings[0]}
      {include 'include/style_tab.tpl'*}

    </div>
    <div class="tab-pane" id="hover" data-id="hover" data-name="visibility[0][1]" data-visibility="1">

      {*$id = 'hover'}
      {$name = 'visibility[0][1]'}
      {$value = $visibility[0][1]}
      {include 'include/style_tab.tpl'*}

    </div>
    {if $styleShow.active == 1}
      <div class="tab-pane" id="active" data-id="active" data-name="visibility[0][2]" data-visibility="2">

        {*$id = 'active'}
        {$name = 'visibility[0][2]'}
        {$value = $visibility[0][2]}
        {include 'include/style_tab.tpl'*}

      </div>
    {/if}
    {$responsive = 1}
    {foreach $settings.media_query as $item}
    <div
            class="tab-pane"
            id="m{$item.id}"
            data-id="{'m'|cat:$item.id}"
            data-name="{'visibility[0]['|cat:$item.id|cat:']'}"
            data-visibility="{$item.id}">

      {*$id = 'm'|cat:$item.id}
      {$name = 'visibility[0]['|cat:$item.id|cat:']'}
      {$value = $visibility[0][$item.id]}
      {include 'include/style_tab.tpl'*}

    </div>
    {/foreach}
    <div class="tab-pane" id="before" data-id="before" data-name="visibility[0][3]" data-visibility="3">

      {*$id = 'before'}
      {$name = 'visibility[0][3]'}
      {$value = $visibility[0][3]}
      {include 'include/style_tab.tpl'*}

    </div>
    <div class="tab-pane" id="after" data-id="after" data-name="visibility[0][4]" data-visibility="4">

      {*$id = 'after'}
      {$name = 'visibility[0][4]'}
      {$value = $visibility[0][4]}
      {include 'include/style_tab.tpl'*}

    </div>
  </div>
</div>



{if !$settings.data_class}
<div class="setting-row menu-list">
  <label for="">{$smarty.const.TEXT_CLASS}</label>
  <input type="text" name="setting[0][style_class]" value="{$settings[0].style_class}" class="form-control" style="width: 200px" />
</div>
{/if}

<div class="setting-row">
  <label for="">{$smarty.const.WIDGET_STATUS}</label>
  <select name="setting[0][status]" id="" class="form-control">
    <option value="">{$smarty.const.TEXT_VISIBLE}</option>
    <option value="hidden"{if $settings[0].status == 'hidden'} selected{/if}>{$smarty.const.TEXT_HIDDEN}</option>
  </select>
</div>

{if $settings.theme_name}<input type="hidden" name="theme_name" value="{$settings.theme_name}"/>{/if}



<script type="text/javascript">
  $(function() {
      var boxSave = $('#box-save');
    var box_id = boxSave.find('input[name="id"]').val();
    var data_class = boxSave.find('input[name="data_class"]').val();

    var changeStyle = function(){
      $('#box-save').trigger('change')
    };

    var createColorpicker = function (){
      setTimeout(function(){
        var cp = $('.colorpicker-component:not(.colorpicker-element)');
        cp.colorpicker({ sliders: {
          saturation: { maxLeft: 200, maxTop: 200 },
          hue: { maxTop: 200 },
          alpha: { maxTop: 200 }
        }}).on('changeColor', changeStyle).on('changeColor', function(){
          window.boxInputChanges[$('input', this).attr('name')] = $('input', this).val()
          console.log(window.boxInputChanges);
        });

        var removeColorpicker = function() {
          cp.colorpicker('destroy');
          cp.closest('.popup-box-wrap').off('remove', removeColorpicker)
          $('.style-tabs-content').off('st_remove', removeColorpicker)
        };

        cp.closest('.popup-box-wrap').on('remove', removeColorpicker);
        $('.style-tabs-content').on('st_remove', removeColorpicker);
      }, 200)
    };

    var changeDimension = function(){
      if ($(this).val() == 'px' || ($(this).val() == '' && !$(this).hasClass('sizing-line-height'))) {
        $('input[name="' + $(this).data('name') + '"]').attr('type', 'number')
      } else if ($(this).val() == 'auto') {
        $('input[name="' + $(this).data('name') + '"]').attr('type', 'hidden')
      } else {
        $('input[name="' + $(this).data('name') + '"]').attr('type', 'text')
      }
    };

    var showStyleTab = function(){
      var styleTab = $('.style-tabs-content > div:visible');

      $('.style-tabs-content').trigger('st_remove');
      $('.style-tabs-content > div > *').remove();

      var dataId = styleTab.data('id');
      var name = styleTab.data('name');
      var visibility = styleTab.data('visibility');
      styleTab.html('<div class="preloader"></div>');
      $.get('design/style-tab', {
        box_id: box_id,
        data_class: data_class,
        visibility: visibility,
        id: dataId,
        name: name,
        responsive_settings: '{json_encode($responsive_settings)}',
        block_view: '{$block_view}',
        theme_name: '{$settings.theme_name}'
      }, function(data){
        styleTab.html(data);

        if (window.boxInputChanges) {
          $.each(window.boxInputChanges, function(key, val){
            $('input[name="' + key + '"], select[name="' + key + '"]', styleTab).val(val)
          })
        }


        $('.box-style-tab input, .box-style-tab select').each(changeStyle);//.on('change', changeStyle);

        createColorpicker();

        $('select.sizing').each(changeDimension).on('change', changeDimension);


      });
    };

    setTimeout(showStyleTab, 100);

    $('.style-tabs a').on('click', function(){
      setTimeout(showStyleTab, 100);
    });


    var hideStyle = false;
    if ($('#style:hidden').length > 0) {
      $('#style').show();
      hideStyle = true;
    }

    $('.nav-tabs-scroll', boxSave).scrollingTabs().on('ready.scrtabs', function () {
      $('.tab-content').show();
      if (hideStyle) {
        $('#style').css('display', '');
      }
    });



  });



</script>