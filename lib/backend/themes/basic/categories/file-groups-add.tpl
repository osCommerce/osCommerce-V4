{use class="Yii"}
<div class="doc-type-box" data-id="{$type_id}">
  <div class="type-heading">
    <div class="remove" data-id="{$type_id}"></div>
    <div class="edit"></div>
    <div class="img" style="background-image: url('{$app->request->baseUrl}/../images/{$types[$language.id][$type_id].document_types_icon}')"></div>
    <div class="title">{$types[$language.id][$type_id].document_types_name}</div>
  </div>
  <div class="type-content" data-id="{$type_id}">

    <label for="">{$smarty.const.TABLE_HEADING_TITLE}</label>
    <input type="text"
           name="type[{$language_id}][{$type_id}][document_types_name]"
           value=""
           class="form-control" />

    <div><span class="btn-remove-img" data-name="type[{$language.id}][{$type_id}][document_types_icon]">Remove image</span></div>
    <div class="title">{$smarty.const.UPLOAD_IMAGE}</div>


    <div class="tabbable tabbable-custom">
      <div class="nav nav-tabs">
        <div class="active" data-bs-toggle="tab" data-bs-target="#comp"><a>{$smarty.const.TEXT_FROM_COMPUTER}</a></div>
        <div data-bs-toggle="tab" data-bs-target="#gallery"><a>{$smarty.const.UPLOAD_FROM_GALLERY}</a></div>
      </div>
      <div class="tab-content">

        <div class="tab-pane active" id="comp">

          <div class="upload"
               data-name="type[{$language_id}][{$type_id}][document_types_icon]"
               data-img="" style="overflow: hidden"></div>

        </div>

        <div class="tab-pane" id="gallery">

          <div class="setting-image"><img
                    src="{$app->request->baseUrl}/../images/"
                    alt=""
                    class="show-image"
                    data-name="type[{$language_id}][{$type_id}][document_types_icon]"></div>
          <div class="from-gallery"></div>
          <div style="float: right; margin: 0 0 20px 30px"><span
                    class="btn btn-upload"
                    data-name="type[{$language_id}][{$type_id}][document_types_icon]">{$smarty.const.UPLOAD_FROM_GALLERY}</span></div>

        </div>

      </div>
    </div>

  </div>



</div>






