{use class="Yii"}


<form action="{$app->request->baseUrl}/design/box-save" method="post" id="box-save">
  <input type="hidden" name="id" value="{$id}"/>
  <div class="popup-heading">
    Property
  </div>
  <div class="popup-content box-sale">


    <div class="tabbable tabbable-custom">
      <ul class="nav nav-tabs">

        <li class="active" data-bs-toggle="tab" data-bs-target="#text"><a>Html</a></li>
        <li data-bs-toggle="tab" data-bs-target="#style"><a>{$smarty.const.HEADING_STYLE}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#align"><a>{$smarty.const.HEADING_WIDGET_ALIGN}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#visibility"><a>{$smarty.const.TEXT_VISIBILITY_ON_PAGES}</a></li>

      </ul>
      <div class="tab-content">

        <div class="tab-pane active" id="text">

          <div class="setting-row">
            <label for="">Property</label>

            <div class="search">
              <select name="setting[0][property]" id="properties_box" class="form-control">
                {foreach $properties_tree as $property}
                  <option value="{$property['id']}" {if $settings[0].products_id == 'category'} style="font-weight:bold;" disabled {else} {if $property['id'] == $settings[0].property} selected {/if} {/if} >{$property['text']}</option>
                {/foreach}
              </select>
            </div>
          </div>
          <div class="setting-row">
            <label for="">Count properties</label>
            <div class="search">
                <input type="text" name="setting[0][count_properties]" class="form-control" value="{$settings[0].count_properties}"/>
            </div>
          </div>


          {include 'include/ajax.tpl'}

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
<script type="text/javascript">

  $(function () {
    var saleProductName = $('#sale-product-name');
    var suggest = $('.box-sale .suggest');

    saleProductName.on('keyup', function(){
      var searchValue = $(this).val();
      var suggestList = '';

      $.get('specials/list', { 'search[value]': searchValue}, function(data){

        data = JSON.parse(data);

        $.each(data.data, function(i, product){

          var productName = $(product[0]);
          var oldPrice = $(product[1]);
          var specialPrice = $(product[2]);

          suggestList += '<div class="item" data-id="' + productName.data('id') + '">' +
                         '  <div class="name">' + productName.text() + '</div>' +
                         '  <div class="old-price">' + oldPrice.text() + '</div>' +
                         '  <div class="special-price">' + specialPrice.text() + '</div>' +
                         '</div>';
        });

        suggest.show().html(suggestList);
      })
    });

    suggest.on('click', '.item', function () {
      $('input[name="setting[0][products_id]"]').val($(this).data('id'));
      saleProductName.val($('.name', this).text())
    });

    saleProductName.on('blur', function(){
      setTimeout(function(){
        suggest.hide();
      }, 100)
    });
    saleProductName.on('focus', function(){
      suggest.show();
    });


    $('#box-save').on('submit', function(){
    })
  });

</script>