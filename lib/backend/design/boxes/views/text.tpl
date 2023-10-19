{use class="Yii"}
<form action="{$app->request->baseUrl}/design/box-save" method="post" id="box-save">
  <input type="hidden" name="id" value="{$id}"/>
  <div class="popup-heading">
    {$smarty.const.TEXT_TEXT}
  </div>
  <div class="popup-content box-img">


    <div class="tabbable tabbable-custom">
      <ul class="nav nav-tabs">

        <li class="active" data-bs-toggle="tab" data-bs-target="#text"><a>{$smarty.const.TEXT_TEXT}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#style"><a>{$smarty.const.HEADING_STYLE}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#align"><a>{$smarty.const.HEADING_WIDGET_ALIGN}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#visibility"><a>{$smarty.const.TEXT_VISIBILITY_ON_PAGES}</a></li>

      </ul>
      <div class="tab-content">

        <div class="tab-pane active" id="text">

          
          <div class="tabbable tabbable-custom">
            <ul class="nav nav-tabs">

              {foreach $languages as $language}
                <li{if $language.id == $languages_id} class="active"{/if} data-bs-toggle="tab" data-bs-target="#{$item.id}_{$language.id}"><a>{$language.logo} {$language.name}</a></li>
              {/foreach}

            </ul>
            <div class="tab-content">

              {foreach $languages as $language}
                <div class="tab-pane{if $language.id == $languages_id} active{/if}" id="{$item.id}_{$language.id}" data-language="{$language.id}">
                  <textarea name="setting[{$language.id}][text]" style="width: 100%" rows="10" class="ckeditor" id="ckeditor-{$language.id}">{$settings[$language.id].text}</textarea>
                </div>
              {/foreach}

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
  $(function(){

    {foreach $languages as $language}
    var text{$language.id} = $('#ckeditor-{$language.id}');
      CKEDITOR.replace( 'ckeditor-{$language.id}', {
        on: {
          change: function( evt ) {
            for ( instance in CKEDITOR.instances ) {
              CKEDITOR.instances[instance].updateElement();
            }
            text{$language.id}.trigger('change')
          }
        },
        toolbar: [
          { name: 'document', items: [ 'Source'] },
          [ 'Cut', 'Copy', 'Paste', 'Undo', 'Redo' ],
          { name: 'basicstyles', items: [ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript' ] },
          { name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ], items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ] },
          { name: 'styles', items: [ 'Format', 'Font', 'FontSize' ] },
          { name: 'colors', items: [ 'TextColor', 'BGColor' ] },
        ],
      } );
    {/foreach}


  });
  $('#box-save').on('submit', function(){
    if (typeof(CKEDITOR) == 'object'){
      for ( instance in CKEDITOR.instances ) {
        CKEDITOR.instances[instance].updateElement();
      }
    }
  })
</script>