{use class="Yii"}
<form action="{$app->request->baseUrl}/categories/file-groups-save" method="post" id="box-save">
  <input type="hidden" name="id" value="{$id}"/>
  <div class="popup-heading">
    {$smarty.const.EDIT_FILES_TYPES}
  </div>
  <div class="popup-content box-img doc-gopes-box">

    <div class="buttons" style="margin-bottom: 20px">
      <span class="btn btn-add-doc-type">{$smarty.const.TEXT_ADD_TYPE}</span>
    </div>

    <div class="tabbable tabbable-custom">
        {if count($languages) > 1}
      <div class="nav nav-tabs">

        {foreach $languages as $language}
            <div{if $language.id == $languages_id} class="active"{/if} data-bs-toggle="tab" data-bs-target="#{$item.id}_{$language.id}"><a title="{$language.name}">{$language.image}<span>{$language.name}</span></a></div>
        {/foreach}

      </div>
        {/if}
      <div class="tab-content {if count($languages) < 2}tab-content-no-lang{/if}">
        {foreach $languages as $language}

          <div class="tab-pane{if $language.id == $languages_id} active{/if}" id="{$item.id}_{$language.id}" data-language="{$language.id}">

            {foreach $types_list as $type_id}

              <div class="doc-type-box{if !{$types[$language.id][$type_id].document_types_name}} opened{/if}" data-id="{$type_id}">
                <div class="type-heading">
                  <div class="remove" data-id="{$type_id}"></div>
                  <div class="edit"></div>
                  <div class="img" style="background-image: url('{$app->request->baseUrl}/../{$types[$language.id][$type_id].document_types_icon}')"></div>
                  <div class="title">{$types[$language.id][$type_id].document_types_name}</div>
                </div>
                <div class="type-content" data-id="{$type_id}">

                    <label for="">{$smarty.const.TABLE_HEADING_TITLE}</label>
                    <input type="text"
                            {if !{$types[$language.id][$type_id].document_types_name}}autofocus{/if}
                           name="type[{$language.id}][{$type_id}][document_types_name]"
                           value="{$types[$language.id][$type_id].document_types_name}"
                           class="form-control" />

                  <div><span class="btn-remove-img" data-name="type[{$language.id}][{$type_id}][document_types_icon]">Remove image</span></div>
                  <div class="title">{$smarty.const.UPLOAD_IMAGE}</div>


                  <div class="tabbable tabbable-custom">
                    <div class="nav nav-tabs">
                        <div class="active" data-bs-toggle="tab" data-bs-target="#comp_{$type_id}"><a>{$smarty.const.TEXT_FROM_COMPUTER}</a></div>
                        <div data-bs-toggle="tab" data-bs-target="#gallery_{$type_id}"><a>{$smarty.const.UPLOAD_FROM_GALLERY}</a></div>
                    </div>
                    <div class="tab-content">

                      <div class="tab-pane active" id="comp_{$type_id}">

                        <div class="upload"
                             data-name="type[{$language.id}][{$type_id}][document_types_icon]"
                             data-img="{$types[$language.id][$type_id].document_types_icon}" style="overflow: hidden"></div>

                      </div>

                      <div class="tab-pane" id="gallery_{$type_id}">

                        <div class="setting-image"><img
                                  src="{$app->request->baseUrl}/../{$types[$language.id][$type_id].document_types_icon}"
                                  alt=""
                                  class="show-image"
                                  data-name="type[{$language.id}][{$type_id}][document_types_icon]"></div>
                        <div class="from-gallery"></div>
                        <div style="float: right; margin: 0 0 20px 30px"><span
                                  class="btn btn-upload"
                                  data-name="type[{$language.id}][{$type_id}][document_types_icon]">{$smarty.const.UPLOAD_FROM_GALLERY}</span></div>

                      </div>

                    </div>
                  </div>

                </div>



              </div>


            {/foreach}

          </div>
        {/foreach}
        <script type="text/javascript">
          $('.upload').uploads();
        </script>
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
  var remove_type = function(){
    var _this = $(this);
    $.popUpConfirm('{$smarty.const.GROUP_WILL_BE_REMOVED}', function(){
      var id = _this.data('id');
      $.get('{$app->request->baseUrl}/categories/file-groups-remove', { document_types_id: id}, function(d){
        $('.doc-type-box[data-id="'+id+'"]').remove()
      }, 'json');
    });
  };

  var remove_image = function(){
    $('.show-image[data-name="'+$(this).data('name')+'"]').attr('src', '');
    $('input[name="'+$(this).data('name')+'"]').val('');
  };

  var open = function(){
    let item = $(this).closest('.doc-type-box');
    if (item.hasClass('opened')){
      item.removeClass('opened')
    } else {
      item.addClass('opened')
    }
  };

  $(function(){
    $('.nav-tabs a').on('click', function(){
      $(this).tab('show');
      $(this).closest('.nav-tabs').find('> div').removeClass('active');
      $(this).parent().addClass('active');

      return false;
    });


    $('.btn-upload').galleryImage('{$app->request->baseUrl}');


  });

  $('#box-save').on('submit', function(){

    $.post($(this).attr('action'), $(this).serializeArray(), function(){
      location.reload()
    });

    return false
  });

  $('.btn-add-doc-type').off('click').on('click', function(){
      console.log(1111);
      $.get('{$app->request->baseUrl}/categories/file-groups-add', function(d){
          console.log(222);
          $('.pop-up-content').html(d)
      });

      return false
  });

  $('.doc-type-box .remove').off('click').on('click', remove_type);

  $('.btn-remove-img').off('click').on('click', remove_image);

  $('.doc-type-box .edit').off('click').on('click', open);

</script>