<div class="style-edit-page">
{include 'menu.tpl'}

<div>Find selector</div>
<div class="find-selector">
  <div class="suggest-selectors">
    <input id="selector" class="form-control" type="text" name="selector" placeholder="Enter css selector"/>
    <span class="btn add-selector">Add</span>
    <div class="suggest-selectors-content" style="display: none"></div>
  </div>
</div>


<div class="row">
  <div class="col-md-6">

    <div class="open-all"><span>Open All</span></div>
    <div class="close-all" style="display: none"><span>Close All</span></div>
    <ul class="styles-list">
      {foreach $list as $key => $item}
        <li>

          {if $item[1]}
            <div class="item daddy closed">
              <div class="close"></div>
              {if $key == $item[0]}
              <span class="edit{if $item[0].new} new{/if}" data-class="{$item[0].long}"></span>
                {if $item[0].new}<span class="remove" data-class="{$item[0].long}"></span>{/if}
              {/if}
              <span class="{if $key == $item[0] && $item[0].new} new{/if}">{$key}</span>
            </div>
            <ul>
              {foreach $item as $i}
                <li><div class="item">
                    <span class="edit{if $i.new} new{/if}" data-class="{$i.long}"></span>
                    {if $i.new}<span class="remove" data-class="{$i.long}"></span>{/if}
                    <span class="{if $i.new} new{/if}">{$i.short}</span>
                  </div></li>
              {/foreach}
            </ul>
          {else}
            <div class="item">
              <span class="edit{if $item[0].new} new{/if}" data-class="{$item[0].long}"></span>
              {if $item[0].new}<span class="remove" data-class="{$item[0].long}"></span>{/if}
              <span class="{if $item[0].new} new{/if}">{$item[0].short}</span>
            </div>
          {/if}

        </li>
      {/foreach}
    </ul>
  </div>
  <div class="col-md-6">


    <h3>Change style all the theme</h3>

    <div class="change-styles-row" data-name="color">
      <div class="change-styles-name">Font color</div><div class="change-styles-from">from</div>
      <div class="dropdown">
        <div class="dropdown-selected"></div>
        <div class="dropdown-content">
          {foreach $fontColors as $color => $items}
            <div data-val="{$color}"><span class="choose-color" style="background: {$color}"></span> {$color} ({$items})</div>
          {/foreach}
        </div>
      </div>
      <div class="change-styles-to">to</div>
      <div class="colors-inp">
        <div id="cp3" class="input-group colorpicker-component">
          <input type="text" name="color" value="" class="form-control style-to" placeholder="{$smarty.const.TEXT_COLOR_}" />
          <span class="input-group-addon"><i></i></span>
        </div>
      </div>
      <span class="btn">Change</span>
    </div>

    <div class="change-styles-row" data-name="background-color">
      <div class="change-styles-name">Background color</div><div class="change-styles-from">from</div>
      <div class="dropdown">
        <div class="dropdown-selected"></div>
        <div class="dropdown-content">
          {foreach $backgroundColors as $color => $items}
            <div data-val="{$color}"><span class="choose-color" style="background: {$color}"></span> {$color} ({$items})</div>
          {/foreach}
        </div>
      </div>
      <div class="change-styles-to">to</div>
      <div class="colors-inp">
        <div id="cp3" class="input-group colorpicker-component">
          <input type="text" name="backgroundColors" value="" class="form-control style-to" placeholder="{$smarty.const.TEXT_COLOR_}" />
          <span class="input-group-addon"><i></i></span>
        </div>
      </div>
      <span class="btn">Change</span>
    </div>

    <div class="change-styles-row" data-name="border-color">
      <div class="change-styles-name">Border color</div><div class="change-styles-from">from</div>
      <div class="dropdown">
        <div class="dropdown-selected"></div>
        <div class="dropdown-content">
          {foreach $borderColors as $color => $items}
            <div data-val="{$color}"><span class="choose-color" style="background: {$color}"></span> {$color} ({$items})</div>
          {/foreach}
        </div>
      </div>
      <div class="change-styles-to">to</div>
      <div class="colors-inp">
        <div id="cp3" class="input-group colorpicker-component">
          <input type="text" name="borderColors" value="" class="form-control style-to" placeholder="{$smarty.const.TEXT_COLOR_}" />
          <span class="input-group-addon"><i></i></span>
        </div>
      </div>
      <span class="btn">Change</span>
    </div>

    <div class="change-styles-row" data-name="font-family">
      <div class="change-styles-name">Font family</div><div class="change-styles-from">from</div>
      <div class="dropdown">
        <div class="dropdown-selected"></div>
        <div class="dropdown-content">
          {foreach $fontFamily as $font => $items}
            <div data-val="{$font}">{$font} ({$items})</div>
          {/foreach}
        </div>
      </div>
      <div class="change-styles-to">to</div>
      <select name="fontFamily" id="" class="form-control style-to">
        <option value=""></option>
        <option value="Arial">Arial</option>
        <option value="Verdana">Verdana</option>
        <option value="Tahoma">Tahomaa</option>
        <option value="Times">Times</option>
        <option value="Times New Roman">Times New Roman</option>
        <option value="Georgia">Georgia</option>
        <option value="Trebuchet MS">Trebuchet MS</option>
        <option value="Sans">Sans</option>
        <option value="Comic Sans MS">Comic Sans MS</option>
        <option value="Courier New">Courier New</option>
        <option value="Garamond">Garamond</option>
        <option value="Helvetica">Helvetica</option>
        {foreach $fontAdded as $item}
          <option value="{$item}">{$item}</option>
        {/foreach}
      </select>
      <span class="btn">Change</span>
    </div>


  </div>
