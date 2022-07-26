{use class="Yii"}
<form action="{Yii::getAlias('@web')}/design/box-save" method="post" id="box-save">
  <input type="hidden" name="id" value="{$id}"/>
  <div class="popup-heading">
    {$smarty.const.TEXT_MENU}
  </div>
  <div class="popup-content">




    <div class="tabbable tabbable-custom">
      <ul class="nav nav-tabs">

        <li class="active"><a href="#type" data-toggle="tab">{$smarty.const.HEADING_TYPE}</a></li>
        <li><a href="#product" data-toggle="tab">{$smarty.const.TEXT_PRODUCT_ITEM}</a></li>
        <li><a href="#style" data-toggle="tab">{$smarty.const.HEADING_STYLE}</a></li>
        <li><a href="#align" data-toggle="tab">{$smarty.const.HEADING_WIDGET_ALIGN}</a></li>
        <li><a href="#visibility" data-toggle="tab">{$smarty.const.TEXT_VISIBILITY_ON_PAGES}</a></li>

      </ul>
      <div class="tab-content">
        <div class="tab-pane active menu-list" id="type">




          <div class="setting-row">
            <label for="">{$smarty.const.TEXT_CHOSE_MENU} {$params}</label>
            <select name="params" id="" class="form-control">
              {foreach $menus as $menu}
              <option value="{$menu.menu_name}"{if $menu.menu_name == $params} selected{/if}>{$menu.menu_name}</option>
              {/foreach}
            </select>
          </div>



          <div class="setting-row">
            <label for="">{$smarty.const.IMAGE_MAP_NAME}</label>
            <div class="" style="width: 247px; display: inline-block; vertical-align: top">
              <input type="text" class="form-control map-name" name="map_name" value="{$mapsTitle}" autocomplete="off" style="width: 243px"/>
              <input type="hidden" name="setting[0][maps_id]" value="{$settings[0].maps_id}"/>
              <div class="search-map"></div>
            </div>
            <div class="map-image-holder" style="width: 150px; display: inline-block; vertical-align: top; margin-left: 20px">
              <img src="../images/maps/{$mapsImage}" class="map-image" alt="" {if !$mapsImage} style="display: none" {/if}>
              <div class="map-image-remove" {if !$mapsImage} style="display: none" {/if}></div>
            </div>
          </div>


          <script>
              $(function(){

                  let searchProductBox = $('.search-map');
                  $('.map-name').keyup(function(e){
                      $.get('image-maps/search', {
                          key: $(this).val()
                      }, function(data){
                          $('.suggest').remove();
                          searchProductBox.append('<div class="suggest">'+data+'</div>');

                          $('a', searchProductBox).on('click', function(e){
                              e.preventDefault();

                              $('input[name="setting[0][maps_id]"]').val($(this).data('id')).trigger('change');
                              $('input[name="map_name"]').val($('.td_name', this).text());
                              $('.map-image').show().attr('src', '../images/maps/' + $(this).data('image'));
                              $('.map-image-remove').show();

                              $('.suggest').remove();
                              return false
                          })
                      })
                  });

                  $('.map-image-remove').on('click', function(){
                      $('input[name="setting[0][maps_id]"]').val('').trigger('change');
                      $('input[name="map_name"]').val('');
                      $('.map-image').show().attr('src', '');
                      $(this).hide()
                  })
              })
          </script>

          {include 'include/ajax.tpl'}



        </div>
        <div class="tab-pane" id="product">
            {include 'include/listings-product.tpl'}
        </div>
        <div class="tab-pane" id="style">
          {include 'include/style.tpl'}
        </div>
        <div class="tab-pane" id="align">
          {include 'include/align.tpl'}
        </div>
        <div class="tab-pane" id="visibility">
          {include 'include/visibility.tpl'}
        </div>

      </div>
    </div>


  </div>
  <div class="popup-buttons">
    <button type="submit" class="btn btn-primary btn-save">{$smarty.const.IMAGE_SAVE}</button>
    <span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span>
  </div>
</form>