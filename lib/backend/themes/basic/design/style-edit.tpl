<form action="{Yii::getAlias('@web')}/design/style-save" method="post" id="box-save">
  <input type="hidden" name="id" value="{$id}"/>
  <input type="hidden" name="theme_name" value="{$theme_name}"/>
  <input type="hidden" name="data_class" value="{$data_class}"/>
  <div class="popup-content">
    
<div class="demo-box">AaBbCc 1 2 3 4 5</div>



<div class="tabbable tabbable-custom box-style-tab">
  <div class="nav nav-tabs">

    <div class="active" data-bs-toggle="tab" data-bs-target="#font"><a>{$smarty.const.TEXT_FONT}</a></div>
    <div data-bs-toggle="tab" data-bs-target="#background"><a>{$smarty.const.TEXT_BACKGROUND}</a></div>
    <div data-bs-toggle="tab" data-bs-target="#padding"><a>{$smarty.const.TEXT_PADDING}</a></div>
    <div data-bs-toggle="tab" data-bs-target="#border"><a>{$smarty.const.TEXT_BORDER}</a></div>

  </div>
  <div class="tab-content menu-list">
    <div class="tab-pane active" id="font">

      <div class="setting-row">
        <label for="">Font Family</label>
        <select name="setting[font_family]" id="" class="form-control">
          <option value=""{if $settings.font_family == ''} selected{/if}></option>
          <option value="Arial"{if $settings.font_family == 'Arial'} selected{/if}>Arial</option>
          <option value="Verdana"{if $settings.font_family == 'Verdana'} selected{/if}>Verdana</option>
          <option value="Tahoma"{if $settings.font_family == 'Tahoma'} selected{/if}>Tahomaa</option>
          <option value="Times"{if $settings.font_family == 'Times'} selected{/if}>Times</option>
          <option value="Times New Roman"{if $settings.font_family == 'Times New Roman'} selected{/if}>Times New Roman</option>
          <option value="Georgia"{if $settings.font_family == 'Georgia'} selected{/if}>Georgia</option>
          <option value="Trebuchet MS"{if $settings.font_family == 'Trebuchet MS'} selected{/if}>Trebuchet MS</option>
          <option value="Sans"{if $settings.font_family == 'Sans'} selected{/if}>Sans</option>
          <option value="Comic Sans MS"{if $settings.font_family == 'Comic Sans MS'} selected{/if}>Comic Sans MS</option>
          <option value="Courier New"{if $settings.font_family == 'Courier New'} selected{/if}>Courier New</option>
          <option value="Garamond"{if $settings.font_family == 'Garamond'} selected{/if}>Garamond</option>
          <option value="Helvetica"{if $settings.font_family == 'Helvetica'} selected{/if}>Helvetica</option>
          <option value="Hind"{if $settings.font_family == 'Hind'} selected{/if}>Hind</option>
          <option value="Varela Round"{if $settings.font_family == 'Varela Round'} selected{/if}>Varela Round</option>
        </select>
      </div>

      <div class="setting-row">
        <label for="">{$smarty.const.TEXT_COLOR}</label>
        <div class="colors-inp">
          <div id="cp3" class="input-group colorpicker-component">
            <input type="text" name="setting[color]" value="{$settings.color}" class="form-control" placeholder="{$smarty.const.TEXT_COLOR_}" />
            <span class="input-group-append"><span class="input-group-text colorpicker-input-addon"><i></i></span></span>
          </div>
        </div>
        <span style="display:inline-block; padding: 7px 0 0 10px">{$smarty.const.TEXT_CLICK_RIGHT_FIELD}</span>
      </div>

      <div class="setting-row">
        <label for="">{$smarty.const.TEXT_FONT_SIZE}</label>
        <input type="number" name="setting[font_size]" value="{$settings.font_size}" class="form-control" /><span class="px">px</span>
      </div>
      <div class="setting-row">
        <label for="">{$smarty.const.TEXT_FONT_WEIGHT}</label>
        <select name="setting[font_weight]" id="" class="form-control">
          <option value=""{if $settings.font_weight == ''} selected{/if}></option>
          <option value="400"{if $settings.font_weight == '400'} selected{/if}>{$smarty.const.TEXT_NORMAL}</option>
          <option value="600"{if $settings.font_weight == '600'} selected{/if}>{$smarty.const.TEXT_BOLD}</option>
        </select>
      </div>
      <div class="setting-row">
        <label for="">{$smarty.const.TEXT_LINE_HEIGHT}</label>
        <input type="number" name="setting[line_height]" value="{$settings.line_height}" class="form-control" /><span class="px">%</span>
      </div>
      <div class="setting-row">
        <label for="">{$smarty.const.TEXT_TEXT_ALIGN}</label>
        <select name="setting[text_align]" id="" class="form-control">
          <option value=""{if $settings.text_align == ''} selected{/if}></option>
          <option value="left"{if $settings.text_align == 'left'} selected{/if}>{$smarty.const.TEXT_LEFT}</option>
          <option value="right"{if $settings.text_align == 'right'} selected{/if}>{$smarty.const.TEXT_RIGHT}</option>
          <option value="center"{if $settings.text_align == 'center'} selected{/if}>{$smarty.const.TEXT_CENTER}</option>
          <option value="justify"{if $settings.text_align == 'justify'} selected{/if}>{$smarty.const.TEXT_JUSTIFY}</option>
        </select>
      </div>
      <div class="setting-row">
        <label for="">{$smarty.const.TEXT_TEXT_SHADOW}</label>
        <div class="" style="display: inline-block; width: 50%">
          <input type="number" name="setting[text_shadow_left]" value="{$settings.text_shadow_left}" class="form-control" placeholder="{$smarty.const.TEXT_LEFT_POSITION}" style="margin-bottom: 5px" /><span class="px">px</span>
          <input type="number" name="setting[text_shadow_top]" value="{$settings.text_shadow_top}" class="form-control" placeholder="{$smarty.const.TEXT_POSITION_TOP}" style="margin-bottom: 5px" /><span class="px">px</span>
          <input type="number" name="setting[text_shadow_size]" value="{$settings.text_shadow_size}" class="form-control" placeholder="{$smarty.const.TEXT_RADIUS}" /><span class="px">px</span>
          <div class="colors-inp">
            <div id="cp3" class="input-group colorpicker-component">
              <input type="text" name="setting[text_shadow_color]" value="{$settings.text_shadow_color}" class="form-control" placeholder="{$smarty.const.TEXT_COLOR_}" />
              <span class="input-group-append"><span class="input-group-text colorpicker-input-addon"><i></i></span></span>
            </div>
          </div>
        </div>
      </div>

    </div>
    <div class="tab-pane" id="background">

      <div class="setting-row">
        <label for="">{$smarty.const.TEXT_BACKGROUND_COLOR}</label>
        <div class="colors-inp">
          <div id="cp2" class="input-group colorpicker-component">
            <input type="text" name="setting[background_color]" value="{$settings.background_color}" class="form-control" placeholder="{$smarty.const.TEXT_COLOR_}" />
            <span class="input-group-append"><span class="input-group-text colorpicker-input-addon"><i></i></span></span>
          </div>
        </div>
        <span style="display:inline-block; padding: 7px 0 0 10px">{$smarty.const.TEXT_CLICK_RIGHT_FIELD}</span>
      </div>

      <div class="setting-row setting-row-image">
        <label for="">{$smarty.const.TEXT_BACKGROUND_IMAGE}</label>

        {if isset($settings.background_image)}
          <div class="image">
            <img src="{$app->request->baseUrl}/../images/{$settings.background_image}" alt="">
            <div class="remove-img"></div>
          </div>
        {/if}

        <div class="image-upload">
          <div class="upload" data-name="setting[background_image]"></div>
          <script type="text/javascript">
            $('.upload').uploads().on('upload', function(){
              var img = $('.dz-image-preview img', this).attr('src');
              $('.demo-box').css('background-image', 'url("'+img+'")')
            });

            $(function(){
              $('.setting-row-image .image > img').each(function(){
                var img = $(this).attr('src');
                $('.demo-box').css('background-image', 'url("'+img+'")');

                $('input[name="setting[background_image]"]').val('{$settings.background_image}');
              });

              $('.setting-row-image .image .remove-img').on('click', function(){
                $('input[name="setting[background_image]"]').val('');
                $('.setting-row-image .image').remove()
              })

            });

          </script>
        </div>

      </div>
      <div class="setting-row">
        <label for="">{$smarty.const.TEXT_BACKGROUND_POSITION}</label>
        <select name="setting[background_position]" id="" class="form-control">
          <option value=""{if $settings.background_position == ''} selected{/if}></option>
          <option value="top left"{if $settings.background_position == 'top left'} selected{/if}>{$smarty.const.TEXT_TOP_LEFT}</option>
          <option value="top center"{if $settings.background_position == 'top center'} selected{/if}>{$smarty.const.TEXT_TOP_CENTER}</option>
          <option value="top right"{if $settings.background_position == 'top right'} selected{/if}>{$smarty.const.TEXT_TOP_RIGHT}</option>
          <option value="left"{if $settings.background_position == 'left'} selected{/if}>{$smarty.const.TEXT_MIDDLE_LEFT}</option>
          <option value="center"{if $settings.background_position == 'center'} selected{/if}>{$smarty.const.TEXT_MIDDLE_CENTER}</option>
          <option value="right"{if $settings.background_position == 'right'} selected{/if}>{$smarty.const.TEXT_MIDDLE_RIGHT}</option>
          <option value="bottom left"{if $settings.background_position == 'bottom left'} selected{/if}>{$smarty.const.TEXT_BOTTOM_LEFT}</option>
          <option value="bottom center"{if $settings.background_position == 'bottom center'} selected{/if}>{$smarty.const.TEXT_BOTTOM_CENTER}</option>
          <option value="bottom right"{if $settings.background_position == 'bottom right'} selected{/if}>{$smarty.const.TEXT_BOTTOM_RIGHT}</option>
        </select>
      </div>
      <div class="setting-row">
        <label for="">{$smarty.const.TEXT_BACKGROUND_REPEAT}</label>
        <select name="setting[background_repeat]" id="" class="form-control">
          <option value=""{if $settings.background_repeat == ''} selected{/if}></option>
          <option value="no-repeat"{if $settings.background_repeat == 'no-repeat'} selected{/if}>{$smarty.const.TEXT_NO_REPEAT}</option>
          <option value="repeat"{if $settings.background_repeat == 'repeat'} selected{/if}>{$smarty.const.TEXT_REPEAT}</option>
          <option value="repeat-x"{if $settings.background_repeat == 'repeat-x'} selected{/if}>{$smarty.const.TEXT_REPEAT_HORIZONTAL}</option>
          <option value="repeat-y"{if $settings.background_repeat == 'repeat-y'} selected{/if}>{$smarty.const.TEXT_REPEAT_VERTICAL}</option>
          <option value="space"{if $settings.background_repeat == 'space'} selected{/if}>{$smarty.const.TEXT_REPEAT_ALL_SPACE}</option>
          <option value="top left"{if $settings.background_repeat == 'top left'} selected{/if}>{$smarty.const.TEXT_REPEAT_ALL_SPACE_RESIZE}</option>
        </select>
      </div>
      <div class="setting-row">
        <label for="">{$smarty.const.TEXT_BACKGROUND_SIZE}</label>
        <select name="setting[background_size]" id="" class="form-control">
          <option value=""{if $settings.background_size == ''} selected{/if}>{$smarty.const.TEXT_NO_RESIZE}</option>
          <option value="cover"{if $settings.background_size == 'cover'} selected{/if}>{$smarty.const.TEXT_FIELD_ALL_BLOCK}</option>
          <option value="contain"{if $settings.background_size == 'contain'} selected{/if}>{$smarty.const.TEXT_WIDTH_HEIGHT_SIZE}</option>
        </select>
      </div>

    </div>
    <div class="tab-pane" id="padding">

      <div class="setting-row">
        <label for="">{$smarty.const.TEXT_PADDING_TOP}</label>
        <input type="number" name="setting[padding_top]" value="{$settings.padding_top}" class="form-control" /><span class="px">px</span>
      </div>
      <div class="setting-row">
        <label for="">{$smarty.const.TEXT_PADDING_LEFT}</label>
        <input type="number" name="setting[padding_left]" value="{$settings.padding_left}" class="form-control" /><span class="px">px</span>
      </div>
      <div class="setting-row">
        <label for="">{$smarty.const.TEXT_PADDING_RIGHT}</label>
        <input type="number" name="setting[padding_right]" value="{$settings.padding_right}" class="form-control" /><span class="px">px</span>
      </div>
      <div class="setting-row">
        <label for="">{$smarty.const.TEXT_PADDING_BOTTOM}</label>
        <input type="number" name="setting[padding_bottom]" value="{$settings.padding_bottom}" class="form-control" /><span class="px">px</span>
      </div>

    </div>
    <div class="tab-pane" id="border">

      <div class="setting-row setting-row-border">
        <label for="">{$smarty.const.TEXT_BORDER_TOP}</label>
        <input type="number" name="setting[border_top_width]" value="{$settings.border_top_width}" class="form-control" /><span class="px">px</span>
        <div class="colors-inp">
          <div class="input-group colorpicker-component">
            <input type="text" name="setting[border_top_color]" value="{$settings.border_top_color}" class="form-control" placeholder="Color" />
            <span class="input-group-append"><span class="input-group-text colorpicker-input-addon"><i></i></span></span>
          </div>
        </div>
        <span style="display:inline-block; padding: 0 0 0 1px">{$smarty.const.TEXT_CLICK_CHOOSE_COLOR}</span>
      </div>
      <div class="setting-row setting-row-border">
        <label for="">{$smarty.const.TEXT_BORDER_LEFT}</label>
        <input type="number" name="setting[border_left_width]" value="{$settings.border_left_width}" class="form-control" /><span class="px">px</span>
        <div class="colors-inp">
          <div class="input-group colorpicker-component">
            <input type="text" name="setting[border_left_color]" value="{$settings.border_left_color}" class="form-control" placeholder="Color" />
            <span class="input-group-append"><span class="input-group-text colorpicker-input-addon"><i></i></span></span>
          </div>
        </div>
      </div>
      <div class="setting-row setting-row-border">
        <label for="">{$smarty.const.TEXT_BORDER_RIGHT}</label>
        <input type="number" name="setting[border_right_width]" value="{$settings.border_right_width}" class="form-control" /><span class="px">px</span>
        <div class="colors-inp">
          <div class="input-group colorpicker-component">
            <input type="text" name="setting[border_right_color]" value="{$settings.border_right_color}" class="form-control" placeholder="Color" />
            <span class="input-group-append"><span class="input-group-text colorpicker-input-addon"><i></i></span></span>
          </div>
        </div>
      </div>
      <div class="setting-row setting-row-border">
        <label for="">{$smarty.const.TEXT_BORDER_BOTTOM}</label>
        <input type="number" name="setting[border_bottom_width]" value="{$settings.border_bottom_width}" class="form-control" /><span class="px">px</span>
        <div class="colors-inp">
          <div class="input-group colorpicker-component">
            <input type="text" name="setting[border_bottom_color]" value="{$settings.border_bottom_color}" class="form-control" placeholder="Color" />
            <span class="input-group-append"><span class="input-group-text colorpicker-input-addon"><i></i></span></span>
          </div>
        </div>
      </div>

    </div>

  </div>
