{include 'menu.tpl'}
{use class="backend\assets\DesignAsset"}
{DesignAsset::register($this)|void}

<div class="style-edit-page">
<form action="{$action}" method="post" class="form-settings">
  <input type="hidden" name="theme_name" value="{$theme_name}"/>

  <div class="tabbable tabbable-custom tabbable-ep">

    <ul class="nav nav-tabs nav-tabs-big {if $isMultiPlatform}tab-radius-ul{/if}">
        <li class="active" data-bs-toggle="tab" data-bs-target="#main"><a><span>{$smarty.const.TEXT_MAIN_DETAILS}</span></a></li>
        <li data-bs-toggle="tab" data-bs-target="#images"><a><span>{$smarty.const.TAB_IMAGES}</span></a></li>
      {if $designer_mode == 'expert'}
        <li data-bs-toggle="tab" data-bs-target="#fonts"><a><span>{$smarty.const.TEXT_FONTS}</span></a></li>
        <li data-bs-toggle="tab" data-bs-target="#sizes"><a><span>{$smarty.const.SIZES_RESPONSIVE_DESIGN}</span></a></li>
      {/if}
    </ul>
    <div class="tab-content {if $isMultiPlatform}tab-content1{/if}">

      <div class="tab-pane topTabPane tabbable-custom active" id="main">

        <div style="max-width: 800px">
          <div class="setting-row">
            <label for="">{$smarty.const.TEXT_AFTER_ADDED}</label>
            <select name="settings[after_add]" id="" class="form-control">
              <option value=""{if $settings.after_add == ''} selected{/if}>{$smarty.const.TEXT_GO_TO_CART}</option>
                {*<option value="reload"{if $settings.after_add == 'reload'} selected{/if}>{$smarty.const.TEXT_RELOAD_PAGE}</option>
                <option value="animate"{if $settings.after_add == 'animate'} selected{/if}>{$smarty.const.TEXT_ANIMATE_FLY_PRODUCT}</option>*}
              <option value="popup"{if $settings.after_add == 'popup'} selected{/if}>{$smarty.const.TEXT_OPEN_CART_POPUP}</option>
            </select>
          </div>

          <div class="setting-row">
            <label for="">{$smarty.const.SHOW_PRODUCTS_FROM_SUBCATEGORIES}</label>
            <select name="settings[show_products_from_subcategories]" id="" class="form-control">
              <option value=""{if $settings.show_products_from_subcategories == ''} selected{/if}>{$smarty.const.TEXT_BTN_NO}</option>
              <option value="1"{if $settings.show_products_from_subcategories == '1'} selected{/if}>{$smarty.const.TEXT_BTN_YES}</option>
            </select>
          </div>

          <div class="setting-row">
            <label for="">{$smarty.const.SHOW_EMPTY_CATEGORIES}</label>
            <select name="settings[show_empty_categories]" id="" class="form-control">
              <option value=""{if $settings.show_empty_categories == ''} selected{/if}>{$smarty.const.TEXT_BTN_NO}</option>
              <option value="1"{if $settings.show_empty_categories == '1'} selected{/if}>{$smarty.const.TEXT_BTN_YES}</option>
            </select>
          </div>

          <div class="setting-row">
            <label for="">{$smarty.const.SHOW_EMPTY_BRANDS}</label>
            <select name="settings[hide_empty_brands]" id="" class="form-control">
              <option value=""{if $settings.hide_empty_brands == ''} selected{/if}>{$smarty.const.TEXT_BTN_YES}</option>
              <option value="1"{if $settings.hide_empty_brands == '1'} selected{/if}>{$smarty.const.TEXT_BTN_NO}</option>
            </select>
          </div>

          <div class="setting-row">
            <label for="">Listing Group by "Products Groups"</label>
            <select name="settings[group_product_by_product_group]" id="" class="form-control">
              <option value=""{if $settings.group_product_by_product_group == ''} selected{/if}>{$smarty.const.TEXT_BTN_NO}</option>
              <option value="1"{if $settings.group_product_by_product_group == '1'} selected{/if}>{$smarty.const.TEXT_BTN_YES}</option>
            </select>
          </div>

          <div class="setting-row">
            <label for="">Show "In your cart" button</label>
            <select name="settings[show_in_cart_button]" id="" class="form-control">
              <option value=""{if $settings.show_in_cart_button == ''} selected{/if}>{$smarty.const.TEXT_BTN_YES}</option>
              <option value="no"{if $settings.show_in_cart_button == 'no'} selected{/if}>{$smarty.const.TEXT_BTN_NO}</option>
            </select>
          </div>

          <div class="setting-row">
            <label for="">{$smarty.const.TEXT_CHECKOUT}</label>
            <select name="settings[checkout_view]" id="" class="form-control">
              <option value=""{if $settings.checkout_view == ''} selected{/if}>{$smarty.const.TEXT_ONE_PAGE}</option>
              <option value="1"{if $settings.checkout_view == '1'} selected{/if}>{$smarty.const.TEXT_MULTI_PAGES}</option>
            </select>
          </div>

          <div class="setting-row"{if $designer_mode != 'expert'} style="display: none" {/if}>
            <label for="">{$smarty.const.TEXT_CUSTOMER_ACCOUNT}</label>
            <select name="settings[customer_account]" id="" class="form-control">
              <option value=""{if $settings.customer_account == ''} selected{/if}>{$smarty.const.TEXT_OLD}</option>
              <option value="new"{if $settings.customer_account == 'new'} selected{/if}>{$smarty.const.TEXT_NEW}</option>
            </select>
          </div>
          <div class="setting-row">
            <label for="">{$smarty.const.TEXT_PRODUCTS_CAROUSEL}</label>
            <select name="settings[products_carousel]" id="" class="form-control">
              <option value=""{if $settings.products_carousel == ''} selected{/if}>{$smarty.const.TEXT_NO}</option>
              <option value="1"{if $settings.products_carousel == '1'} selected{/if}>{$smarty.const.TEXT_YES}</option>
            </select>
          </div>

          <div class="setting-row"{if !$designer_mode} style="display: none" {/if}>
            <label for="">{$smarty.const.TEXT_THEME_COLOR}</label>

            <div id="cp3" class="input-group colorpicker-component" style="width: 243px">
              <input type="text" name="settings[theme_color]" value="{$settings.theme_color}" class="form-control"/>
               <span class="input-group-append"><span class="input-group-text colorpicker-input-addon"><i></i></span></span>
            </div>
          </div>

            {if !$is_mobile}
              <div class="setting-row"{if !$designer_mode} style="display: none" {/if}>
                <label for="">{$smarty.const.TEXT_USE_MOBILE_THEME}</label>
                <select name="settings[use_mobile_theme]" id="" class="form-control">
                  <option value=""{if $settings.use_mobile_theme == ''} selected{/if}>{$smarty.const.TEXT_NO}</option>
                  <option value="1"{if $settings.use_mobile_theme == '1'} selected{/if}>{$smarty.const.TEXT_YES}</option>
                </select>
              </div>
            {/if}

            <div{if $designer_mode != 'expert'} style="display: none" {/if}>
          <div class="setting-row">
            <label for="">Use Service Worker</label>
            <select name="settings[service_worker]" id="" class="form-control">
              <option value=""{if $settings.service_worker == ''} selected{/if}>{$smarty.const.TEXT_NO}</option>
              <option value="1"{if $settings.service_worker == '1'} selected{/if}>{$smarty.const.TEXT_YES}</option>
            </select>
          </div>

          <div class="setting-row">
            <label for="">Backups every hours</label>
            <input type="text" name="settings[backup_hours]" value="{$settings.backup_hours}" class="form-control"/>
          </div>

          <div class="setting-row">
            <label for="">Auto backups count</label>
            <input type="text" name="settings[backup_count]" value="{$settings.backup_count}" class="form-control"/>
          </div>

          <div class="setting-row">
            <label for="">{$smarty.const.CONTACT_US_PAGE_NAME}</label>
            <input type="text" name="settings[contact_name]" value="{$settings.contact_name}" class="form-control" style="width: 243px"/>
          </div>

          <div class="setting-row">
            <label for="">Minimize javascript</label>
            <select name="settings[dev_mode]" id="" class="form-control">
              <option value=""{if $settings.dev_mode == ''} selected{/if}>{$smarty.const.TEXT_YES}</option>
              <option value="1"{if $settings.dev_mode == '1'} selected{/if}>{$smarty.const.TEXT_NO}</option>
            </select>
          </div>

          {*<div class="setting-row">
            <label for="">{$smarty.const.TEXT_OLD_LISTING}</label>
            <select name="settings[old_listing]" id="" class="form-control">
              <option value=""{if $settings.old_listing == ''} selected{/if}>{$smarty.const.TEXT_NO}</option>
              <option value="1"{if $settings.old_listing == '1'} selected{/if}>{$smarty.const.TEXT_YES}</option>
            </select>
          </div>*}

          {*<div class="setting-row">
            <label for="">Css</label>
            <select name="settings[include_css]" id="" class="form-control">
              <option value=""{if $settings.include_css == ''} selected{/if}>Old</option>
              <option value="1"{if $settings.include_css == '1'} selected{/if}>Style 07.17</option>
              <option value="2"{if $settings.include_css == '2'} selected{/if}>Style 10.17</option>
              <option value="3"{if $settings.include_css == '3'} selected{/if}>Style 12.17</option>
            </select>
          </div>*}

            {if $is_mobile}
              <div class="setting-row">
                <label for="">{$smarty.const.REMOVE_MOBILE_CREATE_FROM_DESKTOP}</label>
                <span class="btn btn-copy-theme">{$smarty.const.TEXT_CREATE}</span>
              </div>
              <script type="text/javascript">
                $(function(){
                  $('.btn-copy-theme').on('click', function(){
                    $.popUpConfirm('Current mobile theme will be removed', function(){
                      $.get('design/create-mobile-theme', { 'theme_name': '{$theme_name}'}, function(data){
                        alertMessage('<div class="alert-message">' + data + '</div>')
                      })
                    })
                  })
                })
              </script>
            {/if}


        </div>
        </div>
      </div>

      <div class="tab-pane topTabPane tabbable-custom" id="images">



        <div class="row">
          <div class="col-md-6">
            <div class="widget box">
              <div class="widget-header">
                <h4>{$smarty.const.TEXT_FAVICON}</h4>
              </div>
              <div class="widget-content">
                  {\backend\design\Image::widget([
                  'name' => 'favicon',
                  'value' => {$settings.favicon},
                  'upload' => 'favicon_upload',
                  'unlink' => false
                  ])}
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="widget box">
              <div class="widget-header">
                <h4>{$smarty.const.TEXT_LOGO}</h4>
              </div>
              <div class="widget-content">
                  {\backend\design\Image::widget([
                  'name' => 'logo',
                  'value' => {$settings.logo},
                  'upload' => 'logo_upload',
                  'unlink' => false
                  ])}
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6">
            <div class="widget box">
              <div class="widget-header">
                <h4>{$smarty.const.DEFAULT_IMAGE_FOR_CATEGORY}</h4>
              </div>
              <div class="widget-content">
                  {\backend\design\Image::widget([
                  'name' => 'na_category',
                  'value' => {$settings.na_category},
                  'upload' => 'na_category_upload',
                  'unlink' => false
                  ])}
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="widget box">
              <div class="widget-header">
                <h4>{$smarty.const.DEFAULT_IMAGE_FOR_PRODUCT}</h4>
              </div>
              <div class="widget-content">
                  {\backend\design\Image::widget([
                  'name' => 'na_product',
                  'value' => {$settings.na_product},
                  'upload' => 'na_product_upload',
                  'unlink' => false
                  ])}
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6">
            <div class="widget box">
              <div class="widget-header">
                <h4>{$smarty.const.TEXT_BACKGROUND}</h4>
              </div>
              <div class="widget-content">

                <div class="setting-row setting-row-image">
                  <label for="">{$smarty.const.TEXT_BACKGROUND_IMAGE}</label>

                  {\backend\design\Image::widget([
                      'name' => 'setting[background_image]',
                      'value' => $setting.background_image,
                      'upload' => 'setting[background_image_upload]',
                      'acceptedFiles' => 'image/*',
                      'type' => 'image'
                  ])}

                </div>
                <div class="setting-row">
                  <label for="">{$smarty.const.TEXT_BACKGROUND_COLOR}</label>
                  <div class="colors-inp">


                    <div id="cp2" class="input-group colorpicker-component">
                      <input type="text" name="setting[background_color]" value="{$setting.background_color}" class="form-control" placeholder="{$smarty.const.TEXT_COLOR_}" />
                      <span class="input-group-append">
                        <span class="input-group-text colorpicker-input-addon"><i></i></span>
                      </span>
                    </div>

                  </div>
                  <span style="display:inline-block; padding: 7px 0 0 10px">{$smarty.const.TEXT_CLICK_RIGHT_FIELD}</span>
                </div>
                <script type="text/javascript">
                  $(function(){
                    $('.colorpicker-component').colorpicker({ sliders: {
                      saturation: { maxLeft: 200, maxTop: 200 },
                      hue: { maxTop: 200 },
                      alpha: { maxTop: 200 }
                    }})
                  })
                </script>
                <div class="setting-row">
                  <label for="">{$smarty.const.TEXT_BACKGROUND_POSITION}</label>
                  <select name="setting[background_position]" id="" class="form-control">
                    <option value=""{if $setting.background_position == ''} selected{/if}></option>
                    <option value="top left"{if $setting.background_position == 'top left'} selected{/if}>{$smarty.const.TEXT_TOP_LEFT}</option>
                    <option value="top center"{if $setting.background_position == 'top center'} selected{/if}>{$smarty.const.TEXT_TOP_CENTER}</option>
                    <option value="top right"{if $setting.background_position == 'top right'} selected{/if}>{$smarty.const.TEXT_TOP_RIGHT}</option>
                    <option value="left"{if $setting.background_position == 'left'} selected{/if}>{$smarty.const.TEXT_MIDDLE_LEFT}</option>
                    <option value="center"{if $setting.background_position == 'center'} selected{/if}>{$smarty.const.TEXT_MIDDLE_CENTER}</option>
                    <option value="right"{if $setting.background_position == 'right'} selected{/if}>{$smarty.const.TEXT_MIDDLE_RIGHT}</option>
                    <option value="bottom left"{if $setting.background_position == 'bottom left'} selected{/if}>{$smarty.const.TEXT_BOTTOM_LEFT}</option>
                    <option value="bottom center"{if $setting.background_position == 'bottom center'} selected{/if}>{$smarty.const.TEXT_BOTTOM_CENTER}</option>
                    <option value="bottom right"{if $setting.background_position == 'bottom right'} selected{/if}>{$smarty.const.TEXT_BOTTOM_RIGHT}</option>
                  </select>
                </div>
                <div class="setting-row">
                  <label for="">{$smarty.const.TEXT_BACKGROUND_REPEAT}</label>
                  <select name="setting[background_repeat]" id="" class="form-control">
                    <option value=""{if $setting.background_repeat == ''} selected{/if}></option>
                    <option value="no-repeat"{if $setting.background_repeat == 'no-repeat'} selected{/if}>{$smarty.const.TEXT_NO_REPEAT}</option>
                    <option value="repeat"{if $setting.background_repeat == 'repeat'} selected{/if}>{$smarty.const.TEXT_REPEAT}</option>
                    <option value="repeat-x"{if $setting.background_repeat == 'repeat-x'} selected{/if}>{$smarty.const.TEXT_REPEAT_HORIZONTAL}</option>
                    <option value="repeat-y"{if $setting.background_repeat == 'repeat-y'} selected{/if}>{$smarty.const.TEXT_REPEAT_VERTICAL}</option>
                    <option value="space"{if $setting.background_repeat == 'space'} selected{/if}>{$smarty.const.TEXT_REPEAT_ALL_SPACE}</option>
                    <option value="top left"{if $setting.background_repeat == 'top left'} selected{/if}>{$smarty.const.TEXT_REPEAT_ALL_SPACE_RESIZE}</option>
                  </select>
                </div>
                <div class="setting-row">
                  <label for="">{$smarty.const.TEXT_BACKGROUND_SIZE}</label>
                  <select name="setting[background_size]" id="" class="form-control">
                    <option value=""{if $setting.background_size == ''} selected{/if}>{$smarty.const.TEXT_NO_RESIZE}</option>
                    <option value="cover"{if $setting.background_size == 'cover'} selected{/if}>{$smarty.const.TEXT_FIELD_ALL_BLOCK}</option>
                    <option value="contain"{if $setting.background_size == 'contain'} selected{/if}>{$smarty.const.TEXT_WIDTH_HEIGHT_SIZE}</option>
                  </select>
                </div>


              </div>
            </div>
          </div>
          <div class="col-md-6">

            <div class="widget box"{if $designer_mode != 'expert'} style="display: none" {/if}>
              <div class="widget-header">
                <h4>Image preloader for lazy loading in base64</h4>
              </div>
              <div class="widget-content">

                <div class="setting-row">
                  <label for="">{$smarty.const.DEFAULT_IMAGE_FOR_PRODUCT}</label>
                  <textarea name="settings[base64_product]" class="form-control">{$settings.base64_product}</textarea>
                </div>

                <div class="setting-row">
                  <label for="">{$smarty.const.DEFAULT_IMAGE_FOR_CATEGORY}</label>
                  <textarea name="settings[base64_category]" class="form-control">{$settings.base64_category}</textarea>
                </div>


                <div class="setting-row">
                  <label for="">Default image for banner</label>
                  <textarea name="settings[base64_banner]" class="form-control">{$settings.base64_banner}</textarea>
                </div>

              </div>
            </div>

          </div>
        </div>


      </div>

      
      <div class="tab-pane topTabPane tabbable-custom" id="fonts">

          <div class="row">
            <div class="col-md-6">

              <h4>Fonts <span style="font-size: 12px; color: #999; font-style: italic">(Use CSS code)</span></h4>

              <div class="extend-hidden" data-name="font_added" style="display: none">

                <div class="extend-row">
                  <div class="extend-row-remove"></div>
                  <div class="setting-row">
                    <textarea name="" id="" cols="90" rows="5" class="main-input" style="width: calc(100% - 50px)"></textarea>
                  </div>
                </div>

              </div>
              <div class="extend" data-name="font_added"></div>
              <div><span class="btn btn-extend-add" data-name="font_added">Add font</span></div>

            </div>
            <div class="col-md-1"></div>
            <div class="col-md-5">


              <h4>Add pdf font</h4>
              <input type="text" name="add_pdf_font" value="" class="form-control pdf-font-path" placeholder="enter path to a ttf font" />
              <div class="" style="text-align: right; margin-top: 5px"><span class="btn add-pdf-font">Create</span></div>

              <script type="text/javascript">
                $(function(){
                    $('.add-pdf-font').on('click', function(){
                        $('.add-pdf-font').addClass('loader')
                        $.post('design/create-pdf-font', {
                            'font_path': $('.pdf-font-path').val()
                        }, function(response){
                            $('.add-pdf-font').removeClass('loader')
                            if (response && response !== 'false') {
                                alertMessage(`<div style="padding: 20px">Font ${ response} added</div>`)
                            } else {
                                alertMessage(`<div style="padding: 20px">Error</div>`)
                            }
                        })
                    })
                })
              </script>

            </div>
          </div>

      </div>

      <div class="tab-pane topTabPane tabbable-custom" id="sizes">

        <div style="max-width: 800px">
          <div class="extend-hidden" data-name="media_query" style="display: none">

            <div class="extend-row">
              <div class="extend-row-remove"></div>
              <div class="setting-row setting-row-left">
                <label for="">min-width</label>
                <input type="number" name="min" value="" class="min form-control" /><span class="px">px</span>
              </div>
              <div class="setting-row setting-row-right">
                <label for="">max-width</label>
                <input type="number" name="max" value="" class="max form-control" /><span class="px">px</span>
              </div>
              <input type="hidden" name="" class="main-input"/>
            </div>

          </div>
          <div class="extend" data-name="media_query"></div>
          <div style="margin-bottom: 30px"><span class="btn btn-extend-add" data-name="media_query">{$smarty.const.TEXT_ADD_SIZE}</span></div>

          <script type="text/javascript">
            (jQuery)(function($){
              $(function(){
                $('.extend[data-name="media_query"]').on('change_extend', function(){
                  $('.extend-row', this).each(function(){
                    var main = $('.main-input', this).val();
                    var arr = main.split('w');
                    $('.min', this).val(arr[0]);
                    $('.max', this).val(arr[1]);
                  });
                  $('.extend-row input', this).on('change' ,function(){
                    var row = $(this).closest('.extend-row');
                    $('.main-input', row).val($('.min', row).val() + 'w' + $('.max', row).val())
                  })
                });
              })
            })
          </script>

        </div>
      </div>
    </div>
  </div>




  <div class="btn-bar btn-bar-edp-page after">
    <div class="btn-left">
    </div>
    <div class="btn-right">
      <button type="submit" class="btn btn-confirm">{$smarty.const.IMAGE_SAVE}</button>
    </div>
  </div>



