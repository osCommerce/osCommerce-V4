{use class="Yii"}


<form action="{$app->request->baseUrl}/design/box-save" method="post" id="box-save">
  <input type="hidden" name="id" value="{$id}"/>
  <div class="popup-heading">
    Sale
  </div>
  <div class="popup-content box-sale">


    <div class="tabbable tabbable-custom">
      <ul class="nav nav-tabs">

        <li class="active" data-bs-toggle="tab" data-bs-target="#text"><a>Sale</a></li>
        <li data-bs-toggle="tab" data-bs-target="#style"><a>{$smarty.const.HEADING_STYLE}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#align"><a>{$smarty.const.HEADING_WIDGET_ALIGN}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#visibility"><a>{$smarty.const.TEXT_VISIBILITY_ON_PAGES}</a></li>

      </ul>
      <div class="tab-content">

        <div class="tab-pane active" id="text">

          <div class="setting-row">
            <label for="">{$smarty.const.TEXT_PRODUCT}</label>

            <div class="search">
              <input type="text" name="product" value="{$productName}" id="sale-product-name" class="form-control" style="width: 400px" placeholder="{$smarty.const.TEXT_SEARCH}"/>
              <div class="suggest"></div>
            </div>
            <input type="hidden" name="setting[0][products_id]" value="{$settings[0].products_id}"/>
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

        //data = JSON.parse(data);

        $.each(data.data, function(i, product){
          var id = $('<span>' + product[0] + '</span>').find('.product-id').val();
          var productName = $('<span>' + product[2] + '</span>');
          var oldPrice = $('<span>' + product[3] + '</span>');
          var specialPrice = $('<span>' + product[4] + '</span>');

          suggestList += '<div class="item" data-id="' + id + '">' +
                         '  <div class="name">' + productName.text() + '</div>' +
                         '  <div class="old-price">' + oldPrice.text() + '</div>' +
                         '  <div class="special-price">' + specialPrice.text() + '</div>' +
                         '</div>';
        });

        suggest.show().html(suggestList);
      }, 'json')
    });

    suggest.on('click', '.item', function () {
      $('input[name="setting[0][products_id]"]').val($(this).data('id')).trigger('change');
      saleProductName.val($('.name', this).text())
    });

    saleProductName.on('blur', function(){
      setTimeout(function(){
        suggest.hide();
      }, 200)
    });
    saleProductName.on('focus', function(){
      suggest.show();
    });


    $('#box-save').on('submit', function(){
    })
  });

</script>