</div>



<script type="text/javascript">
  (function($){
    $(function(){

      $('.btn-save-boxes').on('click', function(){
        $.get($(this).data('href'), { 'theme_name': '{$theme_name}'}, function(d){
          alertMessage(d);
          setTimeout(function(){
            $('.popup-box-wrap').remove()
          }, 500)
        })
      });

      var redo_buttons = $('.redo-buttons');
      redo_buttons.on('click', '.btn-undo', function(){
        $(redo_buttons).hide();
        $.get('design/undo', { 'theme_name': '{$theme_name}'}, function(){
          location.reload()
        })
      });
      redo_buttons.on('click', '.btn-redo', function(){
        $(redo_buttons).hide();
        $.get('design/redo', { 'theme_name': '{$theme_name}', 'steps_id': $(this).data('id')}, function(){
          location.reload()
        })
      });
      $.get('design/redo-buttons', { 'theme_name': '{$theme_name}'}, function(data){
        redo_buttons.html(data)
      });

      $('.dropdown-content > div').on('click', function(){
        var selected = $(this).closest('.dropdown').find('.dropdown-selected');
        selected.html($(this).html());
        selected.data('val', $(this).data('val'))
      });

      $('.change-styles-row .btn').on('click', function(){
        var row = $(this).closest('.change-styles-row');
        var from = $('.dropdown-selected', row).data('val');
        var to = $('.style-to', row).val();
        var style = row.data('name');
        $.get('design/styles-change', {
          from: from,
          to: to,
          style: style,
          theme_name: '{$theme_name}'
        }, function(d){
          alertMessage(d);
          setTimeout(function(){
            $('.popup-box-wrap').remove()
          }, 500)
        })
      });

      $('.colorpicker-component').colorpicker({ sliders: {
        saturation: { maxLeft: 200, maxTop: 200 },
        hue: { maxTop: 200 },
        alpha: { maxTop: 200 }
      }});


      var suggestSelectorsContent  = $('.suggest-selectors-content');
      $('#selector')
              .on('keyup', function(){
                $.get('design/find-selector', {
                  selector: $(this).val(),
                  theme_name: '{$theme_name}'
                }, function(d){
                  suggestSelectorsContent.html(d)
                })
              })
              .on('focus', function(){
                suggestSelectorsContent.show()
              })
              .on('blur', function(){
                setTimeout(function(){
                  suggestSelectorsContent.hide()
                }, 1000)
              });

      var openSelectorPopUp = function(){
        $('.popup-draggable').remove();

        $('body').append('<div class="popup-draggable"><div class="pop-up-close"></div><div class="preloader"></div></div>');
        var popup_draggable = $('.popup-draggable');
        popup_draggable.css({
          left: ($(window).width() - popup_draggable.width())/2,
          top: $(window).scrollTop() + 200
        });
        $('.pop-up-close').on('click', function(){
          popup_draggable.remove()
        });

        var val = '';
        var nw = false;
        if ($(this).hasClass('add-selector')) {
          val = $('#selector').val();
        } else if ($(this).hasClass('edit')) {
          val = $(this).data('class');
          if ($(this).hasClass('new')) {
            nw = true
          }
        } else {
          val = $(this).text()
        }

        $.get('design/style-edit', {
          data_class: val,
          theme_name: '{$theme_name}'
        }, function(data){
          popup_draggable.html(data);
          if (nw) {
            $('input[name="data_class"]').remove();
            $('.popup-content').prepend('<input type="text" name="data_class" value="' + val + '" class="popup-heading-small-text">');
          } else {
            $('.popup-content').prepend('<span class="popup-heading-small-text">'+val+'</span>');
          }
            saveSettings();
          $('.pop-up-close').on('click', function(){
            popup_draggable.remove();
          });
          $( ".popup-draggable" ).draggable({ handle: ".popup-heading" });

          var boxSave = $('#box-save');

          var showChanges = function(){
            $('.changed', boxSave).removeClass('changed');
            $('input, select', boxSave).each(function(){
              if ($(this).val() !== '') {
                $(this).closest('.setting-row').find('label').addClass('changed');
                var id = $(this).closest('.tab-pane').attr('id');
                $('.nav a[href="#'+id+'"]').addClass('changed');
                id = $(this).closest('.tab-pane').parents('.tab-pane').attr('id');
                $('.nav a[href="#'+id+'"]').addClass('changed');
              }
            })
          };
          showChanges();
          boxSave.on('change', showChanges);

        });

        popup_draggable.draggable();
      };

      $('.add-selector').on('click', openSelectorPopUp);

      suggestSelectorsContent.on('click', '.item', openSelectorPopUp)



      $('.open-all span').on('click', function(){
        $('.styles-list .closed').removeClass('closed');
        $('.open-all').hide();
        $('.close-all').show();
      });
      $('.close-all span').on('click', function(){
        $('.styles-list .daddy').addClass('closed');
        $('.open-all').show();
        $('.close-all').hide();
      });

      $('.styles-list .close').on('click', function(){
        var daddy = $(this).closest('.daddy');
        if (daddy.hasClass('closed')){
          daddy.removeClass('closed')
        } else {
          daddy.addClass('closed')
        }
      });
      $('.styles-list .edit').on('click', openSelectorPopUp)
      $('.styles-list .remove').on('click', function(){
        var _this = $(this);
        $.get('design/remove-class', {
          'class': $(this).data('class'),
          'theme_name': '{$theme_name}'
        }, function(){
          _this.closest('li').remove()
        })
      })
    })
  })(jQuery)


  function saveSettings () {

      var boxSave = $('#box-save');


      window.boxInputChanges = {};

      boxSave.on('change blur click keyup', 'input, select, textarea', function(){
          if ($(this).attr('type') == 'checkbox' && !$(this).is(':checked')) {
              window.boxInputChanges[$(this).attr('name')] = '';
          } else if ($(this).attr('type') == 'checkbox' && $(this).is(':checked')) {
              window.boxInputChanges[$(this).attr('name')] = 1;
          }else {
              window.boxInputChanges[$(this).attr('name')] = $(this).val();
          }
      });



      boxSave.on('submit', function(){

          window.boxInputChanges['id'] = $('input[name="id"]', this).val();
          var params = $('input[name="params"], select[name="params"]', this).val();
          if (params) {
              window.boxInputChanges['params'] = params;
          }

          var values = [];
          $.each( window.boxInputChanges, function(name, value) {
              values = values.concat({ "name": name, "value": value});
          });

          values = values.concat(
              $('.visibility input[disabled]', this).map(function() {
                  return { "name": this.name, "value": 1}
              }).get()
          );

          $('.check_on_off').each(function(){
              values = values.concat({ "name": $(this).attr('name'), "value": $(this).prop( "checked" )});
          });

          var data = values.reduce(function(obj, item) {
              obj[item.name] = item.value;
              return obj;
          }, {});

          $.post('design/style-save', {
              'values': JSON.stringify(data),
              'theme_name': $('input[name="theme_name"]', this).val(),
              'data_class': $('input[name="data_class"]', this).val()
          }, function(){
              $(window).trigger('reload-frame')
          });
          $('.popup-draggable').remove();
          setTimeout(function(){
          }, 300);
          return false
      });

  };
</script>
</div>