</form>
</div>


<script type="text/javascript">
  (jQuery)(function($){
    $(function(){
      var extend = $('.extend');
      extend.each(function(){
        var _this = $(this);
        var name = _this.data('name');
        var hidden = $('.extend-hidden[data-name="' + name + '"]');
        var btn = $('.btn-extend-add[data-name="' + name + '"]');

        var fill_settings = function(d){
          _this.html('');
          $.each(d, function(i, e){
            var row = $('.extend-row', hidden).clone();
            $('.main-input', row)
              .attr('name', 'extend[' + e.setting_name + '][' + e.id + ']')
              .val(e.setting_value);
            $('.extend-row-remove:last', row).on('click', function(){
              $.get('design/extend', { remove: e.id, setting_name: name, theme_name: '{$theme_name}' }, fill_settings, 'json');
            });
            row.appendTo(_this)
          });
          _this.trigger('change_extend');

          $.get('design/redo-buttons', { 'theme_name': '{$theme_name}'}, function(data){
            redo_buttons.html(data)
          });
        };

        $.get('design/extend', { setting_name: name, theme_name: '{$theme_name}' }, fill_settings, 'json');

        btn.off('click').on('click', function(){
          $.get('design/extend', { add: 1, setting_name: name, theme_name: '{$theme_name}' }, fill_settings, 'json');
        })
      });



      var redo_buttons = $('.redo-buttons');
      redo_buttons.on('click', '.btn-undo', function(){
        $(redo_buttons).hide();
        $.get('design/undo', { 'theme_name': '{$theme_name}'}, function(){
          location.href = location.href
        })
      });
      redo_buttons.on('click', '.btn-redo', function(){
        $(redo_buttons).hide();
        $.get('design/redo', { 'theme_name': '{$theme_name}', 'steps_id': $(this).data('id')}, function(){
          location.href = location.href
        })
      });
      $.get('design/redo-buttons', { 'theme_name': '{$theme_name}'}, function(data){
        redo_buttons.html(data)
      });

    })
  })
</script>