<html>
  <head>
    <script type="text/javascript" src="{$app->request->baseUrl}/plugins/ckeditor/ckeditor.js"></script>
  </head>
  <body>
    <div class="popupEditorArea">
    <textarea class="ckeditor" name="editor" id="editor"></textarea>
    </div>
    <script type="text/javascript">
      if (typeof(window.parent.document.forms['{$app->request->get('form')}']) != "undefined") {
        document.getElementById("editor").value = window.parent.document.forms['{$app->request->get('form')}'].elements['{$app->request->get('field')|escape:'quotes'}'].value;
      } else {
        if (window.opener != null) {
          document.getElementById("editor").value=window.opener.document.forms['{$app->request->get('form')}'].elements['{$app->request->get('field')|escape:'quotes'}'].value;
        }
      }
      var ckeditor = CKEDITOR.replace('editor', { resize_dir: 'both', resize_maxWidth: 1200, width: '100%'});
      ckeditor.on( 'change', function( evt ) {
      // getData() returns CKEditor's HTML content.
      var data = evt.editor.getData();
        if (typeof(window.parent.document.forms['{$app->request->get('form')}']) != "undefined") {
          window.parent.document.forms['{$app->request->get('form')}'].elements['{$app->request->get('field')|escape:'quotes'}'].value = data;
        } else {
          if (window.opener != null) {
            window.opener.document.forms['{$app->request->get('form')}'].elements['{$app->request->get('field')|escape:'quotes'}'].value = data;
          } else {
            return false;
          }
        }
      });

    </script>

  </body>
</html>