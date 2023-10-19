{use class="\backend\design\Style"}
{$styleHide = Style::hide($settings.data_class)}
{$styleShow = Style::show($settings.data_class)}
{if $settings.designer_mode == 'expert'}
<div class="tabbable tabbable-custom box-style-tab">
  <ul class="nav nav-tabs style-tabs">

    <li class="active" data-bs-toggle="tab" data-bs-target="#main_view"><a>{$smarty.const.BOX_HEADING_MAIN_STYLES}</a></li>
    <li data-bs-toggle="tab" data-bs-target="#responsive"><a>{$smarty.const.RESPONSIVE_DESIGN}</a></li>
    <li data-bs-toggle="tab" data-bs-target="#pseudo"><a>{$smarty.const.PSEUDO_CLASSES}</a></li>

  </ul>
  <div class="tab-content menu-list  style-tabs-content">
    <div class="tab-pane active" id="main_view" data-id="main_view" data-name="setting[0]" data-visibility="0"></div>
    <div class="tab-pane" id="responsive">

      {$responsive = 1}
      <div class="tabbable tabbable-custom box-style-tab">
        <ul class="nav nav-tabs nav-tabs-scroll style-tabs">

          {if $styleHide.responsive !== 1}
            <li class="label">{$smarty.const.WINDOW_WIDTH}:</li>
            {foreach $settings.media_query as $item}
              <li {if $item@index == 0} class="active" {/if} data-bs-toggle="tab" data-bs-target="#m{$item.id}">
                <a>
                  {$item.title}
                </a>
              </li>
            {/foreach}
          {/if}

        </ul>
        <div class="tab-content menu-list style-tabs-content">
          {foreach $settings.media_query as $item}
            <div
                    class="tab-pane{if $item@index == 0} active{/if}"
                    id="m{$item.id}"
                    data-id="{'m'|cat:$item.id}"
                    data-name="{'visibility[0]['|cat:$item.id|cat:']'}"
                    data-visibility="{$item.id}">
            </div>
          {/foreach}
        </div>
      </div>

    </div>
    <div class="tab-pane" id="pseudo">

      <div class="tabbable tabbable-custom box-style-tab">
        <ul class="nav nav-tabs style-tabs">

          {if $styleHide.hover !== 1}
            <li class="active" data-bs-toggle="tab" data-bs-target="#hover"><a>hover</a></li>
          {/if}
          {if $styleShow.active == 1}
            <li data-bs-toggle="tab" data-bs-target="#active"><a>active</a></li>
          {/if}
          {if $styleHide.before !== 1}
            <li data-bs-toggle="tab" data-bs-target="#before"><a>:before</a></li>
          {/if}
          {if $styleHide.after !== 1}
            <li data-bs-toggle="tab" data-bs-target="#after"><a>:after</a></li>
          {/if}

        </ul>
        <div class="tab-content menu-list style-tabs-content">
          <div class="tab-pane active" id="hover" data-id="hover" data-name="visibility[0][1]" data-visibility="1"></div>
          {if $styleShow.active == 1}
            <div class="tab-pane" id="active" data-id="active" data-name="visibility[0][2]" data-visibility="2"></div>
          {/if}
          <div class="tab-pane" id="before" data-id="before" data-name="visibility[0][3]" data-visibility="3"></div>
          <div class="tab-pane" id="after" data-id="after" data-name="visibility[0][4]" data-visibility="4"></div>
        </div>
      </div>

    </div>
  </div>
</div>
{else}
  <div class="main-styles" id="main_view" data-id="main_view" data-name="setting[0]" data-visibility="0"></div>
{/if}

{if $settings.designer_mode == 'expert'}

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

{/if}

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

      {if $settings.designer_mode == 'expert'}
      var styleTab = $('.style-tabs-content > div[data-id]:visible');

      $('.style-tabs-content').trigger('st_remove');
      $('.style-tabs-content > div[data-id] > *').remove();
      {else}
      var styleTab = $('.main-styles');
      {/if}

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

    {if $settings.designer_mode == 'expert'}
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
    $('.nav-tabs li').on('click', function () {
      setTimeout(function () {
        $('.nav-tabs-scroll', boxSave).scrollingTabs('refresh')
      }, 100)
    })
    {/if}

  });
</script>