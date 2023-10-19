{use class="Yii"}


<form action="{$app->request->baseUrl}/design/box-save" method="post" id="box-save">
  <input type="hidden" name="id" value="{$id}"/>
  <div class="popup-heading">
    {$smarty.const.TEXT_TEXT}
  </div>
  <div class="popup-content box-img">


    <div class="tabbable tabbable-custom">
      <ul class="nav nav-tabs">

        <li class="active" data-bs-toggle="tab" data-bs-target="#text"><a>Html</a></li>
        <li data-bs-toggle="tab" data-bs-target="#style"><a>{$smarty.const.HEADING_STYLE}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#align"><a>{$smarty.const.HEADING_WIDGET_ALIGN}</a></li>
        <li data-bs-toggle="tab" data-bs-target="#visibility"><a>{$smarty.const.TEXT_VISIBILITY_ON_PAGES}</a></li>

      </ul>
      <div class="tab-content">

        <div class="tab-pane active" id="text">


          <textarea name="setting[0][text]" id="htm" style="width: 100%" rows="10">{$settings[0].text}</textarea>


          <link rel="stylesheet" href="{$app->request->baseUrl}/plugins/codemirror/lib/codemirror.css">
          <link rel="stylesheet" href="{$app->request->baseUrl}/plugins/codemirror/addon/hint/show-hint.css">
          <script src="{$app->request->baseUrl}/plugins/codemirror/lib/codemirror.js"></script>
          <script src="{$app->request->baseUrl}/plugins/codemirror/addon/hint/show-hint.js"></script>
          <script src="{$app->request->baseUrl}/plugins/codemirror/addon/hint/xml-hint.js"></script>
          <script src="{$app->request->baseUrl}/plugins/codemirror/addon/hint/html-hint.js"></script>
          <script src="{$app->request->baseUrl}/plugins/codemirror/mode/xml/xml.js"></script>
          <script src="{$app->request->baseUrl}/plugins/codemirror/mode/javascript/javascript.js"></script>
          <script src="{$app->request->baseUrl}/plugins/codemirror/mode/css/css.js"></script>
          <script src="{$app->request->baseUrl}/plugins/codemirror/mode/htmlmixed/htmlmixed.js"></script>

          <div id="code"></div>
          <script type="text/javascript">
            var CodeMirrorEditor;
            $(function(){
              CodeMirrorEditor = CodeMirror(document.getElementById("code"), {
                mode: "text/html",
                extraKeys: { "Ctrl-Space": "autocomplete"},
                //lineNumbers: true,
              });

              var htm = $('#htm');
              CodeMirrorEditor.setValue(htm.val());

              CodeMirrorEditor.on('change', function(){
                htm.val(CodeMirrorEditor.getValue()).trigger('change');
              });

              htm.hide()
            })
          </script>

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

  $('#box-save').on('submit', function(){
    $('#htm').val(CodeMirrorEditor.getValue())
  })
</script>