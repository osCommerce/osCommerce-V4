{use class="Yii"}
{use class="backend\assets\DesignAsset"}
{DesignAsset::register($this)|void}
{include 'menu.tpl'}


<div class="page-elements" style="padding-top: 40px">




  {*<div class="widget box">
    <div class="widget-header">
      <h4>Edit styles by selector</h4>
      <div class="toolbar no-padding">
        <div class="btn-group">
          <span class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
        </div>
      </div>
    </div>
    <div class="widget-content">

      <div>Find selector</div>
      <div class="find-selector">
      <div class="suggest-selectors">
        <input id="selector" class="form-control" type="text" name="selector" placeholder="Enter css selector"/>
        <span class="btn add-selector">Add</span>
        <div class="suggest-selectors-content" style="display: none"></div>
      </div>
      </div>

      <script type="text/javascript">
        (function($){
          $(function(){
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
              if ($(this).hasClass('add-selector')) {
                val = $('#selector').val();
              } else {
                val = $(this).text()
              }

              $.get('design/style-edit', {
                data_class: val,
                theme_name: '{$theme_name}'
              }, function(data){
                popup_draggable.html(data);
                $('.popup-content').prepend('<span class="popup-heading-small-text">'+val+'</span>');
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
          })
        })(jQuery)
      </script>

    </div>
  </div>*}

  <div class="widget box widget-closed" id="website-style">
    <div class="widget-header">
      <h4>{$smarty.const.TXT_WEBSITE_STYLE}</h4>
      <div class="toolbar no-padding">
        <div class="btn-group">
          <span class="btn btn-xs widget-collapse"><i class="icon-angle-up"></i></span>
        </div>
      </div>
    </div>
    <div class="widget-content">

      <div class="info-view" data-url="{Yii::getAlias('@web')}/../index/design/?page=body&is_admin=1&theme_name={$theme_name}&language={$language_code}"></div>

    </div>
  </div>


  <div class="widget box box widget-closed" id="typography">
    <div class="widget-header">
      <h4>{$smarty.const.TXT_TYPOGRAPHY}</h4>
      <div class="toolbar no-padding">
        <div class="btn-group">
          <span class="btn btn-xs widget-collapse"><i class="icon-angle-up"></i></span>
        </div>
      </div>
    </div>
    <div class="widget-content">

      <div class="info-view" data-url="{Yii::getAlias('@web')}/../index/design/?page=typography&is_admin=1&theme_name={$theme_name}&language={$language_code}"></div>

    </div>
  </div>


  <div class="widget box box widget-closed" id="buttons">
    <div class="widget-header">
      <h4>{$smarty.const.TEXT_BUTTONS}</h4>
      <div class="toolbar no-padding">
        <div class="btn-group">
          <span class="btn btn-xs widget-collapse"><i class="icon-angle-up"></i></span>
        </div>
      </div>
    </div>
    <div class="widget-content">

      <div class="info-view" data-url="{Yii::getAlias('@web')}/../index/design/?page=buttons&is_admin=1&theme_name={$theme_name}&language={$language_code}"></div>

    </div>
  </div>


  <div class="widget box box widget-closed" id="form-elements">
    <div class="widget-header">
      <h4>{$smarty.const.TXT_FORM_ELEMENTS}</h4>
      <div class="toolbar no-padding">
        <div class="btn-group">
          <span class="btn btn-xs widget-collapse"><i class="icon-angle-up"></i></span>
        </div>
      </div>
    </div>
    <div class="widget-content">

      <div class="info-view" data-url="{Yii::getAlias('@web')}/../index/design/?page=form-elements&is_admin=1&theme_name={$theme_name}&language={$language_code}"></div>

    </div>
  </div>


  <div class="widget box widget-closed" id="main-navigation">
    <div class="widget-header">
      <h4>{$smarty.const.TXT_MAIN_NAVIGATION}</h4>
      <div class="toolbar no-padding">
        <div class="btn-group">
          <span class="btn btn-xs widget-collapse"><i class="icon-angle-up"></i></span>
        </div>
      </div>
    </div>
    <div class="widget-content" style="display: none">

      <div class="tabbable tabbable-custom">
        <ul class="nav nav-tabs">
          <li class="active" data-bs-toggle="tab" data-bs-target="#horizontal"><a>{$smarty.const.TEXT_HORIZONTAL}</a></li>
          <li data-bs-toggle="tab" data-bs-target="#slide-menu"><a>{$smarty.const.TEXT_MENU_SLIDER}</a></li>
          <li data-bs-toggle="tab" data-bs-target="#big-dropdown"><a>{$smarty.const.BIG_DROPDOWN_MENU}</a></li>
          <li data-bs-toggle="tab" data-bs-target="#vertical"><a>{$smarty.const.TEXT_VERTICAL}</a></li>
        </ul>
        <div class="tab-content">
          <div class="tab-pane active" id="horizontal">

            <div class="info-view" data-url="{Yii::getAlias('@web')}/../index/design/?page=menu-horizontal&is_admin=1&theme_name={$theme_name}&language={$language_code}"></div>

          </div>
          <div class="tab-pane" id="slide-menu">

            <div class="info-view" data-url="{Yii::getAlias('@web')}/../index/design/?page=menu-slide-menu&is_admin=1&theme_name={$theme_name}&language={$language_code}"></div>

          </div>
          <div class="tab-pane" id="big-dropdown">

            <div class="info-view" data-url="{Yii::getAlias('@web')}/../index/design/?page=menu-big-dropdow&is_admin=1&theme_name={$theme_name}&language={$language_code}">
            </div>

          </div>
          <div class="tab-pane" id="vertical">

            <div class="info-view" data-url="{Yii::getAlias('@web')}/../index/design/?page=menu-vertical&is_admin=1&theme_name={$theme_name}&language={$language_code}"></div>

          </div>

        </div>
      </div>

    </div>
  </div>


  <div class="widget box widget-closed" id="secondary-navigation">
    <div class="widget-header">
      <h4>{$smarty.const.TXT_SECONDARY_NAVIGATION}</h4>
      <div class="toolbar no-padding">
        <div class="btn-group">
          <span class="btn btn-xs widget-collapse"><i class="icon-angle-up"></i></span>
        </div>
      </div>
    </div>
    <div class="widget-content" style="display: none">

      <div class="tabbable tabbable-custom">
        <ul class="nav nav-tabs">
          <li class="active" data-bs-toggle="tab" data-bs-target="#horizontal2"><a>{$smarty.const.TEXT_HORIZONTAL}</a></li>
          <li data-bs-toggle="tab" data-bs-target="#vertical2"><a>{$smarty.const.TEXT_VERTICAL}</a></li>
        </ul>
        <div class="tab-content">
          <div class="tab-pane active" id="horizontal2">
            <div class="info-view" data-url="{Yii::getAlias('@web')}/../index/design/?page=menu-horizontal2&is_admin=1&theme_name={$theme_name}&language={$language_code}"></div>
          </div>
          <div class="tab-pane" id="vertical2">
            <div class="info-view" data-url="{Yii::getAlias('@web')}/../index/design/?page=menu-vertical2&is_admin=1&theme_name={$theme_name}&language={$language_code}"></div>
          </div>

        </div>
      </div>

    </div>
  </div>


  <div class="widget box widget-closed" id="tabs">
    <div class="widget-header">
      <h4>{$smarty.const.TEXT_TABS}</h4>
      <div class="toolbar no-padding">
        <div class="btn-group">
          <span class="btn btn-xs widget-collapse"><i class="icon-angle-up"></i></span>
        </div>
      </div>
    </div>
    <div class="widget-content" style="display: none">

      <div class="info-view" data-url="{Yii::getAlias('@web')}/../index/design/?page=tabs&is_admin=1&theme_name={$theme_name}&language={$language_code}"></div>

    </div>
  </div>


  {*<div class="widget box widget-closed">
    <div class="widget-header">
      <h4>{$smarty.const.TEXT_PRODUCT_LISTING}</h4>
      <div class="toolbar no-padding">
        <div class="btn-group">
          <span class="btn btn-xs widget-collapse"><i class="icon-angle-up"></i></span>
        </div>
      </div>
    </div>
    <div class="widget-content" style="display: none">

      <div class="tabbable tabbable-custom">
        <ul class="nav nav-tabs">
          <li class="active" data-bs-toggle="tab" data-bs-target="#columns"><a>{$smarty.const.TEXT_COLUMNS}</a></li>
          <li data-bs-toggle="tab" data-bs-target="#rows"><a>{$smarty.const.TEXT_ROWS}</a></li>
          <li data-bs-toggle="tab" data-bs-target="#b2b"><a>{$smarty.const.TEXT_B2B}</a></li>
        </ul>
        <div class="tab-content">
          <div class="tab-pane active" id="columns">
            <div class="tabbable tabbable-custom">
              <ul class="nav nav-tabs">
                <li class="active" data-bs-toggle="tab" data-bs-target="#type_1"><a>{$smarty.const.HEADING_TYPE} 1</a></li>
                <li data-bs-toggle="tab" data-bs-target="#type_1_2"><a>{$smarty.const.HEADING_TYPE} 2</a></li>
              </ul>
              <div class="tab-content">
                <div class="tab-pane active" id="type_1">
                  <div class="info-view" data-url="{Yii::getAlias('@web')}/../index/design/?page=listing_1&is_admin=1&theme_name={$theme_name}&language={$language_code}"></div>
                </div>
                <div class="tab-pane" id="type_1_2">
                  <div class="info-view" data-url="{Yii::getAlias('@web')}/../index/design/?page=listing_2&is_admin=1&theme_name={$theme_name}&language={$language_code}"></div>
                </div>
              </div>
            </div>
          </div>
          <div class="tab-pane" id="rows">
            <div class="tabbable tabbable-custom">
              <ul class="nav nav-tabs">
                <li class="active" data-bs-toggle="tab" data-bs-target="#type_2"><a>{$smarty.const.HEADING_TYPE} 1</a></li>
                <li data-bs-toggle="tab" data-bs-target="#type_2_2"><a>{$smarty.const.HEADING_TYPE} 2</a></li>
              </ul>
              <div class="tab-content">
                <div class="tab-pane active" id="type_2">
                  <div class="info-view" data-url="{Yii::getAlias('@web')}/../index/design/?page=listing_1_2&is_admin=1&theme_name={$theme_name}&language={$language_code}"></div>
                </div>
                <div class="tab-pane" id="type_2_2">
                  <div class="info-view" data-url="{Yii::getAlias('@web')}/../index/design/?page=listing_2_2&is_admin=1&theme_name={$theme_name}&language={$language_code}"></div>
                </div>
              </div>
            </div>
          </div>
          <div class="tab-pane" id="b2b">

                <div class="info-view" data-url="{Yii::getAlias('@web')}/../index/design/?page=listing_1_3&is_admin=1&theme_name={$theme_name}&language={$language_code}"></div>

          </div>
        </div>
      </div>

    </div>
  </div>*}
{*

  <div class="widget box widget-closed">
    <div class="widget-header">
      <h4>{$smarty.const.HOME_PAGE_WIDGETS}</h4>
      <div class="toolbar no-padding">
        <div class="btn-group">
          <span class="btn btn-xs widget-collapse"><i class="icon-angle-up"></i></span>
        </div>
      </div>
    </div>
    <div class="widget-content" style="display: none">


      <div class="tabbable tabbable-custom">
        <ul class="nav nav-tabs">
          <li class="active" data-bs-toggle="tab" data-bs-target="#home_1"><a>{$smarty.const.BEST_SELLERS}</a></li>
          <li data-bs-toggle="tab" data-bs-target="#home_2"><a>{$smarty.const.BOX_CATALOG_REVIEWS}</a></li>
          <li data-bs-toggle="tab" data-bs-target="#home_3"><a>{$smarty.const.TEXT_SHOPPING_CART}</a></li>
          <li data-bs-toggle="tab" data-bs-target="#home_4"><a>{$smarty.const.TEXT_ACCOUNT}</a></li>
          <li data-bs-toggle="tab" data-bs-target="#home_5"><a>{$smarty.const.IMAGE_SEARCH}</a></li>
          <li data-bs-toggle="tab" data-bs-target="#home_6"><a>{$smarty.const.BOX_LOCALIZATION_LANGUAGES}</a></li>
          <li data-bs-toggle="tab" data-bs-target="#home_7"><a>{$smarty.const.TITLE_CURRENCY}</a></li>
        </ul>
        <div class="tab-content">
          <div class="tab-pane active" id="home_1">
            <div class="info-view" data-url="{Yii::getAlias('@web')}/../index/design/?page=best-sellers&is_admin=1&theme_name={$theme_name}&language={$language_code}"></div>
          </div>
          <div class="tab-pane" id="home_2">
            <div class="info-view" data-url="{Yii::getAlias('@web')}/../index/design/?page=reviews&is_admin=1&theme_name={$theme_name}&language={$language_code}"></div>
          </div>
          <div class="tab-pane" id="home_3">
            <div class="info-view" data-url="{Yii::getAlias('@web')}/../index/design/?page=cart&is_admin=1&theme_name={$theme_name}&language={$language_code}"></div>
          </div>
          <div class="tab-pane" id="home_4">
            <div class="info-view" data-url="{Yii::getAlias('@web')}/../index/design/?page=account&is_admin=1&theme_name={$theme_name}&language={$language_code}"></div>
          </div>
          <div class="tab-pane" id="home_5">
            <div class="info-view" data-url="{Yii::getAlias('@web')}/../index/design/?page=search&is_admin=1&theme_name={$theme_name}&language={$language_code}"></div>
          </div>
          <div class="tab-pane" id="home_6">
            <div class="info-view" data-url="{Yii::getAlias('@web')}/../index/design/?page=languages&is_admin=1&theme_name={$theme_name}&language={$language_code}"></div>
          </div>
          <div class="tab-pane" id="home_7">
            <div class="info-view" data-url="{Yii::getAlias('@web')}/../index/design/?page=currency&is_admin=1&theme_name={$theme_name}&language={$language_code}"></div>
          </div>
        </div>
      </div>


    </div>
  </div>


  <div class="widget box widget-closed">
    <div class="widget-header">
      <h4>{$smarty.const.TEXT_CATALOG_WIDGETS}</h4>
      <div class="toolbar no-padding">
        <div class="btn-group">
          <span class="btn btn-xs widget-collapse"><i class="icon-angle-up"></i></span>
        </div>
      </div>
    </div>
    <div class="widget-content" style="display: none">

      <div class="tabbable tabbable-custom">
        <ul class="nav nav-tabs">
          <li class="active" data-bs-toggle="tab" data-bs-target="#catalog_1"><a>{$smarty.const.TEXT_PAGINATION}</a></li>
          <li data-bs-toggle="tab" data-bs-target="#catalog_2"><a>{$smarty.const.TEXT_PAGE_STYLE}</a></li>
          <li data-bs-toggle="tab" data-bs-target="#catalog_3"><a>{$smarty.const.TEXT_SORTING}</a></li>
          <li data-bs-toggle="tab" data-bs-target="#catalog_4"><a>{$smarty.const.TEXT_FILTERS}</a></li>
          <li data-bs-toggle="tab" data-bs-target="#catalog_5"><a>{$smarty.const.TEXT_ATTRIBUTES}</a></li>
          <li data-bs-toggle="tab" data-bs-target="#catalog_6"><a>{$smarty.const.TABLE_HEADING_PRODUCTS_PRICE}</a></li>
          <li data-bs-toggle="tab" data-bs-target="#catalog_7"><a>{$smarty.const.TEXT_QUANTITY_INPUT}</a></li>
        </ul>
        <div class="tab-content">
          <div class="tab-pane active" id="catalog_1">
            <div class="info-view" data-url="{Yii::getAlias('@web')}/../index/design/?page=paging&is_admin=1&theme_name={$theme_name}&language={$language_code}"></div>
          </div>
          <div class="tab-pane" id="catalog_2">
            <div class="info-view" data-url="{Yii::getAlias('@web')}/../index/design/?page=page-style&is_admin=1&theme_name={$theme_name}&language={$language_code}"></div>
          </div>
          <div class="tab-pane" id="catalog_3">
            <div class="info-view" data-url="{Yii::getAlias('@web')}/../index/design/?page=sorting&is_admin=1&theme_name={$theme_name}&language={$language_code}"></div>
          </div>
          <div class="tab-pane" id="catalog_4">
            <div class="info-view" data-url="{Yii::getAlias('@web')}/../index/design/?page=filters&is_admin=1&theme_name={$theme_name}&language={$language_code}"></div>
          </div>
          <div class="tab-pane" id="catalog_5">
            <div class="info-view" data-url="{Yii::getAlias('@web')}/../index/design/?page=attributes&is_admin=1&theme_name={$theme_name}&language={$language_code}"></div>
          </div>
          <div class="tab-pane" id="catalog_6">
            <div class="info-view" data-url="{Yii::getAlias('@web')}/../index/design/?page=price&is_admin=1&theme_name={$theme_name}&language={$language_code}"></div>
          </div>
          <div class="tab-pane" id="catalog_7">
            <div class="info-view" data-url="{Yii::getAlias('@web')}/../index/design/?page=quantity&is_admin=1&theme_name={$theme_name}&language={$language_code}"></div>
          </div>
        </div>
      </div>

    </div>
  </div>


  <div class="widget box widget-closed">
    <div class="widget-header">
      <h4>{$smarty.const.TEXT_SHOPPING_CART}</h4>
      <div class="toolbar no-padding">
        <div class="btn-group">
          <span class="btn btn-xs widget-collapse"><i class="icon-angle-up"></i></span>
        </div>
      </div>
    </div>
    <div class="widget-content" style="display: none">

      <div class="tabbable tabbable-custom">
        <ul class="nav nav-tabs">
          <li class="active" data-bs-toggle="tab" data-bs-target="#cart_1"><a>{$smarty.const.TEXT_LISTING}</a></li>
          <li data-bs-toggle="tab" data-bs-target="#cart_2"><a>{$smarty.const.TEXT_PRICE_BOX}</a></li>
          <li data-bs-toggle="tab" data-bs-target="#cart_3"><a>{$smarty.const.TEXT_EMPTY}</a></li>
          <li data-bs-toggle="tab" data-bs-target="#cart_4"><a>{$smarty.const.GIFT_CERTIFICATE}</a></li>
          <li data-bs-toggle="tab" data-bs-target="#cart_5"><a>{$smarty.const.TEXT_INFO}</a></li>
          <li data-bs-toggle="tab" data-bs-target="#cart_6"><a>Give away</a></li>
        </ul>
        <div class="tab-content">
          <div class="tab-pane active" id="cart_1">
            <div class="info-view" data-url="{Yii::getAlias('@web')}/../index/design/?page=shopping-cart&is_admin=1&theme_name={$theme_name}&language={$language_code}"></div>
          </div>
          <div class="tab-pane" id="cart_2">
            <div class="info-view" data-url="{Yii::getAlias('@web')}/../index/design/?page=cart-price&is_admin=1&theme_name={$theme_name}&language={$language_code}"></div>
          </div>
          <div class="tab-pane" id="cart_3">
            <div class="info-view" data-url="{Yii::getAlias('@web')}/../index/design/?page=empty&is_admin=1&theme_name={$theme_name}&language={$language_code}"></div>
          </div>
          <div class="tab-pane" id="cart_4">
            <div class="info-view" data-url="{Yii::getAlias('@web')}/../index/design/?page=gift-certificate&is_admin=1&theme_name={$theme_name}&language={$language_code}"></div>
          </div>
          <div class="tab-pane" id="cart_5">
            <div class="info-view" data-url="{Yii::getAlias('@web')}/../index/design/?page=info&is_admin=1&theme_name={$theme_name}&language={$language_code}"></div>
          </div>
          <div class="tab-pane" id="cart_6">
            <div class="info-view" data-url="{Yii::getAlias('@web')}/../index/design/?page=give-away&is_admin=1&theme_name={$theme_name}&language={$language_code}"></div>
          </div>
        </div>
      </div>


    </div>
  </div>

*}






</div>
<script type="text/javascript">
  (function($){
    $(function(){
      $('.btn-save-boxes').on('click', function(){
        $.get($(this).data('href'), { 'theme_name': '{$theme_name}'}, function(d){
          alertMessage(d);
          setTimeout(function(){
            $(window).trigger('reload-frame')
          }, 500)
        })
      });

      $('.info-view:visible').addClass('editable').editTheme({
        theme_name: '{$theme_name}'
      });
      $(window).on('change-visible', function(){
        $('.info-view:not(.editable):visible').addClass('editable').editTheme({
          theme_name: '{$theme_name}'
        });
      });


      var redo_buttons = $('.redo-buttons');
      redo_buttons.on('click', '.btn-undo', function(){
        $(redo_buttons).hide();
        $.get('design/undo', { 'theme_name': '{$theme_name}'}, function(){
          $(window).trigger('reload-frame');
          $.get('design/redo-buttons', { 'theme_name': '{$theme_name}'}, function(data){
            redo_buttons.html(data);
            $(redo_buttons).show();
          })
        })
      });
      redo_buttons.on('click', '.btn-redo', function(){
        $(redo_buttons).hide();
        $.get('design/redo', { 'theme_name': '{$theme_name}', 'steps_id': $(this).data('id')}, function(){
          $(window).trigger('reload-frame');
          $.get('design/redo-buttons', { 'theme_name': '{$theme_name}'}, function(data){
            redo_buttons.html(data);
            $(redo_buttons).show();
          })
        })
      });
      $.get('design/redo-buttons', { 'theme_name': '{$theme_name}'}, function(data){
        redo_buttons.html(data)
      });


      $('[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        var target = $(e.target).attr("href"); // activated tab
        var _frame = $(target + ' iframe');
        var frame = _frame.contents();
        _frame.height($('html', frame).height());

        $(window).trigger('change-visible')
      });

      $('.widget-collapse').on('click', function(){
        $(window).trigger('change-visible');
        setTimeout(function(){
          $(window).trigger('change-visible');
        }, 500)
      })

    })
  })(jQuery);
</script>