</div>



  </div>
  <div class="popup-buttons">
    <button type="submit" class="btn btn-primary btn-save">{$smarty.const.IMAGE_SAVE}</button>

    <span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span>
    <script type="text/javascript">
      $('.btn-cancel').on('click', function(){
        $('.popup-box-wrap').remove()
      })
    </script>

  </div>
</form>
<script type="text/javascript">
  (function(){

    $(function(){

      $('.nav-tabs a').on('click', function(){
        $(this).tab('show');
        $(this).closest('.nav-tabs').find('> div').removeClass('active');
        $(this).parent().addClass('active');
        return false;
      });


      var demo_box = $('.demo-box');
      var form = $('#info-view').contents();
      var data_class = $('input[name="data_class"]').val();
      var current = $(data_class, form);


      function removeDefault(){
        $('style').remove();

        if (current.css('color').replace(/ /g, '') == $('.box-style-tab input[name="setting[color]"]').val()){
          $('.box-style-tab input[name="setting[color]"]').val('')
        }
        if (current.css('font-size').replace('px', '') == $('.box-style-tab input[name="setting[font_size]"]').val()){
          $('.box-style-tab input[name="setting[font_size]"]').val('')
        }
        if (current.css('font-weight') == $('.box-style-tab select[name="setting[font_weight]"]').val()){
          $('.box-style-tab select[name="setting[font_weight]"]').val('')
        }
      }

      $('#box-save').on('submit', function(){

        //removeDefault();

        var values = $(this).serializeArray();
        values = values.concat(
                $('input[type=checkbox]:not(:checked)', this).map(function() {
                  return { "name": this.name, "value": 0}
                }).get()
        );
        values = values.concat(
                $('.visibility input[disabled]', this).map(function() {
                  return { "name": this.name, "value": 1}
                }).get()
        );

        var data = values.reduce(function(obj, item) {
          obj[item.name] = item.value;
          return obj;
        }, { });

        $.post('design/style-save', { 'values': JSON.stringify(data)}, function(){ });
        setTimeout(function(){
          $(window).trigger('reload-frame')
        }, 300);

        return false
      });


      var changeStyle = function(){
        var _this;

        if ($(this).hasClass('colorpicker-component')){
          _this = $('input', this)
        } else {
          _this = $(this)
        }

        if (_this.attr('name') == 'setting[font_family]'){
          demo_box.css('font-family', '"' + _this.val() + '"')
        }

        if (_this.attr('name') == 'setting[color]'){
          if (_this.val()){
            demo_box.css('color', _this.val())
          } else {
            demo_box.css('color', current.css('color'));
            //_this.val(current.css('color', 'auto').css('color')).trigger('keyup')
          }
        }

        if (_this.attr('name') == 'setting[text_align]'){
          demo_box.css('text-align', _this.val())
          /*if (_this.val()){
            demo_box.css('text-align', _this.val())
          } else {
            demo_box.css('text-align', current.css('text-align'));
            _this.val(current.css('text-align', 'auto').css('text-align')).trigger('keyup')
          }*/
        }

        if (_this.attr('name') == 'setting[font_size]'){
          if (_this.val()){
            demo_box.css('font-size', _this.val()+'px')
          } else {
            demo_box.css('font-size', current.css('font-size'));
            //_this.val(current.css('font-size', 'auto').css('font-size').replace('px', ''))
          }
        }

        if (_this.attr('name') == 'setting[font_weight]'){
          if (_this.val()){
            demo_box.css('font-weight', _this.val())
          } else {
            demo_box.css('font-weight', current.css('font-weight'));
            //_this.val(current.css('font-weight', 'auto').css('font-weight'))
          }
        }

        if (_this.attr('name') == 'setting[line_height]'){
          demo_box.css('line-height', _this.val()+'%')
        }

        if (
                _this.attr('name') == 'setting[text_shadow_left]' ||
                _this.attr('name') == 'setting[text_shadow_top]' ||
                _this.attr('name') == 'setting[text_shadow_size]' ||
                _this.attr('name') == 'setting[text_shadow_color]'
        ){
          var text_shadow_left = $('input[name="setting[text_shadow_left]"]').val();
          var text_shadow_top = $('input[name="setting[text_shadow_top]"]').val();
          var text_shadow_size = $('input[name="setting[text_shadow_size]"]').val();
          var text_shadow_color = $('input[name="setting[text_shadow_color]"]').val();
          if (text_shadow_left) text_shadow_left += 'px';
          else text_shadow_left = '0';
          if (text_shadow_top) text_shadow_top += 'px';
          else text_shadow_top = '0';
          if (text_shadow_size) text_shadow_size += 'px';
          else text_shadow_size = '0';
          if (text_shadow_size && text_shadow_color){
            demo_box.css('text-shadow', text_shadow_left+' '+text_shadow_top+' '+text_shadow_size+' '+text_shadow_color)
          }
        }

        if (_this.attr('name') == 'setting[background_color]'){
          demo_box.css('background-color', _this.val())
        }

        if (_this.attr('name') == 'setting[background_position]'){
          demo_box.css('background-position', _this.val())
        }

        if (_this.attr('name') == 'setting[background_repeat]'){
          demo_box.css('background-repeat', _this.val())
        }

        if (_this.attr('name') == 'setting[background_size]'){
          demo_box.css('background-size', _this.val())
        }

        if (_this.attr('name') == 'setting[padding_top]'){
          demo_box.css('padding-top', _this.val()+'px')
        }

        if (_this.attr('name') == 'setting[padding_left]'){
          demo_box.css('padding-left', _this.val()+'px')
        }

        if (_this.attr('name') == 'setting[padding_right]'){
          demo_box.css('padding-right', _this.val()+'px')
        }

        if (_this.attr('name') == 'setting[padding_bottom]'){
          demo_box.css('padding-bottom', _this.val()+'px')
        }

        if (_this.attr('name') == 'setting[border_top_width]' || _this.attr('name') == 'setting[border_top_color]'){
          var border_top_width = $('input[name="setting[border_top_width]"]').val();
          var border_top_color = $('input[name="setting[border_top_color]"]').val();
          if (border_top_width) {
            demo_box.css('border-top', border_top_width+'px solid '+border_top_color)
          }
        }

        if (_this.attr('name') == 'setting[border_left_width]' || _this.attr('name') == 'setting[border_left_color]'){
          var border_left_width = $('input[name="setting[border_left_width]"]').val();
          var border_left_color = $('input[name="setting[border_left_color]"]').val();
          if (border_left_width) {
            demo_box.css('border-left', border_left_width+'px solid '+border_left_color)
          }
        }

        if (_this.attr('name') == 'setting[border_right_width]' || _this.attr('name') == 'setting[border_right_color]'){
          var border_right_width = $('input[name="setting[border_right_width]"]').val();
          var border_right_color = $('input[name="setting[border_right_color]"]').val();
          if (border_right_width) {
            demo_box.css('border-right', border_right_width+'px solid '+border_right_color)
          }
        }

        if (_this.attr('name') == 'setting[border_bottom_width]' || _this.attr('name') == 'setting[border_bottom_color]'){
          var border_bottom_width = $('input[name="setting[border_bottom_width]"]').val();
          var border_bottom_color = $('input[name="setting[border_bottom_color]"]').val();
          if (border_bottom_width) {
            demo_box.css('border-bottom', border_bottom_width+'px solid '+border_bottom_color)
          }
        }

      };

      $('.box-style-tab input, .box-style-tab select').each(changeStyle).on('change', changeStyle);


      $('.colorpicker-component').colorpicker({ popover: false, sliders: {
        saturation: { maxLeft: 200, maxTop: 200 },
        hue: { maxTop: 200 },
        alpha: { maxTop: 200 }
      }}).on('changeColor', changeStyle);



    });
  })(jQuery);
</script>